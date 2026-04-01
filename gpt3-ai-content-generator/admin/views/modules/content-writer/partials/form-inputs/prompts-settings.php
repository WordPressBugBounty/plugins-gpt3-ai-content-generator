<?php
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompts;

// --- Define Default Prompt Templates ---
$default_custom_content_prompt = AIPKit_Content_Writer_Prompts::get_default_content_prompt();
$default_custom_title_prompt = AIPKit_Content_Writer_Prompts::get_default_title_prompt();
$default_custom_meta_prompt = AIPKit_Content_Writer_Prompts::get_default_meta_prompt();
$default_custom_keyword_prompt = AIPKit_Content_Writer_Prompts::get_default_keyword_prompt();
$default_custom_excerpt_prompt = AIPKit_Content_Writer_Prompts::get_default_excerpt_prompt();
$default_custom_tags_prompt = AIPKit_Content_Writer_Prompts::get_default_tags_prompt();
$prompt_library = AIPKit_Content_Writer_Prompts::get_prompt_library();
$prompt_items = [
    [
        'key' => 'title',
        'label' => __('Title', 'gpt3-ai-content-generator'),
        'desc' => __('Generate post headline', 'gpt3-ai-content-generator'),
        'field_id' => 'aipkit_cw_generate_title',
        'field_name' => 'generate_title',
        'flyout_id' => 'aipkit_cw_title_prompt_flyout',
        'checked' => true,
        'update_only' => true,
    ],
    [
        'key' => 'content',
        'label' => __('Content', 'gpt3-ai-content-generator'),
        'desc' => __('Generate main article body', 'gpt3-ai-content-generator'),
        'field_id' => 'aipkit_cw_generate_content',
        'field_name' => 'generate_content',
        'flyout_id' => 'aipkit_cw_content_prompt_flyout',
        'checked' => true,
        'update_only' => true,
    ],
    [
        'key' => 'meta',
        'label' => __('Meta Description', 'gpt3-ai-content-generator'),
        'desc' => __('SEO meta for search engines', 'gpt3-ai-content-generator'),
        'field_id' => 'aipkit_cw_generate_meta_desc',
        'field_name' => 'generate_meta_description',
        'flyout_id' => 'aipkit_cw_meta_prompt_flyout',
        'checked' => true,
        'update_only' => false,
    ],
    [
        'key' => 'keyword',
        'label' => __('Focus Keyword', 'gpt3-ai-content-generator'),
        'desc' => __('Primary keyword for SEO', 'gpt3-ai-content-generator'),
        'field_id' => 'aipkit_cw_generate_focus_keyword',
        'field_name' => 'generate_focus_keyword',
        'flyout_id' => 'aipkit_cw_keyword_prompt_flyout',
        'checked' => true,
        'update_only' => false,
    ],
    [
        'key' => 'excerpt',
        'label' => __('Excerpt', 'gpt3-ai-content-generator'),
        'desc' => __('Short summary of the post', 'gpt3-ai-content-generator'),
        'field_id' => 'aipkit_cw_generate_excerpt',
        'field_name' => 'generate_excerpt',
        'flyout_id' => 'aipkit_cw_excerpt_prompt_flyout',
        'checked' => false,
        'update_only' => false,
    ],
    [
        'key' => 'tags',
        'label' => __('Tags', 'gpt3-ai-content-generator'),
        'desc' => __('Auto-generate post tags', 'gpt3-ai-content-generator'),
        'field_id' => 'aipkit_cw_generate_tags',
        'field_name' => 'generate_tags',
        'flyout_id' => 'aipkit_cw_tags_prompt_flyout',
        'checked' => false,
        'update_only' => false,
    ],
];

$image_prompt_main_items = [
    [
        'row_id' => 'aipkit_cw_image_prompt_field',
        'label' => __('Content Prompt', 'gpt3-ai-content-generator'),
        'desc' => __('Used for images added inside the content.', 'gpt3-ai-content-generator'),
        'button_id' => 'aipkit_cw_image_prompt_btn',
        'flyout_id' => 'aipkit_cw_image_prompt_flyout',
    ],
    [
        'row_id' => 'aipkit_cw_featured_image_prompt_field',
        'label' => __('Featured Prompt', 'gpt3-ai-content-generator'),
        'desc' => __('Used for the featured image prompt.', 'gpt3-ai-content-generator'),
        'button_id' => 'aipkit_cw_featured_image_prompt_btn',
        'flyout_id' => 'aipkit_cw_featured_image_prompt_flyout',
    ],
];

