<?php

namespace App\Agent\DTO;

class AgentResponse
{
    public function __construct(
        public readonly string $text,
        public readonly array  $quickReplies = [],
    ) {}
}
