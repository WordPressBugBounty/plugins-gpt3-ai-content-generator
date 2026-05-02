<?php
// File: classes/core/providers/xai/parse-error-response.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}
function parse_error_response_logic(XAIProviderStrategy $strategyInstance, $response_body, int $status_code): string {
    $decoded = is_string($response_body) ? json_decode($response_body, true) : $response_body;
    $message = __('An unknown xAI API error occurred.', 'gpt3-ai-content-generator');

    if (is_array($decoded)) {
        if (!empty($decoded['error']['message'])) {
            $message = (string) $decoded['error']['message'];
            if (!empty($decoded['error']['code'])) {
                $message .= ' (Code: ' . (string) $decoded['error']['code'] . ')';
            }
            if (!empty($decoded['error']['type'])) {
                $message .= ' Type: ' . (string) $decoded['error']['type'];
            }
        } elseif (!empty($decoded['message'])) {
            $message = (string) $decoded['message'];
        } elseif (!empty($decoded['detail'])) {
            $message = is_string($decoded['detail']) ? $decoded['detail'] : wp_json_encode($decoded['detail']);
        } elseif (!empty($decoded['errors'][0]['message'])) {
            $message = (string) $decoded['errors'][0]['message'];
        }
    } elseif (is_string($response_body) && trim($response_body) !== '') {
        $message = substr($response_body, 0, 500);
    }

    return trim((string) $message);
}
