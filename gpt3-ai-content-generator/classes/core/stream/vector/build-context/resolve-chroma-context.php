<?php

namespace WPAICG\Core\Stream\Vector\BuildContext;

use WPAICG\AIPKit_Providers;
use WPAICG\Core\AIPKit_AI_Caller;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Chroma context resolution only reads plugin-owned vector log tables with prepared scalar values.

/**
 * Resolves context from Chroma collections.
 *
 * @param AIPKit_AI_Caller $ai_caller
 * @param AIPKit_Vector_Store_Manager $vector_store_manager
 * @param string $user_message
 * @param array<string,mixed> $bot_settings
 * @param string|null $frontend_active_chroma_collection_name
 * @param string|null $frontend_active_chroma_file_upload_context_id
 * @param int $vector_top_k
 * @param \wpdb $wpdb
 * @param string $data_source_table_name
 * @param array|null &$vector_search_scores_output Optional reference to capture scores for logging.
 * @return string Formatted Chroma context results.
 */
function resolve_chroma_context_logic(
    AIPKit_AI_Caller $ai_caller,
    AIPKit_Vector_Store_Manager $vector_store_manager,
    string $user_message,
    array $bot_settings,
    ?string $frontend_active_chroma_collection_name,
    ?string $frontend_active_chroma_file_upload_context_id,
    int $vector_top_k,
    \wpdb $wpdb,
    string $data_source_table_name,
    ?array &$vector_search_scores_output = null
): string {
    $chroma_collection_name_from_settings = $bot_settings['chroma_collection_name'] ?? '';
    $chroma_collection_names_multi = [];
    if (!empty($bot_settings['chroma_collection_names']) && is_array($bot_settings['chroma_collection_names'])) {
        $chroma_collection_names_multi = array_values(array_unique(array_filter(array_map('strval', $bot_settings['chroma_collection_names']))));
    } elseif (!empty($chroma_collection_name_from_settings)) {
        $chroma_collection_names_multi = [$chroma_collection_name_from_settings];
    }

    $active_chroma_collection_name = $frontend_active_chroma_collection_name !== null
        ? trim(sanitize_text_field($frontend_active_chroma_collection_name))
        : (isset($bot_settings['active_chroma_collection_name']) ? trim((string) $bot_settings['active_chroma_collection_name']) : '');
    if ($active_chroma_collection_name !== '' && !in_array($active_chroma_collection_name, $chroma_collection_names_multi, true)) {
        $chroma_collection_names_multi[] = $active_chroma_collection_name;
    }

    $vector_embedding_provider = $bot_settings['vector_embedding_provider'] ?? '';
    $vector_embedding_model = $bot_settings['vector_embedding_model'] ?? '';
    $confidence_threshold_percent = (int) ($bot_settings['vector_store_confidence_threshold'] ?? 20);
    $chroma_score_threshold = round($confidence_threshold_percent / 100, 4);

    if (empty($chroma_collection_names_multi) || empty($vector_embedding_provider) || empty($vector_embedding_model)) {
        return '';
    }

    if (!class_exists(AIPKit_Providers::class)) {
        $providers_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_providers.php';
        if (file_exists($providers_path)) {
            require_once $providers_path;
        } else {
            return '';
        }
    }

    $chroma_api_config = AIPKit_Providers::get_provider_data('Chroma');
    if (empty($chroma_api_config['url'])) {
        return '';
    }

    $embedding_provider_normalized = normalize_embedding_provider_logic($vector_embedding_provider);
    $query_vector_values_or_error = resolve_embedding_vector_logic(
        $ai_caller,
        $user_message,
        $embedding_provider_normalized,
        $vector_embedding_model
    );

    if (is_wp_error($query_vector_values_or_error)) {
        return '';
    }

    $query_vector_values = $query_vector_values_or_error;
    $collections_to_query = $chroma_collection_names_multi;
    $active_file_context_id = $frontend_active_chroma_file_upload_context_id !== null
        ? sanitize_text_field($frontend_active_chroma_file_upload_context_id)
        : '';
    foreach (['active_chroma_file_upload_context_id', 'chroma_file_upload_context_id'] as $context_key) {
        if ($active_file_context_id !== '') {
            break;
        }
        if (!empty($bot_settings[$context_key])) {
            $active_file_context_id = sanitize_text_field((string) $bot_settings[$context_key]);
            break;
        }
    }

    $chroma_results_aggregate = '';

    if ($active_file_context_id !== '') {
        foreach ($collections_to_query as $collection_to_query) {
            $file_specific_filter = [
                'where' => [
                    'file_upload_context_id' => $active_file_context_id,
                ],
            ];
            $file_search_results = $vector_store_manager->query_vectors(
                'Chroma',
                $collection_to_query,
                ['vector' => $query_vector_values],
                $vector_top_k,
                $file_specific_filter,
                $chroma_api_config
            );

            if (!is_wp_error($file_search_results) && !empty($file_search_results)) {
                $formatted_file_results = '';
                foreach ($file_search_results as $item) {
                    if (isset($item['score']) && (float) $item['score'] < $chroma_score_threshold) {
                        continue;
                    }
                    $metadata = isset($item['metadata']) && is_array($item['metadata']) ? $item['metadata'] : [];
                    $content_snippet = resolve_chroma_content_snippet_logic($item, $metadata, $collection_to_query, $wpdb, $data_source_table_name);
                    if ($content_snippet === '') {
                        continue;
                    }
                    $formatted_file_results .= '- ' . trim($content_snippet) . "\n";

                    if ($vector_search_scores_output !== null && isset($item['score'])) {
                        $vector_search_scores_output[] = build_vector_search_score_item_logic(
                            [
                                'provider' => 'Chroma',
                                'collection_name' => $collection_to_query,
                                'file_context_id' => $active_file_context_id,
                                'result_id' => $item['id'] ?? null,
                                'score' => $item['score'],
                                'content_preview' => wp_trim_words(trim($content_snippet), 10, '...'),
                            ],
                            $metadata
                        );
                    }
                }
                if ($formatted_file_results !== '') {
                    $chroma_results_aggregate .= "Context from Uploaded File (Collection {$collection_to_query}, File Context ID: {$active_file_context_id}):\n" . $formatted_file_results . "\n";
                }
            }
        }
    }

    foreach ($collections_to_query as $collection_to_query) {
        $general_search_results = $vector_store_manager->query_vectors(
            'Chroma',
            $collection_to_query,
            ['vector' => $query_vector_values],
            $vector_top_k,
            [],
            $chroma_api_config
        );

        if (is_wp_error($general_search_results) || empty($general_search_results)) {
            continue;
        }

        $formatted_general_results = '';
        foreach ($general_search_results as $item) {
            if (isset($item['score']) && (float) $item['score'] < $chroma_score_threshold) {
                continue;
            }

            $metadata = isset($item['metadata']) && is_array($item['metadata']) ? $item['metadata'] : [];
            if ($active_file_context_id !== '' && ($metadata['file_upload_context_id'] ?? '') === $active_file_context_id) {
                continue;
            }

            $content_snippet = resolve_chroma_content_snippet_logic($item, $metadata, $collection_to_query, $wpdb, $data_source_table_name);
            if ($content_snippet === '') {
                continue;
            }

            $formatted_general_results .= '- ' . trim($content_snippet) . "\n";

            if ($vector_search_scores_output !== null && isset($item['score'])) {
                $vector_search_scores_output[] = build_vector_search_score_item_logic(
                    [
                        'provider' => 'Chroma',
                        'collection_name' => $collection_to_query,
                        'file_context_id' => null,
                        'result_id' => $item['id'] ?? null,
                        'score' => $item['score'],
                        'content_preview' => wp_trim_words(trim($content_snippet), 10, '...'),
                    ],
                    $metadata
                );
            }
        }

        if ($formatted_general_results !== '') {
            $chroma_results_aggregate .= "General Knowledge from Bot (Collection {$collection_to_query}):\n" . $formatted_general_results . "\n";
        }
    }

    return $chroma_results_aggregate;
}

