<?php
// File: classes/core/providers/xai/build-sse-payload.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * @param XAIProviderStrategy $strategyInstance
 * @param array<int, array<string, mixed>> $messages
 * @param string|array|null $system_instruction
 * @param array<string, mixed> $ai_params
 * @param string $model
 * @return array<string, mixed>
 */
function build_sse_payload_logic(
    XAIProviderStrategy $strategyInstance,
    array $messages,
    $system_instruction,
    array $ai_params,
    string $model
): array {
    $instructions = is_string($system_instruction) ? $system_instruction : '';
    return xai_format_responses_payload($instructions, $messages, '', $ai_params, $model, true);
}
