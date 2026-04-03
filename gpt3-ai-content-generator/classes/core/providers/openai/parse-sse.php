<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/providers/openai/parse-sse.php
// Status: NEW FILE

namespace WPAICG\Core\Providers\OpenAI\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the parse_sse_chunk static method of OpenAIResponseParser.
 */
function parse_sse_chunk_logic_for_response_parser(string $sse_chunk, string &$current_buffer): array {
    $current_buffer .= $sse_chunk;
    $result = [
        'delta' => null,
        'usage' => null,
        'is_error' => false,
        'is_warning' => false,
        'is_done' => false,
        'openai_response_id' => null,
        'status' => null,
        'citations' => null,
    ];

    foreach (extract_sse_event_blocks_logic_for_response_parser($current_buffer) as $event_block) {
        $decoded_event = decode_sse_event_block_logic_for_response_parser($event_block);
        if ($decoded_event === null) {
            continue;
        }

        $mapped_event = map_sse_event_logic_for_response_parser($decoded_event);
        $should_stop = reduce_sse_event_logic_for_response_parser($mapped_event, $result);
        if ($should_stop) {
            return $result;
        }
    }

    return $result;
}
