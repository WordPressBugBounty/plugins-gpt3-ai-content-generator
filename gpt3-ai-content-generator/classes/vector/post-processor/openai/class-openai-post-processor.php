<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/post-processor/openai/class-openai-post-processor.php
// Status: NEW FILE

namespace WPAICG\Vector\PostProcessor\OpenAI;

use WPAICG\Vector\PostProcessor\Base\AIPKit_Vector_Post_Processor_Base;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WPAICG\Vector\AIPKit_Vector_Provider_Strategy_Factory; // Corrected Factory namespace
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles indexing WordPress post content into OpenAI Vector Stores.
 */
class OpenAIPostProcessor extends AIPKit_Vector_Post_Processor_Base
{
    private $vector_store_manager;
    private $config_handler;

    public function __construct()
    {
        parent::__construct();
        if (!class_exists(AIPKit_Vector_Store_Manager::class)) {
            $manager_path = WPAICG_PLUGIN_DIR . 'classes/vector/class-aipkit-vector-store-manager.php';
            if (file_exists($manager_path)) {
                require_once $manager_path;
            }
        }
        if (class_exists(AIPKit_Vector_Store_Manager::class)) {
            $this->vector_store_manager = new AIPKit_Vector_Store_Manager();
        }
        if (!class_exists(OpenAIConfig::class)) {
            $config_path = __DIR__ . '/class-openai-config.php';
            if (file_exists($config_path)) {
                require_once $config_path;
            }
        }
        if (class_exists(OpenAIConfig::class)) {
            $this->config_handler = new OpenAIConfig();
        }
    }

