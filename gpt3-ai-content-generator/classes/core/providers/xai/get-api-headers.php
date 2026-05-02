<?php
// File: classes/core/providers/xai/get-api-headers.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}
function get_api_headers_logic(XAIProviderStrategy $strategyInstance, string $api_key, string $operation): array {
    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key,
    ];

    if ($operation === 'stream') {
        $headers['Accept'] = 'text/event-stream';
        $headers['Cache-Control'] = 'no-cache';
    }

    return $headers;
}
