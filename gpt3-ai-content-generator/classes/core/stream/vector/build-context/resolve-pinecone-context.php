<?php

// File: classes/core/stream/vector/build-context/resolve-pinecone-context.php
// Status: NEW FILE

namespace WPAICG\Core\Stream\Vector\BuildContext;

use WPAICG\AIPKit_Providers;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WPAICG\Core\AIPKit_AI_Caller;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Resolves context from Pinecone vector store.
 *
 * @param \WPAICG\Core\AIPKit_AI_Caller $ai_caller
 * @param \WPAICG\Vector\AIPKit_Vector_Store_Manager $vector_store_manager
 * @param string $user_message
 * @param array $bot_settings
 * @param string|null $frontend_active_pinecone_index_name
 * @param string|null $frontend_active_pinecone_namespace
 * @param int $vector_top_k
 * @param \wpdb $wpdb
 * @param string $data_source_table_name
 * @return string Formatted Pinecone context results.
 */
function resolve_pinecone_context_logic(
    AIPKit_AI_Caller $ai_caller,
    AIPKit_Vector_Store_Manager $vector_store_manager,
    string $user_message,
    array $bot_settings,
    ?string $frontend_active_pinecone_index_name,
    ?string $frontend_active_pinecone_namespace,
    int $vector_top_k,
    \wpdb $wpdb,
    string $data_source_table_name
): string {
    $pinecone_results = "";
    $pinecone_index_name_from_settings = $bot_settings['pinecone_index_name'] ?? '';
    $vector_embedding_provider = $bot_settings['vector_embedding_provider'] ?? '';
    $vector_embedding_model = $bot_settings['vector_embedding_model'] ?? '';

    if (empty($pinecone_index_name_from_settings) || empty($vector_embedding_provider) || empty($vector_embedding_model)) {
        return "";
    }

    if (!class_exists(AIPKit_Providers::class)) {
        $providers_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_providers.php';
        if (file_exists($providers_path)) {
            require_once $providers_path;
        } else {
            error_log("ResolvePineconeContext Logic: AIPKit_Providers class file not found.");
            return "";
        }
    }
    $pinecone_api_config = AIPKit_Providers::get_provider_data('Pinecone');
    if (empty($pinecone_api_config['api_key'])) {
        error_log("ResolvePineconeContext Logic: Pinecone API key missing for vector pre-search.");
        return "";
    }

    $embedding_provider_normalized = normalize_embedding_provider_logic($vector_embedding_provider);
    $query_vector_values_or_error = resolve_embedding_vector_logic($ai_caller, $user_message, $embedding_provider_normalized, $vector_embedding_model);

    if (is_wp_error($query_vector_values_or_error)) {
        return ""; // Error already logged by resolver
    }
    $query_vector_values = $query_vector_values_or_error;

    $index_to_query = $pinecone_index_name_from_settings;
    $pinecone_results_this_pass = "";

    // 1. Search with file-specific namespace if provided
    if (!empty($frontend_active_pinecone_namespace)) {
        $query_vector_for_file_context = ['vector' => $query_vector_values, 'namespace' => $frontend_active_pinecone_namespace];
        $file_search_results = $vector_store_manager->query_vectors('Pinecone', $index_to_query, $query_vector_for_file_context, $vector_top_k, [], $pinecone_api_config);
        if (!is_wp_error($file_search_results) && !empty($file_search_results)) {
            $formatted_file_results = "";
            foreach ($file_search_results as $item) {
                $content_snippet = $item['metadata']['original_content'] ?? ($item['metadata']['text_content'] ?? null);
                if (empty($content_snippet) && isset($item['id'])) {
                    $log_entry = $wpdb->get_row($wpdb->prepare("SELECT indexed_content FROM {$data_source_table_name} WHERE provider = %s AND vector_store_id = %s AND batch_id = %s AND file_id = %s ORDER BY timestamp DESC LIMIT 1", 'Pinecone', $index_to_query, $frontend_active_pinecone_namespace, $item['id']), ARRAY_A);
                    if ($log_entry && !empty($log_entry['indexed_content'])) {
                        $content_snippet = $log_entry['indexed_content'];
                    }
                }
                if (!empty($content_snippet)) {
                    $formatted_file_results .= "- " . trim($content_snippet) . "\n";
                }
            }
            if (!empty($formatted_file_results)) {
                $pinecone_results_this_pass .= "Context from Uploaded File (Index {$index_to_query}, Namespace: {$frontend_active_pinecone_namespace}):\n" . $formatted_file_results . "\n";
            }
        } elseif (is_wp_error($file_search_results)) {
            error_log("ResolvePineconeContext Logic: Error Pinecone file-specific search: " . $file_search_results->get_error_message());
        }
    }

    // 2. Search general bot knowledge (default/empty namespace)
    $query_vector_for_general_context = ['vector' => $query_vector_values]; // No namespace implies default
    $general_search_results = $vector_store_manager->query_vectors('Pinecone', $index_to_query, $query_vector_for_general_context, $vector_top_k, [], $pinecone_api_config);
    if (!is_wp_error($general_search_results) && !empty($general_search_results)) {
        $formatted_general_results = "";
        foreach ($general_search_results as $item) {
            // Skip if this result was already part of the file-specific context (if a namespace was used)
            if (!empty($frontend_active_pinecone_namespace) && ($item['metadata']['namespace'] ?? null) === $frontend_active_pinecone_namespace) {
                continue;
            }
            $content_snippet = $item['metadata']['original_content'] ?? ($item['metadata']['text_content'] ?? null);
            if (empty($content_snippet) && isset($item['id'])) {
                $log_entry = $wpdb->get_row($wpdb->prepare("SELECT indexed_content FROM {$data_source_table_name} WHERE provider = %s AND vector_store_id = %s AND (batch_id IS NULL OR batch_id = '') AND file_id = %s ORDER BY timestamp DESC LIMIT 1", 'Pinecone', $index_to_query, $item['id']), ARRAY_A);
                if ($log_entry && !empty($log_entry['indexed_content'])) {
                    $content_snippet = $log_entry['indexed_content'];
                }
            }
            if (!empty($content_snippet)) {
                $formatted_general_results .= "- " . trim($content_snippet) . "\n";
            }
        }
        if (!empty($formatted_general_results)) {
            $pinecone_results_this_pass .= "General Knowledge from Bot (Index {$index_to_query}):\n" . $formatted_general_results . "\n";
        }
    } elseif (is_wp_error($general_search_results)) {
        error_log("ResolvePineconeContext Logic: Error Pinecone general context search: " . $general_search_results->get_error_message());
    }

    if (!empty($pinecone_results_this_pass)) {
        $pinecone_results = $pinecone_results_this_pass;
    }

    return $pinecone_results;
}
