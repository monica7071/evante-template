<?php

namespace App\Agent\DTO;

class NormalizedMessage
{
    public function __construct(
        public readonly string  $channel,    // line | whatsapp | telegram | instagram | tiktok
        public readonly string  $senderId,   // platform user ID
        public readonly string  $text,
        public readonly ?string $imageUrl  = null,
        public readonly array   $metadata  = [], // raw platform-specific data (reply token, chat_id, etc.)
    ) {}
}
