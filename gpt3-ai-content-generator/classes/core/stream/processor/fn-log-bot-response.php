<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/stream/processor/fn-log-bot-response.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Processor;

use WPAICG\Chat\Storage\LogStorage;
use WPAICG\Core\AIPKit_Event_Webhooks;
use WPAICG\Core\TokenManager\Constants\GuestTableConstants;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Emits the canonical AI Forms event after the generated response is fully available.
 *
 * @param array<string, mixed> $log_base_data
 * @param string $full_bot_response
 * @param string|null $current_provider
 * @param string|null $current_model
 * @param string|null $current_conversation_uuid
 * @param string|null $current_bot_message_id
 * @return void
 */
function emit_ai_forms_form_submitted_event_logic(
    array $log_base_data,
    string $full_bot_response,
    ?string $current_provider,
    ?string $current_model,
    ?string $current_conversation_uuid,
    ?string $current_bot_message_id
): void {
    if (!class_exists(AIPKit_Event_Webhooks::class)) {
        return;
    }

    $form_event_meta = isset($log_base_data['aipkit_form_event_meta']) && is_array($log_base_data['aipkit_form_event_meta'])
        ? $log_base_data['aipkit_form_event_meta']
        : [];

    $form_id = absint($form_event_meta['form_id'] ?? ($log_base_data['form_id'] ?? 0));
    $form_name = sanitize_text_field((string) ($form_event_meta['form_name'] ?? ''));
    $submission_count = absint($form_event_meta['submission_count'] ?? 0);
    $submitted_inputs = isset($form_event_meta['inputs']) && is_array($form_event_meta['inputs'])
        ? $form_event_meta['inputs']
        : [];
    $user_id = !empty($log_base_data['user_id']) ? absint($log_base_data['user_id']) : 0;
    $conversation_uuid = sanitize_key((string) ($current_conversation_uuid ?? ''));
    $response_text = sanitize_textarea_field($full_bot_response);

    $payload = [
        'form' => [
            'id' => $form_id,
            'name' => $form_name,
        ],
        'submission' => [
            'id' => $conversation_uuid,
            'count' => $submission_count,
        ],
        'actor' => [
            'type' => !empty($log_base_data['is_guest']) ? 'guest' : 'user',
        ],
        'ai' => [
            'provider' => sanitize_text_field((string) ($current_provider ?: ($form_event_meta['ai']['provider'] ?? ''))),
            'model' => sanitize_text_field((string) ($current_model ?: ($form_event_meta['ai']['model'] ?? ''))),
        ],
        'inputs' => $submitted_inputs,
        'response' => [
            'text' => $response_text,
        ],
    ];

    if ($user_id > 0) {
        $payload['actor']['user_id'] = $user_id;
    }

    AIPKit_Event_Webhooks::emit(
        'form.submitted',
        $payload,
        [
            'module' => 'ai_forms',
            'origin' => 'frontend_submission_completed',
            'resource' => [
                'type' => 'form_submission',
                'id' => $conversation_uuid !== '' ? $conversation_uuid : sanitize_key((string) ($current_bot_message_id ?? '')),
                'label' => $form_name !== ''
                    ? sprintf(
                        /* translators: %s: AI Form title */
                        __('Submission for %s', 'gpt3-ai-content-generator'),
                        $form_name
                    )
                    : __('AI Form submission', 'gpt3-ai-content-generator'),
            ],
            'meta' => [
                'form_id' => $form_id,
                'ai_provider' => sanitize_text_field((string) ($current_provider ?: ($form_event_meta['ai']['provider'] ?? ''))),
                'ai_model' => sanitize_text_field((string) ($current_model ?: ($form_event_meta['ai']['model'] ?? ''))),
                'is_guest' => !empty($log_base_data['is_guest']) ? 1 : 0,
                'conversation_uuid' => $conversation_uuid,
            ],
            'idempotency_key' => sha1(implode('|', [
                'form.submitted',
                'completed',
                (string) $form_id,
                $conversation_uuid,
                $current_bot_message_id ?: '',
                $user_id > 0 ? (string) $user_id : 'guest',
            ])),
        ]
    );
}

