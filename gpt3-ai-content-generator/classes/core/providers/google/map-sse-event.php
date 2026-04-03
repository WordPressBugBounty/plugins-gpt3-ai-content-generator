<?php
// File: classes/core/providers/google/map-sse-event.php

namespace WPAICG\Core\Providers\Google\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Maps a normalized Google SSE event into an internal typed event.
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

    if (isset($payload['error']['message'])) {
        return [
            'kind' => 'error',
            'message' => parse_error_logic_for_response_parser($payload, 500),
        ];
    }

    $candidate = $payload['candidates'][0] ?? null;
    if (!is_array($candidate)) {
        $candidate = [];
    }

    $delta_text = extract_candidate_text_logic_for_response_parser($candidate);
    if ($delta_text === '') {
        $delta_text = null;
    }

    $usage = extract_sse_usage_logic_for_response_parser($payload);
    $grounding_metadata = extract_sse_grounding_metadata_logic_for_response_parser($candidate);
    $citations = is_array($grounding_metadata)
        ? extract_google_citations_from_grounding_metadata_logic_for_response_parser($grounding_metadata)
        : [];
    $notice_text = build_sse_notice_text_logic_for_response_parser($payload, $candidate);
    $is_warning = is_sse_warning_logic_for_response_parser($payload, $candidate);

    if ($delta_text === null && $usage === null && $grounding_metadata === null && empty($citations) && $notice_text === null) {
        return ['kind' => 'skip'];
    }

    return [
        'kind' => 'chunk',
        'delta_text' => $delta_text,
        'usage' => $usage,
        'grounding_metadata' => $grounding_metadata,
        'citations' => $citations,
        'notice_text' => $notice_text,
        'is_warning' => $is_warning,
    ];
}

/**
 * Extracts token usage from a Google SSE payload.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed>|null
 */
function extract_sse_usage_logic_for_response_parser(array $payload): ?array {
    if (!isset($payload['usageMetadata']) || !is_array($payload['usageMetadata'])) {
        return null;
    }

    $usage = $payload['usageMetadata'];

    return [
        'input_tokens' => $usage['promptTokenCount'] ?? 0,
        'output_tokens' => $usage['candidatesTokenCount'] ?? 0,
        'total_tokens' => $usage['totalTokenCount'] ?? 0,
        'provider_raw' => $usage,
    ];
}

/**
 * Extracts grounding metadata from a Google candidate payload.
 *
 * @param array<string, mixed> $candidate
 * @return array<string, mixed>|null
 */
function extract_sse_grounding_metadata_logic_for_response_parser(array $candidate): ?array {
    if (isset($candidate['groundingMetadata']) && is_array($candidate['groundingMetadata'])) {
        return $candidate['groundingMetadata'];
    }

    return null;
}

/**
 * Builds any user-visible notice text from prompt feedback or finish reasons.
 *
 * @param array<string, mixed> $payload
 * @param array<string, mixed> $candidate
 * @return string|null
 */
function build_sse_notice_text_logic_for_response_parser(array $payload, array $candidate): ?string {
    $notice_text = '';

    if (!empty($payload['promptFeedback']['blockReason'])) {
        $notice_text .= sprintf(' (%s: %s)', __('Warning', 'gpt3-ai-content-generator'), $payload['promptFeedback']['blockReason']);
    }

    if (isset($candidate['finishReason']) && $candidate['finishReason'] !== 'STOP') {
        $reason = (string) $candidate['finishReason'];
        if ($reason === 'SAFETY') {
            $notice_text .= sprintf(' (%s: %s)', __('Warning', 'gpt3-ai-content-generator'), $candidate['safetyRatings'][0]['category'] ?? $reason);
        } else {
            $notice_text .= sprintf(' (%s: %s)', __('Note', 'gpt3-ai-content-generator'), $reason);
        }
    }

    if ($notice_text === '') {
        return null;
    }

    return $notice_text;
}

/**
 * Determines whether the current Google SSE payload should be treated as a warning.
 *
 * @param array<string, mixed> $payload
 * @param array<string, mixed> $candidate
 * @return bool
 */
function is_sse_warning_logic_for_response_parser(array $payload, array $candidate): bool {
    if (!empty($payload['promptFeedback']['blockReason'])) {
        return true;
    }

    return isset($candidate['finishReason']) && $candidate['finishReason'] === 'SAFETY';
}
