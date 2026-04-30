<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/list-indexes.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function list_indexes_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, ?int $limit = null, ?string $order = null, ?string $after = null, ?string $before = null): array|WP_Error
{
    $query_params = [];
    if ($limit !== null && $limit > 0) {
        $query_params['limit'] = $limit;
    }
    if ($after !== null && is_numeric($after)) {
        $query_params['offset'] = absint($after);
    }

    $response = _request_logic($strategyInstance, 'GET', collection_base_path_logic($strategyInstance), [], $query_params);
    if (is_wp_error($response)) {
        return $response;
    }

    $collections_data = $response;
    if (isset($response['collections']) && is_array($response['collections'])) {
        $collections_data = $response['collections'];
    } elseif (isset($response['data']) && is_array($response['data'])) {
        $collections_data = $response['data'];
    }

    $formatted_collections = [];
    if (is_array($collections_data)) {
        foreach ($collections_data as $collection) {
            if (!is_array($collection)) {
                continue;
            }
            $name = isset($collection['name']) ? (string) $collection['name'] : '';
            $id = isset($collection['id']) ? (string) $collection['id'] : $name;
            if ($name === '' && $id === '') {
                continue;
            }
            $formatted_collections[] = [
                'id'        => $id,
                'name'      => $name !== '' ? $name : $id,
                'dimension' => $collection['dimension'] ?? null,
                'metadata'  => $collection['metadata'] ?? [],
                'tenant'    => $collection['tenant'] ?? $strategyInstance->get_tenant(),
                'database'  => $collection['database'] ?? $strategyInstance->get_database(),
            ];
        }
    }

    return $formatted_collections;
}
