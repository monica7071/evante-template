<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminJoinedChat;
use App\Events\ChatHandoff;
use App\Events\NewChatMessage;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatManagementController extends Controller
{
    public function index()
    {
        return view('admin.chat.index');
    }

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

    public function messages(int $sessionId)
    {
        $session = ChatSession::findOrFail($sessionId);

        $messages = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ChatMessage $m) => [
                'id'          => $m->id,
                'sender_type' => $m->sender_type,
                'sender_name' => $m->sender_type === 'admin' ? ($m->sender?->name ?? 'Admin') : null,
                'content'     => $m->content,
                'metadata'    => $m->metadata,
                'timestamp'   => $m->created_at->toISOString(),
            ]);

        return response()->json([
            'session'  => [
                'id'          => $session->id,
                'status'      => $session->status,
                'handled_by'  => $session->handled_by,
                'admin_name'  => $session->admin?->name,
            ],
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, int $sessionId)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $session = ChatSession::findOrFail($sessionId);

        // Auto-takeover if not already handled by admin
        if ($session->handled_by !== 'admin') {
            $session->update([
                'handled_by' => 'admin',
                'admin_id'   => Auth::id(),
                'status'     => 'active',
            ]);
        }

        $msg = ChatMessage::create([
            'session_id'  => $session->id,
            'sender_type' => 'admin',
            'sender_id'   => Auth::id(),
            'content'     => $request->message,
        ]);

        $session->update(['last_message_at' => now()]);

        broadcast(new NewChatMessage($msg))->toOthers();

        return response()->json([
            'id'          => $msg->id,
            'sender_type' => 'admin',
            'content'     => $msg->content,
            'timestamp'   => $msg->created_at->toISOString(),
        ]);
    }

    public function takeover(int $sessionId)
    {
        $session = ChatSession::findOrFail($sessionId);

        $session->update([
            'handled_by' => 'admin',
            'admin_id'   => Auth::id(),
            'status'     => 'active',
        ]);

        $session->load('admin');

        broadcast(new AdminJoinedChat($session));
        broadcast(new ChatHandoff($session, 'to_admin'));

        // System message
        $msg = ChatMessage::create([
            'session_id'  => $session->id,
            'sender_type' => 'admin',
            'sender_id'   => Auth::id(),
            'content'     => '👋 Admin (' . Auth::user()->name . ') เข้ามารับช่วงการสนทนาแล้ว',
            'metadata'    => ['system' => true],
        ]);

        $session->update(['last_message_at' => now()]);
        broadcast(new NewChatMessage($msg));

        return response()->json(['status' => 'ok', 'handled_by' => 'admin']);
    }

    public function handback(int $sessionId)
    {
        $session = ChatSession::findOrFail($sessionId);

        $session->update([
            'handled_by' => 'ai',
            'admin_id'   => null,
        ]);

        broadcast(new ChatHandoff($session, 'to_ai'));

        // System message
        $msg = ChatMessage::create([
            'session_id'  => $session->id,
            'sender_type' => 'ai',
            'content'     => '🤖 AI Assistant กลับมาดูแลการสนทนาแล้ว',
            'metadata'    => ['system' => true],
        ]);

        $session->update(['last_message_at' => now()]);
        broadcast(new NewChatMessage($msg));

        return response()->json(['status' => 'ok', 'handled_by' => 'ai']);
    }

    public function resolve(int $sessionId)
    {
        $session = ChatSession::findOrFail($sessionId);
        $session->update(['status' => 'resolved']);

        return response()->json(['status' => 'ok']);
    }
}
