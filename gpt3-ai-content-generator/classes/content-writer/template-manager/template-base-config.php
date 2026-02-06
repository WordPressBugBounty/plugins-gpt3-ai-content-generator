<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/template-manager/template-base-config.php
// Status: NEW FILE

namespace WPAICG\ContentWriter\TemplateManagerMethods;

use WPAICG\AIPKit_Providers;
use WPAICG\AIPKIT_AI_Settings;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds the base configuration used for default and starter templates.
 *
 * @param int $user_id The current user ID.
 * @return array
 */
function get_cw_base_template_config(int $user_id): array
{
    if (!$user_id) {
        return [];
    }
    if (
        !class_exists(AIPKit_Providers::class) ||
        !class_exists(AIPKIT_AI_Settings::class) ||
        !class_exists(AIPKit_Content_Writer_Prompts::class)
    ) {
        return [];
    }

    $default_provider_config = AIPKit_Providers::get_default_provider_config();
    $ai_parameters = AIPKIT_AI_Settings::get_ai_parameters();

    $provider_for_template = $default_provider_config['provider'] ?? 'OpenAI';
    $model_for_template = $default_provider_config['model'] ?? '';

    return [
        'ai_provider' => $provider_for_template,
        'ai_model' => $model_for_template,
        'content_title' => '',
        'content_keywords' => '',
        'ai_temperature' => (string)($ai_parameters['temperature'] ?? 1.0),
        'content_length' => 'medium',
        'post_type' => 'post',
        'post_author' => $user_id ?: 1,
        'post_status' => 'draft',
        'post_schedule_date' => '',
        'post_schedule_time' => '',
        'post_categories' => [],
        'prompt_mode' => 'custom',
        'custom_title_prompt' => AIPKit_Content_Writer_Prompts::get_default_title_prompt(),
        'custom_content_prompt' => AIPKit_Content_Writer_Prompts::get_default_content_prompt(),
        'generate_title' => '1',
        'generate_content' => '1',
        'generate_meta_description' => '1',
        'custom_meta_prompt' => AIPKit_Content_Writer_Prompts::get_default_meta_prompt(),
        'generate_focus_keyword' => '1',
        'custom_keyword_prompt' => AIPKit_Content_Writer_Prompts::get_default_keyword_prompt(),
        'generate_excerpt' => '1',
        'custom_excerpt_prompt' => AIPKit_Content_Writer_Prompts::get_default_excerpt_prompt(),
        'generate_tags' => '1',
        'custom_tags_prompt' => AIPKit_Content_Writer_Prompts::get_default_tags_prompt(),
        'custom_title_prompt_update' => '',
        'custom_content_prompt_update' => '',
        'custom_meta_prompt_update' => '',
        'custom_keyword_prompt_update' => '',
        'custom_excerpt_prompt_update' => '',
        'custom_tags_prompt_update' => '',
        'cw_generation_mode' => 'task',
        'rss_feeds' => '',
        'rss_include_keywords' => '',
        'rss_exclude_keywords' => '',
        'gsheets_sheet_id' => '',
        'gsheets_credentials' => '',
        'url_list' => '',
        'generate_toc' => '0',
        'generate_images_enabled' => '0',
        'image_provider' => 'openai',
        'image_model' => 'gpt-image-1',
        'image_prompt' => AIPKit_Content_Writer_Prompts::get_default_image_prompt(),
        'image_prompt_update' => '',
        'image_count' => '1',
        'image_placement' => 'after_first_h2',
        'image_placement_param_x' => '2',
        'image_alignment' => 'none',
        'image_size' => 'large',
        'generate_image_title' => '1',
        'generate_image_alt_text' => '1',
        'generate_image_caption' => '1',
        'generate_image_description' => '1',
        'image_title_prompt' => AIPKit_Content_Writer_Prompts::get_default_image_title_prompt(),
        'image_alt_text_prompt' => AIPKit_Content_Writer_Prompts::get_default_image_alt_text_prompt(),
        'image_caption_prompt' => AIPKit_Content_Writer_Prompts::get_default_image_caption_prompt(),
        'image_description_prompt' => AIPKit_Content_Writer_Prompts::get_default_image_description_prompt(),
        'image_title_prompt_update' => AIPKit_Content_Writer_Prompts::get_default_image_title_prompt_update(),
        'image_alt_text_prompt_update' => AIPKit_Content_Writer_Prompts::get_default_image_alt_text_prompt_update(),
        'image_caption_prompt_update' => AIPKit_Content_Writer_Prompts::get_default_image_caption_prompt_update(),
        'image_description_prompt_update' => AIPKit_Content_Writer_Prompts::get_default_image_description_prompt_update(),
        'generate_featured_image' => '0',
        'featured_image_prompt' => AIPKit_Content_Writer_Prompts::get_default_featured_image_prompt(),
        'featured_image_prompt_update' => '',
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
        'vector_store_confidence_threshold' => '20',
    ];
}
