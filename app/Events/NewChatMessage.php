<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatMessage $message) {}

    /**
     * Use a short event name so both Evante frontend and Evante-Aura
     * can listen with `.listen('.MessageSent', ...)` or `.listen('MessageSent', ...)`.
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    /**
     * Broadcast on both the numeric DB id channel (for Evante frontend)
     * and the session_token channel (for Evante-Aura, which uses lineUuid).
     */
    public function broadcastOn(): array
    {
        $session = $this->message->session;

        $channels = [
            new Channel('chat.' . $this->message->session_id),
        ];

        if ($session && $session->session_token) {
            $channels[] = new Channel('chat.' . $session->session_token);
        }

        return $channels;
    }

    /**
     * Shape the payload so Evante-Aura receives the structure it expects
     * (nested under a "message" key with field names matching its JS listener),
     * while also keeping the flat fields for Evante's own frontend.
     */
    public function broadcastWith(): array
    {
        $session = $this->message->session;

        // Map sender_type to a messageChannel value Evante-Aura understands
        $messageChannel = match ($this->message->sender_type) {
            'admin'    => 'Admin',
            'ai'       => 'AI',
            'customer' => 'Line',
            default    => $this->message->sender_type,
        };

        return [
            // Flat fields for Evante frontend backward compatibility
            'id'          => $this->message->id,
            'session_id'  => $this->message->session_id,
            'sender_type' => $this->message->sender_type,
            'content'     => $this->message->content,
            'metadata'    => $this->message->metadata,
            'timestamp'   => $this->message->created_at->toISOString(),

            // Nested "message" object for Evante-Aura compatibility
            'message' => [
                'lineUuid'       => $session?->session_token,
                'message'        => $this->message->content,
                'aiResponse'     => '',
                'messageChannel' => $messageChannel,
                'chatSequence'   => $this->message->id,
                'messageId'      => $this->message->id,
                'date'           => $this->message->created_at->toISOString(),
                'timestamp'      => $this->message->created_at->toISOString(),
            ],
        ];
    }
}
