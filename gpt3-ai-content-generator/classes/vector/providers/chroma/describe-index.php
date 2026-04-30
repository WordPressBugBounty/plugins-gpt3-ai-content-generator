<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/describe-index.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function describe_index_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $index_name): array|WP_Error
{
    $collection = resolve_collection_logic($strategyInstance, $index_name);
    if (is_wp_error($collection)) {
        return $collection;
    }

    $collection_id = isset($collection['id']) ? (string) $collection['id'] : '';
    if ($collection_id === '') {
        return new WP_Error('chroma_collection_id_missing', __('Chroma collection ID is missing.', 'gpt3-ai-content-generator'));
    }

    $description = _request_logic($strategyInstance, 'GET', collection_base_path_logic($strategyInstance) . '/' . rawurlencode($collection_id));
    if (is_wp_error($description)) {
        return $description;
    }

    $count_response = _request_logic($strategyInstance, 'GET', collection_records_path_logic($strategyInstance, $collection_id, 'count'));
    $count_value = !is_wp_error($count_response)
        ? ($count_response['value'] ?? ($count_response['count'] ?? null))
        : null;
    if ($count_value !== null && is_numeric($count_value)) {
        $description['total_vector_count'] = (int) $count_value;
        $description['vectors_count'] = (int) $count_value;
    }

    $description['id'] = $description['id'] ?? $collection_id;
    $description['name'] = $description['name'] ?? ($collection['name'] ?? $index_name);
    $description['tenant'] = $description['tenant'] ?? $strategyInstance->get_tenant();
    $description['database'] = $description['database'] ?? $strategyInstance->get_database();

    return $description;
}
