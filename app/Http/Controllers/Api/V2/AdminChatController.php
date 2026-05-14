<?php

namespace App\Http\Controllers\Api\V2;

use App\Events\NewChatMessage;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminChatController extends Controller
{
    // ------------------------------------------------------------------
    // POST /api/v2/chat/messages — admin sends a message back to customer
    // ------------------------------------------------------------------
    public function sendMessage(Request $request)
    {
        $request->validate([
            'session_id'    => 'nullable|integer|exists:chat_sessions,id',
            'session_token' => 'nullable|string|max:255',
            'lineUuid'      => 'nullable|string|max:255',
            'message'       => 'nullable|string|max:5000',
            'aiResponse'    => 'nullable|string|max:5000',
            'sender_type'   => 'nullable|string|in:admin,ai',
            'sender_name'   => 'nullable|string|max:255',
        ]);

        // Resolve message content: accept 'message' or 'aiResponse' alias
        $messageContent = $request->input('message') ?: $request->input('aiResponse');
        if (!$messageContent) {
            return response()->json(['error' => 'message or aiResponse is required'], 422);
        }

        // Resolve session: accept numeric session_id or string session_token/lineUuid
        if ($request->filled('session_id')) {
            $session = ChatSession::findOrFail($request->session_id);
        } else {
            $token = $request->input('session_token') ?? $request->input('lineUuid');
            if (!$token) {
                return response()->json(['error' => 'session_id or session_token/lineUuid is required'], 422);
            }
            $session = ChatSession::where('session_token', $token)->first();
            if (!$session) {
                return response()->json(['error' => 'Session not found for token: ' . $token], 404);
            }
        }

        // Auto-takeover if not already handled by admin
        if ($session->handled_by !== 'admin') {
            $session->update([
                'handled_by' => 'admin',
                'status'     => 'active',
            ]);
        }

        $msg = ChatMessage::create([
            'session_id'  => $session->id,
            'sender_type' => $request->input('sender_type', 'admin'),
            'content'     => $messageContent,
            'metadata'    => $request->sender_name ? ['sender_name' => $request->sender_name] : null,
        ]);

        $session->update(['last_message_at' => now()]);

        try {
            broadcast(new NewChatMessage($msg));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('AdminChatController broadcast failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'id'          => $msg->id,
            'session_id'  => $msg->session_id,
            'sender_type' => $msg->sender_type,
            'sender_name' => $request->input('sender_name', $request->input('displayName', 'Admin')),
            'content'     => $msg->content,
            'metadata'    => $msg->metadata,
            'timestamp'   => $msg->created_at->toISOString(),
        ], 201);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/chat/messages — all messages (dashboard feed)
    // ------------------------------------------------------------------
    public function allMessages(Request $request)
    {
        $limit = min((int) $request->input('limit', 100), 500);
        $since = $request->input('since');

        $query = ChatMessage::with('session')
            ->orderByDesc('created_at');

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $messages = $query->limit($limit)->get()->map(fn (ChatMessage $m) => [
            'id'           => $m->id,
            'session_id'   => $m->session_id,
            'session_token' => $m->session?->session_token,
            'customer_name' => $m->session?->customer_name ?? 'ลูกค้า',
            'sender_type'  => $m->sender_type,
            'content'      => $m->content,
            'metadata'     => $m->metadata,
            'timestamp'    => $m->created_at->toISOString(),
        ]);

        return response()->json(['messages' => $messages]);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/chat/sessions — all sessions with last message + unread
    // ------------------------------------------------------------------
    public function sessions(Request $request)
    {
        $sessions = ChatSession::with(['latestMessage', 'admin'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function (ChatSession $s) {
                return [
                    'id'              => $s->id,
                    'session_token'   => $s->session_token,
                    'customer_name'   => $s->customer_name ?? 'ลูกค้า',
                    'channel'         => $s->channel,
                    'status'          => $s->status,
                    'handled_by'      => $s->handled_by,
                    'admin_name'      => $s->admin?->name,
                    'last_message'    => $s->latestMessage?->content,
                    'last_message_at' => $s->last_message_at?->toISOString(),
                    'unread'          => $s->messages()
                        ->where('sender_type', 'user')
                        ->whereNull('metadata->read_by_admin')
                        ->count(),
                ];
            });

        return response()->json(['sessions' => $sessions]);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/chat/sessions/{id}/messages — messages for a session
    // ------------------------------------------------------------------
    public function sessionMessages(Request $request, int $id)
    {
        $session = ChatSession::findOrFail($id);

        $limit  = min((int) $request->input('limit', 100), 500);
        $offset = (int) $request->input('offset', 0);

        $messages = $session->messages()
            ->orderBy('created_at')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn (ChatMessage $m) => [
                'id'          => $m->id,
                'session_id'  => $m->session_id,
                'sender_type' => $m->sender_type,
                'sender_name' => $m->sender_type === 'admin'
                    ? ($m->metadata['sender_name'] ?? 'Admin')
                    : ($m->session?->customer_name ?? 'ลูกค้า'),
                'content'     => $m->content,
                'metadata'    => $m->metadata,
                'timestamp'   => $m->created_at->toISOString(),
            ]);

        return response()->json([
            'session' => [
                'id'          => $session->id,
                'status'      => $session->status,
                'handled_by'  => $session->handled_by,
                'customer_name' => $session->customer_name ?? 'ลูกค้า',
                'admin_name'  => $session->admin?->name,
            ],
            'messages' => $messages,
        ]);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/chat/sessions/token/{token}/messages
    // ------------------------------------------------------------------
    public function sessionMessagesByToken(Request $request, string $token)
    {
        $session = ChatSession::where('session_token', $token)->firstOrFail();
        return $this->sessionMessages($request, $session->id);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/chat/sessions/{id}/next-sequence
    // ------------------------------------------------------------------
    public function nextSequence(int $id)
    {
        ChatSession::findOrFail($id);

        $count = ChatMessage::where('session_id', $id)->count();

        return response()->json(['next_sequence' => $count + 1, 'chatSequence' => $count + 1]);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/chat/sessions/token/{token}/next-sequence
    // ------------------------------------------------------------------
    public function nextSequenceByToken(string $token)
    {
        $session = ChatSession::where('session_token', $token)->firstOrFail();
        return $this->nextSequence($session->id);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/chat/messages/{messageId}/exists
    // ------------------------------------------------------------------
    public function messageExists(string $messageId)
    {
        $exists = ChatMessage::where('id', $messageId)->exists();

        return response()->json(['exists' => $exists]);
    }

    // ------------------------------------------------------------------
    // PATCH /api/v2/chat/sessions/{id}/viewed — mark session as viewed
    // ------------------------------------------------------------------
    public function markViewed(int $id)
    {
        $session = ChatSession::findOrFail($id);

        // Mark all unread user messages as read
        $session->messages()
            ->where('sender_type', 'user')
            ->whereNull('metadata->read_by_admin')
            ->each(function (ChatMessage $m) {
                $metadata = $m->metadata ?? [];
                $metadata['read_by_admin'] = now()->toISOString();
                $m->update(['metadata' => $metadata]);
            });

        return response()->json(['success' => true]);
    }

    // ------------------------------------------------------------------
    // PATCH /api/v2/chat/sessions/token/{token}/viewed
    // ------------------------------------------------------------------
    public function markViewedByToken(string $token)
    {
        $session = ChatSession::where('session_token', $token)->firstOrFail();
        return $this->markViewed($session->id);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/contacts — customers derived from chat sessions
    // ------------------------------------------------------------------
    public function contacts(Request $request)
    {
        $query = ChatSession::select('id', 'session_token', 'customer_name', 'channel', 'status', 'created_at')
            ->orderByDesc('created_at');

        if ($request->filled('labels')) {
            $labels = array_map('trim', explode(',', $request->input('labels')));
            $query->whereIn('channel', $labels);
        }

        $contacts = $query->get()
            ->map(fn (ChatSession $s) => [
                'id'            => $s->id,
                'session_token' => $s->session_token,
                'name'          => $s->customer_name ?? 'ลูกค้า',
                'channel'       => $s->channel,
                'status'        => $s->status,
                'created_at'    => $s->created_at->toISOString(),
            ]);

        return response()->json(['contacts' => $contacts]);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/contacts/labels
    // ------------------------------------------------------------------
    public function contactLabels()
    {
        $labels = DB::table('chat_sessions')
            ->select('channel', DB::raw('count(*) as count'))
            ->groupBy('channel')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->channel,
                'count' => $row->count,
            ]);

        return response()->json(['labels' => $labels]);
    }

    // ------------------------------------------------------------------
    // GET /api/v2/prompts
    // ------------------------------------------------------------------
    public function prompts()
    {
        // Check if prompts table exists; return from DB if it does
        if (DB::getSchemaBuilder()->hasTable('ai_prompts')) {
            $prompts = DB::table('ai_prompts')->get();
            return response()->json(['prompts' => $prompts]);
        }

        // Fallback: return default prompts as static list
        return response()->json([
            'prompts' => [
                [
                    'id'          => 1,
                    'name'        => 'Default Chat Prompt',
                    'description' => 'System prompt สำหรับ Nick ที่ปรึกษาโครงการ Evante',
                    'content'     => 'คุณชื่อ "นิค" เป็นที่ปรึกษาด้านอสังหาริมทรัพย์ของโครงการ Evante',
                    'is_active'   => true,
                    'updated_at'  => now()->toISOString(),
                ],
            ],
        ]);
    }

    // ------------------------------------------------------------------
    // PUT /api/v2/prompts/{id}
    // ------------------------------------------------------------------
    public function updatePrompt(Request $request, int $id)
    {
        $request->validate([
            'name'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content'     => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        if (DB::getSchemaBuilder()->hasTable('ai_prompts')) {
            $prompt = DB::table('ai_prompts')->where('id', $id)->first();

            if (! $prompt) {
                return response()->json(['error' => 'Prompt not found'], 404);
            }

            DB::table('ai_prompts')->where('id', $id)->update(
                array_filter($request->only(['name', 'description', 'content', 'is_active']), fn ($v) => $v !== null)
                + ['updated_at' => now()]
            );

            return response()->json(DB::table('ai_prompts')->where('id', $id)->first());
        }

        // No prompts table: return the submitted data as-is
        return response()->json([
            'id'         => $id,
            'updated_at' => now()->toISOString(),
        ] + $request->only(['name', 'description', 'content', 'is_active']));
    }

    // ------------------------------------------------------------------
    // GET /api/v2/monitoring/kpis
    // ------------------------------------------------------------------
    public function kpis(Request $request)
    {
        $range = $request->input('filter', $request->input('range', 'today'));

        $from = match ($range) {
            'week'  => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };

        $totalChats = ChatSession::where('created_at', '>=', $from)->count();

        $totalMessages = ChatMessage::where('created_at', '>=', $from)->count();

        $userMessages = ChatMessage::where('sender_type', 'user')
            ->where('created_at', '>=', $from)
            ->count();

        $adminMessages = ChatMessage::where('sender_type', 'admin')
            ->where('created_at', '>=', $from)
            ->count();

        $aiMessages = ChatMessage::where('sender_type', 'ai')
            ->where('created_at', '>=', $from)
            ->count();

        $activeSessions = ChatSession::where('status', 'active')->count();
        $resolvedSessions = ChatSession::where('status', 'resolved')
            ->where('updated_at', '>=', $from)
            ->count();

        // Average response time: time between user msg and next admin/ai msg
        $avgResponseTime = DB::select("
            SELECT AVG(diff) as avg_seconds FROM (
                SELECT CAST((julianday(r.created_at) - julianday(u.created_at)) * 86400 AS INTEGER) as diff
                FROM chat_messages u
                JOIN chat_messages r ON r.session_id = u.session_id
                    AND r.sender_type IN ('admin', 'ai')
                    AND r.id = (
                        SELECT MIN(id) FROM chat_messages
                        WHERE session_id = u.session_id
                          AND sender_type IN ('admin', 'ai')
                          AND id > u.id
                    )
                WHERE u.sender_type = 'user'
                  AND u.created_at >= ?
                  AND diff > 0 AND diff < 3600
            )
        ", [$from->toDateTimeString()]);

        $avgSeconds = (float) ($avgResponseTime[0]->avg_seconds ?? 0);

        return response()->json([
            'range'             => $range,
            'from'              => $from->toISOString(),
            'total_chats'       => $totalChats,
            'total_messages'    => $totalMessages,
            'user_messages'     => $userMessages,
            'admin_messages'    => $adminMessages,
            'ai_messages'       => $aiMessages,
            'active_sessions'   => $activeSessions,
            'resolved_sessions' => $resolvedSessions,
            'avg_response_time' => round($avgSeconds),
            'avg_response_time_label' => $avgSeconds > 0
                ? round($avgSeconds) . 's'
                : 'N/A',
        ]);
    }
}
