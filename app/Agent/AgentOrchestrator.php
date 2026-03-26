<?php

namespace App\Agent;

use App\Agent\DTO\AgentResponse;
use App\Agent\DTO\NormalizedMessage;
use App\Models\Listing;
use App\Models\Sale;
use App\Services\AI\ClaudeService;
use App\Services\AI\MockAgentService;
use Illuminate\Support\Facades\Log;

class AgentOrchestrator
{
    private const MAX_TOOL_ITERATIONS = 5;

    private ClaudeService    $claude;
    private MockAgentService $mock;

    public function __construct(ClaudeService $claude, MockAgentService $mock)
    {
        $this->claude = $claude;
        $this->mock   = $mock;
    }

    /**
     * Process an inbound message and return an AgentResponse.
     *
     * @param  NormalizedMessage $message      The normalised inbound message.
     * @param  array             $history       Prior conversation turns as Claude message objects.
     */
    public function handle(NormalizedMessage $message, array $history = []): AgentResponse
    {
        // Fallback to keyword-based mock when API key is absent
        if (! $this->claude->isConfigured()) {
            Log::info('AgentOrchestrator: Claude not configured, using MockAgentService');
            $result = $this->mock->respond($message->text, $message->imageUrl);
            return new AgentResponse($result['text'], $result['quick_replies'] ?? []);
        }

        try {
            return $this->runClaudeLoop($message, $history);
        } catch (\Throwable $e) {
            Log::error('AgentOrchestrator: Claude failed, falling back to mock', [
                'error' => $e->getMessage(),
            ]);
            $result = $this->mock->respond($message->text, $message->imageUrl);
            return new AgentResponse($result['text'], $result['quick_replies'] ?? []);
        }
    }

    // ── Claude tool_use loop ──────────────────────────────────────────────────

    private function runClaudeLoop(NormalizedMessage $message, array $history): AgentResponse
    {
        $systemPrompt = <<<'PROMPT'
คุณคือ AI ผู้ช่วยด้านอสังหาริมทรัพย์ของ Evante บริษัทขายคอนโดมิเนียมในกรุงเทพฯ
ช่วยลูกค้าค้นหาห้อง สอบถามราคา นัดชมห้อง และให้ข้อมูลโปรโมชั่น
ใช้เครื่องมือ (tools) เพื่อดึงข้อมูลจริงจากระบบ ก่อนตอบทุกครั้งที่เกี่ยวกับห้องหรือการนัดหมาย
ตอบในภาษาเดียวกับที่ลูกค้าใช้ (ไทยหรืออังกฤษ) และกระชับได้ใจความ
PROMPT;

        // Build message array: history + current user message
        $messages   = $history;
        $userContent = [['type' => 'text', 'text' => $message->text]];

        if ($message->imageUrl) {
            // Tell Claude there's an image but we can't pass binary over API here;
            // point to URL if it's publicly accessible.
            $userContent[] = [
                'type' => 'text',
                'text' => "(แนบรูปภาพ: {$message->imageUrl})",
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userContent];

        $tools = $this->toolDefinitions();

        for ($i = 0; $i < self::MAX_TOOL_ITERATIONS; $i++) {
            $response   = $this->claude->chat($messages, $tools, ['system' => $systemPrompt]);
            $stopReason = $response['stop_reason'] ?? 'end_turn';
            $content    = $response['content'] ?? [];

            // Append assistant turn to history
            $messages[] = ['role' => 'assistant', 'content' => $content];

            if ($stopReason === 'end_turn') {
                return new AgentResponse(trim($this->claude->extractText($response)));
            }

            if ($stopReason === 'tool_use') {
                $toolUses    = $this->claude->extractToolUses($response);
                $resultTurns = [];

                foreach ($toolUses as $use) {
                    Log::info("AgentOrchestrator: executing tool [{$use['name']}]", $use['input']);
                    $result       = $this->executeTool($use['name'], $use['input']);
                    $resultTurns  = array_merge(
                        $resultTurns,
                        $this->claude->buildToolResultMessage($use['id'], $result)['content']
                    );
                }

                // Append all tool results as a single user turn and continue loop
                $messages[] = ['role' => 'user', 'content' => $resultTurns];
                continue;
            }

            // max_tokens or other stop — return whatever text we have
            break;
        }

        // Fallback: extract text from last assistant message
        $lastMsg = collect($messages)->last(fn ($m) => ($m['role'] ?? '') === 'assistant');
        $text    = '';
        foreach ((array) ($lastMsg['content'] ?? []) as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'];
            }
        }

        return new AgentResponse(trim($text) ?: 'ขออภัยครับ ไม่สามารถประมวลผลได้ขณะนี้');
    }

    // ── Tool definitions (Claude API format) ──────────────────────────────────

