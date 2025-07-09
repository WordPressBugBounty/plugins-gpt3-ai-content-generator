<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/admin/ajax/conversation_ajax_handler.php
// MODIFIED FILE - Pass through openai_response_id in history.

namespace WPAICG\Chat\Admin\Ajax;

use WPAICG\Chat\Storage\LogStorage; // Use the LogStorage facade
use WPAICG\Chat\Storage\FeedbackManager; // Use the specific FeedbackManager
use WPAICG\Chat\Admin\AdminSetup; // Needed for Bot name lookup
use WPAICG\Speech\AIPKit_Speech_Manager; // Use TTS Manager
use WPAICG\aipkit_dashboard; // Use dashboard class
// --- MODIFIED: Correct namespace for BotSettingsManager ---
use WPAICG\Chat\Storage\BotSettingsManager;
// --- END MODIFICATION ---
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles AJAX requests related to retrieving conversation history and lists, storing feedback, deleting conversations, and generating speech.
 * REVISED: generate_speech now returns base64 data instead of URL.
 * UPDATED: Ensures openai_response_id is part of the history passed to the frontend for sidebar loading.
 */
class ConversationAjaxHandler extends BaseAjaxHandler {

    private $log_storage;
    private $feedback_manager; // Add feedback manager
    private $speech_manager; // Add speech manager

    public function __construct() {
        if (!class_exists(\WPAICG\Chat\Storage\LogStorage::class)) {
            error_log('AIPKit Error: LogStorage class not found during ConversationAjaxHandler construction.');
            return;
        }
        $this->log_storage = new LogStorage();

        if (!class_exists(\WPAICG\Chat\Storage\FeedbackManager::class)) {
             error_log('AIPKit Error: FeedbackManager class not found during ConversationAjaxHandler construction.');
             return;
        }
        $this->feedback_manager = new FeedbackManager();

        if (!class_exists(\WPAICG\Speech\AIPKit_Speech_Manager::class)) {
             error_log('AIPKit Error: AIPKit_Speech_Manager class not found during ConversationAjaxHandler construction.');
             $this->speech_manager = null;
        } else {
            $this->speech_manager = new AIPKit_Speech_Manager();
        }
    }

