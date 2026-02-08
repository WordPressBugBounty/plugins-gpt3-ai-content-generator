<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/images/manager/ajax/ajax_generate_image.php
// Status: MODIFIED

namespace WPAICG\Images\Manager\Ajax;

use WPAICG\Images\AIPKit_Image_Manager;
use WPAICG\AIPKit_Role_Manager;
use WPAICG\Core\TokenManager\Constants\GuestTableConstants;
use WPAICG\Core\AIPKit_Content_Moderator;
use function WPAICG\Images\Manager\Utils\parse_edit_source_image_upload_logic;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function ajax_generate_image_logic(AIPKit_Image_Manager $managerInstance): void
{
    // Unslash all POST data at the beginning for security
    $post_data = wp_unslash($_POST);
    // Sanitize SERVER variable
    $client_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : null;

    $user_id = get_current_user_id();
    $is_logged_in = $user_id > 0;
    $session_id_from_post = isset($post_data['session_id']) ? sanitize_text_field($post_data['session_id']) : null;
    $session_id_for_guest = $is_logged_in ? null : $client_ip;
    if (!$is_logged_in && empty($session_id_for_guest) && !empty($session_id_from_post)) {
        $session_id_for_guest = $session_id_from_post;
    }

    $request_time = time();
    $conversation_uuid = 'imagegen-' . $request_time . '-' . wp_generate_password(12, false);
    $error_response = null;
    $usage_data = null;
    $bot_response_message_id = null;

    $nonce_action = 'aipkit_nonce';
    if (isset($post_data['_ajax_nonce']) && wp_verify_nonce(sanitize_key($post_data['_ajax_nonce']), 'aipkit_image_generator_nonce')) {
        $nonce_action = 'aipkit_image_generator_nonce';
    } elseif (!check_ajax_referer($nonce_action, '_ajax_nonce', false)) {
        $error_response = new WP_Error('nonce_failure', __('Security check failed (nonce).', 'gpt3-ai-content-generator'), ['status' => 403]);
        $managerInstance->log_image_generation_attempt($conversation_uuid, $post_data['prompt'] ?? '', $post_data, $error_response, null, $user_id, $session_id_for_guest, $client_ip);
        $managerInstance->send_wp_error($error_response);
        return;
    }

    if ($is_logged_in && !AIPKit_Role_Manager::user_can_access_module($managerInstance::MODULE_SLUG)) {
        $error_response = new WP_Error('permission_denied', __('You do not have permission to use the Image Generator.', 'gpt3-ai-content-generator'), ['status' => 403]);
        $managerInstance->log_image_generation_attempt($conversation_uuid, $post_data['prompt'] ?? '', $post_data, $error_response, null, $user_id, $session_id_for_guest, $client_ip);
        $managerInstance->send_wp_error($error_response);
        return;
    }

    $prompt = isset($post_data['prompt']) ? sanitize_textarea_field($post_data['prompt']) : '';
    if (empty($prompt)) {
        $error_response = new WP_Error('missing_prompt', __('Image prompt cannot be empty.', 'gpt3-ai-content-generator'), ['status' => 400]);
        $managerInstance->log_image_generation_attempt($conversation_uuid, $prompt, $post_data, $error_response, null, $user_id, $session_id_for_guest, $client_ip);
        $managerInstance->send_wp_error($error_response);
        return;
    }

    $provider = isset($post_data['provider']) ? sanitize_text_field($post_data['provider']) : 'OpenAI';
    $image_mode = isset($post_data['image_mode']) ? sanitize_key($post_data['image_mode']) : 'generate';
    if (!in_array($image_mode, ['generate', 'edit'], true)) {
        $image_mode = 'generate';
    }

    // --- ADDED: Content Moderation Check ---
    if (class_exists(AIPKit_Content_Moderator::class)) {
        $moderation_context = [
            'client_ip' => $client_ip,
            'bot_settings' => ['provider' => $provider], // Provide a minimal settings array for the check
            'skip_banned_checks' => true,
        ];
        $moderation_check = AIPKit_Content_Moderator::check_content($prompt, $moderation_context);
        if (is_wp_error($moderation_check)) {
            // Log the moderation failure and send error response
            $managerInstance->log_image_generation_attempt($conversation_uuid, $prompt, $post_data, $moderation_check, null, $user_id, $session_id_for_guest, $client_ip);
            $managerInstance->send_wp_error($moderation_check);
            return;
        }
    }
    // --- END ADDED ---

    $source_image_payload = null;
    if ($image_mode === 'edit') {
        $edit_provider = strtolower($provider);
        if (!in_array($edit_provider, ['google', 'openai', 'openrouter'], true)) {
            $provider_error = new WP_Error(
                'image_edit_provider_unsupported',
                __('Image editing is currently supported only for Google, OpenAI and OpenRouter providers.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
            $managerInstance->log_image_generation_attempt($conversation_uuid, $prompt, $post_data, $provider_error, null, $user_id, $session_id_for_guest, $client_ip);
            $managerInstance->send_wp_error($provider_error);
            return;
        }

        $source_image_payload = parse_edit_source_image_upload_logic($_FILES);
        if (is_wp_error($source_image_payload)) {
            $managerInstance->log_image_generation_attempt($conversation_uuid, $prompt, $post_data, $source_image_payload, null, $user_id, $session_id_for_guest, $client_ip);
            $managerInstance->send_wp_error($source_image_payload);
            return;
        }
    }

    $num_images_to_generate = isset($post_data['n']) ? absint($post_data['n']) : 1;
    $num_images_to_generate = max(1, $num_images_to_generate);

    $token_manager = $managerInstance->get_token_manager();
    if ($token_manager) {
        $token_check_result = null;
        $context_id_for_token_check = $is_logged_in ? GuestTableConstants::IMG_GEN_GUEST_CONTEXT_ID : GuestTableConstants::IMG_GEN_GUEST_CONTEXT_ID;
        $token_check_result = $token_manager->check_and_reset_tokens($user_id ?: null, $session_id_for_guest, $context_id_for_token_check, 'image_generator');

        if (is_wp_error($token_check_result)) {
            $managerInstance->log_image_generation_attempt($conversation_uuid, $prompt, $post_data, $token_check_result, null, $user_id, $session_id_for_guest, $client_ip);
            $managerInstance->send_wp_error($token_check_result);
            return;
        }
    }

    // --- MODIFICATION: Changed user identifier format ---
    $user_identifier = $is_logged_in ? (string)$user_id : 'guest';
    // --- END MODIFICATION ---

    $runtime_options = array_filter([
        'image_mode' => $image_mode,
        'provider' => $provider,
        'model' => isset($post_data['model']) ? sanitize_text_field($post_data['model']) : null,
        'size' => isset($post_data['size']) ? sanitize_text_field($post_data['size']) : null,
        'n' => $num_images_to_generate,
        'quality' => isset($post_data['quality']) ? sanitize_text_field($post_data['quality']) : null,
        'style' => isset($post_data['style']) ? sanitize_text_field($post_data['style']) : null,
        'response_format' => isset($post_data['response_format']) ? sanitize_text_field($post_data['response_format']) : 'url',
        'user' => $user_identifier,
    ], function ($value) { return $value !== null; });

    if ($image_mode === 'edit' && is_array($source_image_payload)) {
        $runtime_options['source_image'] = $source_image_payload;
    }

    $request_options_for_log = $runtime_options;
    if (isset($request_options_for_log['source_image']) && is_array($request_options_for_log['source_image'])) {
        $request_options_for_log['source_image'] = [
            'mime_type' => $request_options_for_log['source_image']['mime_type'] ?? '',
            'size_bytes' => $request_options_for_log['source_image']['size_bytes'] ?? 0,
            'file_name' => $request_options_for_log['source_image']['file_name'] ?? '',
        ];
    }

    $gpt_image_models = ['gpt-image-1.5', 'gpt-image-1', 'gpt-image-1-mini'];
    if (strtolower($provider) === 'openai' && in_array(($runtime_options['model'] ?? ''), $gpt_image_models, true)) {
        $runtime_options['output_format'] = 'png';
        unset($runtime_options['response_format']);
    }

    $result = $managerInstance->generate_image($prompt, $runtime_options, $is_logged_in ? $user_id : null);
    $images_array = [];
    $videos_array = [];
    $usage_data = null;

    if (!is_wp_error($result)) {
        // Check if this is an async video operation
        if (isset($result['status']) && $result['status'] === 'processing') {
            
            // Log the attempt as processing
            $managerInstance->log_image_generation_attempt(
                $conversation_uuid,
                $prompt,
                $request_options_for_log,
                $result,
                null, // No usage data yet
                $user_id,
                $session_id_for_guest,
                $client_ip,
                null,
                !$is_logged_in ? null : implode(', ', wp_get_current_user()->roles),
                $bot_response_message_id
            );
            
            // Return async operation info
            wp_send_json_success([
                'status' => 'processing',
                'operation_name' => $result['operation_name'],
                'message' => $result['message']
            ]);
            return;
        }
        
        // Handle completed generation (images or videos)
        $images_array = $result['images'] ?? [];
        $videos_array = $result['videos'] ?? [];
        $usage_data = $result['usage'] ?? null;
        
        $media_generated_count = count($images_array) + count($videos_array);
        $tokens_to_record = $usage_data['total_tokens'] ?? ($media_generated_count * $managerInstance::TOKENS_PER_IMAGE);

        if ($token_manager && $tokens_to_record > 0) {
            $context_id_for_token_record = $is_logged_in ? GuestTableConstants::IMG_GEN_GUEST_CONTEXT_ID : GuestTableConstants::IMG_GEN_GUEST_CONTEXT_ID;
            $token_manager->record_token_usage($user_id ?: null, $session_id_for_guest, $context_id_for_token_record, $tokens_to_record, 'image_generator');
        }
    }

    $user_wp_role = !$is_logged_in ? null : implode(', ', wp_get_current_user()->roles);
    $managerInstance->log_image_generation_attempt(
        $conversation_uuid,
        $prompt,
        $request_options_for_log,
        $result,
        $usage_data,
        $user_id,
        $session_id_for_guest,
        $client_ip,
        null,
        $user_wp_role,
        $bot_response_message_id
    );

    if (is_wp_error($result)) {
        $managerInstance->send_wp_error($result);
    } else {
        // Return appropriate success response
        if (!empty($videos_array)) {
            wp_send_json_success([
                /* translators: %d: Number of videos generated. */
                'message' => sprintf(_n('%d video generated successfully.', '%d videos generated successfully.', count($videos_array), 'gpt3-ai-content-generator'), count($videos_array)),
                'videos' => $videos_array
            ]);
        } else {
            wp_send_json_success([
                /* translators: %d: Number of images generated. */
                'message' => sprintf(_n('%d image generated successfully.', '%d images generated successfully.', count($images_array), 'gpt3-ai-content-generator'), count($images_array)),
                'images' => $images_array
            ]);
        }
    }
}
