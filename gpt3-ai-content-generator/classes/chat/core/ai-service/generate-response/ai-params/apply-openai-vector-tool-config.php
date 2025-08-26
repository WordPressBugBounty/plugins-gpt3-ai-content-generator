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
function apply_openai_vector_tool_config_logic($final_ai_params, $bot_settings, $vector_store_ids_to_use_for_tool, $ai_service)
{

    if (empty($vector_store_ids_to_use_for_tool)) {
        $vector_store_ids_to_use_for_tool[] = $frontend_active_openai_vs_id;
    }
    $vector_store_ids_to_use_for_tool = array_unique(array_filter($vector_store_ids_to_use_for_tool));
    $vector_top_k_openai = absint($bot_settings['vector_store_top_k'] ?? 3);
    $vector_top_k_openai = max(1, min($vector_top_k_openai, 20));

    if (($bot_settings['enable_vector_store'] ?? '0') === '1' &&
        ($bot_settings['vector_store_provider'] ?? '') === 'openai' &&
        !empty($vector_store_ids_to_use_for_tool)) {

        // Convert confidence threshold percentage (0-100) to OpenAI score threshold (0.0-1.0)
        // OpenAI expects ranking_options.score_threshold in the file_search tool for server-side filtering
        $confidence_threshold_percent = (int)($bot_settings['vector_store_confidence_threshold'] ?? 20);
        $openai_score_threshold = round($confidence_threshold_percent / 100, 4); // Convert to 0.0-1.0 scale and round to 4 decimal places to avoid precision issues

        $final_ai_params['vector_store_tool_config'] = [
            'type'             => 'file_search',
            'vector_store_ids' => $vector_store_ids_to_use_for_tool,
            'max_num_results'  => $vector_top_k_openai,
            'ranking_options'  => [
                'score_threshold' => $openai_score_threshold
            ]
        ];
    }
}
