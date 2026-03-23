<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('app.chatbot_api_key');

        $provided = $request->bearerToken()
            ?? $request->header('X-API-Key');

        if (!$apiKey || !$provided || !hash_equals($apiKey, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }
}
