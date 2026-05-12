<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/stream/contexts/ai-forms/process/validate-request.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Contexts\AIForms\Process;

use WPAICG\Core\Stream\Contexts\AIForms\SSEAIFormsStreamContextHandler;
use WPAICG\Core\TokenManager\Constants\GuestTableConstants;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

require_once WPAICG_PLUGIN_DIR . 'classes/ai-forms/core/pricing/fn-build-ai-form-pricing-check-context.php';

/**
 * Checks whether the saved AI Form contains at least one image-upload element.
 *
 * @param mixed $form_structure
 * @return bool
 */
function form_structure_has_image_upload_field($form_structure): bool
{
    if (!is_array($form_structure)) {
        return false;
    }

    foreach ($form_structure as $row) {
        if (!is_array($row) || empty($row['columns']) || !is_array($row['columns'])) {
            continue;
        }

        foreach ($row['columns'] as $column) {
            if (!is_array($column) || empty($column['elements']) || !is_array($column['elements'])) {
                continue;
            }

            foreach ($column['elements'] as $element) {
                if (is_array($element) && ($element['type'] ?? '') === 'image-upload') {
                    return true;
                }
            }
        }
    }

    return false;
}

/**
 * Validates the request and checks token limits for an AI Forms stream request.
 *
 * @param SSEAIFormsStreamContextHandler $handlerInstance The instance of the context handler.
 * @param array $cached_data Contains form data retrieved from the cache.
 * @param array $get_params  Original $_GET parameters from the SSE request.
 * @return array|WP_Error An array of validated parameters or a WP_Error on failure.
 */
function validate_request_logic(
    SSEAIFormsStreamContextHandler $handlerInstance,
    array $cached_data,
    array $get_params
): array|WP_Error {
    // 1. Extract and Sanitize Parameters
    $user_id           = $cached_data['user_id'] ?? get_current_user_id();
    $form_id           = $cached_data['form_id'] ?? 0;
    $user_input_values = $cached_data['user_input_values'] ?? [];
    $image_inputs      = isset($cached_data['image_inputs']) && is_array($cached_data['image_inputs']) ? $cached_data['image_inputs'] : null;
    $conversation_uuid = $cached_data['conversation_uuid'] ?? wp_generate_uuid4();
    $session_id        = isset($get_params['session_id']) ? sanitize_text_field(wp_unslash($get_params['session_id'])) : '';

    // 2. Validate Essential Parameters
    if (empty($form_id)) {
        return new WP_Error('missing_form_id_ai_forms_logic', __('Form ID is missing for AI Forms stream.', 'gpt3-ai-content-generator'), ['status' => 400]);
    }
    if (empty($user_input_values) && empty($image_inputs)) {
        return new WP_Error('missing_input_values_ai_forms_logic', __('User input values are missing for AI Forms stream.', 'gpt3-ai-content-generator'), ['status' => 400]);
    }

    $submitted_fields = [];
    foreach ($user_input_values as $raw_key => $value) {
        $key_match = [];
        if (preg_match('/aipkit_form_field\[(.*?)\]/', (string) $raw_key, $key_match)) {
            $submitted_fields[$key_match[1]] = $value;
            continue;
        }

        $submitted_fields[$raw_key] = $value;
    }

    $form_config = $handlerInstance->get_ai_form_storage()->get_form_data($form_id);
    if (is_wp_error($form_config)) {
        return $form_config;
    }

    if (!empty($image_inputs)) {
        $is_pro = class_exists('\WPAICG\aipkit_dashboard') && \WPAICG\aipkit_dashboard::is_pro_plan();
        if (!$is_pro) {
            return new WP_Error('image_upload_requires_pro_ai_forms_logic', __('Image upload is a paid AI Forms feature.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }

        if (!form_structure_has_image_upload_field($form_config['structure'] ?? [])) {
            return new WP_Error('image_upload_field_missing_ai_forms_logic', __('This AI Form is not configured to accept image uploads.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }
    }

    if (isset($submitted_fields['ai_provider']) && $submitted_fields['ai_provider'] !== '') {
        $form_config['ai_provider'] = sanitize_text_field((string) $submitted_fields['ai_provider']);
    }
    if (isset($submitted_fields['ai_model']) && $submitted_fields['ai_model'] !== '') {
        $form_config['ai_model'] = sanitize_text_field((string) $submitted_fields['ai_model']);
    }

    // 3. Perform Token Check
    $token_manager = $handlerInstance->get_token_manager();
    if (!$token_manager) {
        return new WP_Error('dependency_missing_token_manager', 'Token manager component is unavailable.', ['status' => 500]);
    }

    $context_id_for_tokens = !$user_id ? GuestTableConstants::AI_FORMS_GUEST_CONTEXT_ID : null;
    $usage_context = \WPAICG\AIForms\Core\Pricing\build_ai_form_pricing_check_context_logic(
        $form_id,
        $form_config,
        $submitted_fields,
        $image_inputs
    );
    $token_check_result = $token_manager->check_and_reset_tokens($user_id ?: null, $session_id, $context_id_for_tokens, 'ai_forms', $usage_context);

    if (is_wp_error($token_check_result)) {
        return $token_check_result;
    }

    // 4. Return sanitized and validated parameters
    return [
        'user_id'           => $user_id,
        'form_id'           => $form_id,
        'user_input_values' => $submitted_fields,
        'image_inputs'      => $image_inputs,
        'form_config'       => $form_config,
        'conversation_uuid' => $conversation_uuid,
        'session_id'        => $session_id,
        'client_ip'         => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : null,
    ];
}
