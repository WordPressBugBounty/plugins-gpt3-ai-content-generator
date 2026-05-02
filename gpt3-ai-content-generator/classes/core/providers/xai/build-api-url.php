<?php
// File: classes/core/providers/xai/build-api-url.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * @param XAIProviderStrategy $strategyInstance
 * @param string $operation
 * @param array<string, mixed> $params
 */
function build_api_url_logic(XAIProviderStrategy $strategyInstance, string $operation, array $params): string|WP_Error {
    $base_url = !empty($params['base_url']) ? rtrim((string) $params['base_url'], '/') : 'https://api.x.ai';
    $api_version = !empty($params['api_version']) ? trim((string) $params['api_version'], '/') : 'v1';

    if ($base_url === '') {
        return new WP_Error('missing_base_url_xai', __('xAI Base URL is required.', 'gpt3-ai-content-generator'));
    }
    if ($api_version === '') {
        return new WP_Error('missing_api_version_xai', __('xAI API Version is required.', 'gpt3-ai-content-generator'));
    }

    $paths = [
        'chat' => '/responses',
        'stream' => '/responses',
        'responses' => '/responses',
        'models' => '/models',
        'language_models' => '/language-models',
    ];

    if (!isset($paths[$operation])) {
        return new WP_Error(
            'unsupported_operation_xai',
            sprintf(
                /* translators: %s: The operation name. */
                __('Operation "%s" is not supported for xAI.', 'gpt3-ai-content-generator'),
                esc_html($operation)
            )
        );
    }

    $version_segment = '/' . $api_version;
    $path = $paths[$operation];

    if (strpos($base_url, $version_segment) !== false) {
        return $base_url . $path;
    }

    return $base_url . $version_segment . $path;
}
