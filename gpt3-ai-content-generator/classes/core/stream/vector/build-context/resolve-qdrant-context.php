<?php

// File: classes/core/stream/vector/build-context/resolve-qdrant-context.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Vector\BuildContext;

use WPAICG\AIPKit_Providers;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WPAICG\Core\AIPKit_AI_Caller;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Resolves context from Qdrant vector store.
 *
 * @param \WPAICG\Core\AIPKit_AI_Caller $ai_caller
 * @param \WPAICG\Vector\AIPKit_Vector_Store_Manager $vector_store_manager
 * @param string $user_message
 * @param array $bot_settings
 * @param string|null $frontend_active_qdrant_collection_name
 * @param string|null $frontend_active_qdrant_file_upload_context_id
 * @param int $vector_top_k
 * @param \wpdb $wpdb
 * @param string $data_source_table_name
 * @return string Formatted Qdrant context results.
 */
function resolve_qdrant_context_logic(
    AIPKit_AI_Caller $ai_caller,
    AIPKit_Vector_Store_Manager $vector_store_manager,
    string $user_message,
    array $bot_settings,
    ?string $frontend_active_qdrant_collection_name, // Note: Qdrant doesn't use frontend-defined collection name for bot-level context, bot setting is primary
    ?string $frontend_active_qdrant_file_upload_context_id,
    int $vector_top_k,
    \wpdb $wpdb,
    string $data_source_table_name
): string {
    $qdrant_results = "";
    $qdrant_collection_name_from_settings = $bot_settings['qdrant_collection_name'] ?? '';
    $vector_embedding_provider = $bot_settings['vector_embedding_provider'] ?? '';
    $vector_embedding_model = $bot_settings['vector_embedding_model'] ?? ''; // This is the correctly defined variable

    if (empty($qdrant_collection_name_from_settings) || empty($vector_embedding_provider) || empty($vector_embedding_model)) {
        // If $vector_embedding_model is empty, this condition is met, and it returns early.
        // This prevents "Undefined variable" if the model is essential and missing.
        return "";
    }

    if (!class_exists(AIPKit_Providers::class)) {
        $providers_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_providers.php';
        if (file_exists($providers_path)) {
            require_once $providers_path;
        } else {
            return "";
        }
    }
    $qdrant_api_config = AIPKit_Providers::get_provider_data('Qdrant');
    if (empty($qdrant_api_config['url']) || empty($qdrant_api_config['api_key'])) {
        return "";
    }

    $embedding_provider_normalized = normalize_embedding_provider_logic($vector_embedding_provider);

    // --- MODIFIED: Ensure the correct variable $vector_embedding_model is passed ---
    $query_vector_values_or_error = resolve_embedding_vector_logic(
        $ai_caller,
        $user_message,
        $embedding_provider_normalized,
        $vector_embedding_model // Use the defined $vector_embedding_model
    );
    // --- END MODIFICATION ---

    if (is_wp_error($query_vector_values_or_error)) {
        // Error is already logged by resolve_embedding_vector_logic if it fails.
        return "";
    }
    $query_vector_values = $query_vector_values_or_error;

    $collection_to_query = $qdrant_collection_name_from_settings;
    $qdrant_results_this_pass = "";

    // 1. Search with file-specific context ID if provided
    if (!empty($frontend_active_qdrant_file_upload_context_id)) {
        $file_specific_filter = [
            'must' => [
                ['key' => 'payload.file_upload_context_id', 'match' => ['value' => $frontend_active_qdrant_file_upload_context_id]]
            ]
        ];
        $query_vector_for_file_context = ['vector' => $query_vector_values];
        $file_search_results = $vector_store_manager->query_vectors('Qdrant', $collection_to_query, $query_vector_for_file_context, $vector_top_k, $file_specific_filter, $qdrant_api_config);

        if (!is_wp_error($file_search_results) && !empty($file_search_results)) {
            $formatted_file_results = "";
            $file_results_added = 0;
            foreach ($file_search_results as $item) {
                $content_snippet = $item['metadata']['original_content'] ?? ($item['metadata']['text_content'] ?? null);
                if (!empty($content_snippet)) {
                    $formatted_file_results .= "- " . trim($content_snippet) . "\n";
                    $file_results_added++;
                }
            }
            if (!empty($formatted_file_results)) {
                $qdrant_results_this_pass .= "Context from Uploaded File (Collection {$collection_to_query}, File Context ID: {$frontend_active_qdrant_file_upload_context_id}):\n" . $formatted_file_results . "\n";
            }
        }
    }

    // 2. Search general bot knowledge
    $general_knowledge_filter = [];
    if (!empty($frontend_active_qdrant_file_upload_context_id)) {
        $general_knowledge_filter = [
            'must_not' => [
                ['key' => 'payload.file_upload_context_id', 'match' => ['value' => $frontend_active_qdrant_file_upload_context_id]]
            ]
        ];
    }

    $query_vector_for_general_context = ['vector' => $query_vector_values];
    $general_search_results = $vector_store_manager->query_vectors('Qdrant', $collection_to_query, $query_vector_for_general_context, $vector_top_k, $general_knowledge_filter, $qdrant_api_config);

    if (!is_wp_error($general_search_results) && !empty($general_search_results)) {
        $formatted_general_results = "";
        $general_results_added = 0;
        foreach ($general_search_results as $item) {
            $content_snippet = $item['metadata']['original_content'] ?? ($item['metadata']['text_content'] ?? null);
            if (empty($content_snippet) && isset($item['id'])) {
                $log_entry = $wpdb->get_row($wpdb->prepare("SELECT indexed_content FROM {$data_source_table_name} WHERE provider = 'Qdrant' AND vector_store_id = %s AND file_id = %s AND (batch_id IS NULL OR batch_id = '' OR batch_id NOT LIKE %s) ORDER BY timestamp DESC LIMIT 1", $collection_to_query, $item['id'], 'qdrant_chat_file_%'), ARRAY_A);
                if ($log_entry && !empty($log_entry['indexed_content'])) {
                    $content_snippet = $log_entry['indexed_content'];
                }
            }
            if (!empty($content_snippet)) {
                $formatted_general_results .= "- " . trim($content_snippet) . "\n";
                $general_results_added++;
            }
        }
        if (!empty($formatted_general_results)) {
            $qdrant_results_this_pass .= "General Knowledge from Bot (Collection {$collection_to_query}):\n" . $formatted_general_results . "\n";
        }
    }

    if (!empty($qdrant_results_this_pass)) {
        $qdrant_results = $qdrant_results_this_pass;
    }

    return $qdrant_results;
}
