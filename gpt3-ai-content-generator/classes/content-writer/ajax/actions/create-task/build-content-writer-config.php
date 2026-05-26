<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/create-task/build-content-writer-config.php
// Status: MODIFIED

namespace WPAICG\ContentWriter\Ajax\Actions\CreateTask;

use WPAICG\Core\AIPKit_OpenAI_Reasoning;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Image_Provider_Options;
use WPAICG\ContentWriter\SEO\AIPKit_Content_Writer_SEO_Config;
use WPAICG\Utils\AIPKit_Prompt_Sanitizer;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists(AIPKit_Content_Writer_Image_Provider_Options::class)) {
    $aipkit_image_provider_options_path = WPAICG_PLUGIN_DIR . 'classes/content-writer/class-aipkit-content-writer-image-provider-options.php';
    if (file_exists($aipkit_image_provider_options_path)) {
        require_once $aipkit_image_provider_options_path;
    }
}

/**
* Builds and validates the specific configuration array for the content writing task.
* UPDATED: Removed guided mode fields.
*
* @param array $settings The raw POST data.
* @param string $task_frequency The sanitized task frequency.
* @param string $task_status The sanitized task status.
* @return array|WP_Error The sanitized content writer config array or WP_Error on failure.
*/
function build_content_writer_config_logic(array $settings, string $task_frequency, string $task_status): array|WP_Error
{
    $content_writer_config = [];
    if (class_exists(AIPKit_Content_Writer_Template_Manager::class)) {
        // This list should ideally mirror the one in AIPKit_Content_Writer_Template_Manager for consistency.
        $allowed_keys_from_template_manager = [
            'ai_provider', 'ai_model', 'content_title_bulk', 'content_keywords',
            'ai_temperature', 'content_length', 'post_type', 'post_author',
            'post_status', 'post_schedule_date', 'post_schedule_time',
            'schedule_mode', 'smart_schedule_start_datetime', 'smart_schedule_interval_value', 'smart_schedule_interval_unit',
            'post_categories',
            'prompt_mode', 'custom_title_prompt', 'custom_content_prompt',
            'generate_meta_description', 'custom_meta_prompt',
            'generate_focus_keyword', 'custom_keyword_prompt',
            'generate_excerpt', 'custom_excerpt_prompt',
            'generate_tags', 'custom_tags_prompt',
            'cw_generation_mode', 'rss_feeds',
            'gsheets_sheet_id', 'gsheets_credentials',
            'url_list',
            'content_title',
            'generate_toc', 'generate_seo_slug',
            'seo_score_improvement_enabled', 'seo_score_continue_until_target',
            'seo_score_target', 'seo_score_max_passes', 'seo_score_profile', 'seo_score_disabled_rules',
            'generate_images_enabled', 'image_provider', 'image_model', 'image_provider_options', 'image_prompt',
            'image_count', 'image_placement', 'image_placement_param_x', 'image_alignment', 'image_size',
            'generate_image_title', 'generate_image_alt_text', 'generate_image_caption', 'generate_image_description',
            'image_title_prompt', 'image_alt_text_prompt', 'image_caption_prompt', 'image_description_prompt',
            'image_title_prompt_update', 'image_alt_text_prompt_update', 'image_caption_prompt_update', 'image_description_prompt_update',
            'generate_featured_image', 'featured_image_prompt',
            'pexels_orientation', 'pexels_size', 'pexels_color',
            'pixabay_orientation', 'pixabay_image_type', 'pixabay_category',
            'enable_vector_store', 'vector_store_provider', 'openai_vector_store_ids',
            'pinecone_index_name', 'qdrant_collection_name', 'chroma_collection_name', 'vector_embedding_provider',
            'vector_embedding_model', 'vector_store_top_k',
            'vector_store_confidence_threshold',
            'rss_include_keywords', 'rss_exclude_keywords',
            'reasoning_effort', // ADDED
        ];
        $prompt_template_keys = [
            'custom_title_prompt', 'custom_content_prompt', 'custom_meta_prompt',
            'custom_keyword_prompt', 'custom_excerpt_prompt', 'custom_tags_prompt',
            'image_prompt', 'featured_image_prompt',
            'image_title_prompt', 'image_alt_text_prompt', 'image_caption_prompt',
            'image_description_prompt', 'image_title_prompt_update',
            'image_alt_text_prompt_update', 'image_caption_prompt_update',
            'image_description_prompt_update',
        ];
        $textarea_keys = [
            'content_title_bulk', 'rss_feeds', 'url_list', 'rss_include_keywords',
            'rss_exclude_keywords', 'content_title', 'smart_schedule_start_datetime',
        ];

        foreach ($allowed_keys_from_template_manager as $key) {
            if (isset($settings[$key])) {
                if (in_array($key, $prompt_template_keys, true)) {
                    $content_writer_config[$key] = AIPKit_Prompt_Sanitizer::sanitize(wp_unslash($settings[$key]));
                } elseif (in_array($key, $textarea_keys, true)) {
                    $content_writer_config[$key] = sanitize_textarea_field(wp_unslash($settings[$key]));
                } elseif ($key === 'gsheets_credentials') {
                    if (class_exists('\WPAICG\Lib\Utils\AIPKit_Google_Credentials_Handler')) {
                        // The handler returns an array or null, which will be properly JSON encoded later.
                        $content_writer_config[$key] = \WPAICG\Lib\Utils\AIPKit_Google_Credentials_Handler::process_credentials($settings[$key]);
                    } else {
                        $content_writer_config[$key] = null;
                    }
                } elseif ($key === 'image_provider_options') {
                    $content_writer_config[$key] = class_exists(AIPKit_Content_Writer_Image_Provider_Options::class)
                        ? AIPKit_Content_Writer_Image_Provider_Options::sanitize_options_json($settings[$key], $settings)
                        : '{}';
                } elseif ($key === 'ai_provider' || $key === 'image_provider') {
                    $provider_raw = sanitize_text_field(wp_unslash($settings[$key]));
                    $provider_key = strtolower($provider_raw);
                    $provider_map = [
                        'openai' => 'OpenAI',
                        'openrouter' => 'OpenRouter',
                        'google' => 'Google',
                        'azure' => 'Azure',
                        'claude' => 'Claude',
                        'deepseek' => 'DeepSeek',
                        'ollama' => 'Ollama',
                        'xai' => 'xAI',
                    ];
                    $content_writer_config[$key] = $provider_map[$provider_key] ?? ucfirst($provider_key);
                } elseif (in_array($key, ['generate_meta_description', 'generate_focus_keyword', 'generate_excerpt', 'generate_tags', 'generate_toc', 'generate_seo_slug', 'seo_score_improvement_enabled', 'seo_score_continue_until_target', 'generate_images_enabled', 'generate_featured_image', 'generate_image_title', 'generate_image_alt_text', 'generate_image_caption', 'generate_image_description', 'enable_vector_store'], true)) {
                    $content_writer_config[$key] = ($settings[$key] === '1' || $settings[$key] === true || $settings[$key] === 1) ? '1' : '0';
                } elseif ($key === 'post_categories' && is_array($settings[$key])) {
                    $content_writer_config[$key] = array_map('absint', $settings[$key]);
                } elseif (in_array($key, ['post_author', 'image_count', 'image_placement_param_x', 'vector_store_top_k', 'smart_schedule_interval_value'], true)) {
                    $content_writer_config[$key] = absint($settings[$key]);
                } elseif ($key === 'seo_score_target') {
                    $raw = isset($settings[$key]) ? absint($settings[$key]) : 100;
                    $content_writer_config[$key] = (string) max(80, min($raw, 100));
                } elseif ($key === 'seo_score_max_passes') {
                    $raw = isset($settings[$key]) ? absint($settings[$key]) : 3;
                    $content_writer_config[$key] = (string) max(1, min($raw, 5));
                } elseif ($key === 'seo_score_disabled_rules') {
                    $content_writer_config[$key] = class_exists(AIPKit_Content_Writer_SEO_Config::class)
                        ? AIPKit_Content_Writer_SEO_Config::sanitize_disabled_rules($settings[$key])
                        : '[]';
                } elseif ($key === 'vector_store_confidence_threshold') {
                    $raw = isset($settings[$key]) ? absint($settings[$key]) : 20;
                    $content_writer_config[$key] = max(0, min($raw, 100));
                } elseif ($key === 'ai_temperature') {
                    $content_writer_config[$key] = (string)floatval($settings[$key]);
                } elseif ($key === 'content_length') {
                    $value = sanitize_key($settings[$key]);
                    $content_writer_config[$key] = in_array($value, ['short', 'medium', 'long'], true) ? $value : 'medium';
                } elseif ($key === 'openai_vector_store_ids' && is_array($settings[$key])) {
                    $content_writer_config[$key] = array_map('sanitize_text_field', $settings[$key]);
                } elseif ($key === 'reasoning_effort') {
                    $reasoning_effort = AIPKit_OpenAI_Reasoning::sanitize_effort($settings[$key] ?? '');
                    $content_writer_config[$key] = $reasoning_effort !== '' ? $reasoning_effort : 'none';
                } elseif ($key === 'seo_score_profile') {
                    $profile = sanitize_key($settings[$key]);
                    $allowed_profiles = ['auto', 'aipkit', 'yoast', 'rank_math', 'aioseo', 'framework'];
                    $content_writer_config[$key] = in_array($profile, $allowed_profiles, true) ? $profile : 'auto';
                } elseif (in_array($key, ['post_type', 'post_status', 'prompt_mode', 'cw_generation_mode', 'image_provider', 'image_placement', 'image_alignment', 'image_size', 'vector_store_provider', 'vector_embedding_provider', 'pexels_orientation', 'pexels_size', 'pexels_color', 'pixabay_orientation', 'pixabay_image_type', 'pixabay_category', 'schedule_mode', 'smart_schedule_interval_unit'], true)) {
                    $content_writer_config[$key] = sanitize_key($settings[$key]);
                } elseif (is_string($settings[$key])) {
                    $content_writer_config[$key] = sanitize_text_field(wp_unslash($settings[$key]));
                } else {
                    $content_writer_config[$key] = $settings[$key];
                }
            }
        }

        $generation_mode = $content_writer_config['cw_generation_mode'] ?? 'task';
        if ($generation_mode === 'single') {
            $generation_mode = 'task';
            $content_writer_config['cw_generation_mode'] = 'task';
        }

        // Only map bulk input into content_title for bulk/task mode.
        if ($generation_mode === 'task' && !empty($content_writer_config['content_title_bulk'])) {
            $content_writer_config['content_title'] = $content_writer_config['content_title_bulk'];
        }
        unset($content_writer_config['content_title_bulk']);

        $content_writer_config['seo_score_improvement_enabled'] = $content_writer_config['seo_score_improvement_enabled'] ?? '0';
        $content_writer_config['seo_score_continue_until_target'] = $content_writer_config['seo_score_continue_until_target'] ?? '1';
        $content_writer_config['seo_score_target'] = $content_writer_config['seo_score_target'] ?? '100';
        $content_writer_config['seo_score_max_passes'] = $content_writer_config['seo_score_max_passes'] ?? '3';
        $content_writer_config['seo_score_profile'] = $content_writer_config['seo_score_profile'] ?? 'auto';
        $content_writer_config['seo_score_disabled_rules'] = $content_writer_config['seo_score_disabled_rules']
            ?? (class_exists(AIPKit_Content_Writer_SEO_Config::class) ? AIPKit_Content_Writer_SEO_Config::default_disabled_rules() : '[]');
        $content_writer_config['image_provider_options'] = class_exists(AIPKit_Content_Writer_Image_Provider_Options::class)
            ? AIPKit_Content_Writer_Image_Provider_Options::sanitize_options_json($content_writer_config['image_provider_options'] ?? '{}', $content_writer_config)
            : ($content_writer_config['image_provider_options'] ?? '{}');

        if (class_exists(AIPKit_Content_Writer_SEO_Config::class)) {
            $seo_permission_check = AIPKit_Content_Writer_SEO_Config::require_pro_for_improvement($content_writer_config);
            if (is_wp_error($seo_permission_check)) {
                return $seo_permission_check;
            }
            $content_writer_config = AIPKit_Content_Writer_SEO_Config::normalize($content_writer_config);
        }

        $content_writer_config['task_frequency'] = $task_frequency;
        $content_writer_config['task_status_on_creation'] = $task_status;
    }
    return $content_writer_config;
}
