<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse Claude error response.
 */
function parse_error_response_logic(ClaudeProviderStrategy $strategyInstance, $response_body, int $status_code): string {
    $message = __('An unknown API error occurred.', 'gpt3-ai-content-generator');
    $decoded = is_string($response_body) ? json_decode($response_body, true) : $response_body;

    if (is_array($decoded)) {
        if (!empty($decoded['error']['message'])) {
            $message = (string) $decoded['error']['message'];
            if (!empty($decoded['error']['type'])) {
                $message .= ' Type: ' . (string) $decoded['error']['type'];
            }
        } elseif (!empty($decoded['message'])) {
            $message = (string) $decoded['message'];
        }
    } elseif (is_string($response_body)) {
        $message = substr($response_body, 0, 300);
    }

    return trim($message);
}
