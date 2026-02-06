<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fetch Claude models.
 */
function get_models_logic(ClaudeProviderStrategy $strategyInstance, array $api_params): array|WP_Error {
    $url = $strategyInstance->build_api_url('models', $api_params);
    if (is_wp_error($url)) {
        return $url;
    }

    $headers = $strategyInstance->get_api_headers($api_params['api_key'] ?? '', 'models');
    $options = $strategyInstance->get_request_options('models');
    $options['method'] = 'GET';

    $response = wp_remote_get($url, array_merge($options, ['headers' => $headers]));
    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if ($status_code !== 200) {
        $error_msg = $strategyInstance->parse_error_response($body, $status_code);
        return new WP_Error(
            'api_error_claude_models',
            sprintf('Claude API Error (HTTP %d): %s', $status_code, esc_html($error_msg))
        );
    }

    $decoded = $strategyInstance->decode_json($body, 'Claude Models');
    if (is_wp_error($decoded)) {
        return $decoded;
    }

    $raw_models = $decoded['data'] ?? [];
    if (!is_array($raw_models)) {
        return [];
    }

    return $strategyInstance->format_model_list($raw_models, 'id', 'display_name');
}
