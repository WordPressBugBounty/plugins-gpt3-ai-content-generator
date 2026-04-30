<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/delete-index.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function delete_index_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $index_name): bool|WP_Error
{
    $collection = resolve_collection_logic($strategyInstance, $index_name);
    if (is_wp_error($collection)) {
        return $collection;
    }

    $collection_id = isset($collection['id']) ? (string) $collection['id'] : '';
    if ($collection_id === '') {
        return new WP_Error('chroma_collection_id_missing', __('Chroma collection ID is missing for delete collection operation.', 'gpt3-ai-content-generator'));
    }

    $response = _request_logic($strategyInstance, 'DELETE', collection_base_path_logic($strategyInstance) . '/' . rawurlencode($collection_id));
    if (is_wp_error($response)) {
        return $response;
    }

    return isset($response['deleted']) || ($response['status'] ?? '') === 'ok' || is_array($response);
}
