<?php

namespace WPAICG\Core\Providers;

use WP_Error;

if (!class_exists(BaseProviderStrategy::class)) {
    $base_strategy_path = WPAICG_PLUGIN_DIR . 'classes/core/providers/base-provider-strategy.php';
    if (file_exists($base_strategy_path)) {
        require_once $base_strategy_path;
    } else {
        return;
    }
}

$methods_path = __DIR__ . '/';
require_once $methods_path . '_shared-format.php';
require_once $methods_path . 'build-api-url.php';
require_once $methods_path . 'get-api-headers.php';
require_once $methods_path . 'format-chat-payload.php';
require_once $methods_path . 'parse-chat-response.php';
require_once $methods_path . 'parse-error-response.php';
require_once $methods_path . 'get-models.php';
require_once $methods_path . 'build-sse-payload.php';
require_once $methods_path . 'parse-sse-chunk.php';
require_once $methods_path . 'generate-embeddings.php';

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Claude (Anthropic) provider strategy.
 */
class ClaudeProviderStrategy extends BaseProviderStrategy {

    public function build_api_url(string $operation, array $params): string|WP_Error {
        return \WPAICG\Core\Providers\Claude\Methods\build_api_url_logic($this, $operation, $params);
    }

    public function get_api_headers(string $api_key, string $operation): array {
        return \WPAICG\Core\Providers\Claude\Methods\get_api_headers_logic($this, $api_key, $operation);
    }

    public function format_chat_payload(string $user_message, string $instructions, array $history, array $ai_params, string $model): array {
        return \WPAICG\Core\Providers\Claude\Methods\format_chat_payload_logic($this, $user_message, $instructions, $history, $ai_params, $model);
    }

    public function parse_chat_response(array $decoded_response, array $request_data): array|WP_Error {
        return \WPAICG\Core\Providers\Claude\Methods\parse_chat_response_logic($this, $decoded_response, $request_data);
    }

    public function parse_error_response($response_body, int $status_code): string {
        return \WPAICG\Core\Providers\Claude\Methods\parse_error_response_logic($this, $response_body, $status_code);
    }

    public function get_models(array $api_params): array|WP_Error {
        return \WPAICG\Core\Providers\Claude\Methods\get_models_logic($this, $api_params);
    }

    public function build_sse_payload(array $messages, $system_instruction, array $ai_params, string $model): array {
        return \WPAICG\Core\Providers\Claude\Methods\build_sse_payload_logic($this, $messages, $system_instruction, $ai_params, $model);
    }

    public function parse_sse_chunk(string $sse_chunk, string &$current_buffer): array {
        return \WPAICG\Core\Providers\Claude\Methods\parse_sse_chunk_logic($this, $sse_chunk, $current_buffer);
    }

    public function generate_embeddings($input, array $api_params, array $options = []): array|WP_Error {
        return \WPAICG\Core\Providers\Claude\Methods\generate_embeddings_logic($this, $input, $api_params, $options);
    }
}
