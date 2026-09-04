<?php

namespace WPAICG\Chat\Admin\Ajax;

use WPAICG\Chat\Storage\LogStorage; // Use the LogStorage facade
use WPAICG\Chat\Storage\FeedbackManager; // Use the specific FeedbackManager
use WPAICG\Chat\Admin\AdminSetup; // Needed for Bot name lookup
use WPAICG\Speech\AIPKit_Speech_Manager; // Use TTS Manager
use WPAICG\aipkit_dashboard; // Use dashboard class
use WPAICG\Core\AIPKit_Event_Webhooks;
use WPAICG\Chat\Storage\BotSettingsManager;
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
            return;
        }
        $this->log_storage = new LogStorage();

        if (!class_exists(\WPAICG\Chat\Storage\FeedbackManager::class)) {
             return;
        }
        $this->feedback_manager = new FeedbackManager();

        if (!class_exists(\WPAICG\Speech\AIPKit_Speech_Manager::class)) {
             $this->speech_manager = null;
        } else {
            $this->speech_manager = new AIPKit_Speech_Manager();
        }
    }

    /**
     * Emits the canonical chatbot feedback event without impacting the frontend response.
     *
     * @param int|null    $user_id
     * @param int         $bot_id
     * @param string      $conversation_uuid
     * @param string      $message_id
     * @param string      $feedback_type
     * @param string      $operation_id
     * @return void
     */
    private function emit_feedback_submitted_event(?int $user_id, int $bot_id, string $conversation_uuid, string $message_id, string $feedback_type, string $operation_id): void
    {
        if (!class_exists(AIPKit_Event_Webhooks::class)) {
            return;
        }

        $bot_title = $bot_id > 0 ? get_the_title($bot_id) : '';
        $payload = [
            'feedback' => $feedback_type,
            'bot' => [
                'id' => $bot_id,
                'name' => $bot_title ? $bot_title : '',
            ],
            'conversation' => [
                'id' => $conversation_uuid,
            ],
            'message' => [
                'id' => $message_id,
            ],
            'actor' => [
                'type' => $user_id ? 'user' : 'guest',
            ],
        ];

        if ($user_id) {
            $payload['actor']['user_id'] = $user_id;
        }

        AIPKit_Event_Webhooks::emit(
            'chatbot.fb_submitted',
            $payload,
            [
                'module' => 'chatbot',
                'origin' => 'frontend_feedback',
                'resource' => [
                    'type' => 'conversation_message',
                    'id' => $message_id,
                    'label' => $bot_title ? sprintf(
                        /* translators: %s: chatbot title */
                        __('Feedback for %s', 'gpt3-ai-content-generator'),
                        $bot_title
                    ) : __('Chatbot feedback', 'gpt3-ai-content-generator'),
                ],
                'meta' => [
                    'bot_id' => $bot_id,
                    'conversation_uuid' => $conversation_uuid,
                    'feedback' => $feedback_type,
                    'is_guest' => $user_id ? 0 : 1,
                ],
                'idempotency_key' => sha1(implode('|', [
                    'chatbot.fb_submitted',
                    (string) $bot_id,
                    $conversation_uuid,
                    $message_id,
                    $feedback_type,
                    $user_id ? (string) $user_id : 'guest',
                    $operation_id,
                ])),
            ]
        );
    }

    /**
     * AJAX: Retrieves the list of conversations for a user/session and bot.
     * Uses LogStorage facade.
     */
    public function ajax_get_conversations_list() {
        $permission_check = $this->check_frontend_permissions();
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked correctly within the check_frontend_permissions() method.
        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
        $bot_id = isset($_POST['bot_id']) ? absint(wp_unslash($_POST['bot_id'])) : 0;

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

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked correctly within the check_frontend_permissions() method.
        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
        $bot_id = isset($_POST['bot_id']) ? absint(wp_unslash($_POST['bot_id'])) : 0;
        $conversation_uuid = isset($_POST['conversation_uuid']) ? sanitize_key(wp_unslash($_POST['conversation_uuid'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_frontend_permissions().
        $pending_form_only = isset($_POST['pending_form_only']) && rest_sanitize_boolean(wp_unslash($_POST['pending_form_only']));

        if (empty($bot_id) || empty($conversation_uuid)) {
            wp_send_json_error(['message' => __('Bot ID and Conversation ID are required.', 'gpt3-ai-content-generator')], 400); return;
        }
        if (!$user_id && empty($session_id)) {
             wp_send_json_error(['message' => __('Session ID is required for guest history.', 'gpt3-ai-content-generator')], 400); return;
        }

        $pending_form = null;
        $form_state_store_class = '\\WPAICG\\Lib\\Chat\\Triggers\\State\\AIPKit_Conversation_Form_State_Store';
        if (class_exists($form_state_store_class)) {
            $form_state_store = new $form_state_store_class();
            $pending_form_state = $form_state_store->get_pending_form(
                $bot_id,
                $user_id ?: null,
                $session_id,
                $conversation_uuid
            );
            if (is_array($pending_form_state) && !empty($pending_form_state['form_definition']) && is_array($pending_form_state['form_definition'])) {
                $pending_form = $pending_form_state['form_definition'];
            }
        }

        $history = [];
        if (!$pending_form_only) {
            // The reader already includes provider conversation IDs and filters internal trigger logs.
            $history = $this->log_storage->get_conversation_thread_history(
                $user_id ?: null,
                $session_id,
                $bot_id,
                $conversation_uuid
            );
        }

        wp_send_json_success([
            'history' => $history,
            'pending_form' => $pending_form,
        ]);
        // phpcs:enable
    }

     /**
     * AJAX: Stores feedback for a specific message within a conversation.
     * Uses FRONTEND nonce.
     */
    public function ajax_store_feedback() {
        $permission_check = $this->check_frontend_permissions();
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked correctly within the check_frontend_permissions() method.
        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
        $bot_id = isset($_POST['bot_id']) ? absint(wp_unslash($_POST['bot_id'])) : 0;
        $conversation_uuid = isset($_POST['conversation_uuid']) ? sanitize_key(wp_unslash($_POST['conversation_uuid'])) : '';
        $message_id = isset($_POST['message_id']) ? sanitize_key(wp_unslash($_POST['message_id'])) : '';
        $feedback_type = isset($_POST['feedback_type']) ? sanitize_key(wp_unslash($_POST['feedback_type'])) : '';
        $feedback_operation_id = isset($_POST['feedback_operation_id'])
            ? sanitize_key(wp_unslash($_POST['feedback_operation_id']))
            : '';
        if ($feedback_operation_id === '') {
            // Cached legacy clients cannot distinguish a retry from a later
            // intentional repeat, so preserve distinct operations rather than
            // incorrectly suppressing a future selection.
            $feedback_operation_id = wp_generate_uuid4();
        }

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
            $this->emit_feedback_submitted_event(
                $user_id ?: null,
                $bot_id,
                $conversation_uuid,
                $message_id,
                $feedback_type,
                $feedback_operation_id
            );
            if ($conversation_uuid) {
                wp_cache_delete('conv_full_log_' . $conversation_uuid, 'aipkit_chat_logs');
                wp_cache_delete('conv_meta_' . $conversation_uuid, 'aipkit_chat_logs');
            }
            // --- END: Invalidate cache ---
            wp_send_json_success([
                'message' => $feedback_type === 'none'
                    ? __('Feedback removed.', 'gpt3-ai-content-generator')
                    : __('Feedback saved.', 'gpt3-ai-content-generator'),
                'feedback' => $feedback_type,
            ]);
        }
    }

    /**
     * AJAX: Deletes a single conversation thread identified by its unique identifiers.
     * Uses frontend nonce because the action originates from the chat UI sidebar.
     * Only allows deletion of the user's *own* conversation.
     */
    public function ajax_delete_single_conversation()
    {
        // Use frontend nonce check
        $permission_check = $this->check_frontend_permissions();
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked correctly within the check_frontend_permissions() method.
        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : null;
        $bot_id = isset($_POST['bot_id']) ? absint(wp_unslash($_POST['bot_id'])) : 0; // Expect bot ID for the conversation
        $conversation_uuid = isset($_POST['conversation_uuid']) ? sanitize_key(wp_unslash($_POST['conversation_uuid'])) : '';

        // Validation
        if (empty($bot_id) || empty($conversation_uuid)) {
            wp_send_json_error(['message' => __('Bot ID and Conversation ID are required.', 'gpt3-ai-content-generator')], 400);
            return;
        }
        // Ensure we have an identifier for the current user/guest requesting deletion
        if (!$user_id && empty($session_id)) {
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

        if (is_wp_error($result)) {
            $this->send_wp_error($result);
        } else {
            if ($conversation_uuid) {
                wp_cache_delete('conv_full_log_' . $conversation_uuid, 'aipkit_chat_logs');
                wp_cache_delete('conv_meta_' . $conversation_uuid, 'aipkit_chat_logs');
            }
            // --- END: Invalidate cache ---
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

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked correctly within the check_frontend_permissions() method.
        if (!$this->speech_manager) {
            wp_send_json_error(['message' => __('Text-to-Speech service is unavailable.', 'gpt3-ai-content-generator')], 503);
            return;
        }
        $text = isset($_POST['text']) ? sanitize_textarea_field(wp_unslash($_POST['text'])) : '';
        $bot_id = isset($_POST['bot_id']) ? absint(wp_unslash($_POST['bot_id'])) : 0;

        if (empty($text)) { wp_send_json_error(['message' => __('Text cannot be empty.', 'gpt3-ai-content-generator')], 400); return; }
        if (empty($bot_id)) { wp_send_json_error(['message' => __('Bot ID is required.', 'gpt3-ai-content-generator')], 400); return; }

        if (!class_exists(\WPAICG\Chat\Storage\BotStorage::class)) {
             wp_send_json_error(['message' => __('Internal error: Cannot load bot storage.', 'gpt3-ai-content-generator')], 500);
         }
        $bot_storage = new \WPAICG\Chat\Storage\BotStorage();
        $bot_settings = $bot_storage->get_chatbot_settings($bot_id);

        if (!isset($bot_settings['tts_enabled']) || $bot_settings['tts_enabled'] !== '1') {
             wp_send_json_error(['message' => __('Voice playback is not enabled for this chatbot.', 'gpt3-ai-content-generator')], 400); return;
        }

        $tts_provider = $bot_settings['tts_provider'] ?? 'Google';
        $tts_voice_id = $bot_settings['tts_voice_id'] ?? '';

        $format = $tts_provider === 'Google' ? 'wav' : 'mp3';
        $mime_type = $tts_provider === 'Google' ? 'audio/wav' : 'audio/mpeg';
        if ($tts_provider === 'ElevenLabs') { $format = 'mp3_44100_128'; }

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
        if ($tts_provider === 'Google' && !empty($bot_settings['tts_google_model_id'])) {
            $tts_options['google_model_id'] = $bot_settings['tts_google_model_id'];
        }

        $result = $this->speech_manager->text_to_speech($text, $tts_options);

        if (is_wp_error($result)) {
            $error_code = $result->get_error_code();
            $error_message = $result->get_error_message();
            $status_code = 400;
            if ($error_code === 'missing_api_key') {
                /* translators: %s: The name of the Text-to-Speech provider (e.g., Google, OpenAI). */
                 $error_message = sprintf(__('TTS failed: %s API Key is missing in main settings.', 'gpt3-ai-content-generator'), $tts_provider);
                 $status_code = 500;
            } elseif (strpos($error_code, '_http_error') !== false || strpos($error_code, 'dependency_missing') !== false) {
                $status_code = 500;
            }
             $this->send_wp_error(new WP_Error($error_code, $error_message, ['status' => $status_code]));
        } else {
             wp_send_json_success(['audio_data_base64' => $result, 'mime_type' => $mime_type]);
        }
    }
}
