<?php

namespace App\Jobs;

use App\Agent\AgentOrchestrator;
use App\Agent\Channels\InstagramGateway;
use App\Agent\Channels\LineGateway;
use App\Agent\Channels\TelegramGateway;
use App\Agent\Channels\TikTokGateway;
use App\Agent\Channels\WhatsAppGateway;
use App\Agent\Contracts\ChannelGatewayInterface;
use App\Agent\DTO\NormalizedMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    // Store primitives only — gateway is re-instantiated in handle()
    public function __construct(
        private readonly string  $channel,
        private readonly string  $senderId,
        private readonly string  $text,
        private readonly ?string $imageUrl = null,
        private readonly array   $metadata = [],
    ) {
        $this->onQueue('agent-messages');
    }

    public function handle(AgentOrchestrator $orchestrator): void
    {
        $message = new NormalizedMessage(
            channel:  $this->channel,
            senderId: $this->senderId,
            text:     $this->text,
            imageUrl: $this->imageUrl,
            metadata: $this->metadata,
        );

        Log::info("ProcessChatMessage: [{$this->channel}] from {$this->senderId}", [
            'text' => mb_substr($this->text, 0, 100),
        ]);

        $response = $orchestrator->handle($message);

        $gateway  = $this->makeGateway($this->channel);
        $gateway->deliver($message, $response);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessChatMessage failed [{$this->channel}/{$this->senderId}]: " . $exception->getMessage());
    }

    // ── Factory ──────────────────────────────────────────────────────────────

    private function makeGateway(string $channel): ChannelGatewayInterface
    {
        return match ($channel) {
            'line'      => new LineGateway(),
            'whatsapp'  => new WhatsAppGateway(),
            'telegram'  => new TelegramGateway(),
            'instagram' => new InstagramGateway(),
            'tiktok'    => new TikTokGateway(),
            default     => throw new \InvalidArgumentException("Unknown channel: {$channel}"),
        };
    }
}
