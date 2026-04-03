<?php
// File: classes/core/providers/azure/map-sse-event.php

namespace WPAICG\Core\Providers\Azure\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Maps a normalized Azure SSE event into an internal typed event.
 *
 * @param array<string, mixed> $decoded_event
 * @return array<string, mixed>
 */
function map_sse_event_logic_for_response_parser(array $decoded_event): array {
    $kind = isset($decoded_event['kind']) && is_string($decoded_event['kind']) ? $decoded_event['kind'] : 'payload';
    $payload = isset($decoded_event['payload']) && is_array($decoded_event['payload']) ? $decoded_event['payload'] : [];

    if ($kind === 'done') {
        return ['kind' => 'done'];
    }

    if (isset($payload['error'])) {
        return [
            'kind' => 'error',
            'message' => parse_error_logic_for_response_parser($payload, 500),
        ];
    }

    $delta_text = null;
    if (isset($payload['choices'][0]['delta']['content'])) {
        $delta_text = (string) $payload['choices'][0]['delta']['content'];
        if ($delta_text === '') {
            $delta_text = null;
        }
    }

    $usage = extract_sse_usage_logic_for_response_parser($payload);
    $warning_text = extract_sse_warning_logic_for_response_parser($payload);

    if ($delta_text === null && $usage === null && $warning_text === null) {
        return ['kind' => 'skip'];
    }

    return [
        'kind' => 'chunk',
        'delta_text' => $delta_text,
        'usage' => $usage,
        'warning_text' => $warning_text,
    ];
}

/**
 * Extracts token usage from an Azure SSE payload.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed>|null
 */
function extract_sse_usage_logic_for_response_parser(array $payload): ?array {
    if (!isset($payload['usage']) || !is_array($payload['usage'])) {
        return null;
    }

    $usage = $payload['usage'];

    return [
        'input_tokens' => $usage['prompt_tokens'] ?? 0,
        'output_tokens' => $usage['completion_tokens'] ?? 0,
        'total_tokens' => $usage['total_tokens'] ?? 0,
        'provider_raw' => $usage,
    ];
}

/**
 * Extracts user-visible warning text from an Azure SSE payload when the stream is explicitly blocked.
 *
 * @param array<string, mixed> $payload
 * @return string|null
 */
function extract_sse_warning_logic_for_response_parser(array $payload): ?string {
    $choice = $payload['choices'][0] ?? null;
    if (!is_array($choice)) {
        return null;
    }

    $finish_reason = $choice['finish_reason'] ?? null;
    if ($finish_reason === 'content_filter') {
        return sprintf(' (%s)', __('Warning: Content Filtered', 'gpt3-ai-content-generator'));
    }

    return null;
}
