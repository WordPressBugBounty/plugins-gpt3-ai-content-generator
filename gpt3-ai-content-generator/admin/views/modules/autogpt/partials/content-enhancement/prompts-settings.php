<?php
/**
 * Partial: Content Enhancement Automated Task - Prompt Settings (Redesigned)
 */

if (!defined('ABSPATH')) {
    exit;
}

$default_title_prompt = "You are an expert SEO copywriter. Generate the single best and most compelling SEO title based on the provided information. The title must:\n- Be under 60 characters\n- Start with the main focus keyword\n- Include at least one power word (e.g., Stunning, Must-Have, Exclusive)\n- Include a positive or negative sentiment word (e.g., Best, Effortless, Risky)\n\nReturn ONLY the new title text. Do not include any introduction, explanation, or quotation marks.\n\nOriginal title: \"{original_title}\"\nPost content snippet: \"{original_content}\"\nFocus keyword: \"{original_focus_keyword}\"";
$default_excerpt_prompt = "Rewrite the post excerpt to be more compelling and engaging based on the information provided. Use a friendly tone and aim for 1–2 concise sentences. Return ONLY the new excerpt without any explanation or formatting.\n\nPost title: \"{original_title}\"\nPost content snippet: \"{original_content}\"";
$default_meta_prompt = "Generate a single, concise, and SEO-friendly meta description (under 155 characters) for a web page based on the provided information. The description must:\n- Begin with or include the focus keyword near the start\n- Use an active voice\n- Include a clear call-to-action\n\nReturn ONLY the new meta description without any introduction or formatting.\n\nPage title: \"{original_title}\"\nPage content snippet: \"{original_content}\"\nFocus keyword: \"{original_focus_keyword}\"";
$default_content_prompt = "You are an expert editor. Rewrite and improve the following article to make it more engaging, clear, and informative. Maintain the original tone and intent, but enhance the writing quality. Ensure the following:\n- The revised content is at least 600 words long\n- The focus keyword appears in one or more subheadings (H2 or H3)\n- The focus keyword is used naturally throughout the article, especially in the introduction and conclusion\n\nThe article title is: {original_title}\nFocus keyword: {original_focus_keyword}\n\nOriginal Content:\n{original_content}";

$aipkit_ce_base_placeholders = [
    '{original_title}',
    '{original_content}',
    '{original_excerpt}',
    '{original_tags}',
    '{categories}',
];
$aipkit_ce_meta_placeholders = [
    '{original_title}',
    '{original_content}',
    '{original_meta_description}',
    '{original_tags}',
    '{categories}',
];
$aipkit_ce_focus_placeholders = [
    '{original_title}',
    '{original_content}',
    '{original_excerpt}',
    '{original_tags}',
    '{categories}',
    '{original_focus_keyword}',
];

$aipkit_ce_product_placeholders = [
    '{price}',
    '{regular_price}',
    '{sku}',
    '{attributes}',
    '{stock_quantity}',
    '{stock_status}',
    '{weight}',
    '{length}',
    '{width}',
    '{height}',
    '{purchase_note}',
    '{product_categories}',
];

$aipkit_prompt_items = [
    [
        'key' => 'title',
        'label' => __('Title', 'gpt3-ai-content-generator'),
        'description' => __('Rewrite post headlines', 'gpt3-ai-content-generator'),
        'toggle' => [
            'id' => 'aipkit_task_ce_update_title',
            'name' => 'ce_update_title',
        ],
        'flyout_id' => 'aipkit_task_ce_title_prompt_flyout',
        'flyout_title' => __('Title Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_ce_title_prompt',
            'name' => 'ce_title_prompt',
            'value' => $default_title_prompt,
            'placeholder' => __('Enter your title prompt...', 'gpt3-ai-content-generator'),
        ],
        'placeholders' => $aipkit_ce_focus_placeholders,
        'placeholders_extra' => $aipkit_ce_product_placeholders,
        'placeholders_extra_label' => __('For products:', 'gpt3-ai-content-generator'),
    ],
    [
        'key' => 'excerpt',
        'label' => __('Excerpt', 'gpt3-ai-content-generator'),
        'description' => __('Refresh short summaries', 'gpt3-ai-content-generator'),
        'toggle' => [
            'id' => 'aipkit_task_ce_update_excerpt',
            'name' => 'ce_update_excerpt',
        ],
        'flyout_id' => 'aipkit_task_ce_excerpt_prompt_flyout',
        'flyout_title' => __('Excerpt Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_ce_excerpt_prompt',
            'name' => 'ce_excerpt_prompt',
            'value' => $default_excerpt_prompt,
            'placeholder' => __('Enter your excerpt prompt...', 'gpt3-ai-content-generator'),
        ],
        'placeholders' => $aipkit_ce_base_placeholders,
        'placeholders_extra' => $aipkit_ce_product_placeholders,
        'placeholders_extra_label' => __('For products:', 'gpt3-ai-content-generator'),
    ],
    [
        'key' => 'content',
        'label' => __('Content', 'gpt3-ai-content-generator'),
        'description' => __('Improve body copy', 'gpt3-ai-content-generator'),
        'toggle' => [
            'id' => 'aipkit_task_ce_update_content',
            'name' => 'ce_update_content',
        ],
        'flyout_id' => 'aipkit_task_ce_content_prompt_flyout',
        'flyout_title' => __('Content Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_ce_content_prompt',
            'name' => 'ce_content_prompt',
            'value' => $default_content_prompt,
            'placeholder' => __('Enter your content prompt...', 'gpt3-ai-content-generator'),
        ],
        'placeholders' => $aipkit_ce_focus_placeholders,
        'placeholders_extra' => $aipkit_ce_product_placeholders,
        'placeholders_extra_label' => __('For products:', 'gpt3-ai-content-generator'),
    ],
    [
        'key' => 'meta',
        'label' => __('Meta Description', 'gpt3-ai-content-generator'),
        'description' => __('Update SEO meta', 'gpt3-ai-content-generator'),
        'toggle' => [
            'id' => 'aipkit_task_ce_update_meta',
            'name' => 'ce_update_meta',
        ],
        'flyout_id' => 'aipkit_task_ce_meta_prompt_flyout',
        'flyout_title' => __('Meta Description Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_ce_meta_prompt',
            'name' => 'ce_meta_prompt',
            'value' => $default_meta_prompt,
            'placeholder' => __('Enter your meta description prompt...', 'gpt3-ai-content-generator'),
        ],
        'placeholders' => $aipkit_ce_meta_placeholders,
        'placeholders_extra' => $aipkit_ce_product_placeholders,
        'placeholders_extra_label' => __('For products:', 'gpt3-ai-content-generator'),
    ],
];

include __DIR__ . '/../shared/prompts-popover-body.php';
