<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/_request.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function _request_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $method, string $path, array $body = [], array $query_params = []): array|WP_Error
{
    $chroma_url = $strategyInstance->get_chroma_url();
    if (empty($chroma_url)) {
        return new WP_Error('chroma_url_not_set', __('Chroma URL not configured in strategy.', 'gpt3-ai-content-generator'));
    }

    $path = '/' . ltrim($path, '/');
    $url = rtrim($chroma_url, '/') . $path;
    if (!empty($query_params)) {
        $url = add_query_arg($query_params, $url);
    }

    $headers = [
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    ];
    $api_key = $strategyInstance->get_api_key();
    if (!empty($api_key)) {
        $headers['x-chroma-token'] = $api_key;
    }

    $request_args = [
        'method'  => strtoupper($method),
        'headers' => $headers,
        'timeout' => 60,
    ];

    if (!empty($body) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
        $request_args['body'] = wp_json_encode($body);
    }

    $response = wp_remote_request($url, $request_args);
    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $response_body_raw = wp_remote_retrieve_body($response);
    $trimmed_body = trim((string) $response_body_raw);
    $decoded_response = [];

    if ($trimmed_body !== '') {
        $decoded = json_decode($trimmed_body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($status_code >= 400) {
                $error_msg = $strategyInstance->parse_error_response_public_wrapper($response_body_raw, $status_code, 'Chroma Vector Store');
                /* translators: %1$d: HTTP status code, %2$s: Error message from the API. */
                return new WP_Error('chroma_api_error', sprintf(__('Chroma API Error (%1$d): %2$s', 'gpt3-ai-content-generator'), $status_code, $error_msg), ['status' => $status_code]);
            }
            /* translators: %s: JSON parse error. */
            return new WP_Error('chroma_json_decode_error', sprintf(__('Failed to parse JSON response from Chroma Vector Store. Error: %s', 'gpt3-ai-content-generator'), json_last_error_msg()), ['status' => $status_code]);
        }
        $decoded_response = is_array($decoded) ? $decoded : ['value' => $decoded];
    }

    if ($status_code >= 400) {
        $error_msg = $strategyInstance->parse_error_response_public_wrapper($decoded_response ?: $response_body_raw, $status_code, 'Chroma Vector Store');
        /* translators: %1$d: HTTP status code, %2$s: Error message from the API. */
        return new WP_Error('chroma_api_error', sprintf(__('Chroma API Error (%1$d): %2$s', 'gpt3-ai-content-generator'), $status_code, $error_msg), ['status' => $status_code]);
    }

    if (strtoupper($method) === 'DELETE' && in_array($status_code, [200, 202, 204], true) && empty($decoded_response)) {
        return ['deleted' => true];
    }

    return !empty($decoded_response) ? $decoded_response : ['status' => 'ok'];
}
