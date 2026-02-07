<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/stream/contexts/ai-forms/process/prepare-stream-data.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Contexts\AIForms\Process;

use WPAICG\Core\AIPKit_OpenAI_Reasoning;
use WPAICG\Core\Stream\Contexts\AIForms\SSEAIFormsStreamContextHandler;
use WPAICG\AIPKit_Providers;
use WPAICG\AIPKIT_AI_Settings;
use WPAICG\Core\Providers\Google\GoogleSettingsHandler;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Logs the user request and prepares the final data array for the SSE stream processor.
 *
 * @param SSEAIFormsStreamContextHandler $handlerInstance The instance of the context handler.
 * @param array $validated_params Validated request parameters.
 * @param array $form_config The configuration of the form.
 * @param string $final_user_prompt The final constructed user prompt.
 * @param string $system_instruction The system instruction, potentially with vector context.
 * @param array $vector_search_scores Array of captured vector search scores for logging.
 * @return array|WP_Error The structured data for the SSE processor, or a WP_Error on failure.
 */
function prepare_stream_data_logic(
    SSEAIFormsStreamContextHandler $handlerInstance,
    array $validated_params,
    array $form_config,
    string $final_user_prompt,
    string $system_instruction,
    array $vector_search_scores = []
): array|WP_Error {
    $log_storage = $handlerInstance->get_log_storage();
    $user_id = $validated_params['user_id'];
    $user_wp_role = $user_id ? implode(', ', wp_get_current_user()->roles) : null;
    $provider = $form_config['ai_provider'];
    $model = $form_config['ai_model'];

    // 1. Log User Request
    $base_log_data = [
        'bot_id'            => null,
        'user_id'           => $user_id ?: null,
        'session_id'        => $user_id ? null : $validated_params['session_id'],
        'conversation_uuid' => $validated_params['conversation_uuid'],
        'module'            => 'ai_forms',
        'is_guest'          => ($user_id === 0),
        'role'              => $user_wp_role,
        'ip_address'        => $validated_params['client_ip'],
        'form_id'           => $validated_params['form_id'],
    ];
    $bot_message_id = 'aif-msg-' . uniqid('', true);
    $base_log_data['bot_message_id'] = $bot_message_id;

    $log_user_data = array_merge($base_log_data, [
        'message_role'       => 'user',
        'message_content'    => "AI Form Submission (ID: {$validated_params['form_id']}): " . ($form_config['title'] ?? 'Untitled'),
        'timestamp'          => time(),
        'request_payload'    => ['form_id' => $validated_params['form_id'], 'inputs' => $validated_params['user_input_values'], 'constructed_prompt' => $final_user_prompt]
    ]);
    $log_storage->log_message($log_user_data);

    $form_id = absint($validated_params['form_id']);
    if ($form_id > 0) {
        $count_meta_key = '_aipkit_ai_form_submission_count';
        $current_count = (int) get_post_meta($form_id, $count_meta_key, true);
        update_post_meta($form_id, $count_meta_key, $current_count + 1);
    }

    // 2. Prepare AI and API Parameters
    $global_ai_params = AIPKIT_AI_Settings::get_ai_parameters();
    $ai_params_for_payload = $global_ai_params; // Start with all global defaults

    // Override with form-specific settings if they are numeric
    if (isset($form_config['temperature']) && is_numeric($form_config['temperature'])) {
        $ai_params_for_payload['temperature'] = floatval($form_config['temperature']);
    }
    if (isset($form_config['max_tokens']) && is_numeric($form_config['max_tokens'])) {
        $ai_params_for_payload['max_completion_tokens'] = absint($form_config['max_tokens']);
    }
    if (isset($form_config['top_p']) && is_numeric($form_config['top_p'])) {
        $ai_params_for_payload['top_p'] = floatval($form_config['top_p']);
    }
    if (isset($form_config['frequency_penalty']) && is_numeric($form_config['frequency_penalty'])) {
        $ai_params_for_payload['frequency_penalty'] = floatval($form_config['frequency_penalty']);
    }
    if (isset($form_config['presence_penalty']) && is_numeric($form_config['presence_penalty'])) {
        $ai_params_for_payload['presence_penalty'] = floatval($form_config['presence_penalty']);
    }
    // Add reasoning effort to AI params
    if ($provider === 'OpenAI') {
        $reasoning_effort = AIPKit_OpenAI_Reasoning::normalize_effort_for_model(
            (string) $model,
            $form_config['reasoning_effort'] ?? ''
        );
        if ($reasoning_effort !== '') {
            $ai_params_for_payload['reasoning'] = ['effort' => $reasoning_effort];
        }
    }


    if ($provider === 'Google' && class_exists(GoogleSettingsHandler::class)) {
        $ai_params_for_payload['safety_settings'] = GoogleSettingsHandler::get_safety_settings();
    }
    $ai_params_for_payload['model_id_for_grounding'] = $model;

    // Vector Store Tool Config (OpenAI)
    $is_vector_enabled = ($form_config['enable_vector_store'] ?? '0') === '1';
    $is_openai_vector_provider = ($form_config['vector_store_provider'] ?? '') === 'openai';
    $has_vector_store_ids = !empty($form_config['openai_vector_store_ids']) && is_array($form_config['openai_vector_store_ids']);

    if ($provider === 'OpenAI' && $is_vector_enabled && $is_openai_vector_provider && $has_vector_store_ids) {
        $vector_top_k = isset($form_config['vector_store_top_k']) ? absint($form_config['vector_store_top_k']) : 3;
        $vector_top_k = max(1, min($vector_top_k, 20));

        // Get confidence threshold and convert to OpenAI score threshold
        $confidence_threshold_percent = (int)($form_config['vector_store_confidence_threshold'] ?? 20);
        $openai_score_threshold = round($confidence_threshold_percent / 100, 4); // Round to avoid precision issues

        $ai_params_for_payload['vector_store_tool_config'] = [
            'type'             => 'file_search',
            'vector_store_ids' => $form_config['openai_vector_store_ids'],
            'max_num_results'  => $vector_top_k,
            'ranking_options'  => [
                'score_threshold' => $openai_score_threshold
            ]
        ];
    }

    // --- NEW: Add Web Search & Grounding Params ---
    if ($provider === 'OpenAI' && ($form_config['openai_web_search_enabled'] ?? '0') === '1') {
        $ai_params_for_payload['web_search_tool_config'] = ['enabled' => true];
        // For AI Forms, web search is implicitly active if the form setting is enabled.
        $ai_params_for_payload['frontend_web_search_active'] = true;
    }
    if ($provider === 'Claude' && ($form_config['claude_web_search_enabled'] ?? '0') === '1') {
        $split_domains = static function ($domains_raw): array {
            if (!is_string($domains_raw) || trim($domains_raw) === '') {
                return [];
            }
            $parts = preg_split('/[\r\n,]+/', $domains_raw);
            if (!is_array($parts)) {
                return [];
            }
            $domains = array_values(array_filter(array_map(static function ($part) {
                $domain = strtolower(trim((string) $part));
                if ($domain === '') {
                    return '';
                }
                $domain = preg_replace('/^https?:\/\//', '', $domain);
                $domain = trim((string) $domain, " \t\n\r\0\x0B/");
                if ($domain === '' || !preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $domain)) {
                    return '';
                }
                return $domain;
            }, $parts)));
            return array_values(array_unique($domains));
        };

        $web_search_config = [
            'enabled' => true,
            'type' => 'web_search_20250305',
        ];

        $claude_max_uses = isset($form_config['claude_web_search_max_uses'])
            ? absint($form_config['claude_web_search_max_uses'])
            : 5;
        $web_search_config['max_uses'] = max(1, min($claude_max_uses, 20));

        $allowed_domains = $split_domains($form_config['claude_web_search_allowed_domains'] ?? '');
        $blocked_domains = $split_domains($form_config['claude_web_search_blocked_domains'] ?? '');
        if (!empty($allowed_domains)) {
            $web_search_config['allowed_domains'] = $allowed_domains;
        } elseif (!empty($blocked_domains)) {
            $web_search_config['blocked_domains'] = $blocked_domains;
        }

        if (($form_config['claude_web_search_loc_type'] ?? 'none') === 'approximate') {
            $claude_user_location = array_filter([
                'country' => $form_config['claude_web_search_loc_country'] ?? null,
                'city' => $form_config['claude_web_search_loc_city'] ?? null,
                'region' => $form_config['claude_web_search_loc_region'] ?? null,
                'timezone' => $form_config['claude_web_search_loc_timezone'] ?? null,
            ]);
            if (!empty($claude_user_location)) {
                $claude_user_location['type'] = 'approximate';
                $web_search_config['user_location'] = $claude_user_location;
            }
        }

        $cache_ttl = $form_config['claude_web_search_cache_ttl'] ?? 'none';
        if (in_array($cache_ttl, ['5m', '1h'], true)) {
            $web_search_config['cache_control'] = [
                'type' => 'ephemeral',
                'ttl' => $cache_ttl,
            ];
        }

        $ai_params_for_payload['web_search_tool_config'] = $web_search_config;
        // For AI Forms, web search is implicitly active if the form setting is enabled.
        $ai_params_for_payload['frontend_web_search_active'] = true;
    }
    if ($provider === 'OpenRouter' && ($form_config['openrouter_web_search_enabled'] ?? '0') === '1') {
        $web_search_config = ['enabled' => true];

        $openrouter_engine = isset($form_config['openrouter_web_search_engine'])
            ? sanitize_key((string) $form_config['openrouter_web_search_engine'])
            : 'auto';
        if (in_array($openrouter_engine, ['native', 'exa'], true)) {
            $web_search_config['engine'] = $openrouter_engine;
        }

        $openrouter_max_results = isset($form_config['openrouter_web_search_max_results'])
            ? absint($form_config['openrouter_web_search_max_results'])
            : 5;
        $web_search_config['max_results'] = max(1, min($openrouter_max_results, 10));

        $openrouter_search_prompt = isset($form_config['openrouter_web_search_search_prompt'])
            ? sanitize_textarea_field((string) $form_config['openrouter_web_search_search_prompt'])
            : '';
        if ($openrouter_search_prompt !== '') {
            $web_search_config['search_prompt'] = $openrouter_search_prompt;
        }

        $ai_params_for_payload['web_search_tool_config'] = $web_search_config;
        $ai_params_for_payload['frontend_web_search_active'] = true;
    }
    if ($provider === 'Google' && ($form_config['google_search_grounding_enabled'] ?? '0') === '1') {
        // For AI Forms, grounding is implicitly active if the form setting is enabled.
        $ai_params_for_payload['frontend_google_search_grounding_active'] = true;
    }
    // --- END NEW ---

        $provData = AIPKit_Providers::get_provider_data($provider);
    $api_params_for_stream = [
        'api_key' => $provData['api_key'] ?? '', 'base_url' => $provData['base_url'] ?? '', 'api_version' => $provData['api_version'] ?? '',
        'azure_endpoint' => ($provider === 'Azure') ? ($provData['endpoint'] ?? '') : '',
        'stream' => true,
    ];

    if (empty($api_params_for_stream['api_key']) && $provider !== 'Ollama') {
        /* translators: %s: The name of the AI provider (e.g., OpenAI, Google). */
        return new WP_Error('missing_api_key_ai_forms_logic', sprintf(__('API key missing for %s (AI Forms).', 'gpt3-ai-content-generator'), $provider), ['status' => 400]);
    }
    if ($provider === 'Azure' && empty($api_params_for_stream['azure_endpoint'])) {
        return new WP_Error('missing_azure_endpoint_ai_forms_logic', __('Azure endpoint is missing (AI Forms).', 'gpt3-ai-content-generator'), ['status' => 400]);
    }

    // 3. Construct and return the final data array
    return [
        'provider'                      => $provider,
        'model'                         => $model,
        'user_message'                  => $final_user_prompt,
        'history'                       => [], // AI Forms do not have chat history
        'system_instruction_filtered'   => $system_instruction, // Pass the (potentially new) system instruction
        'api_params'                    => $api_params_for_stream,
        'ai_params'                     => $ai_params_for_payload,
        'conversation_uuid'             => $validated_params['conversation_uuid'],
        'base_log_data'                 => $base_log_data,
        'bot_message_id'                => $bot_message_id,
        'vector_search_scores'          => $vector_search_scores, // Include captured vector search scores
    ];
}
