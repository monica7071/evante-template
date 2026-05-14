<?php

namespace App\Agent\Channels;

use App\Agent\Contracts\ChannelGatewayInterface;
use App\Agent\DTO\AgentResponse;
use App\Agent\DTO\NormalizedMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramGateway implements ChannelGatewayInterface
{
    private string $appSecret;
    private string $accessToken;
    private string $verifyToken;
    private string $apiUrl = 'https://graph.facebook.com/v19.0';

    public function __construct()
    {
        $this->appSecret   = env('INSTAGRAM_APP_SECRET', '');
        $this->accessToken = env('INSTAGRAM_ACCESS_TOKEN', '');
        $this->verifyToken = env('INSTAGRAM_VERIFY_TOKEN', '');
    }

    public function parse(Request $request): ?NormalizedMessage
    {
        if ($request->isMethod('GET')) {
            $this->handleVerificationChallenge($request);
            return null;
        }

        $this->verifySignature($request);

        $body     = $request->json()->all();
        $entry    = $body['entry'][0] ?? [];
        $messaging = $entry['messaging'][0] ?? null;

        if (! $messaging) {
            return null;
        }

        $senderId = $messaging['sender']['id'] ?? 'unknown';
        $message  = $messaging['message'] ?? null;

        if (! $message) {
            return null; // Delivery/read events
        }

        $text     = $message['text'] ?? '';
        $imageUrl = null;

        if (isset($message['attachments'])) {
            foreach ($message['attachments'] as $att) {
                if (($att['type'] ?? '') === 'image') {
                    $imageUrl = $att['payload']['url'] ?? null;
                    if (empty($text)) {
                        $text = '[ส่งรูปภาพ]';
                    }
                    break;
                }
            }
        }

        if (empty($text) && empty($imageUrl)) {
            return null;
        }

        return new NormalizedMessage(
            channel:  'instagram',
            senderId: $senderId,
            text:     $text,
            imageUrl: $imageUrl,
            metadata: ['message_id' => $message['mid'] ?? null],
        );
    }

    public function deliver(NormalizedMessage $original, AgentResponse $response): void
    {
        $payload = [
            'recipient' => ['id' => $original->senderId],
            'message'   => ['text' => mb_substr($response->text, 0, 2000)],
        ];

        $result = Http::withToken($this->accessToken)
            ->post("{$this->apiUrl}/me/messages", $payload);

        if (! $result->successful()) {
            Log::error('InstagramGateway deliver failed', [
                'status' => $result->status(),
                'body'   => $result->body(),
            ]);
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function verifySignature(Request $request): void
    {
        if (empty($this->appSecret)) {
            Log::warning('InstagramGateway: INSTAGRAM_APP_SECRET not set, skipping signature check');
            return;
        }

        $signature = $request->header('X-Hub-Signature-256', '');
        $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), $this->appSecret);

        if (! hash_equals($expected, $signature)) {
            abort(401, 'Invalid Instagram signature');
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
}
