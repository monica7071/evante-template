<?php

namespace App\Http\Controllers\Api\V2;

use App\Agent\Channels\InstagramGateway;
use App\Agent\Channels\LineGateway;
use App\Agent\Channels\TelegramGateway;
use App\Agent\Channels\TikTokGateway;
use App\Agent\Channels\WhatsAppGateway;
use App\Agent\Contracts\ChannelGatewayInterface;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Handles inbound webhook events from all messaging channels.
 *
 * Flow:
 *   POST /api/v2/webhook/{channel}
 *   → ChannelGateway::parse()   — verify signature, normalise message
 *   → ProcessChatMessage::dispatch()  — queued on 'agent-messages'
 *   → return 200 OK immediately (channels require fast ack)
 */
class WebhookController extends Controller
{
    /** @var array<string, class-string<ChannelGatewayInterface>> */
    private array $gatewayMap = [
        'line'      => LineGateway::class,
        'whatsapp'  => WhatsAppGateway::class,
        'telegram'  => TelegramGateway::class,
        'instagram' => InstagramGateway::class,
        'tiktok'    => TikTokGateway::class,
    ];

    /**
     * Handle an inbound webhook for any channel.
     *
     * GET  requests are used by WhatsApp/Instagram for webhook verification.
     * POST requests carry actual message events.
     */
    public function handle(Request $request, string $channel): JsonResponse|Response
    {
        $gatewayClass = $this->gatewayMap[$channel] ?? null;

        if (! $gatewayClass) {
            return response()->json(['error' => "Unknown channel: {$channel}"], 404);
        }

        /** @var ChannelGatewayInterface $gateway */
        $gateway = new $gatewayClass();

        // WhatsApp/Instagram: GET is a verification handshake — gateway echoes the challenge.
        if ($request->isMethod('GET')) {
            $gateway->parse($request); // handles echo internally
            return response('', 200);
        }

        try {
            $message = $gateway->parse($request);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // Signature mismatch — log and return 401 without processing
            Log::warning("WebhookController [{$channel}]: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        if ($message === null) {
            // Ignored event (delivery receipt, read event, etc.) — ack with 200
            return response()->json(['ok' => true]);
        }

        Log::info("WebhookController [{$channel}]: message from {$message->senderId}", [
            'text'     => mb_substr($message->text, 0, 80),
            'imageUrl' => $message->imageUrl,
        ]);

        ProcessChatMessage::dispatch(
            $message->channel,
            $message->senderId,
            $message->text,
            $message->imageUrl,
            $message->metadata,
        );

        // Return 200 immediately — channels will retry on non-2xx
        return response()->json(['ok' => true]);
    }
}
