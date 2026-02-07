<?php
// File: classes/core/providers/openrouter/generate-embeddings.php
// Status: NEW FILE

namespace WPAICG\Core\Providers\OpenRouter\Methods;

use WPAICG\Core\Providers\OpenRouterProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the generate_embeddings method of OpenRouterProviderStrategy.
 *
 * @param OpenRouterProviderStrategy $strategyInstance The instance of the strategy class.
 * @param string|array $input The input text or array of texts.
 * @param array $api_params Provider-specific API connection parameters.
 * @param array $options Embedding options (model, dimensions, encoding_format, etc.).
 * @return array|WP_Error ['embeddings' => array, 'usage' => array|null] or WP_Error.
 */
function generate_embeddings_logic(
    OpenRouterProviderStrategy $strategyInstance,
    $input,
    array $api_params,
    array $options = []
): array|WP_Error {
    $model = isset($options['model']) ? sanitize_text_field((string) $options['model']) : '';
    if ($model === '') {
        return new WP_Error(
            'missing_openrouter_embedding_model_logic',
            __('OpenRouter embedding model ID is required.', 'gpt3-ai-content-generator')
        );
    }

    $url = $strategyInstance->build_api_url('embeddings', $api_params);
    if (is_wp_error($url)) {
        return $url;
    }

    $headers = $strategyInstance->get_api_headers($api_params['api_key'] ?? '', 'embeddings');
    $request_options = $strategyInstance->get_request_options('embeddings');

    $payload = [
        'model' => $model,
        'input' => $input,
    ];

    if (isset($options['dimensions']) && absint($options['dimensions']) > 0) {
        $payload['dimensions'] = absint($options['dimensions']);
    }

    if (isset($options['encoding_format'])) {
        $encoding_format = sanitize_key((string) $options['encoding_format']);
        if (in_array($encoding_format, ['float', 'base64'], true)) {
            $payload['encoding_format'] = $encoding_format;
        }
    }

    $request_body_json = wp_json_encode($payload);
    $response = wp_remote_post(
        $url,
        array_merge(
            $request_options,
            [
                'headers' => $headers,
                'body'    => $request_body_json,
            ]
        )
    );

    if (is_wp_error($response)) {
        return new WP_Error(
            'openrouter_embedding_http_error_logic',
            __('HTTP error during OpenRouter embedding generation.', 'gpt3-ai-content-generator')
        );
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $decoded_response = $strategyInstance->decode_json($body, 'OpenRouter Embeddings');

    if ($status_code !== 200 || is_wp_error($decoded_response)) {
        $error_msg = is_wp_error($decoded_response)
            ? $decoded_response->get_error_message()
            : $strategyInstance->parse_error_response($body, $status_code);
        /* translators: %1$d: HTTP status code, %2$s: API error message. */
        return new WP_Error(
            'openrouter_embedding_api_error_logic',
            sprintf(__('OpenRouter Embeddings API Error (%1$d): %2$s', 'gpt3-ai-content-generator'), $status_code, esc_html($error_msg))
        );
    }

    $embeddings = [];
    if (isset($decoded_response['data']) && is_array($decoded_response['data'])) {
        foreach ($decoded_response['data'] as $item) {
            if (isset($item['embedding']) && is_array($item['embedding'])) {
                $embeddings[] = $item['embedding'];
            }
        }
    }

    if (empty($embeddings)) {
        return new WP_Error(
            'openrouter_embedding_no_data_logic',
            __('No embedding data found in OpenRouter response.', 'gpt3-ai-content-generator')
        );
    }

    $usage = null;
    if (isset($decoded_response['usage']) && is_array($decoded_response['usage'])) {
        $usage = [
            'input_tokens'  => $decoded_response['usage']['input_tokens'] ?? $decoded_response['usage']['prompt_tokens'] ?? 0,
            'total_tokens'  => $decoded_response['usage']['total_tokens'] ?? 0,
            'provider_raw'  => $decoded_response['usage'],
        ];
    }

    return [
        'embeddings' => $embeddings,
        'usage' => $usage,
    ];
}
