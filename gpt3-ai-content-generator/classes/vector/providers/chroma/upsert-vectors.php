<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/upsert-vectors.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function upsert_vectors_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $index_name, array $vectors_data): array|WP_Error
{
    $collection = resolve_collection_logic($strategyInstance, $index_name);
    if (is_wp_error($collection)) {
        return $collection;
    }

    $collection_id = isset($collection['id']) ? (string) $collection['id'] : '';
    if ($collection_id === '') {
        return new WP_Error('chroma_collection_id_missing', __('Chroma collection ID is missing for upsert operation.', 'gpt3-ai-content-generator'));
    }

    $payload = prepare_vectors_for_chroma_logic($vectors_data);
    if (is_wp_error($payload)) {
        return $payload;
    }

    $batch_size = (int) apply_filters('aipkit_chroma_upsert_batch_size', 100, $index_name);
    $batch_size = max(1, $batch_size);
    $total = count($payload['ids']);
    $batches = 0;

    for ($i = 0; $i < $total; $i += $batch_size) {
        $chunk_body = [
            'ids'        => array_slice($payload['ids'], $i, $batch_size),
            'embeddings' => array_slice($payload['embeddings'], $i, $batch_size),
        ];
        foreach (['documents', 'metadatas', 'uris'] as $optional_key) {
            if (isset($payload[$optional_key])) {
                $chunk_body[$optional_key] = array_slice($payload[$optional_key], $i, $batch_size);
            }
        }

        $response = _request_logic($strategyInstance, 'POST', collection_records_path_logic($strategyInstance, $collection_id, 'upsert'), $chunk_body);
        if (is_wp_error($response)) {
            return $response;
        }
        $batches++;
    }

    return [
        'status' => 'success',
        'upserted_count' => $total,
        'aipkit_batches' => $batches,
    ];
}
