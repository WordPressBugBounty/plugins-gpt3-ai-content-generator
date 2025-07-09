<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/autogpt/ajax/actions/save-task/build-task-config-enhancement.php
// Status: MODIFIED
// I have updated this file to read the new 'ce_' prefixed keys from the form submission.

namespace WPAICG\AutoGPT\Ajax\Actions\SaveTask;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
* Builds and validates the configuration for an 'enhance_existing_content' task.
*
* @param array $post_data The raw POST data.
* @return array|WP_Error The validated config array or WP_Error on failure.
*/
function build_task_config_enhancement_logic(array $post_data): array|WP_Error
{
    $task_config = [];
    $task_config['post_types'] = isset($post_data['ce_post_types']) && is_array($post_data['ce_post_types']) ? array_map('sanitize_key', $post_data['ce_post_types']) : [];
    $task_config['post_categories'] = isset($post_data['ce_post_categories']) && is_array($post_data['ce_post_categories']) ? array_map('absint', $post_data['ce_post_categories']) : [];
    $task_config['post_authors'] = isset($post_data['ce_post_authors']) && is_array($post_data['ce_post_authors']) ? array_map('absint', $post_data['ce_post_authors']) : [];
    $task_config['post_statuses'] = isset($post_data['ce_post_statuses']) && is_array($post_data['ce_post_statuses']) ? array_map('sanitize_key', $post_data['ce_post_statuses']) : ['publish'];

    // Fields to enhance
    $task_config['update_title'] = isset($post_data['ce_update_title']) ? '1' : '0';
    $task_config['update_excerpt'] = isset($post_data['ce_update_excerpt']) ? '1' : '0';
    $task_config['update_meta'] = isset($post_data['ce_update_meta']) ? '1' : '0';
    $task_config['update_content'] = isset($post_data['ce_update_content']) ? '1' : '0';

    // Prompts
    $task_config['title_prompt'] = isset($post_data['ce_title_prompt']) ? sanitize_textarea_field(wp_unslash($post_data['ce_title_prompt'])) : '';
    $task_config['excerpt_prompt'] = isset($post_data['ce_excerpt_prompt']) ? sanitize_textarea_field(wp_unslash($post_data['ce_excerpt_prompt'])) : '';
    $task_config['meta_prompt'] = isset($post_data['ce_meta_prompt']) ? sanitize_textarea_field(wp_unslash($post_data['ce_meta_prompt'])) : '';
    $task_config['content_prompt'] = isset($post_data['ce_content_prompt']) ? sanitize_textarea_field(wp_unslash($post_data['ce_content_prompt'])) : '';

    // AI Settings
    $provider_raw = $post_data['ce_ai_provider'] ?? 'openai';
    $task_config['ai_provider'] = match (strtolower($provider_raw)) {
        'openai' => 'OpenAI', 'openrouter' => 'OpenRouter', 'google' => 'Google', 'azure' => 'Azure', 'deepseek' => 'DeepSeek',
        default => ucfirst(strtolower($provider_raw))
    };
    $task_config['ai_model'] = $post_data['ce_ai_model'] ?? '';
    $task_config['ai_temperature'] = isset($post_data['ce_ai_temperature']) ? floatval($post_data['ce_ai_temperature']) : 1.0;
    $task_config['content_max_tokens'] = isset($post_data['ce_content_max_tokens']) ? absint($post_data['ce_content_max_tokens']) : 4000;

    // Task Frequency
    $task_config['task_frequency'] = isset($post_data['task_frequency']) ? sanitize_key($post_data['task_frequency']) : 'daily';

    // One-time run flag
    $task_config['enhance_existing_now_flag'] = isset($post_data['ce_enhance_existing_now_flag']) ? '1' : '0';

    // --- START: NEW Vector Store Settings ---
    $task_config['enable_vector_store'] = isset($post_data['ce_enable_vector_store']) && $post_data['ce_enable_vector_store'] === '1' ? '1' : '0';
    if ($task_config['enable_vector_store'] === '1') {
        $task_config['vector_store_provider'] = isset($post_data['ce_vector_store_provider']) ? sanitize_key($post_data['ce_vector_store_provider']) : 'openai';
        $task_config['vector_store_top_k'] = isset($post_data['ce_vector_store_top_k']) ? absint($post_data['ce_vector_store_top_k']) : 3;

        if ($task_config['vector_store_provider'] === 'openai') {
            $task_config['openai_vector_store_ids'] = isset($post_data['ce_openai_vector_store_ids']) && is_array($post_data['ce_openai_vector_store_ids']) ? array_map('sanitize_text_field', $post_data['ce_openai_vector_store_ids']) : [];
        } elseif ($task_config['vector_store_provider'] === 'pinecone') {
            $task_config['pinecone_index_name'] = isset($post_data['ce_pinecone_index_name']) ? sanitize_text_field($post_data['ce_pinecone_index_name']) : '';
        } elseif ($task_config['vector_store_provider'] === 'qdrant') {
            $task_config['qdrant_collection_name'] = isset($post_data['ce_qdrant_collection_name']) ? sanitize_text_field($post_data['ce_qdrant_collection_name']) : '';
        }

        if ($task_config['vector_store_provider'] === 'pinecone' || $task_config['vector_store_provider'] === 'qdrant') {
            $task_config['vector_embedding_provider'] = isset($post_data['ce_vector_embedding_provider']) ? sanitize_key($post_data['ce_vector_embedding_provider']) : 'openai';
            $task_config['vector_embedding_model'] = isset($post_data['ce_vector_embedding_model']) ? sanitize_text_field($post_data['ce_vector_embedding_model']) : '';
        }
    }
    // --- END: NEW ---

    // Validation
    if (empty($task_config['post_types'])) {
        return new WP_Error('missing_post_types_enhance', __('Please select at least one post type to enhance.', 'gpt3-ai-content-generator'));
    }
    if ($task_config['update_title'] !== '1' && $task_config['update_excerpt'] !== '1' && $task_config['update_meta'] !== '1' && $task_config['update_content'] !== '1') {
        return new WP_Error('no_enhancement_selected', __('Please select at least one field to enhance.', 'gpt3-ai-content-generator'));
    }

    return $task_config;
}
