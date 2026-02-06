<?php
/**
 * Partial: Content Writing Automated Task - Prompt Settings (Redesigned)
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompts;

$default_custom_content_prompt = AIPKit_Content_Writer_Prompts::get_default_content_prompt();
$default_custom_title_prompt = AIPKit_Content_Writer_Prompts::get_default_title_prompt();
$default_custom_meta_prompt = AIPKit_Content_Writer_Prompts::get_default_meta_prompt();
$default_custom_keyword_prompt = AIPKit_Content_Writer_Prompts::get_default_keyword_prompt();
$default_custom_excerpt_prompt = AIPKit_Content_Writer_Prompts::get_default_excerpt_prompt();
$default_custom_tags_prompt = AIPKit_Content_Writer_Prompts::get_default_tags_prompt();
$prompt_library = AIPKit_Content_Writer_Prompts::get_prompt_library();

$aipkit_cw_placeholders_by_type = [
    'title' => ['{topic}', '{keywords}'],
    'content' => ['{topic}', '{keywords}'],
    'meta' => ['{topic}', '{content_summary}', '{keywords}'],
    'keyword' => ['{topic}', '{content_summary}'],
    'excerpt' => ['{topic}', '{keywords}', '{content_summary}'],
    'tags' => ['{topic}', '{keywords}', '{content_summary}'],
];

$aipkit_prompt_items = [
    [
        'key' => 'title',
        'label' => __('Title', 'gpt3-ai-content-generator'),
        'description' => __('Generate post headline', 'gpt3-ai-content-generator'),
        'flyout_id' => 'aipkit_task_cw_title_prompt_flyout',
        'flyout_title' => __('Title Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_cw_custom_title_prompt',
            'name' => 'custom_title_prompt',
            'value' => $default_custom_title_prompt,
            'placeholder' => __('Enter your title prompt...', 'gpt3-ai-content-generator'),
        ],
        'library' => [
            'select_id' => 'aipkit_task_cw_title_prompt_library',
            'options' => $prompt_library['title'] ?? [],
            'default_prompt' => $default_custom_title_prompt,
        ],
        'placeholders' => $aipkit_cw_placeholders_by_type['title'],
        'placeholders_prompt_type' => 'title',
    ],
    [
        'key' => 'content',
        'label' => __('Content', 'gpt3-ai-content-generator'),
        'description' => __('Generate main article body', 'gpt3-ai-content-generator'),
        'flyout_id' => 'aipkit_task_cw_content_prompt_flyout',
        'flyout_title' => __('Content Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_cw_custom_content_prompt',
            'name' => 'custom_content_prompt',
            'value' => $default_custom_content_prompt,
            'placeholder' => __('Enter your content prompt...', 'gpt3-ai-content-generator'),
        ],
        'library' => [
            'select_id' => 'aipkit_task_cw_content_prompt_library',
            'options' => $prompt_library['content'] ?? [],
            'default_prompt' => $default_custom_content_prompt,
        ],
        'placeholders' => $aipkit_cw_placeholders_by_type['content'],
        'placeholders_prompt_type' => 'content',
    ],
    [
        'key' => 'meta',
        'label' => __('Meta Description', 'gpt3-ai-content-generator'),
        'description' => __('SEO meta for search engines', 'gpt3-ai-content-generator'),
        'toggle' => [
            'id' => 'aipkit_task_cw_generate_meta_desc',
            'name' => 'generate_meta_description',
            'checked' => true,
        ],
        'flyout_id' => 'aipkit_task_cw_meta_prompt_flyout',
        'flyout_title' => __('Meta Description Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_cw_custom_meta_prompt',
            'name' => 'custom_meta_prompt',
            'value' => $default_custom_meta_prompt,
            'placeholder' => __('Enter your meta description prompt...', 'gpt3-ai-content-generator'),
        ],
        'library' => [
            'select_id' => 'aipkit_task_cw_meta_prompt_library',
            'options' => $prompt_library['meta'] ?? [],
            'default_prompt' => $default_custom_meta_prompt,
        ],
        'placeholders' => $aipkit_cw_placeholders_by_type['meta'],
        'placeholders_prompt_type' => 'meta',
    ],
    [
        'key' => 'keyword',
        'label' => __('Focus Keyword', 'gpt3-ai-content-generator'),
        'description' => __('Primary keyword for SEO', 'gpt3-ai-content-generator'),
        'toggle' => [
            'id' => 'aipkit_task_cw_generate_focus_keyword',
            'name' => 'generate_focus_keyword',
            'checked' => true,
        ],
        'flyout_id' => 'aipkit_task_cw_keyword_prompt_flyout',
        'flyout_title' => __('Focus Keyword Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_cw_custom_keyword_prompt',
            'name' => 'custom_keyword_prompt',
            'value' => $default_custom_keyword_prompt,
            'placeholder' => __('Enter your focus keyword prompt...', 'gpt3-ai-content-generator'),
        ],
        'library' => [
            'select_id' => 'aipkit_task_cw_keyword_prompt_library',
            'options' => $prompt_library['keyword'] ?? [],
            'default_prompt' => $default_custom_keyword_prompt,
        ],
        'placeholders' => $aipkit_cw_placeholders_by_type['keyword'],
        'placeholders_prompt_type' => 'keyword',
    ],
    [
        'key' => 'excerpt',
        'label' => __('Excerpt', 'gpt3-ai-content-generator'),
        'description' => __('Short summary of the post', 'gpt3-ai-content-generator'),
        'toggle' => [
            'id' => 'aipkit_task_cw_generate_excerpt',
            'name' => 'generate_excerpt',
            'checked' => true,
        ],
        'flyout_id' => 'aipkit_task_cw_excerpt_prompt_flyout',
        'flyout_title' => __('Excerpt Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_cw_custom_excerpt_prompt',
            'name' => 'custom_excerpt_prompt',
            'value' => $default_custom_excerpt_prompt,
            'placeholder' => __('Enter your excerpt prompt...', 'gpt3-ai-content-generator'),
        ],
        'library' => [
            'select_id' => 'aipkit_task_cw_excerpt_prompt_library',
            'options' => $prompt_library['excerpt'] ?? [],
            'default_prompt' => $default_custom_excerpt_prompt,
        ],
        'placeholders' => $aipkit_cw_placeholders_by_type['excerpt'],
        'placeholders_prompt_type' => 'excerpt',
    ],
    [
        'key' => 'tags',
        'label' => __('Tags', 'gpt3-ai-content-generator'),
        'description' => __('Auto-generate post tags', 'gpt3-ai-content-generator'),
        'toggle' => [
            'id' => 'aipkit_task_cw_generate_tags',
            'name' => 'generate_tags',
            'checked' => true,
        ],
        'flyout_id' => 'aipkit_task_cw_tags_prompt_flyout',
        'flyout_title' => __('Tags Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_cw_custom_tags_prompt',
            'name' => 'custom_tags_prompt',
            'value' => $default_custom_tags_prompt,
            'placeholder' => __('Enter your tags prompt...', 'gpt3-ai-content-generator'),
        ],
        'library' => [
            'select_id' => 'aipkit_task_cw_tags_prompt_library',
            'options' => $prompt_library['tags'] ?? [],
            'default_prompt' => $default_custom_tags_prompt,
        ],
        'placeholders' => $aipkit_cw_placeholders_by_type['tags'],
        'placeholders_prompt_type' => 'tags',
    ],
];

include __DIR__ . '/../shared/prompts-popover-body.php';