$image_prompt_metadata_items = [
    [
        'label' => __('Image Title', 'gpt3-ai-content-generator'),
        'desc' => __('Optimizes the attachment title.', 'gpt3-ai-content-generator'),
        'toggle_id' => 'aipkit_cw_generate_image_title',
        'toggle_name' => 'generate_image_title',
        'flyout_id' => 'aipkit_cw_image_title_prompt_flyout',
    ],
    [
        'label' => __('Alt Text', 'gpt3-ai-content-generator'),
        'desc' => __('Describes the image for accessibility.', 'gpt3-ai-content-generator'),
        'toggle_id' => 'aipkit_cw_generate_image_alt_text',
        'toggle_name' => 'generate_image_alt_text',
        'flyout_id' => 'aipkit_cw_image_alt_text_prompt_flyout',
    ],
    [
        'label' => __('Caption', 'gpt3-ai-content-generator'),
        'desc' => __('Adds a short image caption.', 'gpt3-ai-content-generator'),
        'toggle_id' => 'aipkit_cw_generate_image_caption',
        'toggle_name' => 'generate_image_caption',
        'flyout_id' => 'aipkit_cw_image_caption_prompt_flyout',
    ],
    [
        'label' => __('Description', 'gpt3-ai-content-generator'),
        'desc' => __('Updates the image description.', 'gpt3-ai-content-generator'),
        'toggle_id' => 'aipkit_cw_generate_image_description',
        'toggle_name' => 'generate_image_description',
        'flyout_id' => 'aipkit_cw_image_description_prompt_flyout',
    ],
];

$render_prompt_library_options = static function(array $options): void {
    foreach ($options as $option) {
        if (empty($option['label']) || empty($option['prompt'])) {
            continue;
        }
        printf(
            '<option value="%s">%s</option>',
            esc_attr($option['prompt']),
            esc_html($option['label'])
        );
    }
};
?>

<!-- Hidden input to ensure prompt_mode is always 'custom' -->
<input type="hidden" name="prompt_mode" id="aipkit_cw_prompt_mode_hidden_input" value="custom">
<input type="hidden" name="custom_title_prompt_update" id="aipkit_cw_custom_title_prompt_update" value="">
<input type="hidden" name="custom_content_prompt_update" id="aipkit_cw_custom_content_prompt_update" value="">
<input type="hidden" name="custom_meta_prompt_update" id="aipkit_cw_custom_meta_prompt_update" value="">
<input type="hidden" name="custom_keyword_prompt_update" id="aipkit_cw_custom_keyword_prompt_update" value="">
<input type="hidden" name="custom_excerpt_prompt_update" id="aipkit_cw_custom_excerpt_prompt_update" value="">
<input type="hidden" name="custom_tags_prompt_update" id="aipkit_cw_custom_tags_prompt_update" value="">

<div class="aipkit_cw_ai_row">
    <div class="aipkit_cw_panel_label_wrap">
        <div class="aipkit_cw_panel_label">
            <?php esc_html_e('Prompts', 'gpt3-ai-content-generator'); ?>
        </div>
    </div>
    <div class="aipkit_cw_ai_control aipkit_cw_ai_control--compact">
        <button
            type="button"
            id="aipkit_cw_prompt_trigger"
            class="button button-secondary aipkit_btn aipkit_btn-secondary aipkit_cw_popover_trigger"
            data-aipkit-popover-target="aipkit_cw_prompt_settings_popover"
            data-aipkit-popover-placement="left"
            aria-controls="aipkit_cw_prompt_settings_popover"
            aria-expanded="false"
        >
            <?php esc_html_e('Customize', 'gpt3-ai-content-generator'); ?>
        </button>
    </div>
