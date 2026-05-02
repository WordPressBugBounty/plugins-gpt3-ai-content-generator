<?php

// File: classes/chat/core/ai-service/generate-response/ai-params/apply-xai-web-search.php

namespace WPAICG\Chat\Core\AIService\GenerateResponse\AiParams;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Applies xAI Responses API web search parameters.
 *
 * @param array<string, mixed> $final_ai_params
 * @param array<string, mixed> $bot_settings
 * @param bool $frontend_web_search_active
 */
function apply_xai_web_search_logic(array &$final_ai_params, array $bot_settings, bool $frontend_web_search_active): void
{
    if (($bot_settings['xai_web_search_enabled'] ?? '0') !== '1') {
        return;
    }

    $final_ai_params['xai_web_search_tool_config'] = ['enabled' => true];
    $final_ai_params['frontend_web_search_active'] = $frontend_web_search_active;
}