    private function toolDefinitions(): array
    {
        return [
            [
                'name'        => 'get_projects',
                'description' => 'ดึงรายการโครงการอสังหาริมทรัพย์ทั้งหมดของ Evante',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => new \stdClass(), // no required inputs
                ],
            ],
            [
                'name'        => 'search_rooms',
                'description' => 'ค้นหาห้องว่างตามเงื่อนไขที่กำหนด เช่น จำนวนห้องนอน ราคา ชั้น หรือโครงการ',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'project_id' => ['type' => 'integer', 'description' => 'ID โครงการ (ถ้าต้องการเจาะจง)'],
                        'bedrooms'   => ['type' => 'integer', 'description' => 'จำนวนห้องนอน'],
                        'min_price'  => ['type' => 'number',  'description' => 'ราคาขั้นต่ำ (บาท)'],
                        'max_price'  => ['type' => 'number',  'description' => 'ราคาสูงสุด (บาท)'],
                        'floor'      => ['type' => 'integer', 'description' => 'ชั้น'],
                        'unit_type'  => ['type' => 'string',  'description' => 'ประเภทยูนิต เช่น studio, 1br, 2br'],
                    ],
                ],
            ],
            [
                'name'        => 'get_room_detail',
                'description' => 'ดูรายละเอียดห้องเฉพาะเจาะจง รวมถึงราคา สถานะ และการนัดหมาย',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'unit_code' => ['type' => 'string', 'description' => 'รหัสห้อง เช่น A-1205'],
                    ],
                    'required' => ['unit_code'],
                ],
            ],
            [
                'name'        => 'book_appointment',
                'description' => 'จองนัดชมห้องสำหรับลูกค้า ต้องการข้อมูลครบถ้วน',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'unit_code'        => ['type' => 'string', 'description' => 'รหัสห้องที่ต้องการชม'],
                        'appointment_date' => ['type' => 'string', 'description' => 'วันที่นัด (YYYY-MM-DD)'],
                        'appointment_time' => ['type' => 'string', 'description' => 'เวลานัด (HH:MM)'],
                        'visitor_name'     => ['type' => 'string', 'description' => 'ชื่อผู้เข้าชม'],
                        'visitor_phone'    => ['type' => 'string', 'description' => 'เบอร์โทรผู้เข้าชม'],
                    ],
                    'required' => ['unit_code', 'appointment_date', 'appointment_time', 'visitor_name', 'visitor_phone'],
                ],
            ],
            [
                'name'        => 'cancel_appointment',
                'description' => 'ยกเลิกนัดชมห้องที่มีอยู่',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'unit_code' => ['type' => 'string', 'description' => 'รหัสห้องที่ต้องการยกเลิกนัด'],
                    ],
                    'required' => ['unit_code'],
                ],
            ],
        ];
    }

    // ── Tool execution ─────────────────────────────────────────────────────────

    private function executeTool(string $name, array $input): array
    {
        try {
            return match ($name) {
                'get_projects'      => $this->toolGetProjects(),
                'search_rooms'      => $this->toolSearchRooms($input),
                'get_room_detail'   => $this->toolGetRoomDetail($input['unit_code']),
                'book_appointment'  => $this->toolBookAppointment($input),
                'cancel_appointment'=> $this->toolCancelAppointment($input['unit_code']),
                default             => ['error' => "Unknown tool: {$name}"],
            };
        } catch (\Throwable $e) {
            Log::error("AgentOrchestrator tool [{$name}] error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    private function toolGetProjects(): array
    {
        $projects = \App\Models\Project::with('location')->orderBy('name')->get()
            ->map(fn ($p) => [
                'project_id'   => $p->id,
                'name'         => $p->name,
                'location'     => $p->location->name ?? null,
                'total_floors' => $p->total_floors,
                'total_units'  => $p->total_units,
            ])->values()->all();

        return ['projects' => $projects, 'total' => count($projects)];
    }

    private function toolSearchRooms(array $input): array
    {
        $query = Listing::with('project')
            ->whereHas('sales', fn ($q) => $q->where('status', 'available'))
            ->whereDoesntHave('sales', fn ($q) => $q->whereNotIn('status', ['available', 'transferred']));

        if (!empty($input['project_id'])) {
            $query->where('project_id', $input['project_id']);
        }
        if (!empty($input['bedrooms'])) {
            $query->where('bedrooms', $input['bedrooms']);
        }
        if (!empty($input['min_price'])) {
            $query->where('price_per_room', '>=', $input['min_price']);
        }
        if (!empty($input['max_price'])) {
            $query->where('price_per_room', '<=', $input['max_price']);
        }
        if (!empty($input['floor'])) {
            $query->where('floor', $input['floor']);
        }
        if (!empty($input['unit_type'])) {
            $query->where('unit_type', $input['unit_type']);
        }

        $rooms = $query->orderBy('floor')->orderBy('room_number')->limit(10)->get()
            ->map(fn ($l) => [
                'unit_code' => $l->unit_code,
                'project'   => $l->project->name ?? null,
                'floor'     => $l->floor,
                'room'      => $l->room_number,
                'type'      => $l->unit_type,
                'area'      => $l->area ? (float) $l->area : null,
                'price'     => $l->price_per_room ? (float) $l->price_per_room : null,
                'bedrooms'  => $l->bedrooms,
            ])->values()->all();

        return ['rooms' => $rooms, 'total' => count($rooms)];
    }

    private function toolGetRoomDetail(string $unitCode): array
    {
        $listing = Listing::with('project')->where('unit_code', $unitCode)->first();

        if (! $listing) {
            return ['error' => "ไม่พบห้อง '{$unitCode}'"];
        }

        $sale = Sale::where('listing_id', $listing->id)->latest()->first();

        $appointment = null;
        if ($sale?->status === 'appointment') {
            $appointment = [
                'date'  => $sale->appointment_date?->format('Y-m-d'),
                'time'  => $sale->appointment_time ? substr($sale->appointment_time, 0, 5) : null,
                'name'  => $sale->appointment_name,
                'phone' => $sale->appointment_phone,
            ];
        }

        return [
            'unit_code'   => $listing->unit_code,
            'project'     => $listing->project->name ?? null,
            'floor'       => $listing->floor,
            'room'        => $listing->room_number,
            'type'        => $listing->unit_type,
            'area'        => $listing->area ? (float) $listing->area : null,
            'price'       => $listing->price_per_room ? (float) $listing->price_per_room : null,
            'bedrooms'    => $listing->bedrooms,
            'status'      => $sale?->status ?? 'no_sale_record',
            'appointment' => $appointment,
            'financial'   => [
                'reservation_deposit' => $listing->reservation_deposit ? (float) $listing->reservation_deposit : null,
                'annual_common_fee'   => $listing->annual_common_fee ? (float) $listing->annual_common_fee : null,
                'sinking_fund'        => $listing->sinking_fund ? (float) $listing->sinking_fund : null,
            ],
        ];
    }

    private function toolBookAppointment(array $input): array
    {
        $listing = Listing::where('unit_code', $input['unit_code'])->first();

        if (! $listing) {
            return ['error' => "ไม่พบห้อง '{$input['unit_code']}'"];
        }

        $sale = Sale::where('listing_id', $listing->id)->where('status', 'available')->first();

        if (! $sale) {
            return ['error' => 'ห้องนี้ไม่พร้อมให้จองขณะนี้'];
        }

        $previousStatus = $sale->status;

        $sale->update([
            'status'            => 'appointment',
            'previous_status'   => $previousStatus,
            'appointment_date'  => $input['appointment_date'],
            'appointment_time'  => $input['appointment_time'] . ':00',
            'appointment_name'  => $input['visitor_name'],
            'appointment_phone' => $input['visitor_phone'],
        ]);

        $sale->statusHistories()->create([
            'status'          => 'appointment',
            'previous_status' => $previousStatus,
            'notes'           => 'Booked via AI agent by ' . $input['visitor_name'],
            'user_id'         => null,
        ]);

        return [
            'success'          => true,
            'message'          => 'จองนัดหมายเรียบร้อยแล้ว',
            'unit_code'        => $input['unit_code'],
            'appointment_date' => $input['appointment_date'],
            'appointment_time' => $input['appointment_time'],
            'visitor_name'     => $input['visitor_name'],
            'visitor_phone'    => $input['visitor_phone'],
        ];
    }

    private function toolCancelAppointment(string $unitCode): array
    {
        $listing = Listing::where('unit_code', $unitCode)->first();

        if (! $listing) {
            return ['error' => "ไม่พบห้อง '{$unitCode}'"];
        }

        $sale = Sale::where('listing_id', $listing->id)->where('status', 'appointment')->first();

        if (! $sale) {
            return ['error' => 'ไม่พบนัดหมายที่ active สำหรับห้องนี้'];
        }

        $sale->update([
            'status'            => 'available',
            'previous_status'   => 'appointment',
            'appointment_date'  => null,
            'appointment_time'  => null,
            'appointment_name'  => null,
            'appointment_phone' => null,
        ]);

        $sale->statusHistories()->create([
            'status'          => 'available',
            'previous_status' => 'appointment',
            'notes'           => 'Appointment cancelled via AI agent',
            'user_id'         => null,
        ]);

        return ['success' => true, 'message' => 'ยกเลิกนัดหมายเรียบร้อยแล้ว', 'unit_code' => $unitCode];
    }
}
