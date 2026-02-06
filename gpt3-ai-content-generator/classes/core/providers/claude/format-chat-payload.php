<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build non-stream Claude payload.
 */
function format_chat_payload_logic(
    ClaudeProviderStrategy $strategyInstance,
    string $user_message,
    string $instructions,
    array $history,
    array $ai_params,
    string $model
): array {
    return build_claude_payload_shared($instructions, $history, $user_message, $ai_params, $model, false);
}
