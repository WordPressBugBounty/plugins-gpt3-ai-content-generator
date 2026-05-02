<?php
// File: classes/core/providers/xai/format-chat-payload.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * @param XAIProviderStrategy $strategyInstance
 * @param string $user_message
 * @param string $instructions
 * @param array<int, array<string, mixed>> $history
 * @param array<string, mixed> $ai_params
 * @param string $model
 * @return array<string, mixed>
 */
function format_chat_payload_logic(
    XAIProviderStrategy $strategyInstance,
    string $user_message,
    string $instructions,
    array $history,
    array $ai_params,
    string $model
): array {
    return xai_format_responses_payload($instructions, $history, $user_message, $ai_params, $model, false);
}

/**
 * @param string $instructions
 * @param array<int, array<string, mixed>> $messages
 * @param string $user_message
 * @param array<string, mixed> $ai_params
 * @param string $model
 * @param bool $stream
 * @return array<string, mixed>
 */
function xai_format_responses_payload(
    string $instructions,
    array $messages,
    string $user_message,
    array $ai_params,
    string $model,
    bool $stream
): array {
    $has_image_inputs = !empty($ai_params['image_inputs']) && is_array($ai_params['image_inputs']);
    $body = [
        'model' => $model,
        'input' => xai_build_input_messages($instructions, $messages, $user_message, $ai_params),
        'store' => isset($ai_params['xai_store_conversation']) ? xai_truthy($ai_params['xai_store_conversation']) : false,
    ];

    if ($stream) {
        $body['stream'] = true;
    }

    if (!$has_image_inputs && !empty($ai_params['xai_previous_response_id']) && is_string($ai_params['xai_previous_response_id'])) {
        $body['previous_response_id'] = $ai_params['xai_previous_response_id'];
        $body['store'] = true;
    }
    if ($has_image_inputs) {
        $body['store'] = false;
    }

    $tools = xai_build_tools($ai_params);
    if (!empty($tools)) {
        $body['tools'] = $tools;
    }

    if (isset($ai_params['temperature'])) {
        $body['temperature'] = floatval($ai_params['temperature']);
    }
    if (isset($ai_params['top_p'])) {
        $body['top_p'] = floatval($ai_params['top_p']);
    }
    if (isset($ai_params['max_completion_tokens'])) {
        $body['max_output_tokens'] = absint($ai_params['max_completion_tokens']);
    } elseif (isset($ai_params['max_output_tokens'])) {
        $body['max_output_tokens'] = absint($ai_params['max_output_tokens']);
    }
    if (isset($ai_params['xai_reasoning']) && is_array($ai_params['xai_reasoning'])) {
        $body['reasoning'] = $ai_params['xai_reasoning'];
    }

    return $body;
}