</div>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover aipkit_cw_prompt_popover" id="aipkit_cw_prompt_settings_popover" aria-hidden="true" data-aipkit-popover-default-view="root" data-aipkit-popover-active-view="root">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel aipkit_cw_prompt_popover_panel" role="dialog" aria-label="<?php esc_attr_e('Prompts', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_model_settings_popover_header aipkit_cw_settings_sheet_header aipkit_cw_prompt_sheet_header">
            <button
                type="button"
                class="aipkit_cw_prompt_nav_back is-hidden"
                data-aipkit-popover-view-back
                aria-label="<?php esc_attr_e('Back to prompts', 'gpt3-ai-content-generator'); ?>"
                title="<?php esc_attr_e('Back', 'gpt3-ai-content-generator'); ?>"
                aria-hidden="true"
                tabindex="-1"
            >
                <span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
            </button>
            <span class="aipkit_model_settings_popover_title" data-aipkit-popover-title><?php esc_html_e('Prompts', 'gpt3-ai-content-generator'); ?></span>
        </div>
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body aipkit_cw_prompt_popover_body">
            <div class="aipkit_cw_prompt_popover_stage">
                <div class="aipkit_cw_prompt_popover_view aipkit_cw_prompt_popover_view--root is-active" data-aipkit-popover-view="root" data-aipkit-popover-view-label="<?php esc_attr_e('Prompts', 'gpt3-ai-content-generator'); ?>" aria-hidden="false">
                    <div class="aipkit_cw_prompt_panel_rows">
                        <?php foreach ($prompt_items as $item): ?>
                            <div class="aipkit_cw_prompt_item" data-prompt-key="<?php echo esc_attr($item['key']); ?>">
                                <div class="aipkit_cw_prompt_item_text">
                                    <span class="aipkit_cw_prompt_item_label"><?php echo esc_html($item['label']); ?></span>
                                    <span class="aipkit_cw_prompt_item_desc"><?php echo esc_html($item['desc']); ?></span>
                                </div>
                                <div class="aipkit_cw_prompt_item_actions">
                                    <label class="aipkit_switch<?php echo $item['update_only'] ? ' aipkit_prompt_update_only' : ''; ?>">
                                        <input
                                            type="checkbox"
                                            id="<?php echo esc_attr($item['field_id']); ?>"
                                            name="<?php echo esc_attr($item['field_name']); ?>"
                                            class="aipkit_toggle_switch aipkit_autosave_trigger"
                                            value="1"
                                            <?php checked($item['checked']); ?>
                                        >
                                        <span class="aipkit_switch_slider"></span>
                                    </label>
                                    <button
                                        type="button"
                                        class="aipkit_cw_prompt_edit_btn"
                                        data-aipkit-flyout-target="<?php echo esc_attr($item['flyout_id']); ?>"
                                        aria-controls="<?php echo esc_attr($item['flyout_id']); ?>"
                                        aria-expanded="false"
                                        title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                                    >
                                        <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="aipkit_cw_prompt_item aipkit_cw_prompt_item--nested" id="aipkit_cw_image_prompts_prompt_item" hidden>
                            <button
                                type="button"
                                class="aipkit_cw_prompt_nested_btn"
                                data-aipkit-popover-view-target="image-prompts"
                                aria-controls="aipkit_cw_prompt_settings_popover"
                            >
                                <span class="aipkit_cw_prompt_item_text">
                                    <span class="aipkit_cw_prompt_item_label"><?php esc_html_e('Image prompts', 'gpt3-ai-content-generator'); ?></span>
                                    <span class="aipkit_cw_prompt_item_desc"><?php esc_html_e('Content, featured, metadata', 'gpt3-ai-content-generator'); ?></span>
                                </span>
                                <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="aipkit_cw_prompt_popover_view aipkit_cw_prompt_popover_view--image" data-aipkit-popover-view="image-prompts" data-aipkit-popover-view-label="<?php esc_attr_e('Image prompts', 'gpt3-ai-content-generator'); ?>" aria-hidden="true">
                    <div class="aipkit_popover_options_list">
                        <div id="aipkit_cw_image_prompt_main_block" hidden>
                            <?php foreach ($image_prompt_main_items as $item): ?>
                                <div class="aipkit_popover_option_row" id="<?php echo esc_attr($item['row_id']); ?>" hidden>
                                    <div class="aipkit_popover_option_main">
                                        <div class="aipkit_cw_settings_option_text">
                                            <span class="aipkit_popover_option_label"><?php echo esc_html($item['label']); ?></span>
                                            <span class="aipkit_popover_option_helper"><?php echo esc_html($item['desc']); ?></span>
                                        </div>
                                        <button
                                            type="button"
                                            id="<?php echo esc_attr($item['button_id']); ?>"
                                            class="aipkit_cw_prompt_edit_btn"
                                            data-aipkit-flyout-target="<?php echo esc_attr($item['flyout_id']); ?>"
                                            aria-controls="<?php echo esc_attr($item['flyout_id']); ?>"
                                            aria-expanded="false"
                                            title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                                            aria-label="<?php printf(esc_attr__('Edit %s prompt', 'gpt3-ai-content-generator'), esc_attr($item['label'])); ?>"
                                        >
                                            <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div id="aipkit_cw_image_metadata_block" hidden>
                            <?php foreach ($image_prompt_metadata_items as $item): ?>
                                <div class="aipkit_popover_option_row aipkit_cw_image_metadata_row">
                                    <div class="aipkit_popover_option_main">
                                        <div class="aipkit_cw_settings_option_text">
                                            <span class="aipkit_popover_option_label"><?php echo esc_html($item['label']); ?></span>
                                            <span class="aipkit_popover_option_helper"><?php echo esc_html($item['desc']); ?></span>
                                        </div>
                                        <div class="aipkit_popover_option_actions aipkit_popover_option_actions--image">
                                            <label class="aipkit_switch">
                                                <input
                                                    type="checkbox"
                                                    id="<?php echo esc_attr($item['toggle_id']); ?>"
                                                    name="<?php echo esc_attr($item['toggle_name']); ?>"
                                                    class="aipkit_toggle_switch aipkit_autosave_trigger aipkit_cw_image_metadata_subtoggle"
                                                    value="1"
                                                >
                                                <span class="aipkit_switch_slider"></span>
                                            </label>
                                            <button
                                                type="button"
                                                class="aipkit_cw_prompt_edit_btn aipkit_cw_image_metadata_prompt_btn"
                                                data-aipkit-flyout-target="<?php echo esc_attr($item['flyout_id']); ?>"
                                                aria-controls="<?php echo esc_attr($item['flyout_id']); ?>"
                                                aria-expanded="false"
                                                title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                                                aria-label="<?php printf(esc_attr__('Edit %s prompt', 'gpt3-ai-content-generator'), esc_attr($item['label'])); ?>"
                                            >
                                                <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════════
     PROMPT FLYOUTS (Edit Panels) - REDESIGNED
     Clean, minimal design following 3 core UX principles:
     1. Aesthetic - No headers, clean layout
     2. Choice Overload Prevention - Template dropdown tucked away top-right
     3. Chunking - Textarea is the hero, placeholders grouped below
