<?php
// File: prompts-settings.php
// Status: REDESIGNED
/**
 * Partial: Content Writer Form - Prompts Settings
 * 
 * Simple card-based layout matching Image Settings popover design.
 * Each prompt is a toggle card with an edit button to open the flyout.
 * 
 * @since 2.1
 */
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

<div class="aipkit_prompts_redesigned">

    <!-- Title Prompt Card -->
    <div class="aipkit_prompt_toggle_card" data-prompt-key="title">
        <div class="aipkit_prompt_toggle_row">
            <div class="aipkit_prompt_toggle_info">
                <span class="aipkit_prompt_toggle_label"><?php esc_html_e('Title', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_prompt_toggle_desc"><?php esc_html_e('Generate post headline', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_prompt_toggle_controls">
                <label class="aipkit_switch aipkit_prompt_update_only">
                    <input
                        type="checkbox"
                        id="aipkit_cw_generate_title"
                        name="generate_title"
                        class="aipkit_toggle_switch aipkit_autosave_trigger"
                        value="1"
                        checked
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_prompt_edit_btn"
                    data-aipkit-flyout-target="aipkit_cw_title_prompt_flyout"
                    aria-controls="aipkit_cw_title_prompt_flyout"
                    aria-expanded="false"
                    title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Content Prompt Card -->
    <div class="aipkit_prompt_toggle_card" data-prompt-key="content">
        <div class="aipkit_prompt_toggle_row">
            <div class="aipkit_prompt_toggle_info">
                <span class="aipkit_prompt_toggle_label"><?php esc_html_e('Content', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_prompt_toggle_desc"><?php esc_html_e('Generate main article body', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_prompt_toggle_controls">
                <label class="aipkit_switch aipkit_prompt_update_only">
                    <input
                        type="checkbox"
                        id="aipkit_cw_generate_content"
                        name="generate_content"
                        class="aipkit_toggle_switch aipkit_autosave_trigger"
                        value="1"
                        checked
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_prompt_edit_btn"
                    data-aipkit-flyout-target="aipkit_cw_content_prompt_flyout"
                    aria-controls="aipkit_cw_content_prompt_flyout"
                    aria-expanded="false"
                    title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Meta Description Prompt Card -->
    <div class="aipkit_prompt_toggle_card" data-prompt-key="meta">
        <div class="aipkit_prompt_toggle_row">
            <div class="aipkit_prompt_toggle_info">
                <span class="aipkit_prompt_toggle_label"><?php esc_html_e('Meta Description', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_prompt_toggle_desc"><?php esc_html_e('SEO meta for search engines', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_prompt_toggle_controls">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_cw_generate_meta_desc"
                        name="generate_meta_description"
                        class="aipkit_toggle_switch aipkit_autosave_trigger"
                        value="1"
                        checked
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_prompt_edit_btn"
                    data-aipkit-flyout-target="aipkit_cw_meta_prompt_flyout"
                    aria-controls="aipkit_cw_meta_prompt_flyout"
                    aria-expanded="false"
                    title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Focus Keyword Prompt Card -->
    <div class="aipkit_prompt_toggle_card" data-prompt-key="keyword">
        <div class="aipkit_prompt_toggle_row">
            <div class="aipkit_prompt_toggle_info">
                <span class="aipkit_prompt_toggle_label"><?php esc_html_e('Focus Keyword', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_prompt_toggle_desc"><?php esc_html_e('Primary keyword for SEO', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_prompt_toggle_controls">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_cw_generate_focus_keyword"
                        name="generate_focus_keyword"
                        class="aipkit_toggle_switch aipkit_autosave_trigger"
                        value="1"
                        checked
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_prompt_edit_btn"
                    data-aipkit-flyout-target="aipkit_cw_keyword_prompt_flyout"
                    aria-controls="aipkit_cw_keyword_prompt_flyout"
                    aria-expanded="false"
                    title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Excerpt Prompt Card -->
    <div class="aipkit_prompt_toggle_card" data-prompt-key="excerpt">
        <div class="aipkit_prompt_toggle_row">
            <div class="aipkit_prompt_toggle_info">
                <span class="aipkit_prompt_toggle_label"><?php esc_html_e('Excerpt', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_prompt_toggle_desc"><?php esc_html_e('Short summary of the post', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_prompt_toggle_controls">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_cw_generate_excerpt"
                        name="generate_excerpt"
                        class="aipkit_toggle_switch aipkit_autosave_trigger"
                        value="1"
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_prompt_edit_btn"
                    data-aipkit-flyout-target="aipkit_cw_excerpt_prompt_flyout"
                    aria-controls="aipkit_cw_excerpt_prompt_flyout"
                    aria-expanded="false"
                    title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tags Prompt Card -->
    <div class="aipkit_prompt_toggle_card" data-prompt-key="tags">
        <div class="aipkit_prompt_toggle_row">
            <div class="aipkit_prompt_toggle_info">
                <span class="aipkit_prompt_toggle_label"><?php esc_html_e('Tags', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_prompt_toggle_desc"><?php esc_html_e('Auto-generate post tags', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_prompt_toggle_controls">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_cw_generate_tags"
                        name="generate_tags"
                        class="aipkit_toggle_switch aipkit_autosave_trigger"
                        value="1"
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_prompt_edit_btn"
                    data-aipkit-flyout-target="aipkit_cw_tags_prompt_flyout"
                    aria-controls="aipkit_cw_tags_prompt_flyout"
                    aria-expanded="false"
                    title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                </button>
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
