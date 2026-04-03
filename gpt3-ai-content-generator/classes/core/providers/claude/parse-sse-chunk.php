<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse Claude SSE chunks.
 */
function parse_sse_chunk_logic(
    ClaudeProviderStrategy $strategyInstance,
    string $sse_chunk,
    string &$current_buffer
): array {
    $current_buffer .= $sse_chunk;

    $result = [
        'delta' => null,
        'usage' => null,
        'is_error' => false,
        'is_warning' => false,
        'is_done' => false,
        'status' => null,
        'citations' => null,
    ];

    foreach (extract_sse_event_blocks_logic_for_response_parser($current_buffer) as $event_block) {
        $decoded_event = decode_sse_event_block_logic_for_response_parser($event_block);
        if ($decoded_event === null) {
            continue;
        }

        $mapped_event = map_sse_event_logic_for_response_parser($decoded_event);
        if (reduce_sse_event_logic_for_response_parser($mapped_event, $result)) {
            return $result;
        }
    }

    return $result;
}
