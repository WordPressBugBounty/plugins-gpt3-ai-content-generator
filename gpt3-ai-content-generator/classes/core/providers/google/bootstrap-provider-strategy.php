<?php

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

namespace WPAICG\Core\Providers\Google;

use WP_Error;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsErrorParser;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/interactions/bootstrap.php';
require_once __DIR__ . '/files/bootstrap.php';
require_once __DIR__ . '/file-search/bootstrap.php';
require_once __DIR__ . '/methods.php';

class GoogleUrlBuilder {

    /**
     * @return string|\WP_Error
     */
    public static function build(string $operation, array $params) {
        return Methods\build_logic_for_url_builder($operation, $params);
    }
}

class GooglePayloadFormatter {
    public static function format_embeddings($input, array $options): array {
        return Methods\format_embeddings_logic_for_payload_formatter($input, $options);
    }
}

class GoogleResponseParser {
    public static function parse_error($response_body, int $status_code): string {
        $parsed = GoogleInteractionsErrorParser::parse($response_body, $status_code);
        return (string) $parsed['message'];
    }

    /**
     * @return mixed[]|\WP_Error
     */
    public static function parse_embeddings(array $decoded_response) {
        return Methods\parse_embeddings_logic_for_response_parser($decoded_response);
    }
}

namespace WPAICG\Core\Providers;

use WP_Error;
use WPAICG\AIPKit_Providers;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsClient;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsResponseParser;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsStreamParser;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsTextAdapter;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsUrlBuilder;

if (!class_exists(BaseProviderStrategy::class)) {
    $base_strategy_path = WPAICG_PLUGIN_DIR . 'classes/core/providers/base-provider-strategy.php';
    if (file_exists($base_strategy_path)) {
        require_once $base_strategy_path;
    } else {
        return;
    }
}

class GoogleProviderStrategy extends BaseProviderStrategy {

    /**
     * @return string|\WP_Error
     */
    public function build_api_url(string $operation, array $params) {
        if ($operation === 'chat' || $operation === 'stream') {
            return GoogleInteractionsUrlBuilder::build($params);
        }
        return Google\Methods\build_api_url_logic($this, $operation, $params);
    }

    public function get_api_headers(string $api_key, string $operation): array {
        if ($operation === 'chat' || $operation === 'stream') {
            return GoogleInteractionsClient::headers($api_key, $operation === 'stream');
        }
        return Google\Methods\get_api_headers_logic($this, $api_key, $operation);
    }

    public function format_chat_payload(string $user_message, string $instructions, array $history, array $ai_params, string $model) {
        $model = AIPKit_Providers::normalize_google_text_model($model);
        return GoogleInteractionsTextAdapter::build($user_message, $instructions, $history, $ai_params, $model);
    }

    /**
     * @return mixed[]|\WP_Error
     */
    public function parse_chat_response(array $decoded_response, array $request_data) {
        return GoogleInteractionsResponseParser::parse_text($decoded_response);
    }

    public function parse_error_response($response_body, int $status_code): string {
        return Google\Methods\parse_error_response_logic($this, $response_body, $status_code);
    }

    /**
     * @return mixed[]|\WP_Error
     */
    public function get_models(array $api_params) {
        return Google\Methods\get_models_logic($this, $api_params);
    }

    public function build_sse_payload(array $messages, $system_instruction, array $ai_params, string $model) {
        $ai_params['stream'] = true;
        $model = AIPKit_Providers::normalize_google_text_model($model);
        return GoogleInteractionsTextAdapter::build('', (string) $system_instruction, $messages, $ai_params, $model);
    }

    public function parse_sse_chunk(string $sse_chunk, string &$current_buffer): array {
        return GoogleInteractionsStreamParser::parse($sse_chunk, $current_buffer);
    }

    /**
     * @return mixed[]|\WP_Error
     */
    public function generate_embeddings($input, array $api_params, array $options = []) {
        return Google\Methods\generate_embeddings_logic($this, $input, $api_params, $options);
    }

    public function format_google_model_list_public(array $raw_models): array {
        return Google\Methods\format_google_model_list_logic($this, $raw_models);
    }
}
