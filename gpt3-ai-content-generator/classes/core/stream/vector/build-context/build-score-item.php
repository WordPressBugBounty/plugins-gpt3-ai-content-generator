<?php

namespace WPAICG\Core\Stream\Vector\BuildContext;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds optional vector metadata to a score item before it is stored in logs.
 *
 * @param array<string,mixed> $score_item
 * @param array<string,mixed> $metadata
 * @return array<string,mixed>
 */
function build_vector_search_score_item_logic(array $score_item, array $metadata = []): array
{
    $chunk_number = null;
    $chunk_index = null;

    if (isset($metadata['chunk_number']) && is_numeric($metadata['chunk_number'])) {
        $chunk_number = max(1, (int) $metadata['chunk_number']);
        $chunk_index = $chunk_number - 1;
    } elseif (isset($metadata['chunk_index']) && is_numeric($metadata['chunk_index'])) {
        $chunk_index = max(0, (int) $metadata['chunk_index']);
        $chunk_number = $chunk_index + 1;
    }

    $total_chunks = isset($metadata['total_chunks']) && is_numeric($metadata['total_chunks'])
        ? (int) $metadata['total_chunks']
        : null;

    if ($chunk_number !== null && $total_chunks !== null && $total_chunks > 0) {
        $score_item['chunk_index'] = $chunk_index;
        $score_item['chunk_number'] = $chunk_number;
        $score_item['total_chunks'] = $total_chunks;
    }

    foreach (['original_filename', 'filename'] as $file_key) {
        if (!empty($metadata[$file_key]) && is_scalar($metadata[$file_key])) {
            $score_item['file_name'] = sanitize_text_field((string) $metadata[$file_key]);
            break;
        }
    }

    if (isset($metadata['char_start']) && is_numeric($metadata['char_start'])) {
        $score_item['char_start'] = (int) $metadata['char_start'];
    }
    if (isset($metadata['char_end']) && is_numeric($metadata['char_end'])) {
        $score_item['char_end'] = (int) $metadata['char_end'];
    }

    return $score_item;
}