/**
 * Emits the canonical chatbot response event after the full response is stored.
 *
 * @param LogStorage $log_storage
 * @param array<string, mixed> $log_base_data
 * @param string $full_bot_response
 * @param string|null $current_provider
 * @param string|null $current_model
 * @param string|null $current_conversation_uuid
 * @param string|null $current_bot_message_id
 * @param array<string, mixed> $bot_log_result
 * @return void
 */
function emit_chatbot_response_generated_event_logic(
    LogStorage $log_storage,
    array $log_base_data,
    string $full_bot_response,
    ?string $current_provider,
    ?string $current_model,
    ?string $current_conversation_uuid,
    ?string $current_bot_message_id,
    array $bot_log_result
): void {
    if (!class_exists(AIPKit_Event_Webhooks::class)) {
        return;
    }

    $bot_id = absint($log_base_data['bot_id'] ?? 0);
    $user_id = absint($log_base_data['user_id'] ?? 0);
    $conversation_uuid = sanitize_key((string) ($current_conversation_uuid ?? ''));
    $response_id = sanitize_key((string) ($current_bot_message_id ?? ''));
    $log_id = absint($bot_log_result['log_id'] ?? 0);

    if ($bot_id <= 0 || $conversation_uuid === '' || $response_id === '' || $log_id <= 0) {
        return;
    }

    $conversation_log = $log_storage->get_log_by_id($log_id);
    $bot_name = sanitize_text_field((string) ($conversation_log['bot_name'] ?? get_the_title($bot_id)));
    $message_count = absint($conversation_log['message_count'] ?? 0);
    $history = $log_storage->get_conversation_thread_history(
        $user_id > 0 ? $user_id : null,
        $user_id > 0 ? null : sanitize_text_field((string) ($log_base_data['session_id'] ?? '')),
        $bot_id,
        $conversation_uuid
    );

    $last_user_message = [];
    if (!empty($history)) {
        for ($index = count($history) - 1; $index >= 0; $index--) {
            $history_item = $history[$index] ?? [];
            if (!is_array($history_item)) {
                continue;
            }

            if (sanitize_key((string) ($history_item['role'] ?? '')) === 'user') {
                $last_user_message = $history_item;
                break;
            }
        }
    }

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
            'type' => !empty($log_base_data['is_guest']) ? 'guest' : 'user',
        ],
        'response' => [
            'id' => $response_id,
            'text' => sanitize_textarea_field($full_bot_response),
        ],
        'ai' => [
            'provider' => sanitize_text_field((string) ($current_provider ?? '')),
            'model' => sanitize_text_field((string) ($current_model ?? '')),
        ],
    ];

    if ($user_id > 0) {
        $payload['actor']['user_id'] = $user_id;
    }

    if (!empty($last_user_message)) {
        $payload['user_message'] = [
            'id' => sanitize_key((string) ($last_user_message['message_id'] ?? '')),
            'text' => sanitize_textarea_field((string) ($last_user_message['content'] ?? '')),
        ];
    }

    AIPKit_Event_Webhooks::emit(
        'chatbot.response_generated',
        $payload,
        [
            'module' => 'chatbot',
            'origin' => 'frontend_response_completed',
            'resource' => [
                'type' => 'conversation_message',
                'id' => $response_id,
                'label' => $bot_name !== ''
                    ? sprintf(
                        /* translators: %s: chatbot name */
                        __('Response from %s', 'gpt3-ai-content-generator'),
                        $bot_name
                    )
                    : __('Chat response generated', 'gpt3-ai-content-generator'),
            ],
            'meta' => [
                'bot_id' => $bot_id,
                'conversation_uuid' => $conversation_uuid,
                'message_id' => $response_id,
                'message_count' => $message_count,
                'is_guest' => !empty($log_base_data['is_guest']) ? 1 : 0,
            ],
            'idempotency_key' => sha1(implode('|', [
                'chatbot.response_generated',
                (string) $bot_id,
                $conversation_uuid,
                $response_id,
                $user_id > 0 ? (string) $user_id : 'guest',
            ])),
        ]
    );
}

