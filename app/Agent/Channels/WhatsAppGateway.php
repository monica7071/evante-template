<?php

namespace App\Agent\Channels;

use App\Agent\Contracts\ChannelGatewayInterface;
use App\Agent\DTO\AgentResponse;
use App\Agent\DTO\NormalizedMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGateway implements ChannelGatewayInterface
{
    private string $appSecret;
    private string $accessToken;
    private string $phoneNumberId;
    private string $verifyToken;
    private string $apiUrl = 'https://graph.facebook.com/v19.0';

    public function __construct()
    {
        $this->appSecret     = env('WHATSAPP_APP_SECRET', '');
        $this->accessToken   = env('WHATSAPP_ACCESS_TOKEN', '');
        $this->phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID', '');
        $this->verifyToken   = env('WHATSAPP_VERIFY_TOKEN', '');
    }

    public function parse(Request $request): ?NormalizedMessage
    {
        // Webhook verification handshake (GET)
        if ($request->isMethod('GET')) {
            $this->handleVerificationChallenge($request);
            return null;
        }

        $this->verifySignature($request);

        $body    = $request->json()->all();
        $changes = $body['entry'][0]['changes'][0]['value'] ?? [];
        $msgs    = $changes['messages'] ?? [];

        if (empty($msgs)) {
            return null; // Status updates, read receipts, etc.
        }

        $msg      = $msgs[0];
        $senderId = $msg['from'] ?? 'unknown';
        $type     = $msg['type'] ?? '';

        $text     = '';
        $imageUrl = null;

        if ($type === 'text') {
            $text = $msg['text']['body'] ?? '';
        } elseif ($type === 'image') {
            $mediaId  = $msg['image']['id'] ?? null;
            $imageUrl = $mediaId ? $this->resolveMediaUrl($mediaId) : null;
            $text     = '[ส่งรูปภาพ]';
        } else {
            return null;
        }

        return new NormalizedMessage(
            channel:  'whatsapp',
            senderId: $senderId,
            text:     $text,
            imageUrl: $imageUrl,
            metadata: ['message_id' => $msg['id'] ?? null],
        );
    }

    public function deliver(NormalizedMessage $original, AgentResponse $response): void
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $original->senderId,
            'type'              => 'text',
            'text'              => ['body' => $response->text],
        ];

        $result = Http::withToken($this->accessToken)
            ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

        if (! $result->successful()) {
            Log::error('WhatsAppGateway deliver failed', [
                'status' => $result->status(),
                'body'   => $result->body(),
            ]);
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function verifySignature(Request $request): void
    {
        if (empty($this->appSecret)) {
            Log::warning('WhatsAppGateway: WHATSAPP_APP_SECRET not set, skipping signature check');
            return;
        }

        $signature = $request->header('X-Hub-Signature-256', '');
        $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), $this->appSecret);

        if (! hash_equals($expected, $signature)) {
            abort(401, 'Invalid WhatsApp signature');
        }
    }

    private function handleVerificationChallenge(Request $request): void
    {
        if ($request->query('hub_verify_token') === $this->verifyToken) {
            echo $request->query('hub_challenge', '');
        } else {
            abort(403, 'Verification token mismatch');
        }
    }

    private function resolveMediaUrl(string $mediaId): ?string
    {
        $result = Http::withToken($this->accessToken)
            ->get("{$this->apiUrl}/{$mediaId}");

        return $result->json('url');
    }
}
