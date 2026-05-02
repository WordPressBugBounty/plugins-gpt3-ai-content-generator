<?php
// File: classes/core/providers/xai/generate-embeddings.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}
function generate_embeddings_logic(XAIProviderStrategy $strategyInstance, $input, array $api_params, array $options = []): array|WP_Error {
    return new WP_Error(
        'embeddings_not_supported_xai',
        __('Embedding generation is not supported by the xAI provider strategy.', 'gpt3-ai-content-generator'),
        ['status' => 501]
    );
}
