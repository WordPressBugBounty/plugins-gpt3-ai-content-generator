<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/ajax/class-chat-form-submission-ajax-handler.php
// Status: MODIFIED
// I have fixed the PHPCS warnings by properly unslashing and sanitizing all input from $_POST and $_SERVER.

namespace WPAICG\Chat\Frontend\Ajax;

use WPAICG\Chat\Admin\Ajax\Traits\Trait_CheckFrontendPermissions;
use WPAICG\Chat\Admin\Ajax\Traits\Trait_SendWPError;
use WPAICG\aipkit_dashboard;
use WPAICG\Chat\Storage\BotStorage;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles AJAX requests for chatbot form submissions from the frontend.
 */
class ChatFormSubmissionAjaxHandler {

    use Trait_CheckFrontendPermissions;
    use Trait_SendWPError;

    private $bot_storage;

    public function __construct() {
        if (class_exists(\WPAICG\Chat\Storage\BotStorage::class)) {
            $this->bot_storage = new \WPAICG\Chat\Storage\BotStorage();
        } else {
            $this->bot_storage = null;
        }
    }

    /**
     * AJAX handler for 'aipkit_handle_form_submission'.
     */
    public function ajax_handle_form_submission(): void {

        $permission_check = $this->check_frontend_permissions();
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_frontend_permissions().
        $post_data = wp_unslash($_POST);
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_frontend_permissions().
        $bot_id = isset($post_data['bot_id']) ? absint($post_data['bot_id']) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_frontend_permissions().
        $form_id = isset($post_data['form_id']) ? sanitize_text_field($post_data['form_id']) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_frontend_permissions().
        $submitted_data_json = isset($post_data['submitted_data']) ? wp_kses_post($post_data['submitted_data']) : '{}';
        // Optional compatibility payloads from newer frontend bundles.
        $submitted_data_display_json = isset($post_data['submitted_data_display']) ? wp_kses_post($post_data['submitted_data_display']) : '{}';
        $submitted_data_labels_json = isset($post_data['submitted_data_labels']) ? wp_kses_post($post_data['submitted_data_labels']) : '{}';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_frontend_permissions().
        $conversation_uuid = isset($post_data['conversation_uuid']) ? sanitize_key($post_data['conversation_uuid']) : '';
        
        $user_id = get_current_user_id();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_frontend_permissions().
        $session_id_from_post = isset($post_data['session_id']) ? sanitize_text_field($post_data['session_id']) : '';

        $final_session_id = ''; 
        if (!$user_id) { 
            if (!empty($session_id_from_post)) {
                $final_session_id = $session_id_from_post;
            }
        }
        
        $post_id_from_request = isset($post_data['post_id']) ? absint($post_data['post_id']) : 0;

        if (empty($bot_id) || empty($form_id) || empty($conversation_uuid)) {
            $this->send_wp_error(new WP_Error('missing_params', __('Missing required parameters (bot, form, or conversation ID).', 'gpt3-ai-content-generator'), ['status' => 400]));
            return;
        }

        $submitted_data = json_decode($submitted_data_json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($submitted_data)) {
            $this->send_wp_error(new WP_Error('invalid_submitted_data', __('Invalid submitted form data.', 'gpt3-ai-content-generator'), ['status' => 400]));
            return;
        }
        $submitted_data_display = json_decode($submitted_data_display_json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($submitted_data_display)) {
            $submitted_data_display = [];
        }
        $submitted_data_labels = json_decode($submitted_data_labels_json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($submitted_data_labels)) {
            $submitted_data_labels = [];
        }

        $sanitize_recursive_values = static function ($value) use (&$sanitize_recursive_values) {
            if (is_array($value)) {
                $sanitized = [];
                foreach ($value as $key => $item) {
                    $sanitized_key = sanitize_text_field((string) $key);
                    if ($sanitized_key === '') {
                        continue;
                    }
                    $sanitized[$sanitized_key] = $sanitize_recursive_values($item);
                }
                return $sanitized;
            }
            return sanitize_text_field((string) $value);
        };
        $sanitize_labels_map = static function (array $labels): array {
            $sanitized = [];
            foreach ($labels as $key => $label) {
                $sanitized_key = sanitize_text_field((string) $key);
                if ($sanitized_key === '') {
                    continue;
                }
                $sanitized[$sanitized_key] = sanitize_text_field((string) $label);
            }
            return $sanitized;
        };

        $submitted_data_display = $sanitize_recursive_values($submitted_data_display);
        $submitted_data_labels = $sanitize_labels_map($submitted_data_labels);

        // Backward-compatible fallbacks for older frontend bundles.
        if (empty($submitted_data_display) && !empty($submitted_data)) {
            $submitted_data_display = $sanitize_recursive_values($submitted_data);
        }
        if (empty($submitted_data_labels) && !empty($submitted_data)) {
            foreach ($submitted_data as $field_key => $_unused) {
                $sanitized_key = sanitize_text_field((string) $field_key);
                if ($sanitized_key !== '') {
                    $submitted_data_labels[$sanitized_key] = $sanitized_key;
                }
            }
        }

        if (!$user_id && empty($final_session_id)) {
             $this->send_wp_error(new WP_Error('missing_identifier', __('User or Session ID is required for guests.', 'gpt3-ai-content-generator'), ['status' => 400]));
             return;
        }


        $triggers_enabled = false;
        if (class_exists(\WPAICG\aipkit_dashboard::class)) {
            $triggers_enabled = \WPAICG\aipkit_dashboard::is_pro_plan();
        }

        $trigger_storage_class = '\WPAICG\Lib\Chat\Triggers\AIPKit_Trigger_Storage';
        $trigger_manager_class = '\WPAICG\Lib\Chat\Triggers\AIPKit_Trigger_Manager';
        
        $trigger_handler_function = '\WPAICG\Lib\Chat\Triggers\process_chat_triggers';

        if (!$triggers_enabled || !class_exists($trigger_storage_class) || !class_exists($trigger_manager_class) || !function_exists($trigger_handler_function)) {
            wp_send_json_success(['message' => __('Form submitted.', 'gpt3-ai-content-generator') . ' (' . __('Triggers not active or fully available.', 'gpt3-ai-content-generator') . ')']);
            return;
        }

        if (!$this->bot_storage) {
             $this->send_wp_error(new WP_Error('internal_error', __('Chat system (storage) not ready.', 'gpt3-ai-content-generator'), ['status' => 500]));
            return;
        }
        $bot_settings = $this->bot_storage->get_chatbot_settings($bot_id);
        if (empty($bot_settings)) {
            $this->send_wp_error(new WP_Error('bot_not_found', __('Chatbot configuration not found.', 'gpt3-ai-content-generator'), ['status' => 404]));
            return;
        }
        $enable_ip_anonymization = isset($bot_settings['enable_ip_anonymization']) && $bot_settings['enable_ip_anonymization'] === '1';

        $client_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : null;
        $http_referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        $user_wp_roles = $user_id ? (array) wp_get_current_user()->roles : ['guest'];
        $log_storage_instance = null;
        if (class_exists('\WPAICG\Chat\Storage\LogStorage')) {
            $log_storage_instance = new \WPAICG\Chat\Storage\LogStorage();
        }

        // --- MODIFIED: Populate base_log_data correctly for trigger context ---
        $base_log_data_for_triggers = [
            'bot_id'            => $bot_id,
            'user_id'           => $user_id ?: null,
            'session_id'        => $final_session_id,
            'conversation_uuid' => $conversation_uuid,
            'module'            => 'chat', // This ensures trigger meta-logs go to the right conversation
            'is_guest'          => ($user_id === 0 || $user_id === null),
            'ip_address'        => $client_ip,
            'ip_anonymize'      => $enable_ip_anonymization,
            'role'              => $user_wp_roles ? implode(', ', $user_wp_roles) : null,
        ];

        $trigger_context = [
            'event_type'            => 'form_submitted', // Added for clarity
            'bot_id'                => $bot_id,
            'form_id'               => $form_id,
            'submitted_data'        => $submitted_data,
            'submitted_data_json'   => $submitted_data_json,
            'submitted_data_display' => $submitted_data_display,
            'submitted_data_display_json' => wp_json_encode($submitted_data_display, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'submitted_data_labels' => $submitted_data_labels,
            'submitted_data_labels_json' => wp_json_encode($submitted_data_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'user_id'               => $user_id ?: null,
            'session_id'            => $final_session_id,
            'conversation_uuid'     => $conversation_uuid,
            'client_ip'             => $client_ip,
            'post_id'               => $post_id_from_request,
            'bot_settings'          => $bot_settings,
            'user_roles'            => $user_wp_roles,
            'current_provider'      => $bot_settings['provider'] ?? null,
            'current_model_id'      => $bot_settings['model'] ?? null,
            'http_referer'          => $http_referer,
            'user_agent'            => $user_agent,
            'log_storage'           => $log_storage_instance,
            'base_log_data'         => $base_log_data_for_triggers, // Pass this populated array
            'module'                => 'chat' // Explicitly set top-level module as well
        ];
        // --- END MODIFICATION ---
        
        try {
            $trigger_storage_instance = new $trigger_storage_class();
            $trigger_manager_instance = new $trigger_manager_class($trigger_storage_instance, $log_storage_instance);
            $result = $trigger_manager_instance->process_event($bot_id, 'form_submitted', $trigger_context);


            $response_data = [
                'message' => $result['message_to_user'] ?? __('Form processed.', 'gpt3-ai-content-generator'),
                'actions_executed' => $result['actions_executed'] ?? [],
                'message_id' => $result['message_id'] ?? null,
                'status' => $result['status'] ?? 'processed',
            ];

            if ($result['status'] === 'blocked') {
                wp_send_json_error($response_data, 400);
            } else {
                wp_send_json_success($response_data);
            }

        } catch (\Exception $e) {
            $this->send_wp_error(new WP_Error('trigger_processing_error', __('Error processing form submission triggers.', 'gpt3-ai-content-generator'), ['status' => 500]));
        }
    }
}
