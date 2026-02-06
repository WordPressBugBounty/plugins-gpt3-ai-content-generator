<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build Anthropic request headers.
 */
function get_api_headers_logic(ClaudeProviderStrategy $strategyInstance, string $api_key, string $operation): array {
    $anthropic_version = '2023-06-01';

    if (class_exists('\WPAICG\AIPKit_Providers')) {
        $provider_data = \WPAICG\AIPKit_Providers::get_provider_data('Claude');
        if (!empty($provider_data['api_version'])) {
            $anthropic_version = sanitize_text_field((string) $provider_data['api_version']);
        }
    }

    $headers = [
        'Content-Type' => 'application/json',
        'x-api-key' => $api_key,
        'anthropic-version' => $anthropic_version,
    ];

    // Note: files beta header is injected conditionally by callers only when
    // the payload contains Claude document blocks sourced from Files API.

    if ($operation === 'stream') {
        $headers['Accept'] = 'text/event-stream';
        $headers['Cache-Control'] = 'no-cache';
    }

    return $headers;
}
