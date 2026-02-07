<?php
// File: classes/core/providers/openrouter/_shared-format.php
// Status: NEW FILE

namespace WPAICG\Core\Providers\OpenRouter\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Attach image inputs to the latest user message in OpenRouter Responses input format.
 *
 * @param array $input_array Existing input message array.
 * @param array $image_inputs Image payload array from chat/frontend flow.
 * @return array
 */
function _openrouter_attach_image_inputs(array $input_array, array $image_inputs): array {
    if (empty($image_inputs)) {
        return $input_array;
    }

    $last_key = array_key_last($input_array);
    if ($last_key === null || !isset($input_array[$last_key]['role']) || $input_array[$last_key]['role'] !== 'user') {
        return $input_array;
    }

    $current_content = $input_array[$last_key]['content'] ?? '';
    $user_text = '';
    if (is_string($current_content)) {
        $user_text = $current_content;
    } elseif (is_array($current_content)) {
        foreach ($current_content as $part) {
            if (!is_array($part)) {
                continue;
            }
            $part_type = $part['type'] ?? '';
            if (($part_type === 'text' || $part_type === 'input_text') && isset($part['text']) && is_string($part['text'])) {
                $user_text = $part['text'];
                break;
            }
        }
    }

    $content_parts = [];
    $has_valid_image = false;
    if ($user_text !== '') {
        $content_parts[] = [
            'type' => 'input_text',
            'text' => $user_text,
        ];
    }

    foreach ($image_inputs as $image_input) {
        if (!is_array($image_input)) {
            continue;
        }
        $mime_type = isset($image_input['type']) ? sanitize_text_field((string) $image_input['type']) : '';
        $base64_data = isset($image_input['base64']) ? trim((string) $image_input['base64']) : '';
        if ($mime_type === '' || $base64_data === '') {
            continue;
        }

        $content_parts[] = [
            'type' => 'input_image',
            'image_url' => 'data:' . $mime_type . ';base64,' . $base64_data,
        ];
        $has_valid_image = true;
    }

    if ($has_valid_image) {
        if (empty($content_parts)) {
            $content_parts[] = ['type' => 'input_text', 'text' => ''];
        }
        $input_array[$last_key]['content'] = $content_parts;
    }

    return $input_array;
}

/**
 * Shared formatting logic for OpenRouter Responses API payloads.
 *
 * @param string $instructions System instructions.
 * @param array  $history Conversation history.
 * @param array  $ai_params AI parameters.
 * @param string $model Model name.
 * @return array The formatted payload base.
 */
function _shared_format_logic(string $instructions, array $history, array $ai_params, string $model): array {
    $input_array = [];
    if (!empty($instructions)) {
        $input_array[] = ['role' => 'system', 'content' => $instructions];
    }

    foreach ($history as $msg) {
        if (!is_array($msg)) {
            continue;
        }
        $raw_role = $msg['role'] ?? '';
        $role = ($raw_role === 'bot') ? 'assistant' : $raw_role;
        if (!in_array($role, ['system', 'user', 'assistant', 'developer'], true)) {
            continue;
        }
        if ($role === 'system' && !empty($instructions)) {
            continue;
        }

        $content = $msg['content'] ?? '';
        if (is_string($content)) {
            $content = trim($content);
            if ($content === '') {
                continue;
            }
        } elseif (is_array($content)) {
            if (empty($content)) {
                continue;
            }
        } else {
            continue;
        }

        $input_array[] = ['role' => $role, 'content' => $content];
    }

    if (!empty($ai_params['image_inputs']) && is_array($ai_params['image_inputs'])) {
        $input_array = _openrouter_attach_image_inputs($input_array, $ai_params['image_inputs']);
    }

    $body_data = [
        'model' => $model,
        'input' => $input_array,
    ];

    $param_map = [
        'temperature'           => 'temperature',
        'max_completion_tokens' => 'max_output_tokens',
        'top_p'                 => 'top_p',
        'top_k'                 => 'top_k',
        'presence_penalty'      => 'presence_penalty',
        'frequency_penalty'     => 'frequency_penalty',
        'top_logprobs'          => 'top_logprobs',
    ];

    foreach ($param_map as $aipkit_key => $api_key) {
        if (!isset($ai_params[$aipkit_key])) {
            continue;
        }

        $value = $ai_params[$aipkit_key];
        if ($api_key === 'max_output_tokens' || $api_key === 'top_logprobs') {
            $body_data[$api_key] = absint($value);
            continue;
        }

        $body_data[$api_key] = floatval($value);
    }

    if (isset($ai_params['reasoning']) && is_array($ai_params['reasoning'])) {
        $body_data['reasoning'] = $ai_params['reasoning'];
    }

    $capability_map = get_capability_map_logic();
    $model_supports_web_search = model_supports_web_search_plugin_logic($model);
    $bot_allows_web_search = !empty($ai_params['web_search_tool_config']['enabled']);
    $frontend_requests_web_search = !empty($ai_params['frontend_web_search_active']);
    if (
        !empty($capability_map['web_search_plugin']) &&
        $model_supports_web_search &&
        $bot_allows_web_search &&
        $frontend_requests_web_search
    ) {
        $web_plugin = array_merge(
            ['id' => 'web'],
            sanitize_web_search_config_logic(
                is_array($ai_params['web_search_tool_config'] ?? null)
                    ? $ai_params['web_search_tool_config']
                    : []
            )
        );
        $body_data['plugins'] = [$web_plugin];
    }

    return $body_data;
}
