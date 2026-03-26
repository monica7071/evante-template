<?php

namespace App\Events;

use App\Models\ChatSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatHandoff implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatSession $session,
        public string $direction // 'to_admin' or 'to_ai'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->session->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'session_id'  => $this->session->id,
            'direction'   => $this->direction,
            'handled_by'  => $this->session->handled_by,
        ];
    }
}
