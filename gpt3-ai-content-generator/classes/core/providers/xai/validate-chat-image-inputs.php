<?php
// File: classes/core/providers/xai/validate-chat-image-inputs.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('add_filter')) {
    add_filter('aipkit_chat_image_input_validation_error', __NAMESPACE__ . '\\xai_validate_chat_image_inputs', 10, 6);
}

/**
 * @param mixed $validation_error Existing validation error.
 * @param mixed $image_inputs Normalized image input payload.
 * @param mixed $provider Selected provider.
 * @param mixed $model Selected model.
 * @return mixed
 */
function xai_validate_chat_image_inputs($validation_error, $image_inputs, $provider, $model = '', $bot_settings = [], $flow = '') {
    if (is_wp_error($validation_error) || (string) $provider !== 'xAI') {
        return $validation_error;
    }

    if (!is_array($image_inputs)) {
        return new WP_Error(
            'xai_invalid_image_payload',
            __('Invalid image upload payload for xAI.', 'gpt3-ai-content-generator'),
            ['status' => 400]
        );
    }

    if (!class_exists('\WPAICG\AIPKit_Providers') && defined('WPAICG_PLUGIN_DIR')) {
        $providers_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_providers.php';
        if (file_exists($providers_path)) {
            require_once $providers_path;
        }
    }
    if (class_exists('\WPAICG\AIPKit_Providers') && !\WPAICG\AIPKit_Providers::xai_model_supports_image_input((string) $model)) {
        return new WP_Error(
            'xai_model_no_image_input',
            __('The selected xAI model does not support image analysis.', 'gpt3-ai-content-generator'),
            ['status' => 400]
        );
    }

    $allowed_mime_types = [
        'image/jpeg' => true,
        'image/jpg' => true,
        'image/png' => true,
    ];
    foreach ($image_inputs as $image_input) {
        if (!is_array($image_input)) {
            return new WP_Error(
                'xai_invalid_image_payload',
                __('Invalid image upload payload for xAI.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }

        $mime_type = isset($image_input['type']) ? strtolower(trim((string) $image_input['type'])) : '';
        if ($mime_type === '' || !isset($allowed_mime_types[$mime_type])) {
            return new WP_Error(
                'xai_unsupported_image_type',
                __('xAI image analysis supports JPG and PNG images only.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }
    }

    return $validation_error;
}
