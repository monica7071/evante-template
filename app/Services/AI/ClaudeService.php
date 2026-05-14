<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private readonly ?string $apiKey;
    private readonly string  $apiUrl;
    private readonly string  $model;
    private readonly int     $maxTokens;
    private readonly int     $timeout;

    public function __construct()
    {
        // Claude Desktop app overrides ANTHROPIC_API_KEY to empty string.
        // Read CHATBOT_API_KEY first (?: skips empty), then ANTHROPIC_API_KEY,
        // then fall back to cached config as last resort.
        $this->apiKey    = env('CHATBOT_API_KEY') ?: env('ANTHROPIC_API_KEY') ?: config('ai.anthropic.api_key') ?: null;
        $this->apiUrl    = rtrim(config('ai.anthropic.api_url'), '/');
        $this->model     = config('ai.anthropic.model');
        $this->maxTokens = config('ai.anthropic.max_tokens');
        $this->timeout   = config('ai.anthropic.timeout');
    }

    /**
     * Send a messages request to Claude and return the raw response body.
     *
     * @param  array  $messages   Conversation history in Anthropic messages format
     * @param  array  $tools      Tool definitions (optional)
     * @param  array  $options    Override defaults: model, max_tokens, system
     * @return array              Anthropic response body
     *
     * @throws \RuntimeException on non-retryable API errors
     */
    public function chat(array $messages, array $tools = [], array $options = []): array
    {
        $payload = $this->buildPayload($messages, $tools, $options);

        return $this->request($payload);
    }

    /**
     * Extract the first text content block from a Claude response.
     */
    public function extractText(array $response): string
    {
        foreach ($response['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                return $block['text'] ?? '';
            }
        }

        return '';
    }

    /**
     * Extract all tool_use blocks from a Claude response.
     *
     * @return array[]  Each element: ['id' => string, 'name' => string, 'input' => array]
     */
    public function extractToolUses(array $response): array
    {
        $uses = [];
        foreach ($response['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'tool_use') {
                $uses[] = [
                    'id'    => $block['id'],
                    'name'  => $block['name'],
                    'input' => $block['input'] ?? [],
                ];
            }
        }

        return $uses;
    }

    /**
     * Build a tool_result user message to send back after executing a tool.
     */
    public function buildToolResultMessage(string $toolUseId, array $result, bool $isError = false): array
    {
        return [
            'role'    => 'user',
            'content' => [
                [
                    'type'        => 'tool_result',
                    'tool_use_id' => $toolUseId,
                    'content'     => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'is_error'    => $isError,
                ],
            ],
        ];
    }

    /**
     * Build an image content block (vision). Accepts URL or base64.
     */
    public function buildImageBlock(string $imageData, string $mediaType = 'image/jpeg'): array
    {
        if (str_starts_with($imageData, 'http://') || str_starts_with($imageData, 'https://')) {
            return [
                'type'   => 'image',
                'source' => ['type' => 'url', 'url' => $imageData],
            ];
        }

        return [
            'type'   => 'image',
            'source' => [
                'type'       => 'base64',
                'media_type' => $mediaType,
                'data'       => $imageData,
            ],
        ];
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    private function buildPayload(array $messages, array $tools, array $options): array
    {
        $systemPrompt = $options['system'] ?? config('ai.system_prompt');

        $payload = [
            'model'      => $options['model'] ?? $this->model,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'messages'   => $messages,
        ];

        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        if (! empty($tools)) {
            $payload['tools'] = $tools;
        }

        return $payload;
    }

    private function request(array $payload, int $attempt = 1): array
    {
        $response = $this->httpClient()->post("{$this->apiUrl}/messages", $payload);
        $body     = $response->json();

        if ($response->successful()) {
            $this->logUsage($body);
            return $body;
        }

        $status  = $response->status();
        $message = $body['error']['message'] ?? "HTTP {$status}";

        // Retry on 529 (overloaded) or 500 up to 3 times
        if (in_array($status, [500, 529]) && $attempt < 3) {
            $backoff = $attempt * 2;
            Log::warning("ClaudeService: retrying after {$backoff}s (attempt {$attempt}, status {$status})");
            sleep($backoff);
            return $this->request($payload, $attempt + 1);
        }

        Log::error("ClaudeService error [{$status}]: {$message}");
        throw new \RuntimeException("Claude API error [{$status}]: {$message}", $status);
    }

    private function httpClient(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => config('ai.anthropic.version'),
            'content-type'      => 'application/json',
        ])->timeout($this->timeout);
    }

    private function logUsage(array $response): void
    {
        $usage = $response['usage'] ?? [];
        if (empty($usage)) {
            return;
        }

        Log::debug('ClaudeService usage', [
            'model'         => $response['model'] ?? $this->model,
            'input_tokens'  => $usage['input_tokens'] ?? 0,
            'output_tokens' => $usage['output_tokens'] ?? 0,
            'stop_reason'   => $response['stop_reason'] ?? null,
        ]);
    }
}
