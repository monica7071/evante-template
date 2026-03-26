<?php

namespace App\Agent\Channels;

use App\Agent\Contracts\ChannelGatewayInterface;
use App\Agent\DTO\AgentResponse;
use App\Agent\DTO\NormalizedMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramGateway implements ChannelGatewayInterface
{
    private string $botToken;
    private string $secretToken;
    private string $apiUrl;

    public function __construct()
    {
        $this->botToken    = env('TELEGRAM_BOT_TOKEN', '');
        $this->secretToken = env('TELEGRAM_WEBHOOK_SECRET', '');
        $this->apiUrl      = "https://api.telegram.org/bot{$this->botToken}";
    }

    public function parse(Request $request): ?NormalizedMessage
    {
        $this->verifySignature($request);

        $body    = $request->json()->all();
        $message = $body['message'] ?? $body['channel_post'] ?? null;

        if (! $message) {
            return null; // Callback queries, edited messages, etc.
        }

        $senderId = (string) ($message['from']['id'] ?? $message['chat']['id'] ?? 'unknown');
        $text     = $message['text'] ?? '';
        $imageUrl = null;

        if (empty($text) && isset($message['photo'])) {
            // photo array sorted by size; take the largest
            $photo    = end($message['photo']);
            $fileId   = $photo['file_id'] ?? null;
            $imageUrl = $fileId ? $this->resolveFileUrl($fileId) : null;
            $text     = $message['caption'] ?? '[ส่งรูปภาพ]';
        }

        if (empty($text) && empty($imageUrl)) {
            return null;
        }

        return new NormalizedMessage(
            channel:  'telegram',
            senderId: $senderId,
            text:     $text,
            imageUrl: $imageUrl,
            metadata: [
                'chat_id'    => $message['chat']['id'] ?? null,
                'message_id' => $message['message_id'] ?? null,
            ],
        );
    }

    public function deliver(NormalizedMessage $original, AgentResponse $response): void
    {
        $chatId = $original->metadata['chat_id'] ?? $original->senderId;

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $response->text,
            'parse_mode' => 'Markdown',
        ];

        // Keyboard buttons from quick replies
        if (!empty($response->quickReplies)) {
            $keyboard = array_map(fn ($label) => [['text' => $label]], $response->quickReplies);
            $payload['reply_markup'] = json_encode([
                'keyboard'          => $keyboard,
                'one_time_keyboard' => true,
                'resize_keyboard'   => true,
            ]);
        }

        $result = Http::post("{$this->apiUrl}/sendMessage", $payload);

        if (! $result->successful()) {
            Log::error('TelegramGateway deliver failed', [
                'status' => $result->status(),
                'body'   => $result->body(),
            ]);
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function verifySignature(Request $request): void
    {
        if (empty($this->secretToken)) {
            return; // Optional for Telegram
        }

        $provided = $request->header('X-Telegram-Bot-Api-Secret-Token', '');

        if (! hash_equals($this->secretToken, $provided)) {
            abort(401, 'Invalid Telegram secret token');
        }
    }

    private function resolveFileUrl(string $fileId): ?string
    {
        $result = Http::get("{$this->apiUrl}/getFile", ['file_id' => $fileId]);
        $path   = $result->json('result.file_path');

        return $path
            ? "https://api.telegram.org/file/bot{$this->botToken}/{$path}"
            : null;
    }
}
