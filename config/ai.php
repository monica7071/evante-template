<?php

return [

    'system_prompt' => env('AI_SYSTEM_PROMPT', 'You are a helpful assistant.'),

    // Default organization ID used for the public chat (no auth context)
    'default_organization_id' => (int) env('AI_DEFAULT_ORGANIZATION_ID', 1),

    'anthropic' => [
        'api_key'    => env('CHATBOT_API_KEY') ?: env('ANTHROPIC_API_KEY', ''),
        'api_url'    => env('ANTHROPIC_API_URL', 'https://api.anthropic.com/v1'),
        'version'    => env('ANTHROPIC_VERSION', '2023-06-01'),
        'model'      => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
        'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 1024),
        'timeout'    => (int) env('ANTHROPIC_TIMEOUT', 30),
    ],

];
