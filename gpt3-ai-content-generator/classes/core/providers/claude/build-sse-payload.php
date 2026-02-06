<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build Claude streaming payload.
 */
function build_sse_payload_logic(
    ClaudeProviderStrategy $strategyInstance,
    array $messages,
    $system_instruction,
    array $ai_params,
    string $model
): array {
    $instructions = is_string($system_instruction) ? $system_instruction : '';
    return build_claude_payload_shared($instructions, $messages, '', $ai_params, $model, true);
}
