<?php

// File: classes/core/stream/vector/build-context/resolve-openai-context.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Vector\BuildContext;

use WPAICG\AIPKit_Providers;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Resolves context from OpenAI vector stores.
 *
 * @param \WPAICG\Vector\AIPKit_Vector_Store_Manager $vector_store_manager
 * @param string $user_message
 * @param array $bot_settings
 * @param string $main_provider
 * @param string|null $frontend_active_openai_vs_id
 * @param int $vector_top_k
 * @return string Formatted OpenAI context results.
 */
function resolve_openai_context_logic(
    AIPKit_Vector_Store_Manager $vector_store_manager,
    string $user_message,
    array $bot_settings,
    string $main_provider,
    ?string $frontend_active_openai_vs_id,
    int $vector_top_k
): string {
    $openai_results = "";
    $openai_vector_store_ids_from_settings = $bot_settings['openai_vector_store_ids'] ?? [];

    $final_openai_vector_store_ids = $openai_vector_store_ids_from_settings;
    if ($frontend_active_openai_vs_id && !in_array($frontend_active_openai_vs_id, $final_openai_vector_store_ids, true)) {
        $final_openai_vector_store_ids[] = $frontend_active_openai_vs_id;
    }
    $final_openai_vector_store_ids = array_unique(array_filter($final_openai_vector_store_ids));

    if (!empty($final_openai_vector_store_ids) && $main_provider !== 'OpenAI') {
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

                $search_query_vector = ['query_text' => $user_message];
                $search_results = $vector_store_manager->query_vectors('OpenAI', $current_vs_id, $search_query_vector, $vector_top_k, [], $openai_api_config);

                if (!is_wp_error($search_results) && !empty($search_results)) {
                    $current_store_results = "";
                    foreach ($search_results as $item) {
                        if (!empty($item['content'])) {
                            $textContent = is_array($item['content']) ? implode(" ", array_column(array_filter($item['content'], fn ($p) => $p['type'] === 'text'), 'text')) : $item['content'];
                            if (!empty(trim($textContent))) {
                                $current_store_results .= "- " . trim($textContent) . "\n";
                                $total_results_added++;
                            }
                        }
                    }
                    if (!empty($current_store_results)) {
                        $openai_results .= "Context from Store ID {$current_vs_id}:\n" . $current_store_results . "\n";
                    }
                }
            }
        }
    }
    return $openai_results;
}