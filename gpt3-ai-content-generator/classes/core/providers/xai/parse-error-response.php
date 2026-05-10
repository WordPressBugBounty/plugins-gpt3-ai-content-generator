<?php
// File: classes/core/providers/xai/parse-error-response.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}

function xai_error_value_to_string($value): string {
    if (is_string($value)) {
        return trim($value);
    }
    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }
    if (is_array($value)) {
        $encoded = wp_json_encode($value);
        return is_string($encoded) ? trim($encoded) : '';
    }

    return '';
}

/**
 * @param array<string, mixed> $error
 */
function xai_extract_error_object_message(array $error): string {
    $message = '';
    foreach (['message', 'detail', 'description', 'reason'] as $message_key) {
        if (!empty($error[$message_key])) {
            $message = xai_error_value_to_string($error[$message_key]);
            break;
        }
    }

    $metadata = [];
    foreach (['code', 'type', 'param'] as $metadata_key) {
        if (!empty($error[$metadata_key])) {
            $metadata_value = xai_error_value_to_string($error[$metadata_key]);
            if ($metadata_value !== '') {
                $metadata[] = ucfirst($metadata_key) . ': ' . $metadata_value;
            }
        }
    }

    if ($message === '' && !empty($metadata)) {
        return implode(' ', $metadata);
    }
    if ($message !== '' && !empty($metadata)) {
        return $message . ' (' . implode(', ', $metadata) . ')';
    }

    return $message;
}

/**
 * @param array<string, mixed> $decoded
 */
function xai_extract_error_message(array $decoded): string {
    if (!empty($decoded['error'])) {
        if (is_string($decoded['error'])) {
            return trim($decoded['error']);
        }
        if (is_array($decoded['error'])) {
            $message = xai_extract_error_object_message($decoded['error']);
            if ($message !== '') {
                return $message;
            }
        }
    }

    foreach (['message', 'detail', 'description'] as $message_key) {
        if (!empty($decoded[$message_key])) {
            return xai_error_value_to_string($decoded[$message_key]);
        }
    }

    if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
        foreach ($decoded['errors'] as $error_item) {
            if (is_string($error_item)) {
                return trim($error_item);
            }
            if (is_array($error_item)) {
                $message = xai_extract_error_object_message($error_item);
                if ($message !== '') {
                    return $message;
                }
            }
        }
    }

    foreach (['code', 'type', 'status'] as $metadata_key) {
        if (!empty($decoded[$metadata_key])) {
            return ucfirst($metadata_key) . ': ' . xai_error_value_to_string($decoded[$metadata_key]);
        }
    }

    return '';
}

function xai_parse_error_response_body($response_body, int $status_code, string $fallback_message): string {
    $decoded = is_string($response_body) ? json_decode($response_body, true) : $response_body;
    $message = '';

    if (is_array($decoded)) {
        $message = xai_extract_error_message($decoded);
    } elseif (is_string($response_body) && trim($response_body) !== '') {
        $message = trim(substr($response_body, 0, 500));
    }

    if ($message === '' && $status_code === 429) {
        return __('Rate limit or quota exceeded. Please wait and retry, or check your xAI Console rate limits and billing.', 'gpt3-ai-content-generator');
    }
    if ($message === '') {
        return $fallback_message;
    }

    return trim((string) $message);
}

function parse_error_response_logic(XAIProviderStrategy $strategyInstance, $response_body, int $status_code): string {
    return xai_parse_error_response_body(
        $response_body,
        $status_code,
        __('An unknown xAI API error occurred.', 'gpt3-ai-content-generator')
    );
}
