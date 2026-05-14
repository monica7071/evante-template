<?php

/**
 * GitHub Webhook receiver for auto-deploy on push to main.
 *
 * Setup:
 * 1. GitHub repo → Settings → Webhooks → Add webhook
 * 2. Payload URL: https://evante.yesdemo.co/deploy-webhook.php
 * 3. Content type: application/json
 * 4. Secret: (same as $secret below)
 * 5. Events: Just the push event
 */

$secret = '5d0054fbe6c1825588e07e59b67a15b8cd303113f713ab951103aedabf6377b5';

// Verify GitHub signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload   = file_get_contents('php://input');

if (! hash_equals('sha256=' . hash_hmac('sha256', $payload, $secret), $signature)) {
    http_response_code(403);
    exit('Unauthorized');
}

$data = json_decode($payload, true);

// Only deploy on push to main
if (($data['ref'] ?? '') !== 'refs/heads/main') {
    echo 'Not main branch, skipping.';
    exit;
}

$deployScript = dirname(__DIR__) . '/deploy.sh';
$logFile      = dirname(__DIR__) . '/storage/logs/deploy.log';

exec("bash {$deployScript} > {$logFile} 2>&1 &");

echo 'Deploy triggered.';
