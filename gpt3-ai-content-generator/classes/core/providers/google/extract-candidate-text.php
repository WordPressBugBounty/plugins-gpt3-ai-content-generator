<?php
// File: classes/core/providers/google/extract-candidate-text.php

namespace WPAICG\Core\Providers\Google\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Extracts and concatenates all text parts from a Gemini candidate payload.
 *
 * @param array<string, mixed> $candidate
 * @return string|null
 */
function extract_candidate_text_logic_for_response_parser(array $candidate): ?string {
    if (!isset($candidate['content']['parts']) || !is_array($candidate['content']['parts'])) {
        return null;
    }

    $text = '';
    $has_text = false;

    foreach ($candidate['content']['parts'] as $part) {
        if (!is_array($part) || !isset($part['text'])) {
            continue;
        }

        $text .= (string) $part['text'];
        $has_text = true;
    }

    if (!$has_text) {
        return null;
    }

    return $text;
}
