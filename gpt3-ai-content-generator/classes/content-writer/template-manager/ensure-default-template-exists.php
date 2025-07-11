<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/template-manager/ensure-default-template-exists.php
// Status: MODIFIED
// I have added logic to update the existing default template with the new "Tags" prompt fields if they are missing.

namespace WPAICG\ContentWriter\TemplateManagerMethods;

use WPAICG\AIPKit_Providers;
use WPAICG\AIPKIT_AI_Settings;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompts;

if (!defined('ABSPATH')) {
    exit;
}

/**
* Logic for ensuring the default template exists.
* UPDATED: Removed guided mode fields (tone, length) and hardcoded prompt_mode to 'custom'.
*
* @param \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance The instance of the template manager.
*/
function ensure_default_template_exists_logic(\WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance)
{
    $wpdb = $managerInstance->get_wpdb();
    $table_name = $managerInstance->get_table_name();

    $current_user_id = get_current_user_id();
    $user_id_for_default = 0;

    $default_template = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d AND is_default = 1 AND template_type = 'content_writer' LIMIT 1",
            $user_id_for_default
        )
    );

    if (!$default_template) {
        if (!class_exists(AIPKit_Providers::class) || !class_exists(AIPKIT_AI_Settings::class)) {
            error_log("AIPKit Content Writer Default Template: Missing Providers or AI Settings class.");
            return;
        }

        $default_provider_config = AIPKit_Providers::get_default_provider_config();
        $ai_parameters = AIPKIT_AI_Settings::get_ai_parameters();

        // --- START MODIFICATION ---
        $provider_for_template = $default_provider_config['provider'] ?? 'OpenAI';

        $model_for_template = '';
        switch (strtolower($provider_for_template)) {
            case 'openai':
                $model_for_template = 'gpt-4.1-mini';
                break;
            case 'google':
                $model_for_template = 'gemini-2.5-flash';
                break;
            case 'openrouter':
                $model_for_template = 'anthropic/claude-3.7-sonnet';
                break;
            case 'azure':
            case 'deepseek':
            default:
                // For Azure and DeepSeek, use the model specified in their respective provider settings.
                $model_for_template = $default_provider_config['model'] ?? '';
                break;
        }
        // --- END MODIFICATION ---

        $default_config = [
        'ai_provider' => $provider_for_template, // Use the determined provider
        'ai_model' => $model_for_template, // Use the determined model
        'content_title' => '',
        'content_keywords' => '',
        'ai_temperature' => (string)($ai_parameters['temperature'] ?? 1.0),
        'content_max_tokens' => (string)($ai_parameters['max_completion_tokens'] ?? 1500),
        'post_type' => 'post',
        'post_author' => $current_user_id ?: 1,
        'post_status' => 'draft',
        'post_schedule_date' => '',
        'post_schedule_time' => '',
        'post_categories' => [],
        'prompt_mode' => 'custom',
        'custom_title_prompt' => AIPKit_Content_Writer_Prompts::get_default_title_prompt(),
        'custom_content_prompt' => AIPKit_Content_Writer_Prompts::get_default_content_prompt(),
        'generate_meta_description' => '1',
        'custom_meta_prompt' => AIPKit_Content_Writer_Prompts::get_default_meta_prompt(),
        'generate_focus_keyword' => '1',
        'custom_keyword_prompt' => AIPKit_Content_Writer_Prompts::get_default_keyword_prompt(),
        'generate_excerpt' => '0',
        'custom_excerpt_prompt' => AIPKit_Content_Writer_Prompts::get_default_excerpt_prompt(),
        'generate_tags' => '0',
        'custom_tags_prompt' => AIPKit_Content_Writer_Prompts::get_default_tags_prompt(),
        'cw_generation_mode' => 'single',
        'rss_feeds' => '',
        'rss_include_keywords' => '', // ADDED
        'rss_exclude_keywords' => '', // ADDED
        'gsheets_sheet_id' => '',
        'gsheets_credentials' => '',
        'url_list' => '',
        'generate_toc' => '0',
        'generate_images_enabled' => '0',
        'image_provider' => 'openai',
        'image_model' => 'gpt-image-1',
        'image_prompt' => AIPKit_Content_Writer_Prompts::get_default_image_prompt(),
        'image_count' => '1',
        'image_placement' => 'after_first_h2',
        'image_placement_param_x' => '2',
        'image_alignment' => 'none',
        'image_size' => 'large',
        'generate_featured_image' => '0',
        'featured_image_prompt' => AIPKit_Content_Writer_Prompts::get_default_featured_image_prompt(),
        'pexels_orientation' => 'none',
        'pexels_size' => 'none',
        'pexels_color' => '',
        'pixabay_orientation' => 'all',
        'pixabay_image_type' => 'all',
        'pixabay_category' => '',
        'enable_vector_store' => '0',
        'vector_store_provider' => 'openai',
        'openai_vector_store_ids' => [],
        'pinecone_index_name' => '',
        'qdrant_collection_name' => '',
        'vector_embedding_provider' => 'openai',
        'vector_embedding_model' => 'text-embedding-3-small',
        'vector_store_top_k' => '3',
        ];

        $wpdb->insert(
            $table_name,
            [
            'user_id' => $user_id_for_default,
            'template_name' => __('Default Template', 'gpt3-ai-content-generator'),
            'template_type' => 'content_writer',
            'config' => wp_json_encode($default_config),
            'is_default' => 1,
            'created_at' => current_time('mysql', 1),
            'updated_at' => current_time('mysql', 1),
            'post_type' => $default_config['post_type'],
            'post_author' => $default_config['post_author'],
            'post_status' => $default_config['post_status'],
            'post_schedule' => null,
            'post_categories' => wp_json_encode([]),
            ],
            ['%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );
        error_log("AIPKit Content Writer: Default template created for global user (ID {$user_id_for_default}).");
    } else {
        // Default template exists, check if it needs updating with new fields.
        $config = json_decode($default_template->config, true);
        $needs_update = false;

        // Check for 'generate_tags'
        if (!isset($config['generate_tags'])) {
            $config['generate_tags'] = '0'; // default value
            $needs_update = true;
        }

        // Check for 'custom_tags_prompt'
        if (!isset($config['custom_tags_prompt'])) {
            $config['custom_tags_prompt'] = AIPKit_Content_Writer_Prompts::get_default_tags_prompt();
            $needs_update = true;
        }

        // --- ADDED: Also check for excerpt, as it was added recently too ---
        if (!isset($config['generate_excerpt'])) {
            $config['generate_excerpt'] = '0';
            $needs_update = true;
        }
        if (!isset($config['custom_excerpt_prompt'])) {
            $config['custom_excerpt_prompt'] = AIPKit_Content_Writer_Prompts::get_default_excerpt_prompt();
            $needs_update = true;
        }
        // --- END ADDED ---

        if ($needs_update) {
            $wpdb->update(
                $table_name,
                ['config' => wp_json_encode($config)],
                ['id' => $default_template->id],
                ['%s'],
                ['%d']
            );
            error_log("AIPKit Content Writer: Updated existing default template with new fields.");
        }
    }
}
