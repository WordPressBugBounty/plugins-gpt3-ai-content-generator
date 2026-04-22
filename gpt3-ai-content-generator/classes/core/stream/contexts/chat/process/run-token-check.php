<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/stream/contexts/chat/process/run-token-check.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Contexts\Chat\Process;

use WPAICG\Core\TokenManager\AIPKit_Token_Manager;
use WPAICG\Chat\Storage\LogStorage; // For trigger manager dependency
use WPAICG\Lib\Chat\Triggers\AIPKit_Trigger_Storage; // For trigger check
use WPAICG\Lib\Chat\Triggers\AIPKit_Trigger_Manager; // For trigger check
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once WPAICG_PLUGIN_DIR . 'classes/chat/core/pricing/fn-build-chat-pricing-check-context.php';

/**
 * Performs token limit checks for a chat stream request.
 * Dispatches a 'system_error_occurred' trigger if the token check fails.
 *
 * @param AIPKit_Token_Manager $token_manager Instance of the token manager.
 * @param int|null    $user_id          User ID, or null for guests.
 * @param string|null $session_id       Session ID for guests.
 * @param int         $bot_id           Bot ID for the current chat context.
 * @param array<string, mixed> $bot_settings Bot configuration used to estimate priced usage.
 * @param string $user_message_text Current user message for conservative token estimation.
 * @param array<int, mixed>|null $image_inputs Optional image inputs for multimodal estimation.
 * @param LogStorage|null $log_storage    Instance of LogStorage (for trigger manager).
 * @return true|WP_Error True if token check passes or not applicable, WP_Error if limit exceeded.
 */
function run_token_check_logic(
    AIPKit_Token_Manager $token_manager,
    ?int $user_id,
    ?string $session_id,
    int $bot_id,
    array $bot_settings,
    string $user_message_text,
    ?array $image_inputs,
    ?LogStorage $log_storage
): bool|WP_Error {
    $usage_context = \WPAICG\Chat\Core\Pricing\build_chat_pricing_check_context_logic(
        $bot_id,
        $bot_settings,
        $user_message_text,
        $image_inputs
    );
    $token_check_result = $token_manager->check_and_reset_tokens($user_id ?: null, $session_id, $bot_id, 'chat', $usage_context);

    if (is_wp_error($token_check_result)) {
        $trigger_storage_class = '\WPAICG\Lib\Chat\Triggers\AIPKit_Trigger_Storage';
        $trigger_manager_class = '\WPAICG\Lib\Chat\Triggers\AIPKit_Trigger_Manager';
        $triggers_enabled = false;
        if (class_exists('\WPAICG\aipkit_dashboard')) {
            $triggers_enabled = \WPAICG\aipkit_dashboard::is_pro_plan();
        }

        if ($triggers_enabled && class_exists($trigger_manager_class) && class_exists($trigger_storage_class)) {
            // Only proceed if log storage is available for the trigger manager
            if ($log_storage) {
                $error_data = $token_check_result->get_error_data() ?: [];
                $error_event_context = [
                    'error_code'    => $token_check_result->get_error_code(),
                    'error_message' => $token_check_result->get_error_message(),
                    'bot_id'        => $bot_id,
                    'user_id'       => $user_id ?: null,
                    'session_id'    => $session_id,
                    'module'        => 'chat_stream_context',
                    'operation'     => 'token_check',
                    'status_code'   => is_array($error_data) && isset($error_data['status']) ? (int)$error_data['status'] : 429,
                ];
                try {
                    $trigger_storage = new $trigger_storage_class();
                    $trigger_manager = new $trigger_manager_class($trigger_storage, $log_storage);
                    $trigger_manager->process_event($bot_id, 'system_error_occurred', $error_event_context);
                } catch (\Exception $e) {
                    // Exception is caught and ignored to prevent fatal errors.
                }
            }
        }
        // Return the original error from token manager, including status code
        $token_error_data = $token_check_result->get_error_data();
        if (!is_array($token_error_data)) {
            $token_error_data = [];
        }
        if (!isset($token_error_data['status'])) {
            $token_error_data['status'] = 429;
        }
        return new WP_Error($token_check_result->get_error_code(), $token_check_result->get_error_message(), $token_error_data);
    }
    return true; // Token check passed
}
