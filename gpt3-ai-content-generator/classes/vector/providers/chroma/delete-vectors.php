<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/delete-vectors.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function delete_vectors_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $index_name, array $vector_ids_or_filter): bool|WP_Error
{
    $collection = resolve_collection_logic($strategyInstance, $index_name);
    if (is_wp_error($collection)) {
        return $collection;
    }

    $collection_id = isset($collection['id']) ? (string) $collection['id'] : '';
    if ($collection_id === '') {
        return new WP_Error('chroma_collection_id_missing', __('Chroma collection ID is missing for delete operation.', 'gpt3-ai-content-generator'));
    }

    if (isset($vector_ids_or_filter['ids']) && is_array($vector_ids_or_filter['ids'])) {
        $body = ['ids' => $vector_ids_or_filter['ids']];
    } elseif (isset($vector_ids_or_filter['where']) && is_array($vector_ids_or_filter['where'])) {
        $body = ['where' => $vector_ids_or_filter['where']];
        if (isset($vector_ids_or_filter['where_document']) && is_array($vector_ids_or_filter['where_document'])) {
            $body['where_document'] = $vector_ids_or_filter['where_document'];
        }
    } else {
        $body = ['ids' => array_values($vector_ids_or_filter)];
    }

    if (empty($body['ids']) && empty($body['where'])) {
        return new WP_Error('chroma_delete_missing_selector', __('Chroma delete requires IDs or a metadata filter.', 'gpt3-ai-content-generator'));
    }

    $response = _request_logic($strategyInstance, 'POST', collection_records_path_logic($strategyInstance, $collection_id, 'delete'), $body);
    if (is_wp_error($response)) {
        return $response;
    }

    return isset($response['deleted']) || ($response['status'] ?? '') === 'ok' || is_array($response);
}
