<?php

namespace App\Agent\Channels;

use App\Agent\Contracts\ChannelGatewayInterface;
use App\Agent\DTO\AgentResponse;
use App\Agent\DTO\NormalizedMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineGateway implements ChannelGatewayInterface
{
    private string $channelSecret;
    private string $accessToken;
    private string $apiUrl = 'https://api.line.me/v2/bot';

    public function __construct()
    {
        $this->channelSecret = env('LINE_CHANNEL_SECRET', '');
        $this->accessToken   = env('LINE_CHANNEL_ACCESS_TOKEN', '');
    }

    public function parse(Request $request): ?NormalizedMessage
    {
        $this->verifySignature($request);

        $body   = $request->json()->all();
        $events = $body['events'] ?? [];

        foreach ($events as $event) {
            if (($event['type'] ?? '') !== 'message') {
                continue;
            }

            $message = $event['message'] ?? [];
            $source  = $event['source'] ?? [];
            $senderId = $source['userId'] ?? $source['groupId'] ?? $source['roomId'] ?? 'unknown';

            $text     = '';
            $imageUrl = null;

            if (($message['type'] ?? '') === 'text') {
                $text = $message['text'] ?? '';
            } elseif (($message['type'] ?? '') === 'image') {
                $imageUrl = $this->fetchImageUrl($message['id']);
                $text     = '[ส่งรูปภาพ]';
            } else {
                // Sticker, audio, video — skip
                continue;
            }

            return new NormalizedMessage(
                channel:  'line',
                senderId: $senderId,
                text:     $text,
                imageUrl: $imageUrl,
                metadata: [
                    'reply_token' => $event['replyToken'] ?? null,
                    'source'      => $source,
                ],
            );
        }

        return null; // No actionable event
    }

    public function deliver(NormalizedMessage $original, AgentResponse $response): void
    {
        $messages = [['type' => 'text', 'text' => $response->text]];

        // Append quick reply buttons if provided (max 13)
        if (!empty($response->quickReplies)) {
            $items = array_map(fn ($label) => [
                'type'   => 'action',
                'action' => ['type' => 'message', 'label' => mb_substr($label, 0, 20), 'text' => $label],
            ], array_slice($response->quickReplies, 0, 13));

            $messages[0]['quickReply'] = ['items' => $items];
        }

        // Use push message (async-safe; reply tokens expire quickly)
        $result = Http::withToken($this->accessToken)
            ->post("{$this->apiUrl}/message/push", [
                'to'       => $original->senderId,
                'messages' => $messages,
            ]);

        if (! $result->successful()) {
            Log::error('LineGateway deliver failed', [
                'status' => $result->status(),
                'body'   => $result->body(),
            ]);
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function verifySignature(Request $request): void
    {
        if (empty($this->channelSecret)) {
            Log::warning('LineGateway: LINE_CHANNEL_SECRET not set, skipping signature check');
            return;
        }

        $signature = $request->header('X-Line-Signature', '');
        $expected  = base64_encode(
            hash_hmac('sha256', $request->getContent(), $this->channelSecret, true)
        );

        if (! hash_equals($expected, $signature)) {
            abort(401, 'Invalid LINE signature');
        }
    }

    private function fetchImageUrl(string $messageId): ?string
    {
        // Returns the LINE content URL; caller is responsible for downloading if needed.
        return "{$this->apiUrl}/message/{$messageId}/content";
    }
}
