<?php

// File: classes/chat/core/ai-service/generate-response/ai-params/apply-openai-vector-tool-config.php
// Status: NEW FILE

namespace WPAICG\Chat\Core\AIService\GenerateResponse\AiParams;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Applies OpenAI Vector Store tool configuration to AI parameters.
 *
 * @param array &$final_ai_params Reference to the final AI parameters array to be modified.
 * @param array $bot_settings Bot settings.
 * @param string|null $frontend_active_openai_vs_id Active OpenAI Vector Store ID from frontend.
 */
function apply_openai_vector_tool_config_logic(
    array &$final_ai_params,
    array $bot_settings,
    ?string $frontend_active_openai_vs_id
): void {
    $vector_store_ids_to_use_for_tool = $bot_settings['openai_vector_store_ids'] ?? [];
    if ($frontend_active_openai_vs_id && !in_array($frontend_active_openai_vs_id, $vector_store_ids_to_use_for_tool, true)) {
        $vector_store_ids_to_use_for_tool[] = $frontend_active_openai_vs_id;
    }
    $vector_store_ids_to_use_for_tool = array_unique(array_filter($vector_store_ids_to_use_for_tool));
    $vector_top_k_openai = absint($bot_settings['vector_store_top_k'] ?? 3);
    $vector_top_k_openai = max(1, min($vector_top_k_openai, 20));

    if (($bot_settings['enable_vector_store'] ?? '0') === '1' &&
        ($bot_settings['vector_store_provider'] ?? '') === 'openai' &&
        !empty($vector_store_ids_to_use_for_tool)) {
        $final_ai_params['vector_store_tool_config'] = [
            'type'             => 'file_search',
            'vector_store_ids' => $vector_store_ids_to_use_for_tool,
            'max_num_results'  => $vector_top_k_openai
        ];
        error_log("ApplyOpenAIVectorToolConfig Logic: Added vector_store_tool_config. VS IDs: " . implode(', ', $vector_store_ids_to_use_for_tool));
    }
}