/**
 * Logs the final successful bot response.
 *
 * @param \WPAICG\Core\Stream\Processor\SSEStreamProcessor $processorInstance The instance of the processor class.
 * @return void
 */
function log_bot_response_logic(\WPAICG\Core\Stream\Processor\SSEStreamProcessor $processorInstance): void
{
    $full_bot_response = $processorInstance->get_full_bot_response();
    $log_base_data = $processorInstance->get_log_base_data();
    $error_occurred = $processorInstance->get_error_occurred_status();
    $current_bot_message_id = $processorInstance->get_current_bot_message_id();
    $log_storage = $processorInstance->get_log_storage();
    $current_provider = $processorInstance->get_current_provider();
    $current_model = $processorInstance->get_current_model();
    $final_usage_data = $processorInstance->get_final_usage_data();
    $request_payload_log = $processorInstance->get_request_payload_log();
    $current_stream_context = $processorInstance->get_current_stream_context();
    $current_conversation_uuid = $processorInstance->get_current_conversation_uuid();
    $current_openai_response_id = $processorInstance->get_current_openai_response_id();
    $used_previous_openai_response_id = $processorInstance->get_used_previous_openai_response_id_status();
    $grounding_metadata = $processorInstance->get_grounding_metadata();
    $citations = $processorInstance->get_citations();
    $token_manager = $processorInstance->get_token_manager();
    $vector_search_scores = $processorInstance->get_vector_search_scores();

    if (!$log_storage) {
        return;
    }


    if (!empty($full_bot_response) && !empty($log_base_data) && !$error_occurred && !empty($current_bot_message_id)) {
        $log_bot_data = array_merge($log_base_data, [
            'message_role'    => 'bot',
            'message_content' => $full_bot_response,
            'timestamp'       => time(),
            'ai_provider'     => $current_provider,
            'ai_model'        => $current_model,
            'usage'           => $final_usage_data,
            'message_id'      => $current_bot_message_id,
            'request_payload' => $request_payload_log,
        ]);

        if ($current_provider === 'OpenAI') {
            if ($current_openai_response_id) {
                $log_bot_data['openai_response_id'] = $current_openai_response_id;
            }
            if ($used_previous_openai_response_id) {
                $log_bot_data['used_previous_response_id'] = true;
            }
        }
        if ($current_provider === 'Google' && $grounding_metadata !== null) {
            $log_bot_data['grounding_metadata'] = $grounding_metadata;
        }
        if (!empty($citations)) {
            $log_bot_data['citations'] = $citations;
        }
        if (!empty($vector_search_scores)) {
            $log_bot_data['vector_search_scores'] = $vector_search_scores;
        }
        $bot_log_result = $log_storage->log_message($log_bot_data);

        $tokens_consumed = $final_usage_data['total_tokens'] ?? 0;
        if ($token_manager && $tokens_consumed > 0) { // Check if token_manager is available
            $module_for_tokens = $current_stream_context;
            $context_id_for_tokens = null;

            if ($module_for_tokens === 'chat' && !empty($log_base_data['bot_id'])) {
                $context_id_for_tokens = $log_base_data['bot_id'];
            } elseif ($module_for_tokens === 'ai_forms') {
                if ($log_base_data['is_guest']) {
                    $context_id_for_tokens = GuestTableConstants::AI_FORMS_GUEST_CONTEXT_ID;
                } else {
                    $context_id_for_tokens = null; // Correct for logged-in users with a generic AI Forms limit
                }
            } elseif ($module_for_tokens === 'content_writer') {
                if ($log_base_data['is_guest']) {
                    $context_id_for_tokens = GuestTableConstants::CONTENT_WRITER_GUEST_CONTEXT_ID;
                } else {
                    $context_id_for_tokens = null;
                }
            }


            if ($context_id_for_tokens !== null || !$log_base_data['is_guest']) {
                $usage_context = [
                    'provider' => $current_provider,
                    'model' => $current_model,
                    'usage_data' => is_array($final_usage_data) ? $final_usage_data : [],
                ];

                if ($module_for_tokens === 'chat' && !empty($log_base_data['bot_id'])) {
                    $usage_context['operation'] = 'chat';
                } elseif ($module_for_tokens === 'ai_forms') {
                    $usage_context['operation'] = 'form_submit';
                    if (!empty($log_base_data['form_id'])) {
                        $usage_context['form_id'] = absint($log_base_data['form_id']);
                        $usage_context['pricing_scope_type'] = 'ai_form';
                        $usage_context['pricing_scope_id'] = absint($log_base_data['form_id']);
                    }
                }

                $token_manager->record_token_usage(
                    $log_base_data['user_id'],
                    $log_base_data['session_id'],
                    $context_id_for_tokens,
                    $tokens_consumed,
                    $module_for_tokens,
                    $usage_context
                );
            }
        }

        if ($current_stream_context === 'chat' && is_array($bot_log_result)) {
            emit_chatbot_response_generated_event_logic(
                $log_storage,
                $log_base_data,
                $full_bot_response,
                $current_provider,
                $current_model,
                $current_conversation_uuid,
                $current_bot_message_id,
                $bot_log_result
            );
        }

        if ($current_stream_context === 'content_writer' && class_exists(AIPKit_Event_Webhooks::class)) {
            AIPKit_Event_Webhooks::emit(
                'content.generated',
                [
                    'content' => $full_bot_response,
                    'conversation' => [
                        'id' => $current_conversation_uuid,
                    ],
                    'ai' => [
                        'provider' => $current_provider,
                        'model' => $current_model,
                    ],
                    'actor' => [
                        'type' => !empty($log_base_data['is_guest']) ? 'guest' : 'user',
                        'user_id' => !empty($log_base_data['user_id']) ? (int) $log_base_data['user_id'] : null,
                    ],
                ],
                [
                    'module' => 'content_writer',
                    'origin' => 'direct_stream',
                    'resource' => [
                        'type' => 'content_generation',
                        'id' => $current_conversation_uuid ?: $current_bot_message_id,
                        'label' => __('Generated content', 'gpt3-ai-content-generator'),
                    ],
                    'meta' => [
                        'provider' => $current_provider,
                        'model' => $current_model,
                        'conversation_uuid' => $current_conversation_uuid,
                    ],
                    'idempotency_key' => sha1(implode('|', [
                        'content.generated',
                        'direct_stream',
                        (string) $current_conversation_uuid,
                        (string) $current_bot_message_id,
                        $current_provider ?: '',
                        $current_model ?: '',
                    ])),
                ]
            );
        }

        if ($current_stream_context === 'ai_forms') {
            emit_ai_forms_form_submitted_event_logic(
                $log_base_data,
                $full_bot_response,
                $current_provider,
                $current_model,
                $current_conversation_uuid,
                $current_bot_message_id
            );
        }

    } elseif (empty($current_bot_message_id)) {
        // Cannot log bot response because current_bot_message_id is empty. This indicates an internal error state.
    } elseif ($error_occurred) {
        // Skipped logging a successful response because an error was flagged earlier in the process.
    } elseif (empty($full_bot_response)) {
        if (function_exists(__NAMESPACE__ . '\log_bot_error_logic')) {
            log_bot_error_logic($processorInstance, "(Empty Response)");
        }
    }
}
