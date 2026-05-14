<?php

namespace App\Agent\Channels;

use App\Agent\Contracts\ChannelGatewayInterface;
use App\Agent\DTO\AgentResponse;
use App\Agent\DTO\NormalizedMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TikTok Business Messaging Gateway.
 *
 * Requires TikTok for Business API access (TikTok Business Center).
 * ENV vars:
 *   TIKTOK_APP_SECRET       — for HMAC verification
 *   TIKTOK_ACCESS_TOKEN     — Business API access token
 *   TIKTOK_OPEN_ID          — TikTok account open_id
 */
class TikTokGateway implements ChannelGatewayInterface
{
    private string $appSecret;
    private string $accessToken;
    private string $openId;
    private string $apiUrl = 'https://open.tiktokapis.com/v2';

    public function __construct()
    {
        $this->appSecret   = env('TIKTOK_APP_SECRET', '');
        $this->accessToken = env('TIKTOK_ACCESS_TOKEN', '');
        $this->openId      = env('TIKTOK_OPEN_ID', '');
    }

    public function parse(Request $request): ?NormalizedMessage
    {
        $this->verifySignature($request);

        $body    = $request->json()->all();
        $event   = $body['event'] ?? [];
        $type    = $event['event_type'] ?? '';

        // Only handle direct messages
        if ($type !== 'direct_message') {
            return null;
        }

        $message  = $event['message'] ?? [];
        $senderId = $event['sender']['open_id'] ?? 'unknown';
        $text     = $message['text'] ?? '';
        $imageUrl = null;

        if (empty($text) && ($message['message_type'] ?? '') === 'image') {
            $imageUrl = $message['image_url'] ?? null;
            $text     = '[ส่งรูปภาพ]';
        }

        if (empty($text) && empty($imageUrl)) {
            return null;
        }

        return new NormalizedMessage(
            channel:  'tiktok',
            senderId: $senderId,
            text:     $text,
            imageUrl: $imageUrl,
            metadata: ['message_id' => $message['message_id'] ?? null],
        );
    }

    public function deliver(NormalizedMessage $original, AgentResponse $response): void
    {
        $payload = [
            'open_id'      => $this->openId,
            'to_open_id'   => $original->senderId,
            'message_type' => 'text',
            'content'      => json_encode(['text' => mb_substr($response->text, 0, 500)]),
        ];

        $result = Http::withToken($this->accessToken)
            ->post("{$this->apiUrl}/dm/conversation/send/", $payload);

        if (! $result->successful()) {
            Log::error('TikTokGateway deliver failed', [
                'status' => $result->status(),
                'body'   => $result->body(),
            ]);
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function verifySignature(Request $request): void
    {
        if (empty($this->appSecret)) {
            Log::warning('TikTokGateway: TIKTOK_APP_SECRET not set, skipping signature check');
            return;
        }

        // TikTok signs: HMAC-SHA256(app_secret + nonce + timestamp + raw_body)
        $nonce     = $request->header('x-tiktok-nonce', '');
        $timestamp = $request->header('x-tiktok-timestamp', '');
        $signature = $request->header('x-tiktok-signature', '');
        $raw       = $request->getContent();

        $expected = hash_hmac('sha256', $this->appSecret . $nonce . $timestamp . $raw, $this->appSecret);

        if (! hash_equals($expected, ltrim($signature, 'sha256='))) {
            abort(401, 'Invalid TikTok signature');
        }
    }
}
