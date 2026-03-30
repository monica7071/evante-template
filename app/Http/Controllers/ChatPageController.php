<?php

namespace App\Http\Controllers;

use App\Events\NewChatMessage;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\AI\ClaudeService;
use App\Services\AI\MockAgentService;
use App\Services\AI\Tools\AppointmentBookTool;
use App\Services\AI\Tools\FacilitiesTool;
use App\Services\AI\Tools\FinancialCalculatorTool;
use App\Services\AI\Tools\KnowledgeBaseTool;
use App\Services\AI\Tools\ProjectInfoTool;
use App\Services\AI\Tools\PropertySearchTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatPageController extends Controller
{
    private MockAgentService $mock;
    private ClaudeService    $claude;

    /** @var array<string, \App\Services\AI\Tools\ToolInterface> */
    private array $tools;

    public function __construct(MockAgentService $mock, ClaudeService $claude)
    {
        $this->mock   = $mock;
        $this->claude = $claude;
        $this->tools  = $this->buildTools();
    }

    public function index()
    {
        return view('chat.index');
    }

    public function send(Request $request)
    {
        $request->validate([
            'message'    => 'nullable|string|max:2000',
            'session_id' => 'nullable|string|max:64',
            'image_url'  => 'nullable|string|max:500',
        ]);

        $message   = trim($request->input('message', ''));
        $imageUrl  = $request->input('image_url');
        $sessionToken = $request->input('session_id');

        if ($message === '' && ! $imageUrl) {
            return response()->json(['error' => 'Message is required'], 422);
        }

        // Find or create the chat session in DB
        $session = null;
        if ($sessionToken) {
            $session = ChatSession::firstOrCreate(
                ['session_token' => $sessionToken],
                [
                    'channel'      => 'web',
                    'status'       => 'active',
                    'handled_by'   => 'ai',
                    'organization_id' => (int) config('ai.default_organization_id', 1),
                ]
            );
        }

        // If admin is handling this session, reject AI response and just save user message
        if ($session && $session->handled_by === 'admin') {
            $userMsg = ChatMessage::create([
                'session_id'  => $session->id,
                'sender_type' => 'user',
                'content'     => $message,
                'metadata'    => $imageUrl ? ['image_url' => $imageUrl] : null,
            ]);
            $session->update(['last_message_at' => now()]);
            try { broadcast(new NewChatMessage($userMsg)); } catch (\Throwable $e) {
                Log::warning('ChatPageController broadcast failed (admin mode)', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'message'        => null,
                'handled_by'     => 'admin',
                'db_session_id'  => $session->id,
                'quick_replies'  => [],
                'timestamp'      => now()->toISOString(),
                'source'         => 'admin_mode',
            ]);
        }

        // Save user message to DB
        if ($session) {
            $userMsg = ChatMessage::create([
                'session_id'  => $session->id,
                'sender_type' => 'user',
                'content'     => $message,
                'metadata'    => $imageUrl ? ['image_url' => $imageUrl] : null,
            ]);
            $session->update(['last_message_at' => now()]);
            try { broadcast(new NewChatMessage($userMsg)); } catch (\Throwable $e) {
                Log::warning('ChatPageController broadcast failed (user msg)', ['error' => $e->getMessage()]);
            }
        }

        // Build history from DB (last 20 turns)
        $history = [];
        if ($session) {
            $dbMessages = $session->messages()
                ->orderByDesc('created_at')
                ->limit(40)
                ->get()
                ->reverse()
                ->values();

            foreach ($dbMessages as $dbMsg) {
                if ($dbMsg->sender_type === 'user') {
                    $history[] = ['role' => 'user', 'content' => $dbMsg->content];
                } elseif ($dbMsg->sender_type === 'ai') {
                    $history[] = ['role' => 'assistant', 'content' => $dbMsg->content];
                }
            }
        }

        if ($this->claude->isConfigured()) {
            return $this->handleWithClaude($message, $imageUrl, $history, $session);
        }

        $result = $this->mock->respond($message, $imageUrl);

        if ($session) {
            $aiMsg = ChatMessage::create([
                'session_id'  => $session->id,
                'sender_type' => 'ai',
                'content'     => $result['text'],
                'metadata'    => ['quick_replies' => $result['quick_replies'] ?? []],
            ]);
            $session->update(['last_message_at' => now()]);
            try { broadcast(new NewChatMessage($aiMsg)); } catch (\Throwable $e) {
                Log::warning('ChatPageController broadcast failed (mock ai)', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'message'        => $result['text'],
            'quick_replies'  => $result['quick_replies'] ?? [],
            'db_session_id'  => $session?->id,
            'timestamp'      => now()->toISOString(),
            'source'         => 'mock',
        ]);
    }

    private function handleWithClaude(
        string $message,
        ?string $imageUrl,
        array $history,
        ?ChatSession $session
    ): \Illuminate\Http\JsonResponse {
        // Build message list from DB history (already formatted)
        $messages = $history;

        // Replace last user message with current one (may include image)
        // Remove the last user message we already saved — we'll re-add with image if needed
        if (! empty($messages) && end($messages)['role'] === 'user') {
            array_pop($messages);
        }

        if ($imageUrl) {
            $content = [
                ['type' => 'image', 'source' => ['type' => 'url', 'url' => $imageUrl]],
            ];
            if ($message !== '') {
                $content[] = ['type' => 'text', 'text' => $message];
            }
            $messages[] = ['role' => 'user', 'content' => $content];
        } else {
            $messages[] = ['role' => 'user', 'content' => $message];
        }

        $toolDefs       = array_values(array_map(fn ($t) => $t->toDefinition(), $this->tools));
        $organizationId = (int) config('ai.default_organization_id', 1);

        try {
            $replyText = $this->runToolLoop($messages, $toolDefs, $organizationId);

            if ($session) {
                $aiMsg = ChatMessage::create([
                    'session_id'  => $session->id,
                    'sender_type' => 'ai',
                    'content'     => $replyText,
                ]);
                $session->update(['last_message_at' => now()]);
                try { broadcast(new NewChatMessage($aiMsg)); } catch (\Throwable $e) {
                    Log::warning('ChatPageController broadcast failed (claude ai)', ['error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'message'        => $replyText,
                'quick_replies'  => [],
                'db_session_id'  => $session?->id,
                'timestamp'      => now()->toISOString(),
                'source'         => 'claude',
            ]);
        } catch (\RuntimeException $e) {
            Log::warning('Claude API failed, falling back to mock: ' . $e->getMessage());

            $result = $this->mock->respond($message, $imageUrl);

            if ($session) {
                $aiMsg = ChatMessage::create([
                    'session_id'  => $session->id,
                    'sender_type' => 'ai',
                    'content'     => $result['text'],
                    'metadata'    => ['quick_replies' => $result['quick_replies'] ?? []],
                ]);
                $session->update(['last_message_at' => now()]);
                try { broadcast(new NewChatMessage($aiMsg)); } catch (\Throwable $e) {
                    Log::warning('ChatPageController broadcast failed (mock fallback)', ['error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'message'        => $result['text'],
                'quick_replies'  => $result['quick_replies'] ?? [],
                'db_session_id'  => $session?->id,
                'timestamp'      => now()->toISOString(),
                'source'         => 'mock_fallback',
            ]);
        }
    }

    /**
     * Build the dynamic system prompt including live promotions, facilities, and appointment info.
     */
    private function buildSystemPrompt(int $organizationId): string
    {
        // Load active promotions
        $promotions = \Illuminate\Support\Facades\DB::table('promotions')
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })
            ->get(['title', 'description', 'discount_type', 'discount_value', 'end_date', 'conditions']);

        $promoLines = '';
        if ($promotions->isNotEmpty()) {
            $promoLines = "\n\n## โปรโมชั่นที่มีอยู่ตอนนี้\n";
            foreach ($promotions as $p) {
                $until = $p->end_date ? " (ถึง {$p->end_date})" : '';
                $promoLines .= "- **{$p->title}**{$until}: {$p->description}\n";
                if ($p->conditions) {
                    $promoLines .= "  เงื่อนไข: {$p->conditions}\n";
                }
            }
        }

        // Load available slot summary (next 7 days)
        $appointmentInfo = "\n\n## การนัดชมห้อง\n"
            . "รับนัดชมทุกวัน เวลา 09:00–17:00 น. ไม่เว้นวันหยุด\n"
            . "เมื่อลูกค้าต้องการนัดชม ให้ถามชื่อ เบอร์โทร วันที่ และเวลาที่สะดวก\n"
            . "จากนั้นใช้ tool appointment_book เพื่อบันทึกนัดหมาย";

        return <<<SYSTEM
คุณชื่อ "เอวอง" เป็นที่ปรึกษาด้านอสังหาริมทรัพย์ของโครงการ Evante เป็นผู้หญิง ตอบเป็นภาษาไทยเสมอ

## บุคลิกภาพและวิธีพูด
- พูดเป็นกันเอง ใช้ภาษาธรรมชาติ เหมือนเซลล์คอนโดที่เป็นคนจริงๆ
- ใช้ "คะ" สำหรับคำถาม และ "ค่ะ" สำหรับบอกเล่า ท้ายประโยค (เป็นผู้หญิง)
- ไม่ใช้ emoji มากเกินไป ใช้แค่ 1-2 ตัวต่อข้อความถ้าเหมาะสม
- ไม่ตอบเป็น bullet point ยาวๆ ตอบเป็นประโยคธรรมชาติ พูดเหมือนคุยกันจริงๆ
- ตอบสั้นกระชับ 2-4 ประโยค ไม่เยิ่นเย้อ ไม่ต้องอธิบายทุกอย่าง
- ถ้าไม่รู้ให้บอกตรงๆ ว่า "ขอเช็คให้อีกทีนะคะ"
- ถามกลับเพื่อเข้าใจความต้องการ เช่น "สนใจห้องแบบไหนคะ?" "งบประมาณประมาณเท่าไหร่คะ?"
- ลูกค้าอาจจะไม่ได้ระบุห้อง สามารถนัดชมโครงการโดยไม่ต้องเลือกห้องก็ได้

## สิ่งที่ห้ามทำเด็ดขาด
- ห้ามบอกว่าตัวเองเป็น AI หรือ chatbot
- ห้ามใช้คำว่า "ในฐานะ AI" หรือ "เป็น AI" หรือ "ในฐานะผู้ช่วย AI"
- ถ้าถูกถามว่าเป็น AI ให้ตอบว่า "เอวองเป็นที่ปรึกษาของโครงการค่ะ ยินดีช่วยเหลือเลยค่ะ"
- ห้ามแสดงข้อมูลเป็นตารางหรือ list ยาวๆ ให้เล่าเป็นประโยคธรรมชาติแทน
- ห้ามขึ้นต้นด้วย "สวัสดีค่ะ!" ทุกข้อความ ใช้แค่ครั้งแรกที่ทักทาย

## วิธีใช้ข้อมูลจากระบบ
- ถ้าลูกค้าถามเรื่องห้อง ราคา หรือยูนิตที่ว่าง ให้ใช้ tool search_rooms ดึงข้อมูลจริงก่อนตอบเสมอ
- ถ้าห้องมีรูป floor_plan_image หรือ room_layout_image ให้แสดงรูปด้วย markdown: ![ชื่อห้อง](url)
- ถ้าลูกค้าถามเรื่อง facilities ให้ใช้ tool get_facilities แล้วแสดงรูปด้วย markdown: ![ชื่อ](url)
- ถ้าลูกค้าถามเรื่องโปรโมชั่น FAQ หรือเงื่อนไขซื้อ ให้ใช้ tool knowledge_base ก่อนตอบ
- ถ้าลูกค้าอยากคำนวณผ่อน ให้ใช้ tool calculate_loan
- ถ้าลูกค้าอยากนัดชม ให้ถามชื่อ เบอร์โทร วันที่ เวลาที่สะดวก แล้วใช้ tool book_appointment (ไม่จำเป็นต้องระบุห้อง)

## ตัวอย่างการตอบที่ดี
ลูกค้า: "มีห้อง 2 นอนว่างมั้ย"
เอวอง: "มีค่ะ ตอนนี้ห้อง 2 นอนว่างอยู่หลายห้องเลย ห้องหนึ่งชั้น 15 วิวสวยมาก อีกห้องชั้น 22 มุมห้องดีเลย สนใจดูห้องไหนก่อนคะ?"

ลูกค้า: "ราคาเท่าไหร่"
เอวอง: "ห้อง 2 นอนอยู่ช่วง 5-7 ล้านค่ะ ขึ้นอยู่กับชั้นและวิวนะคะ งบประมาณคร่าวๆ คุณลูกค้ามีในใจบ้างมั้ยคะ?"

ลูกค้า: "อยากมาดูโครงการ"
เอวอง: "ยินดีเลยค่ะ สะดวกวันไหน เวลาไหนดีคะ? ขอชื่อกับเบอร์โทรด้วยนะคะ เดี๋ยวเอวองจัดให้เลยค่ะ"
SYSTEM
            . $promoLines
            . $appointmentInfo;
    }

    /**
     * Run the Claude tool-use loop until end_turn or max iterations.
     */
    private function runToolLoop(array $messages, array $toolDefs, int $organizationId): string
    {
        $maxIterations = 8;
        $systemPrompt  = $this->buildSystemPrompt($organizationId);

        for ($i = 0; $i < $maxIterations; $i++) {
            $response   = $this->claude->chat($messages, $toolDefs, ['system' => $systemPrompt, 'max_tokens' => 4096]);
            $stopReason = $response['stop_reason'] ?? 'end_turn';

            $messages[] = [
                'role'    => 'assistant',
                'content' => $response['content'] ?? [],
            ];

            if ($stopReason === 'end_turn') {
                return $this->claude->extractText($response);
            }

            if ($stopReason === 'tool_use') {
                $toolUses = $this->claude->extractToolUses($response);

                if (empty($toolUses)) {
                    return $this->claude->extractText($response);
                }

                $toolResultBlocks = [];
                foreach ($toolUses as $toolUse) {
                    $result    = $this->executeTool($toolUse['name'], $toolUse['input'], $organizationId);
                    $isError   = ($result['status'] ?? '') === 'error';
                    $resultMsg = $this->claude->buildToolResultMessage($toolUse['id'], $result, $isError);

                    $toolResultBlocks[] = $resultMsg['content'][0];

                    Log::debug('ChatAgent tool executed', [
                        'tool'   => $toolUse['name'],
                        'input'  => $toolUse['input'],
                        'status' => $result['status'] ?? 'unknown',
                    ]);
                }

                $messages[] = ['role' => 'user', 'content' => $toolResultBlocks];
                continue;
            }

            return $this->claude->extractText($response);
        }

        Log::warning('ChatAgent: reached max tool iterations');
        return 'ขออภัย ไม่สามารถประมวลผลคำขอได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง';
    }

    private function executeTool(string $name, array $input, int $organizationId): array
    {
        if (! isset($this->tools[$name])) {
            return [
                'status'  => 'error',
                'code'    => 'unknown_tool',
                'message' => "Tool '{$name}' is not registered.",
            ];
        }

        try {
            return $this->tools[$name]->execute($input, $organizationId);
        } catch (\Throwable $e) {
            Log::error("ChatAgent: tool '{$name}' threw an exception", [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);

            return [
                'status'  => 'error',
                'code'    => 'tool_exception',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, \App\Services\AI\Tools\ToolInterface>
     */
    private function buildTools(): array
    {
        $list = [
            new PropertySearchTool(),
            new ProjectInfoTool(),
            new FinancialCalculatorTool(),
            new AppointmentBookTool(),
            new KnowledgeBaseTool(),
            new FacilitiesTool(),
        ];

        $indexed = [];
        foreach ($list as $tool) {
            $indexed[$tool->name()] = $tool;
        }

        return $indexed;
    }

    // ------------------------------------------------------------------
    // Session / upload endpoints
    // ------------------------------------------------------------------

    public function sessions()
    {
        return response()->json([
            'sessions' => [
                [
                    'id'         => 'demo-1',
                    'title'      => 'สอบถามห้องชุด',
                    'preview'    => 'มีห้องว่างแบบ 1 ห้องนอนไหม?',
                    'updated_at' => now()->subHours(2)->toISOString(),
                ],
                [
                    'id'         => 'demo-2',
                    'title'      => 'คำนวณสินเชื่อ',
                    'preview'    => 'ผ่อนเดือนละเท่าไหร่ถ้าราคา 3.5 ล้าน',
                    'updated_at' => now()->subDays(1)->toISOString(),
                ],
                [
                    'id'         => 'demo-3',
                    'title'      => 'โปรโมชั่นเดือนมีนา',
                    'preview'    => 'มีโปรโมชั่นพิเศษอะไรบ้าง?',
                    'updated_at' => now()->subDays(3)->toISOString(),
                ],
            ],
        ]);
    }

    public function messages(string $sessionId)
    {
        $session = ChatSession::where('id', $sessionId)
            ->orWhere('session_token', $sessionId)
            ->first();

        if (!$session) {
            return response()->json(['messages' => []]);
        }

        $since = request()->query('since');
        $query = $session->messages()->orderBy('created_at');

        if ($since) {
            try {
                $sinceCarbon = \Illuminate\Support\Carbon::parse($since)
                    ->setTimezone(config('app.timezone', 'Asia/Bangkok'));
                $query->where('created_at', '>', $sinceCarbon->toDateTimeString());
            } catch (\Throwable $e) {
                // Invalid date — ignore filter
            }
        }

        $messages = $query->limit(50)->get()->map(fn ($m) => [
            'id'          => $m->id,
            'sender_type' => $m->sender_type,
            'content'     => $m->content,
            'metadata'    => $m->metadata,
            'timestamp'   => $m->created_at->toISOString(),
        ]);

        return response()->json(['messages' => $messages]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('chat-uploads', 'public');

        return response()->json([
            'url'  => asset('storage/' . $path),
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}