    /**
     * Helper to get metadata for a specific conversation (for detail view header).
     * NOTE: This function is now less critical as the full log row is fetched, but kept for potential future use or refactoring.
     */
    private function get_conversation_metadata(?int $user_id, ?string $session_id, ?int $bot_id, string $conversation_uuid): array { // Made bot_id nullable
        global $wpdb;
         if (empty($conversation_uuid)) {
            return [];
        }
        $table = $wpdb->prefix . 'aipkit_chat_logs';

        $where_sql = "conversation_uuid = %s";
        $params = [$conversation_uuid];

        if ($bot_id !== null) {
            $where_sql .= " AND bot_id = %d";
            $params[] = $bot_id;
        } else {
             $where_sql .= " AND bot_id IS NULL";
        }

        if ($user_id) {
            $where_sql .= " AND user_id = %d";
            $params[] = $user_id;
        } elseif ($session_id) {
            $where_sql .= " AND user_id IS NULL AND session_id = %s AND is_guest = 1";
            $params[] = $session_id;
        } else {
             return []; // Cannot identify user/session
        }

        $meta_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT user_id, session_id, ip_address, is_guest, first_message_ts, last_message_ts, bot_id, module FROM {$table} WHERE {$where_sql} LIMIT 1",
                $params
            ), ARRAY_A
        );

        if(!$meta_row) return [];

        $bot_name = '';
        if (!empty($meta_row['bot_id'])) {
            $bot_name = get_the_title($meta_row['bot_id']) ?: __('(Deleted Bot)', 'gpt3-ai-content-generator');
        } elseif (!empty($meta_row['module'])) {
            $bot_name = sprintf('(%s)', esc_html(ucfirst(str_replace('_', ' ', $meta_row['module']))));
        } else {
             $bot_name = __('(No Bot/Module)', 'gpt3-ai-content-generator');
        }

        $user_display_name = __('(Unknown)', 'gpt3-ai-content-generator');
         if (!$meta_row['is_guest'] && !empty($meta_row['user_id'])) {
            $user_data = get_userdata($meta_row['user_id']);
            $user_display_name = $user_data ? $user_data->display_name : __('(Deleted User)', 'gpt3-ai-content-generator');
        } elseif ($meta_row['is_guest']) {
            $user_display_name = __('Guest', 'gpt3-ai-content-generator');
             if(!empty($meta_row['session_id'])) {
                 $user_display_name .= ' (' . substr($meta_row['session_id'], 0, 8) . '...)';
             }
        } else {
             $user_display_name = __('(Unknown)', 'gpt3-ai-content-generator');
        }

        return [
            'bot_name' => $bot_name,
            'user_display_name' => $user_display_name,
            'session_id' => $meta_row['session_id'],
            'conversation_uuid' => $conversation_uuid,
            'ip_address' => $meta_row['ip_address'],
            'module' => $meta_row['module'],
            'created_at' => $meta_row['first_message_ts'] ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $meta_row['first_message_ts']) : 'N/A',
            'updated_at' => $meta_row['last_message_ts'] ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $meta_row['last_message_ts']) : 'N/A',
        ];
    }

    /**
     * AJAX: Retrieves the list of conversations for a user/session and bot.
     * Uses LogStorage facade.
     */
    public function ajax_get_conversations_list() {
        $permission_check = $this->check_frontend_permissions();
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
        $bot_id = isset($_POST['bot_id']) ? absint($_POST['bot_id']) : 0;

        if (empty($bot_id)) { wp_send_json_error(['message' => __('Bot ID is required.', 'gpt3-ai-content-generator')], 400); return; }
        if (!$user_id && empty($session_id)) { wp_send_json_error(['message' => __('User or Session ID is required.', 'gpt3-ai-content-generator')], 400); return; }

        $conversations_list = $this->log_storage->get_all_conversation_data(
            $user_id ?: null,
            $session_id,
            $bot_id
        );

        if ($conversations_list === null) {
            wp_send_json_error(['message' => __('Could not retrieve conversation data.', 'gpt3-ai-content-generator')], 500); return;
        }

        wp_send_json_success(['conversations' => $conversations_list]);
    }


    /**
     * AJAX: Retrieves the message history for a specific conversation thread.
     * Uses LogStorage facade.
     * Ensures `openai_response_id` is included for each relevant message.
     */
    public function ajax_get_conversation_history() {
        $permission_check = $this->check_frontend_permissions();
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
        $bot_id = isset($_POST['bot_id']) ? absint($_POST['bot_id']) : 0;
        $conversation_uuid = isset($_POST['conversation_uuid']) ? sanitize_key($_POST['conversation_uuid']) : '';

        if (empty($bot_id) || empty($conversation_uuid)) {
            wp_send_json_error(['message' => __('Bot ID and Conversation ID are required.', 'gpt3-ai-content-generator')], 400); return;
        }
        if (!$user_id && empty($session_id)) {
             wp_send_json_error(['message' => __('Session ID is required for guest history.', 'gpt3-ai-content-generator')], 400); return;
        }

        // The get_conversation_thread_history method in ConversationReader
        // already includes openai_response_id and used_previous_response_id if present in the JSON.
        $history = $this->log_storage->get_conversation_thread_history(
            $user_id ?: null,
            $session_id,
            $bot_id,
            $conversation_uuid
        );

        wp_send_json_success(['history' => $history]);
    }

     /**
     * AJAX: Retrieves the FULL log data for a specific conversation thread.
     * Used by the ADMIN log viewer. Uses ADMIN nonce.
     */
    public function ajax_admin_get_conversation_history() {
        $permission_check = $this->check_module_access_permissions('logs');
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        // Get identifiers from the POST data (sent by the log viewer JS)
        $user_id_from_log = isset($_POST['user_id']) && !empty($_POST['user_id']) ? absint($_POST['user_id']) : null;
        $session_id_from_log = isset($_POST['session_id']) && !empty($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
        $bot_id_raw = isset($_POST['bot_id']) ? $_POST['bot_id'] : null; // Keep raw value (could be null, '', '0', or ID string)
        $conversation_uuid = isset($_POST['conversation_uuid']) ? sanitize_key($_POST['conversation_uuid']) : '';

        if (empty($conversation_uuid)) {
            wp_send_json_error(['message' => __('Conversation ID is required.', 'gpt3-ai-content-generator')], 400); return;
        }
        if ($user_id_from_log === null && empty($session_id_from_log)) {
             wp_send_json_error(['message' => __('User or Session ID must be provided from log data.', 'gpt3-ai-content-generator')], 400); return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'aipkit_chat_logs';
        $where_sql = "conversation_uuid = %s";
        $params = [$conversation_uuid];

        if ($bot_id_raw === null || $bot_id_raw === '' || $bot_id_raw === '0') {
            $where_sql .= " AND bot_id IS NULL";
        } elseif (ctype_digit((string)$bot_id_raw) && absint($bot_id_raw) > 0) {
            $where_sql .= " AND bot_id = %d";
            $params[] = absint($bot_id_raw);
        } else {
            error_log("AIPKit Log Detail Query: Received unexpected bot_id format: " . print_r($bot_id_raw, true) . ". Assuming IS NULL.");
             $where_sql .= " AND bot_id IS NULL";
        }

        $is_guest_log = ($user_id_from_log === 0 || $user_id_from_log === null) && !empty($session_id_from_log);

        if ($is_guest_log) {
            $where_sql .= " AND user_id IS NULL AND session_id = %s AND is_guest = 1";
            $params[] = $session_id_from_log;
        } elseif ($user_id_from_log > 0) {
            $where_sql .= " AND user_id = %d AND is_guest = 0";
            $params[] = $user_id_from_log;
        } else {
            error_log("AIPKit Log Detail Query Error: Cannot determine user/guest from provided IDs. UserID: {$user_id_from_log}, SessionID: {$session_id_from_log}");
            wp_send_json_error(['message' => __('Internal error: Invalid user/session identifier combination.', 'gpt3-ai-content-generator')], 500);
            return;
        }

        $prepared_sql = $wpdb->prepare("SELECT * FROM {$table_name} WHERE {$where_sql} LIMIT 1", $params);
        $log_data = $wpdb->get_row($prepared_sql, ARRAY_A);

        if (!$log_data) {
            wp_send_json_error(['message' => __('Log entry not found.', 'gpt3-ai-content-generator')], 404);
            return;
        }

        if (!empty($log_data['bot_id'])) {
            $log_data['bot_name'] = get_the_title($log_data['bot_id']) ?: __('(Deleted Bot)', 'gpt3-ai-content-generator');
        } elseif (!empty($log_data['module'])) {
             $log_data['bot_name'] = sprintf('(%s)', esc_html(ucfirst(str_replace('_', ' ', $log_data['module']))));
        } else {
             $log_data['bot_name'] = __('(Unknown)', 'gpt3-ai-content-generator');
        }

        if (!$log_data['is_guest'] && !empty($log_data['user_id'])) {
            $user_data = get_userdata($log_data['user_id']);
            $log_data['user_display_name'] = $user_data ? $user_data->display_name : __('(Deleted User)', 'gpt3-ai-content-generator');
        } elseif ($log_data['is_guest']) {
            $log_data['user_display_name'] = __('Guest', 'gpt3-ai-content-generator');
             if(!empty($log_data['session_id'])) {
                 $log_data['user_display_name'] .= ' (' . substr($log_data['session_id'], 0, 8) . '...)';
             }
        } else {
             $log_data['user_display_name'] = __('(Unknown)', 'gpt3-ai-content-generator');
        }

        wp_send_json_success(['log_data' => $log_data]);
    }

     /**
     * AJAX: Stores feedback for a specific message within a conversation.
     * Uses FRONTEND nonce.
     */
    public function ajax_store_feedback() {
        $permission_check = $this->check_frontend_permissions();
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
        $bot_id = isset($_POST['bot_id']) ? absint($_POST['bot_id']) : 0;
        $conversation_uuid = isset($_POST['conversation_uuid']) ? sanitize_key($_POST['conversation_uuid']) : '';
        $message_id = isset($_POST['message_id']) ? sanitize_key($_POST['message_id']) : '';
        $feedback_type = isset($_POST['feedback_type']) ? sanitize_key($_POST['feedback_type']) : '';

        if (!$this->feedback_manager) {
             wp_send_json_error(['message' => __('Feedback service unavailable.', 'gpt3-ai-content-generator')], 500); return;
        }
        $result = $this->feedback_manager->store_feedback_for_message(
            $user_id ?: null,
            $session_id,
            $bot_id,
            $conversation_uuid,
            $message_id,
            $feedback_type
        );

        if (is_wp_error($result)) {
            $this->send_wp_error($result);
        } else {
            wp_send_json_success(['message' => __('Feedback saved.', 'gpt3-ai-content-generator')]);
        }
    }

    /**
     * AJAX: Deletes a single conversation thread identified by its unique identifiers.
     * Uses frontend nonce because the action originates from the chat UI sidebar.
     * Only allows deletion of the user's *own* conversation.
     * Moved from LogAjaxHandler to here.
     */
    public function ajax_delete_single_conversation()
    {
        error_log("AIPKit ConversationAjaxHandler: ajax_delete_single_conversation triggered.");

        // Use frontend nonce check
        $permission_check = $this->check_frontend_permissions();
        if (is_wp_error($permission_check)) {
            error_log("AIPKit ConversationAjaxHandler: Permission check failed: " . $permission_check->get_error_message());
            $this->send_wp_error($permission_check);
            return;
        }

        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
        $bot_id = isset($_POST['bot_id']) ? absint($_POST['bot_id']) : 0; // Expect bot ID for the conversation
        $conversation_uuid = isset($_POST['conversation_uuid']) ? sanitize_key($_POST['conversation_uuid']) : '';

        error_log("AIPKit ConversationAjaxHandler: Request params - UserID: {$user_id}, SessionID: {$session_id}, BotID: {$bot_id}, ConvUUID: {$conversation_uuid}");

        // Validation
        if (empty($bot_id) || empty($conversation_uuid)) {
            error_log("AIPKit ConversationAjaxHandler: Validation failed - Bot ID or Conversation ID is empty.");
            wp_send_json_error(['message' => __('Bot ID and Conversation ID are required.', 'gpt3-ai-content-generator')], 400);
            return;
        }
        // Ensure we have an identifier for the current user/guest requesting deletion
        if (!$user_id && empty($session_id)) {
            error_log("AIPKit ConversationAjaxHandler: Validation failed - Cannot identify user or session.");
            wp_send_json_error(['message' => __('Cannot identify user or session.', 'gpt3-ai-content-generator')], 400);
            return;
        }

        // Use the facade method to perform the deletion
        $result = $this->log_storage->delete_single_conversation(
            $user_id ?: null,
            $session_id,
            $bot_id,
            $conversation_uuid
        );

        error_log("AIPKit ConversationAjaxHandler: Deletion result: " . (is_wp_error($result) ? $result->get_error_message() : 'Success'));

        if (is_wp_error($result)) {
            $this->send_wp_error($result);
        } else {
            wp_send_json_success(['message' => __('Conversation deleted.', 'gpt3-ai-content-generator')]);
        }
    }

    /**
     * AJAX handler for generating speech from text.
     * Uses FRONTEND nonce as it's called from the chat UI.
     * REVISED: Return base64 encoded audio data instead of a file URL.
     * REVISED: Added OpenAI format mapping.
     * @since NEXT_VERSION
     */
    public function ajax_generate_speech() {
        $permission_check = $this->check_frontend_permissions('aipkit_frontend_chat_nonce');
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        if (!$this->speech_manager) {
            wp_send_json_error(['message' => __('Text-to-Speech service is unavailable.', 'gpt3-ai-content-generator')], 503);
            return;
        }
        if (!\WPAICG\aipkit_dashboard::is_addon_active('voice_playback')) {
             wp_send_json_error(['message' => __('Voice Playback addon is not active.', 'gpt3-ai-content-generator')], 403);
            return;
        }

        $text = isset($_POST['text']) ? sanitize_textarea_field(wp_unslash($_POST['text'])) : '';
        $bot_id = isset($_POST['bot_id']) ? absint($_POST['bot_id']) : 0;

        if (empty($text)) { wp_send_json_error(['message' => __('Text cannot be empty.', 'gpt3-ai-content-generator')], 400); return; }
        if (empty($bot_id)) { wp_send_json_error(['message' => __('Bot ID is required.', 'gpt3-ai-content-generator')], 400); return; }

        if (!class_exists(\WPAICG\Chat\Storage\BotStorage::class)) {
             wp_send_json_error(['message' => __('Internal error: Cannot load bot storage.', 'gpt3-ai-content-generator')], 500); return;
         }
        $bot_storage = new \WPAICG\Chat\Storage\BotStorage();
        $bot_settings = $bot_storage->get_chatbot_settings($bot_id);

        if (!isset($bot_settings['tts_enabled']) || $bot_settings['tts_enabled'] !== '1') {
             wp_send_json_error(['message' => __('Voice playback is not enabled for this chatbot.', 'gpt3-ai-content-generator')], 400); return;
        }

        $tts_provider = $bot_settings['tts_provider'] ?? 'Google';
        $tts_voice_id = $bot_settings['tts_voice_id'] ?? '';
        // $tts_auto_play = ($bot_settings['tts_auto_play'] ?? BotSettingsManager::DEFAULT_TTS_AUTO_PLAY) === '1'; // Not needed for this AJAX

        $format = 'mp3';
        $mime_type = 'audio/mpeg';
        if ($tts_provider === 'ElevenLabs') { $format = 'mp3_44100_128'; }
        elseif ($tts_provider === 'OpenAI') { $format = 'mp3'; $mime_type = 'audio/mpeg';} // Default for OpenAI

        $tts_options = [
            'provider' => $tts_provider,
            'voice' => $tts_voice_id,
            'format' => $format
        ];
        if ($tts_provider === 'ElevenLabs' && !empty($bot_settings['tts_elevenlabs_model_id'])) {
            $tts_options['elevenlabs_model_id'] = $bot_settings['tts_elevenlabs_model_id'];
        }
        if ($tts_provider === 'OpenAI' && !empty($bot_settings['tts_openai_model_id'])) {
            $tts_options['openai_model_id'] = $bot_settings['tts_openai_model_id'];
        }

        $result = $this->speech_manager->text_to_speech($text, $tts_options);

        if (is_wp_error($result)) {
            $error_code = $result->get_error_code();
            $error_message = $result->get_error_message();
            $status_code = 400;
            if ($error_code === 'missing_api_key') {
                 $error_message = sprintf(__('TTS failed: %s API Key is missing in main settings.', 'gpt3-ai-content-generator'), $tts_provider);
                 $status_code = 500;
            } elseif (str_contains($error_code, '_http_error') || str_contains($error_code, 'dependency_missing')) {
                $status_code = 500;
            }
            error_log("AIPKit TTS Error (AJAX Handler): Code='{$error_code}', Message='{$error_message}', Status={$status_code}");
             $this->send_wp_error(new WP_Error($error_code, $error_message, ['status' => $status_code]));
        } else {
             wp_send_json_success(['audio_data_base64' => $result, 'mime_type' => $mime_type]);
        }
    }
}