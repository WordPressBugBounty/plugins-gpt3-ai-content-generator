<?php

namespace WPAICG\Dashboard\Ajax\Chroma\HandlerCollections;

use WP_Error;
use WPAICG\Dashboard\Ajax\AIPKit_Vector_Store_Chroma_Ajax_Handler;

if (!defined('ABSPATH')) {
    exit;
}

function _aipkit_chroma_ajax_upsert_to_collection_logic(AIPKit_Vector_Store_Chroma_Ajax_Handler $handler_instance): void
{
    $vector_store_manager = $handler_instance->get_vector_store_manager();
    if (!$vector_store_manager) {
        $handler_instance->send_wp_error(new WP_Error('manager_not_ready_upsert_chroma', __('Vector Store Manager not available for Chroma upsert.', 'gpt3-ai-content-generator'), ['status' => 500]));
        return;
    }

    $chroma_config = $handler_instance->_get_chroma_config();
    if (is_wp_error($chroma_config)) {
        $handler_instance->send_wp_error($chroma_config);
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in the calling handler method.
    $post_data = wp_unslash($_POST);
    $collection_name = isset($post_data['collection_name']) ? sanitize_text_field($post_data['collection_name']) : '';
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string, decoded and validated below.
    $vectors_json = isset($post_data['vectors']) ? $post_data['vectors'] : '';
    $embedding_provider = isset($post_data['embedding_provider']) ? sanitize_key($post_data['embedding_provider']) : null;
    $embedding_model = isset($post_data['embedding_model']) ? sanitize_text_field($post_data['embedding_model']) : null;
    $original_text_content = isset($post_data['original_text_content']) ? wp_kses_post($post_data['original_text_content']) : null;

    if ($collection_name === '') {
        $handler_instance->send_wp_error(new WP_Error('missing_collection_name_chroma_upsert', __('Chroma collection name is required.', 'gpt3-ai-content-generator'), ['status' => 400]));
        return;
    }
    if ($vectors_json === '') {
        $handler_instance->send_wp_error(new WP_Error('missing_vectors_chroma_upsert', __('Records data is required for Chroma upsert.', 'gpt3-ai-content-generator'), ['status' => 400]));
        return;
    }

    $records = json_decode($vectors_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($records) || empty($records)) {
        $handler_instance->send_wp_error(new WP_Error('invalid_records_json_chroma_upsert', __('Invalid or empty records JSON format for Chroma.', 'gpt3-ai-content-generator'), ['status' => 400]));
        return;
    }

    $result = $vector_store_manager->upsert_vectors('Chroma', $collection_name, ['points' => $records], $chroma_config);

    $first_record = is_array($records[0] ?? null) ? $records[0] : [];
    $record_id = $first_record['id'] ?? null;
    $metadata = isset($first_record['payload']) && is_array($first_record['payload'])
        ? $first_record['payload']
        : (isset($first_record['metadata']) && is_array($first_record['metadata']) ? $first_record['metadata'] : []);
    $source_type_for_log = $metadata['source'] ?? 'unknown';
    $wp_post_id_for_log = null;
    $wp_post_title_for_log = null;
    $content_for_log = null;

    if ($source_type_for_log === 'wordpress_post') {
        if (!empty($metadata['post_id'])) {
            $wp_post_id_for_log = absint($metadata['post_id']);
        }
        if ($wp_post_id_for_log) {
            $wp_post_title_for_log = get_the_title($wp_post_id_for_log) ?: 'Post ' . $wp_post_id_for_log;
        }
        $content_for_log = $original_text_content;
    } elseif (in_array($source_type_for_log, ['text_entry_global_form', 'file_upload_global_form', 'text_entry_chroma_direct', 'chatbot_training_text', 'chatbot_training_qa'], true) && $original_text_content !== null) {
        $content_for_log = $original_text_content;
        if ($source_type_for_log === 'file_upload_global_form' && !empty($metadata['filename'])) {
            $wp_post_title_for_log = sanitize_file_name((string) $metadata['filename']);
        }
    }

    if (is_wp_error($result)) {
        $handler_instance->_log_vector_data_source_entry([
            'vector_store_id' => $collection_name,
            'vector_store_name' => $collection_name,
            'post_id' => $wp_post_id_for_log,
            'post_title' => $wp_post_title_for_log,
            'status' => 'failed',
            'message' => 'Chroma upsert failed: ' . $result->get_error_message(),
            'embedding_provider' => $embedding_provider,
            'embedding_model' => $embedding_model,
            'indexed_content' => $content_for_log,
            'file_id' => $record_id,
            'source_type_for_log' => $source_type_for_log,
        ]);
        $handler_instance->send_wp_error($result);
        return;
    }

    $upserted_count = isset($result['upserted_count']) ? absint($result['upserted_count']) : count($records);
    $handler_instance->_log_vector_data_source_entry([
        'vector_store_id' => $collection_name,
        'vector_store_name' => $collection_name,
        'post_id' => $wp_post_id_for_log,
        'post_title' => $wp_post_title_for_log,
        'status' => 'indexed',
        'message' => 'Chroma records upserted. Count: ' . $upserted_count,
        'embedding_provider' => $embedding_provider,
        'embedding_model' => $embedding_model,
        'indexed_content' => $content_for_log,
        'file_id' => $record_id,
        'source_type_for_log' => $source_type_for_log,
    ]);

    wp_send_json_success([
        'message' => __('Records upserted to Chroma successfully.', 'gpt3-ai-content-generator'),
        'result' => $result,
    ]);
}