═══════════════════════════════════════════════════════════════════════════════ -->

<!-- Title Prompt Flyout -->
<div
    class="aipkit_cw_prompt_flyout"
    id="aipkit_cw_title_prompt_flyout"
    aria-hidden="true"
    data-default-title="<?php esc_attr_e('Title Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-images-title="<?php esc_attr_e('Image Title Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-products-title="<?php esc_attr_e('Product Title Prompt', 'gpt3-ai-content-generator'); ?>"
>
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Title Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Title Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_cw_title_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_custom_title_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_custom_title_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['title'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_custom_title_prompt" name="custom_title_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" placeholder="<?php esc_attr_e('Enter your title prompt...', 'gpt3-ai-content-generator'); ?>"><?php echo esc_textarea($default_custom_title_prompt); ?></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="title"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                ><?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code></span>
            </div>
        </div>
    </div>
</div>

<!-- Content Prompt Flyout -->
<div
    class="aipkit_cw_prompt_flyout"
    id="aipkit_cw_content_prompt_flyout"
    aria-hidden="true"
    data-default-title="<?php esc_attr_e('Content Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-images-title="<?php esc_attr_e('Image Description Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-products-title="<?php esc_attr_e('Product Description Prompt', 'gpt3-ai-content-generator'); ?>"
>
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Content Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Content Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_cw_content_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_custom_content_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_custom_content_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['content'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_custom_content_prompt" name="custom_content_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" placeholder="<?php esc_attr_e('Enter your content prompt...', 'gpt3-ai-content-generator'); ?>"><?php echo esc_textarea($default_custom_content_prompt); ?></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="content"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                ><?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code></span>
            </div>
        </div>
    </div>
</div>

<!-- Meta Description Prompt Flyout -->
<div
    class="aipkit_cw_prompt_flyout"
    id="aipkit_cw_meta_prompt_flyout"
    aria-hidden="true"
    data-default-title="<?php esc_attr_e('Meta Description Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-products-title="<?php esc_attr_e('Product Meta Description Prompt', 'gpt3-ai-content-generator'); ?>"
>
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Meta Description Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Meta Description Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_cw_meta_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_custom_meta_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_custom_meta_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['meta'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_custom_meta_prompt" name="custom_meta_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" placeholder="<?php esc_attr_e('Enter your meta description prompt...', 'gpt3-ai-content-generator'); ?>"><?php echo esc_textarea($default_custom_meta_prompt); ?></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="meta"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                ><?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{content_summary}</code> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code></span>
            </div>
        </div>
    </div>
</div>

<!-- Focus Keyword Prompt Flyout -->
<div
    class="aipkit_cw_prompt_flyout"
    id="aipkit_cw_keyword_prompt_flyout"
    aria-hidden="true"
    data-default-title="<?php esc_attr_e('Focus Keyword Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-images-title="<?php esc_attr_e('Image Alt Text Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-products-title="<?php esc_attr_e('Product Focus Keyword Prompt', 'gpt3-ai-content-generator'); ?>"
>
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Focus Keyword Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Focus Keyword Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_cw_keyword_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_custom_keyword_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_custom_keyword_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['keyword'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_custom_keyword_prompt" name="custom_keyword_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" placeholder="<?php esc_attr_e('Enter your focus keyword prompt...', 'gpt3-ai-content-generator'); ?>"><?php echo esc_textarea($default_custom_keyword_prompt); ?></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="keyword"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                ><?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{content_summary}</code></span>
            </div>
        </div>
    </div>
</div>

<!-- Excerpt Prompt Flyout -->
<div
    class="aipkit_cw_prompt_flyout"
    id="aipkit_cw_excerpt_prompt_flyout"
    aria-hidden="true"
    data-default-title="<?php esc_attr_e('Excerpt Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-images-title="<?php esc_attr_e('Image Caption Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-products-title="<?php esc_attr_e('Product Excerpt Prompt', 'gpt3-ai-content-generator'); ?>"
>
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Excerpt Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Excerpt Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_cw_excerpt_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_custom_excerpt_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_custom_excerpt_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['excerpt'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_custom_excerpt_prompt" name="custom_excerpt_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" placeholder="<?php esc_attr_e('Enter your excerpt prompt...', 'gpt3-ai-content-generator'); ?>"><?php echo esc_textarea($default_custom_excerpt_prompt); ?></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="excerpt"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                ><?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{content_summary}</code></span>
            </div>
        </div>
    </div>
</div>

<!-- Tags Prompt Flyout -->
<div
    class="aipkit_cw_prompt_flyout"
    id="aipkit_cw_tags_prompt_flyout"
    aria-hidden="true"
    data-default-title="<?php esc_attr_e('Tags Prompt', 'gpt3-ai-content-generator'); ?>"
    data-existing-products-title="<?php esc_attr_e('Product Tags Prompt', 'gpt3-ai-content-generator'); ?>"
>
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Tags Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Tags Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_cw_tags_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_custom_tags_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_custom_tags_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['tags'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_custom_tags_prompt" name="custom_tags_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" placeholder="<?php esc_attr_e('Enter your tags prompt...', 'gpt3-ai-content-generator'); ?>"><?php echo esc_textarea($default_custom_tags_prompt); ?></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="tags"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                ><?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code> <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{content_summary}</code></span>
            </div>
        </div>
    </div>
</div>
