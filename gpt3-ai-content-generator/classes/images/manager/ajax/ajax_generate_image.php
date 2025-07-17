<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/images/manager/ajax/ajax_generate_image.php
// Status: MODIFIED

namespace WPAICG\Images\Manager\Ajax;

use WPAICG\Images\AIPKit_Image_Manager;
use WPAICG\AIPKit_Role_Manager;
use WPAICG\aipkit_dashboard;
use WPAICG\AIPKit\Addons\AIPKit_IP_Anonymization;
use WPAICG\Core\TokenManager\Constants\GuestTableConstants;
use WPAICG\Core\AIPKit_Content_Moderator;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function ajax_generate_image_logic(AIPKit_Image_Manager $managerInstance): void
{
    $user_id = get_current_user_id();
    $is_logged_in = $user_id > 0;
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $session_id_from_post = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
    $session_id_for_guest = $is_logged_in ? null : AIPKit_IP_Anonymization::maybe_anonymize($client_ip);
    if (!$is_logged_in && empty($session_id_for_guest) && !empty($session_id_from_post)) {
        $session_id_for_guest = $session_id_from_post;
    }

    $request_time = time();
    $conversation_uuid = 'imagegen-' . $request_time . '-' . wp_generate_password(12, false);
    $error_response = null;
    $usage_data = null;
    $bot_response_message_id = null;

    $nonce_action = 'aipkit_nonce';
    if (isset($_POST['_ajax_nonce']) && wp_verify_nonce(sanitize_key($_POST['_ajax_nonce']), 'aipkit_image_generator_nonce')) {
        $nonce_action = 'aipkit_image_generator_nonce';
    } elseif (!check_ajax_referer($nonce_action, '_ajax_nonce', false)) {
        $error_response = new WP_Error('nonce_failure', __('Security check failed (nonce).', 'gpt3-ai-content-generator'), ['status' => 403]);
        $managerInstance->log_image_generation_attempt($conversation_uuid, $_POST['prompt'] ?? '', $_POST, $error_response, null, $user_id, $session_id_for_guest, $client_ip);
        $managerInstance->send_wp_error($error_response);
        return;
    }

    if ($is_logged_in && !AIPKit_Role_Manager::user_can_access_module($managerInstance::MODULE_SLUG)) {
        $error_response = new WP_Error('permission_denied', __('You do not have permission to use the Image Generator.', 'gpt3-ai-content-generator'), ['status' => 403]);
        $managerInstance->log_image_generation_attempt($conversation_uuid, $_POST['prompt'] ?? '', $_POST, $error_response, null, $user_id, $session_id_for_guest, $client_ip);
        $managerInstance->send_wp_error($error_response);
        return;
    }

    $prompt = isset($_POST['prompt']) ? sanitize_textarea_field(wp_unslash($_POST['prompt'])) : '';
    if (empty($prompt)) {
        $error_response = new WP_Error('missing_prompt', __('Image prompt cannot be empty.', 'gpt3-ai-content-generator'), ['status' => 400]);
        $managerInstance->log_image_generation_attempt($conversation_uuid, $prompt, $_POST, $error_response, null, $user_id, $session_id_for_guest, $client_ip);
        $managerInstance->send_wp_error($error_response);
        return;
    }

    // --- ADDED: Content Moderation Check ---
    $provider_for_moderation = isset($_POST['provider']) ? sanitize_text_field($_POST['provider']) : 'OpenAI';
    if (class_exists(AIPKit_Content_Moderator::class)) {
        $moderation_context = [
            'client_ip' => $client_ip,
            'bot_settings' => ['provider' => $provider_for_moderation] // Provide a minimal settings array for the check
        ];
        $moderation_check = AIPKit_Content_Moderator::check_content($prompt, $moderation_context);
        if (is_wp_error($moderation_check)) {
            // Log the moderation failure and send error response
            $managerInstance->log_image_generation_attempt($conversation_uuid, $prompt, $_POST, $moderation_check, null, $user_id, $session_id_for_guest, $client_ip);
            $managerInstance->send_wp_error($moderation_check);
            return;
        }
    }
    // --- END ADDED ---

    $num_images_to_generate = isset($_POST['n']) ? absint($_POST['n']) : 1;
    $num_images_to_generate = max(1, $num_images_to_generate);

    $token_manager = $managerInstance->get_token_manager();
    if (aipkit_dashboard::is_addon_active('token_management') && $token_manager) {
        $token_check_result = null;
        $context_id_for_token_check = $is_logged_in ? GuestTableConstants::IMG_GEN_GUEST_CONTEXT_ID : GuestTableConstants::IMG_GEN_GUEST_CONTEXT_ID;
        $token_check_result = $token_manager->check_and_reset_tokens($user_id ?: null, $session_id_for_guest, $context_id_for_token_check, 'image_generator');

        if (is_wp_error($token_check_result)) {
            $managerInstance->log_image_generation_attempt($conversation_uuid, $prompt, $_POST, $token_check_result, null, $user_id, $session_id_for_guest, $client_ip);
            $managerInstance->send_wp_error($token_check_result);
            return;
        }
    }

    $provider = isset($_POST['provider']) ? sanitize_text_field($_POST['provider']) : 'OpenAI';
    $user_identifier = $is_logged_in ? 'wp_user_' . $user_id : ('guest_ip_' . ($session_id_for_guest ?? 'unknown'));

    $runtime_options = array_filter([
        'provider' => $provider,
        'model' => isset($_POST['model']) ? sanitize_text_field($_POST['model']) : null,
        'size' => isset($_POST['size']) ? sanitize_text_field($_POST['size']) : null,
        'n' => $num_images_to_generate,
        'quality' => isset($_POST['quality']) ? sanitize_text_field($_POST['quality']) : null,
        'style' => isset($_POST['style']) ? sanitize_text_field($_POST['style']) : null,
        'response_format' => isset($_POST['response_format']) ? sanitize_text_field($_POST['response_format']) : 'url',
        'user' => $user_identifier,
    ], function ($value) { return $value !== null; });

    if (strtolower($provider) === 'openai' && ($runtime_options['model'] ?? '') === 'gpt-image-1') {
        $runtime_options['output_format'] = 'png';
        unset($runtime_options['response_format']);
    }

    $result = $managerInstance->generate_image($prompt, $runtime_options, $is_logged_in ? $user_id : null);
    $images_array = [];
    $usage_data = null;

    if (!is_wp_error($result)) {
        $images_array = $result['images'] ?? [];
        $usage_data = $result['usage'] ?? null;
        $images_generated_count = count($images_array);
        $tokens_to_record = $usage_data['total_tokens'] ?? ($images_generated_count * $managerInstance::TOKENS_PER_IMAGE);

        if (aipkit_dashboard::is_addon_active('token_management') && $tokens_to_record > 0 && $token_manager) {
            $context_id_for_token_record = $is_logged_in ? GuestTableConstants::IMG_GEN_GUEST_CONTEXT_ID : GuestTableConstants::IMG_GEN_GUEST_CONTEXT_ID;
            $token_manager->record_token_usage($user_id ?: null, $session_id_for_guest, $context_id_for_token_record, $tokens_to_record, 'image_generator');
        }
    }

    $user_wp_role = !$is_logged_in ? null : implode(', ', wp_get_current_user()->roles);
    $managerInstance->log_image_generation_attempt(
        $conversation_uuid,
        $prompt,
        $runtime_options,
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
        wp_send_json_success([
            /* translators: %d: Number of images generated. */
            'message' => sprintf(_n('%d image generated successfully.', '%d images generated successfully.', count($images_array), 'gpt3-ai-content-generator'), count($images_array)),
            'images' => $images_array
        ]);
    }
}
