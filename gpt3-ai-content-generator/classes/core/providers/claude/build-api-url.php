<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build Anthropic API URL.
 */
function build_api_url_logic(ClaudeProviderStrategy $strategyInstance, string $operation, array $params): string|WP_Error {
    $base_url = !empty($params['base_url']) ? rtrim((string) $params['base_url'], '/') : '';
    if ($base_url === '') {
        return new WP_Error('missing_base_url_claude', __('Claude base URL is required.', 'gpt3-ai-content-generator'));
    }

    $paths = [
        'chat' => '/v1/messages',
        'stream' => '/v1/messages',
        'models' => '/v1/models',
    ];

    if (!isset($paths[$operation])) {
        /* translators: %s: Operation name. */
        return new WP_Error('unsupported_operation_claude', sprintf(__('Operation "%s" is not supported for Claude.', 'gpt3-ai-content-generator'), $operation));
    }

    $path = $paths[$operation];
    if (strpos($base_url, '/v1') === (strlen($base_url) - 3) && strpos($path, '/v1/') === 0) {
        $path = substr($path, 3);
    }

    return $base_url . $path;
}
