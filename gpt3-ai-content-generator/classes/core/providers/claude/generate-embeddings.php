<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Claude does not provide native embeddings in this strategy.
 */
function generate_embeddings_logic(
    ClaudeProviderStrategy $strategyInstance,
    $input,
    array $api_params,
    array $options = []
): array|WP_Error {
    return new WP_Error(
        'embeddings_not_supported_claude',
        __('Embedding generation is not supported for Claude in this provider strategy.', 'gpt3-ai-content-generator')
    );
}