    /**
     * Indexes a single post's content to a specified OpenAI Vector Store.
     *
     * @param int $post_id The ID of the post to index.
     * @param string $vector_store_id The ID of the target OpenAI Vector Store.
     * @param string|null $vector_store_name_for_log Optional name of the store for logging purposes.
     * @param bool $force_reindex If true, will attempt to delete an existing file for this post in the store before re-indexing.
     * @return array ['status' => 'success'|'error', 'message' => string, 'file_id' => string|null, 'batch_id' => string|null]
     */
    public function index_single_post_to_store(int $post_id, string $vector_store_id, ?string $vector_store_name_for_log = null, bool $force_reindex = false): array
    {
        $post_obj = get_post($post_id);
        $post_title_for_log = $post_obj ? $post_obj->post_title : 'N/A';
        $log_entry_base = [
            'provider' => 'OpenAI', 'vector_store_id' => $vector_store_id, 'vector_store_name' => $vector_store_name_for_log ?: $vector_store_id,
            'post_id' => $post_id, 'post_title' => $post_title_for_log,
            'source_type_for_log' => 'wordpress_post'
        ];

        if (!$this->config_handler || !$this->vector_store_manager) {
            $error_msg = __('OpenAI processing components not available.', 'gpt3-ai-content-generator');
            $this->log_event(array_merge($log_entry_base, ['status' => 'config_error', 'message' => $error_msg]));
            return ['status' => 'error', 'message' => $error_msg, 'file_id' => null, 'batch_id' => null];
        }

        $openai_config = $this->config_handler->get_config();
        if (is_wp_error($openai_config)) {
            $error_msg = $openai_config->get_error_message();
            $this->log_event(array_merge($log_entry_base, ['status' => 'config_error', 'message' => $error_msg]));
            return ['status' => 'error', 'message' => $error_msg, 'file_id' => null, 'batch_id' => null];
        }

        $strategy = AIPKit_Vector_Provider_Strategy_Factory::get_strategy('OpenAI');
        if (is_wp_error($strategy) || !method_exists($strategy, 'delete_openai_file_object') || !method_exists($strategy, 'upload_file_for_vector_store')) {
            $error_msg = __('OpenAI file processing strategy not available.', 'gpt3-ai-content-generator');
            $this->log_event(array_merge($log_entry_base, ['status' => 'config_error', 'message' => $error_msg]));
            return ['status' => 'error', 'message' => $error_msg, 'file_id' => null, 'batch_id' => null];
        }
        $strategy->connect($openai_config); // Connect strategy with fetched config

        $old_file_id_meta_key = '_aipkit_openai_file_id_for_vs_' . sanitize_key($vector_store_id);
        $old_file_id = get_post_meta($post_id, $old_file_id_meta_key, true);

        if ($force_reindex && !empty($old_file_id)) {
            $this->log_event(array_merge($log_entry_base, ['status' => 'reindexing_started', 'message' => 'Re-indexing: Attempting to delete old file object.', 'file_id' => $old_file_id]));
            $delete_file_result = $strategy->delete_openai_file_object($old_file_id); // Call strategy method
            if (is_wp_error($delete_file_result)) {
                $this->log_event(array_merge($log_entry_base, ['status' => 'reindex_delete_failed', 'message' => 'Re-indexing: Failed to delete old file: ' . $delete_file_result->get_error_message(), 'file_id' => $old_file_id]));
            } else {
                $this->log_event(array_merge($log_entry_base, ['status' => 'old_file_deleted', 'message' => 'Re-indexing: Old file deleted.', 'file_id' => $old_file_id]));
                delete_post_meta($post_id, $old_file_id_meta_key);
            }
        } elseif (!empty($old_file_id) && !$force_reindex) {
            $this->log_event(array_merge($log_entry_base, ['status' => 'skipped_already_indexed', 'message' => 'Skipped: Already indexed.', 'file_id' => $old_file_id]));
            return ['status' => 'success', 'message' => 'Already indexed, skipped.', 'file_id' => $old_file_id, 'batch_id' => null];
        }

        $content_string_or_error = $this->get_post_content_as_string($post_id); // Call base method
        if (is_wp_error($content_string_or_error)) {
            $error_msg = 'Content retrieval error: ' . $content_string_or_error->get_error_message();
            $this->log_event(array_merge($log_entry_base, ['status' => 'content_error', 'message' => $error_msg]));
            return ['status' => 'error', 'message' => $error_msg, 'file_id' => null, 'batch_id' => null];
        }
        $log_entry_base['indexed_content'] = $content_string_or_error;

        if (empty(trim($content_string_or_error))) {
            $error_msg = __('Post content is empty.', 'gpt3-ai-content-generator');
            $this->log_event(array_merge($log_entry_base, ['status' => 'content_error', 'message' => $error_msg]));
            return ['status' => 'error', 'message' => $error_msg, 'file_id' => null, 'batch_id' => null];
        }

        $temp_file_result = $this->create_temp_file_from_string($content_string_or_error, 'post-' . $post_id . '-vs-' . $vector_store_id . '-'); // Call base method
        if (is_wp_error($temp_file_result)) {
            $error_msg = 'Temp file error: ' . $temp_file_result->get_error_message();
            $this->log_event(array_merge($log_entry_base, ['status' => 'content_error', 'message' => $error_msg]));
            return ['status' => 'error', 'message' => $error_msg, 'file_id' => null, 'batch_id' => null];
        }

        $upload_result = $strategy->upload_file_for_vector_store($temp_file_result, basename($temp_file_result), 'user_data'); // Call strategy method
        wp_delete_file($temp_file_result);

        if (is_wp_error($upload_result) || !isset($upload_result['id'])) {
            $err_msg = is_wp_error($upload_result) ? $upload_result->get_error_message() : 'Missing file ID in response.';
            $this->log_event(array_merge($log_entry_base, ['status' => 'upload_failed', 'message' => 'Upload error: ' . $err_msg]));
            return ['status' => 'error', 'message' => 'Upload error: ' . $err_msg, 'file_id' => null, 'batch_id' => null];
        }
        $uploaded_file_id = $upload_result['id'];
        $this->log_event(array_merge($log_entry_base, ['status' => 'file_uploaded', 'message' => 'File uploaded.', 'file_id' => $uploaded_file_id]));

        $batch_result = $this->vector_store_manager->upsert_vectors('OpenAI', $vector_store_id, ['file_ids' => [$uploaded_file_id]], $openai_config);
        if (is_wp_error($batch_result)) {
            $error_msg = 'Batch add error: ' . $batch_result->get_error_message();
            $this->log_event(array_merge($log_entry_base, ['status' => 'batch_failed', 'message' => $error_msg, 'file_id' => $uploaded_file_id]));
            return ['status' => 'error', 'message' => $error_msg, 'file_id' => $uploaded_file_id, 'batch_id' => null];
        }
        $batch_id = $batch_result['id'] ?? null;
        $this->log_event(array_merge($log_entry_base, ['status' => 'batch_submitted', 'message' => 'File submitted to batch.', 'file_id' => $uploaded_file_id, 'batch_id' => $batch_id]));

        update_post_meta($post_id, $old_file_id_meta_key, $uploaded_file_id);
        update_post_meta($post_id, '_aipkit_indexed_to_vs_' . sanitize_key($vector_store_id), '1');

        return ['status' => 'success', 'message' => 'Submitted to batch.', 'file_id' => $uploaded_file_id, 'batch_id' => $batch_id];
    }
}
