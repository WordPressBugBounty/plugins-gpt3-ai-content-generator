<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/stream/contexts/chat/process/emit-chatbot-events.php

namespace WPAICG\Core\Stream\Contexts\Chat\Process;

use WPAICG\Chat\Storage\LogStorage;
use WPAICG\Core\AIPKit_Event_Webhooks;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Emits the canonical chatbot user-side events after the user message is stored.
 *
 * @param LogStorage $log_storage
 * @param array<string, mixed> $context
 * @param array<string, mixed> $user_log_result
 * @param array<string, mixed> $provider_model_info
 * @return void
 */
function emit_chatbot_user_events_logic(
    LogStorage $log_storage,
    array $context,
    array $user_log_result,
    array $provider_model_info = []
): void {
    if (!class_exists(AIPKit_Event_Webhooks::class)) {
        return;
    }

    $log_id = absint($user_log_result['log_id'] ?? 0);
    $message_id = sanitize_key((string) ($user_log_result['message_id'] ?? ''));
    $conversation_uuid = sanitize_key((string) ($context['conversation_uuid'] ?? ($context['base_log_data']['conversation_uuid'] ?? '')));
    $bot_id = absint($context['bot_id'] ?? 0);
    $user_id = absint($context['user_id'] ?? 0);
    $actor_type = $user_id > 0 ? 'user' : 'guest';

    if ($log_id <= 0 || $message_id === '' || $conversation_uuid === '' || $bot_id <= 0) {
        return;
    }

    $conversation_log = $log_storage->get_log_by_id($log_id);
    $bot_name = sanitize_text_field((string) ($conversation_log['bot_name'] ?? get_the_title($bot_id)));
    $message_count = absint($conversation_log['message_count'] ?? 1);
    $message_text = sanitize_textarea_field((string) ($context['user_message_text'] ?? ''));
    $provider = sanitize_text_field((string) ($provider_model_info['provider'] ?? ($context['current_provider'] ?? '')));
    $model = sanitize_text_field((string) ($provider_model_info['model'] ?? ($context['current_model_id'] ?? '')));

    $payload = [
        'bot' => [
            'id' => $bot_id,
            'name' => $bot_name,
        ],
        'conversation' => [
            'id' => $conversation_uuid,
            'message_count' => $message_count,
        ],
        'actor' => [
            'type' => $actor_type,
        ],
        'message' => [
            'id' => $message_id,
            'text' => $message_text,
        ],
        'ai' => [
            'provider' => $provider,
            'model' => $model,
        ],
    ];

    if ($user_id > 0) {
        $payload['actor']['user_id'] = $user_id;
    }

    if (!empty($user_log_result['is_new_session'])) {
        AIPKit_Event_Webhooks::emit(
            'chatbot.session_started',
            $payload,
            [
                'module' => 'chatbot',
                'origin' => 'frontend_session_started',
                'resource' => [
                    'type' => 'conversation',
                    'id' => $conversation_uuid,
                    'label' => $bot_name !== ''
                        ? sprintf(
                            /* translators: %s: chatbot name */
                            __('Conversation started for %s', 'gpt3-ai-content-generator'),
                            $bot_name
                        )
                        : __('Chat session started', 'gpt3-ai-content-generator'),
                ],
                'meta' => [
                    'bot_id' => $bot_id,
                    'conversation_uuid' => $conversation_uuid,
                    'message_id' => $message_id,
                    'message_count' => $message_count,
                    'is_guest' => $user_id > 0 ? 0 : 1,
                ],
                'idempotency_key' => sha1(implode('|', [
                    'chatbot.session_started',
                    (string) $bot_id,
                    $conversation_uuid,
                    $message_id,
                    $user_id > 0 ? (string) $user_id : 'guest',
                ])),
            ]
        );
    }

    AIPKit_Event_Webhooks::emit(
        'chatbot.user_message_submitted',
        $payload,
        [
            'module' => 'chatbot',
            'origin' => 'frontend_user_message',
            'resource' => [
                'type' => 'conversation_message',
                'id' => $message_id,
                'label' => $bot_name !== ''
                    ? sprintf(
                        /* translators: %s: chatbot name */
                        __('User message to %s', 'gpt3-ai-content-generator'),
                        $bot_name
                    )
                    : __('Chat user message', 'gpt3-ai-content-generator'),
            ],
            'meta' => [
                'bot_id' => $bot_id,
                'conversation_uuid' => $conversation_uuid,
                'message_id' => $message_id,
                'message_count' => $message_count,
                'is_guest' => $user_id > 0 ? 0 : 1,
            ],
            'idempotency_key' => sha1(implode('|', [
                'chatbot.user_message_submitted',
                (string) $bot_id,
                $conversation_uuid,
                $message_id,
                $user_id > 0 ? (string) $user_id : 'guest',
            ])),
        ]
    );
}
