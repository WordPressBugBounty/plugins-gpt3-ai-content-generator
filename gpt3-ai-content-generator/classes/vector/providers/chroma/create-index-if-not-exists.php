<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/create-index-if-not-exists.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function create_index_if_not_exists_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $index_name, array $index_config): array|WP_Error
{
    $existing = describe_index_logic($strategyInstance, $index_name);
    if (!is_wp_error($existing)) {
        return $existing;
    }

    $error_data = $existing->get_error_data();
    $status = is_array($error_data) ? (int) ($error_data['status'] ?? 0) : 0;
    if ($status !== 404) {
        return $existing;
    }

    $body = ['name' => $index_name];
    foreach (['metadata', 'configuration', 'schema', 'get_or_create'] as $optional_key) {
        if (array_key_exists($optional_key, $index_config)) {
            $body[$optional_key] = $index_config[$optional_key];
        }
    }

    $create_response = _request_logic($strategyInstance, 'POST', collection_base_path_logic($strategyInstance), $body);
    if (is_wp_error($create_response)) {
        return $create_response;
    }

    if (isset($create_response['name']) || isset($create_response['id'])) {
        return $create_response;
    }

    return describe_index_logic($strategyInstance, $index_name);
}
