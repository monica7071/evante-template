<?php

namespace App\Agent\Contracts;

use App\Agent\DTO\AgentResponse;
use App\Agent\DTO\NormalizedMessage;
use Illuminate\Http\Request;

interface ChannelGatewayInterface
{
    /**
     * Verify the platform signature and parse the inbound request into a NormalizedMessage.
     * Returns null if the event should be ignored (e.g. delivery receipts, unseen events).
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException on invalid signature (abort 401)
     */
    public function parse(Request $request): ?NormalizedMessage;

    /**
     * Deliver the agent response back to the originating user.
     */
    public function deliver(NormalizedMessage $original, AgentResponse $response): void;
}
