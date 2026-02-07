<?php
// File: classes/core/providers/openrouter/format-sse.php
// Status: NEW FILE

namespace WPAICG\Core\Providers\OpenRouter\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the format_sse static method of OpenRouterPayloadFormatter.
 *
 * @param array  $messages Formatted messages array (user/assistant).
 * @param string $system_instruction System instructions.
 * @param array  $ai_params AI parameters.
 * @param string $model Model name.
 * @return array The formatted SSE payload.
 */
function format_sse_logic_for_payload_formatter(array $messages, string $system_instruction, array $ai_params, string $model): array {
    // The history array already contains user/assistant messages; system instructions are merged by shared formatter.
    $payload = _shared_format_logic($system_instruction, $messages, $ai_params, $model);
    $payload['stream'] = true;
    return $payload;
}
