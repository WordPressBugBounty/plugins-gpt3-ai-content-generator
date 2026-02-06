<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/stream/vector/build-context/resolve-openai-context.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Vector\BuildContext;

use WPAICG\AIPKit_Providers;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WPAICG\Vector\AIPKit_Vector_Store_Registry;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Resolves OpenAI vector search context.
 *
 * @param AIPKit_Vector_Store_Manager $vector_store_manager Instance of Vector Store Manager.
 * @param string $user_message The user's current message.
 * @param array $bot_settings The settings of the current bot.
 * @param string $main_provider The main AI provider being used for the chat.
 * @param string|null $frontend_active_openai_vs_id Optional active OpenAI Vector Store ID from frontend.
 * @param int $vector_top_k Number of results to fetch.
 * @param array|null &$vector_search_scores_output Optional reference to capture scores for logging.
 * @return string Formatted OpenAI context results.
 */
function resolve_openai_context_logic(
    AIPKit_Vector_Store_Manager $vector_store_manager,
    string $user_message,
    array $bot_settings,
    string $main_provider,
    ?string $frontend_active_openai_vs_id,
    int $vector_top_k,
    ?array &$vector_search_scores_output = null
): string {
    // For OpenAI main provider, use File Search tool instead of prompt injection.
    // For non-OpenAI providers, inject a concise context string from OpenAI vector stores.
    $openai_results = "";
    $should_inject_context = ($main_provider !== 'OpenAI');
    $openai_vector_store_ids_from_settings = $bot_settings['openai_vector_store_ids'] ?? [];
    $confidence_threshold_percent = (int)($bot_settings['vector_store_confidence_threshold'] ?? 20);
    $openai_score_threshold = round($confidence_threshold_percent / 100, 4); // Convert to 0.0-1.0 scale for OpenAI and round to avoid precision issues

    $final_openai_vector_store_ids = $openai_vector_store_ids_from_settings;
    if ($frontend_active_openai_vs_id && !in_array($frontend_active_openai_vs_id, $final_openai_vector_store_ids, true)) {
        $final_openai_vector_store_ids[] = $frontend_active_openai_vs_id;
    }
    $final_openai_vector_store_ids = array_unique(array_filter($final_openai_vector_store_ids));

    if (!empty($final_openai_vector_store_ids)) {
        if (!class_exists(AIPKit_Providers::class)) {
            $providers_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_providers.php';
            if (file_exists($providers_path)) {
                require_once $providers_path;
            } else {
                return "";
            }
        }
        $openai_api_config = AIPKit_Providers::get_provider_data('OpenAI');
        // Only proceed if the OpenAI API key is available for the pre-search.
        if (!empty($openai_api_config['api_key'])) {
            $total_results_added = 0;
            foreach ($final_openai_vector_store_ids as $current_vs_id) {
                if (empty($current_vs_id)) {
                    continue;
                }

                // Add ranking_options to the search query for OpenAI server-side filtering
                $search_query_vector = [
                    'query_text' => $user_message,
                    'ranking_options' => [
                        'score_threshold' => $openai_score_threshold
                    ]
                ];
                
                $search_results = $vector_store_manager->query_vectors('OpenAI', $current_vs_id, $search_query_vector, $vector_top_k, [], $openai_api_config);

                if (!is_wp_error($search_results) && !empty($search_results)) {
                    $current_store_results = "";
                    foreach ($search_results as $item) {
                        if (isset($item['score']) && (float)$item['score'] < $openai_score_threshold) {
                            continue;
                        }

                        if (!empty($item['content'])) {
                            $textContent = is_array($item['content']) ? implode(" ", array_column(array_filter($item['content'], fn ($p) => $p['type'] === 'text'), 'text')) : $item['content'];
                            if (!empty(trim($textContent))) {
                                $total_results_added++;
                                if ($should_inject_context) {
                                    $current_store_results .= "- " . trim($textContent) . "\n";
                                }
                                
                                // Capture score data if reference provided
                                if ($vector_search_scores_output !== null && isset($item['score'])) {
                                    // Get store name from registry
                                    $store_name = $current_vs_id; // fallback to ID
                                    $openai_stores = AIPKit_Vector_Store_Registry::get_registered_stores_by_provider('OpenAI');
                                    foreach ($openai_stores as $store) {
                                        if (isset($store['id']) && $store['id'] === $current_vs_id) {
                                            $store_name = $store['name'] ?? $current_vs_id;
                                            break;
                                        }
                                    }
                                    
                                    $vector_search_scores_output[] = [
                                        'provider' => 'OpenAI',
                                        'store_id' => $current_vs_id,
                                        'store_name' => $store_name,
                                        'result_id' => $item['id'] ?? null,
                                        'score' => $item['score'],
                                        'content_preview' => wp_trim_words(trim($textContent), 10, '...')
                                    ];
                                }
                            }
                        }
                    }
                    if ($should_inject_context && !empty($current_store_results)) {
                        $store_label = sanitize_text_field($store_name ?? $current_vs_id);
                        $openai_results .= "Context from OpenAI Vector Store ({$store_label}):\n" . $current_store_results . "\n";
                    }
                }
            }
        }
    }
    return $openai_results;
}