/**
 * @param array<string,mixed> $item
 * @param array<string,mixed> $metadata
 */
function resolve_chroma_content_snippet_logic(array $item, array $metadata, string $collection_name, \wpdb $wpdb, string $data_source_table_name): string
{
    $content_snippet = $metadata['original_content'] ?? ($metadata['text_content'] ?? null);
    if (!empty($content_snippet)) {
        return trim((string) $content_snippet);
    }

    $result_id = isset($item['id']) ? (string) $item['id'] : '';
    if ($result_id === '') {
        return '';
    }

    $cache_key = 'aipkit_vds_content_' . md5('chroma_general_' . $collection_name . $result_id);
    $cache_group = 'aipkit_vector_source_content';
    $log_entry = wp_cache_get($cache_key, $cache_group);

    if (false === $log_entry) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $log_entry = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT indexed_content FROM {$data_source_table_name} WHERE provider = 'Chroma' AND vector_store_id = %s AND file_id = %s ORDER BY timestamp DESC LIMIT 1",
                $collection_name,
                $result_id
            ),
            ARRAY_A
        );
        wp_cache_set($cache_key, $log_entry, $cache_group, HOUR_IN_SECONDS);
    }

    if ($log_entry && !empty($log_entry['indexed_content'])) {
        return trim((string) $log_entry['indexed_content']);
    }

    return '';
}
