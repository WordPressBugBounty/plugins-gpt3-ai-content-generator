<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/connect.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function connect_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, array $config): bool|WP_Error
{
    if (empty($config['url'])) {
        return new WP_Error('missing_chroma_url', __('Chroma URL is required for connection.', 'gpt3-ai-content-generator'));
    }

    $strategyInstance->set_chroma_url($config['url']);
    $strategyInstance->set_api_key($config['api_key'] ?? null);
    $strategyInstance->set_tenant($config['tenant'] ?? null);
    $strategyInstance->set_database($config['database'] ?? null);
    $strategyInstance->set_is_connected_status(false);

    $path = '/api/v2/tenants/' . rawurlencode($strategyInstance->get_tenant()) . '/databases/' . rawurlencode($strategyInstance->get_database());
    $database_response = _request_logic($strategyInstance, 'GET', $path);

    if (is_wp_error($database_response)) {
        /* translators: %1$s: Chroma URL, %2$s: error message. */
        return new WP_Error('chroma_connection_failed', sprintf(__('Failed to connect to Chroma at %1$s. Error: %2$s', 'gpt3-ai-content-generator'), esc_html((string) $strategyInstance->get_chroma_url()), $database_response->get_error_message()), $database_response->get_error_data());
    }

    if (isset($database_response['name']) || isset($database_response['id']) || ($database_response['status'] ?? '') === 'ok') {
        $strategyInstance->set_is_connected_status(true);
        return true;
    }

    $collections_response = _request_logic($strategyInstance, 'GET', collection_base_path_logic($strategyInstance), [], ['limit' => 1]);
    if (!is_wp_error($collections_response) && is_array($collections_response)) {
        $strategyInstance->set_is_connected_status(true);
        return true;
    }

    return new WP_Error('chroma_connection_unexpected_response', __('Unexpected response while connecting to Chroma. Please check URL, tenant, database, and API key.', 'gpt3-ai-content-generator'));
}
