<?php
// File: classes/core/providers/azure/extract-sse-event-blocks.php

namespace WPAICG\Core\Providers\Azure\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Extracts complete SSE event blocks from the current buffer and leaves any partial tail untouched.
 *
 * @param string $current_buffer Reference to the incomplete SSE buffer.
 * @return array<int, string>
 */
function extract_sse_event_blocks_logic_for_response_parser(string &$current_buffer): array {
    $event_blocks = [];

    while (preg_match("/\r?\n\r?\n/", $current_buffer, $separator_match, PREG_OFFSET_CAPTURE) === 1) {
        $separator_offset = (int) $separator_match[0][1];
        $separator_length = strlen((string) $separator_match[0][0]);
        $event_block = substr($current_buffer, 0, $separator_offset);
        $current_buffer = substr($current_buffer, $separator_offset + $separator_length);

        if (trim($event_block) !== '') {
            $event_blocks[] = $event_block;
        }
    }

    return $event_blocks;
}
