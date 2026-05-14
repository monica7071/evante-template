<?php

namespace App\Services\AI\Tools;

interface ToolInterface
{
    public function name(): string;

    public function description(): string;

    /**
     * @return array<string, mixed>
     */
    public function inputSchema(): array;

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function execute(array $input, int $organizationId): array;

    /**
     * @return array{name: string, description: string, input_schema: array}
     */
    public function toDefinition(): array;
}
