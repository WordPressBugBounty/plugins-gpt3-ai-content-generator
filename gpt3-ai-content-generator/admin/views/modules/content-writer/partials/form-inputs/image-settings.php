<?php
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\AIPKit_Providers;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompts;

$prompt_library = AIPKit_Content_Writer_Prompts::get_prompt_library();
$default_image_prompt = AIPKit_Content_Writer_Prompts::get_default_image_prompt();
$default_featured_image_prompt = AIPKit_Content_Writer_Prompts::get_default_featured_image_prompt();
$default_image_title_prompt = AIPKit_Content_Writer_Prompts::get_default_image_title_prompt();
$default_image_alt_text_prompt = AIPKit_Content_Writer_Prompts::get_default_image_alt_text_prompt();
$default_image_caption_prompt = AIPKit_Content_Writer_Prompts::get_default_image_caption_prompt();
$default_image_description_prompt = AIPKit_Content_Writer_Prompts::get_default_image_description_prompt();
$default_image_title_prompt_update = AIPKit_Content_Writer_Prompts::get_default_image_title_prompt_update();
$default_image_alt_text_prompt_update = AIPKit_Content_Writer_Prompts::get_default_image_alt_text_prompt_update();
$default_image_caption_prompt_update = AIPKit_Content_Writer_Prompts::get_default_image_caption_prompt_update();
$default_image_description_prompt_update = AIPKit_Content_Writer_Prompts::get_default_image_description_prompt_update();
$pexels_data = AIPKit_Providers::get_provider_data('Pexels');
$pixabay_data = AIPKit_Providers::get_provider_data('Pixabay');
$current_pexels_api_key = $pexels_data['api_key'] ?? '';
$current_pixabay_api_key = $pixabay_data['api_key'] ?? '';
$render_prompt_library_options = static function(array $options, string $mode = ''): void {
    foreach ($options as $option) {
        if (empty($option['label']) || empty($option['prompt'])) {
            continue;
        }
        $mode_attr = $mode !== '' ? sprintf(' data-aipkit-mode="%s"', esc_attr($mode)) : '';
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($option['prompt']),
            $mode_attr,
            esc_html($option['label'])
        );
    }
};
?>

<input type="hidden" name="image_prompt_update" id="aipkit_cw_image_prompt_update" value="">
<input type="hidden" name="featured_image_prompt_update" id="aipkit_cw_featured_image_prompt_update" value="">
<input type="hidden" name="image_title_prompt_update" id="aipkit_cw_image_title_prompt_update" value="<?php echo esc_attr($default_image_title_prompt_update); ?>">
<input type="hidden" name="image_alt_text_prompt_update" id="aipkit_cw_image_alt_text_prompt_update" value="<?php echo esc_attr($default_image_alt_text_prompt_update); ?>">
<input type="hidden" name="image_caption_prompt_update" id="aipkit_cw_image_caption_prompt_update" value="<?php echo esc_attr($default_image_caption_prompt_update); ?>">
<input type="hidden" name="image_description_prompt_update" id="aipkit_cw_image_description_prompt_update" value="<?php echo esc_attr($default_image_description_prompt_update); ?>">

<div class="aipkit_cw_image_section">
    <div class="aipkit_cw_image_hidden_fields" hidden aria-hidden="true">
        <input
            type="checkbox"
            id="aipkit_cw_generate_images_enabled"
            name="generate_images_enabled"
            class="aipkit_toggle_switch aipkit_cw_image_enable_toggle aipkit_autosave_trigger"
            value="1"
            tabindex="-1"
        >
        <input
            type="checkbox"
            id="aipkit_cw_generate_featured_image"
            name="generate_featured_image"
            class="aipkit_toggle_switch aipkit_autosave_trigger"
            value="1"
            tabindex="-1"
        >
        <select id="aipkit_cw_image_provider" name="image_provider" class="aipkit_autosave_trigger" tabindex="-1">
            <optgroup label="<?php echo esc_attr__('AI Providers', 'gpt3-ai-content-generator'); ?>">
                <option value="openai" selected>OpenAI</option>
                <option value="google">Google</option>
                <option value="openrouter">OpenRouter</option>
                <option value="azure">Azure</option>
                <option value="replicate"><?php esc_html_e('Replicate', 'gpt3-ai-content-generator'); ?></option>
            </optgroup>
            <optgroup label="<?php echo esc_attr__('Stock Photos', 'gpt3-ai-content-generator'); ?>">
                <option value="pexels"><?php esc_html_e('Pexels', 'gpt3-ai-content-generator'); ?></option>
                <option value="pixabay"><?php esc_html_e('Pixabay', 'gpt3-ai-content-generator'); ?></option>
            </optgroup>
        </select>
        <select id="aipkit_cw_image_model" name="image_model" class="aipkit_autosave_trigger" tabindex="-1">
            <?php // Populated by JS ?>
        </select>
    </div>

    <div class="aipkit_cw_image_row aipkit_cw_image_row--mode">
        <div class="aipkit_cw_panel_label_wrap">
            <label class="aipkit_cw_panel_label" for="aipkit_cw_image_mode_control">
                <?php esc_html_e('Images', 'gpt3-ai-content-generator'); ?>
            </label>
        </div>
        <div class="aipkit_cw_image_control">
            <select id="aipkit_cw_image_mode_control" class="aipkit_form-input aipkit_cw_blended_chevron_select">
                <option value="off"><?php esc_html_e('Off', 'gpt3-ai-content-generator'); ?></option>
                <option value="content"><?php esc_html_e('Content', 'gpt3-ai-content-generator'); ?></option>
                <option value="featured"><?php esc_html_e('Featured', 'gpt3-ai-content-generator'); ?></option>
                <option value="both"><?php esc_html_e('Content + Featured', 'gpt3-ai-content-generator'); ?></option>
            </select>
        </div>
    </div>

    <div class="aipkit_cw_image_row aipkit_cw_image_row--source" id="aipkit_cw_image_source_row" hidden>
        <div class="aipkit_cw_panel_label_wrap">
            <label class="aipkit_cw_panel_label" for="aipkit_content_writer_image_selection">
                <?php esc_html_e('Image source', 'gpt3-ai-content-generator'); ?>
            </label>
        </div>
        <div class="aipkit_cw_image_control aipkit_cw_image_control--selection">
            <div class="aipkit_cw_image_selection_inline">
                <select
                    id="aipkit_content_writer_image_selection"
                    class="aipkit_form-input"
                    data-aipkit-picker-title="<?php echo esc_attr__('Image source', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php // Populated by JS ?>
                </select>
                <?php $aipkit_cw_image_display_settings_render_mode = 'trigger'; ?>
                <?php include __DIR__ . '/image-display-settings.php'; ?>
            </div>
        </div>
    </div>

    <div class="aipkit_cw_image_row aipkit_cw_image_row--settings" id="aipkit_cw_image_settings_row" hidden>
        <div class="aipkit_cw_panel_label_wrap">
            <span class="aipkit_cw_panel_label">
                <?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?>
            </span>
        </div>
        <div class="aipkit_cw_image_control aipkit_cw_image_control--settings">
            <button
                type="button"
                class="aipkit_cw_settings_icon_trigger"
                id="aipkit_cw_image_display_settings_trigger_fallback"
                data-aipkit-popover-target="aipkit_cw_image_display_settings_popover"
                data-aipkit-popover-placement="left"
                aria-controls="aipkit_cw_image_display_settings_popover"
                aria-expanded="false"
                aria-label="<?php esc_attr_e('Image settings', 'gpt3-ai-content-generator'); ?>"
                title="<?php esc_attr_e('Image settings', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</div>

<?php $aipkit_cw_image_display_settings_render_mode = 'popover'; ?>
<?php include __DIR__ . '/image-display-settings.php'; ?>

<!-- Image Prompt Flyout -->
<div class="aipkit_cw_prompt_flyout" id="aipkit_cw_image_prompt_flyout" aria-hidden="true">
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Image Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Image Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_cw_image_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_image_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_image_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['image'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_image_prompt" name="image_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" rows="8" placeholder="<?php esc_attr_e('e.g., A photo of a freshly baked chocolate cake on a wooden table.', 'gpt3-ai-content-generator'); ?>"></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="image"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{excerpt}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{post_title}</code>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Image Title Prompt Flyout -->
<div class="aipkit_cw_prompt_flyout" id="aipkit_cw_image_title_prompt_flyout" aria-hidden="true">
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Image Title Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Image Title Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_image_title_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_image_title_prompt); ?>" data-aipkit-mode="both"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['image_title'] ?? [], 'create'); ?>
                    <?php $render_prompt_library_options($prompt_library['image_title_update'] ?? [], 'update'); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_image_title_prompt" name="image_title_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" rows="6" placeholder="<?php esc_attr_e('e.g., Golden retriever playing in a park.', 'gpt3-ai-content-generator'); ?>"></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="image_title"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{post_title}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{excerpt}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{file_name}</code>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Image Alt Text Prompt Flyout -->
<div class="aipkit_cw_prompt_flyout" id="aipkit_cw_image_alt_text_prompt_flyout" aria-hidden="true">
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Alt Text Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Alt Text Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_image_alt_text_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_image_alt_text_prompt); ?>" data-aipkit-mode="both"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['image_alt_text'] ?? [], 'create'); ?>
                    <?php $render_prompt_library_options($prompt_library['image_alt_text_update'] ?? [], 'update'); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_image_alt_text_prompt" name="image_alt_text_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" rows="6" placeholder="<?php esc_attr_e('e.g., Dog running through tall grass at sunset.', 'gpt3-ai-content-generator'); ?>"></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="image_alt_text"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{post_title}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{excerpt}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{file_name}</code>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Image Caption Prompt Flyout -->
<div class="aipkit_cw_prompt_flyout" id="aipkit_cw_image_caption_prompt_flyout" aria-hidden="true">
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Caption Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Caption Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_image_caption_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_image_caption_prompt); ?>" data-aipkit-mode="both"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['image_caption'] ?? [], 'create'); ?>
                    <?php $render_prompt_library_options($prompt_library['image_caption_update'] ?? [], 'update'); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_image_caption_prompt" name="image_caption_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" rows="6" placeholder="<?php esc_attr_e('e.g., A quiet morning in the mountains.', 'gpt3-ai-content-generator'); ?>"></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="image_caption"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{post_title}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{excerpt}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{file_name}</code>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Image Description Prompt Flyout -->
<div class="aipkit_cw_prompt_flyout" id="aipkit_cw_image_description_prompt_flyout" aria-hidden="true">
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Description Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Description Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_image_description_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_image_description_prompt); ?>" data-aipkit-mode="both"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['image_description'] ?? [], 'create'); ?>
                    <?php $render_prompt_library_options($prompt_library['image_description_update'] ?? [], 'update'); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_image_description_prompt" name="image_description_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" rows="6" placeholder="<?php esc_attr_e('e.g., A detailed scene of a cat lounging in sunlight.', 'gpt3-ai-content-generator'); ?>"></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="image_description"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{post_title}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{excerpt}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{file_name}</code>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Featured Image Prompt Flyout -->
<div class="aipkit_cw_prompt_flyout" id="aipkit_cw_featured_image_prompt_flyout" aria-hidden="true">
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Featured Image Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Featured Image Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_cw_featured_image_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_cw_featured_image_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_featured_image_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['featured_image'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_cw_featured_image_prompt" name="featured_image_prompt" class="aipkit_cw_prompt_editor_textarea aipkit_autosave_trigger" rows="8" placeholder="<?php esc_attr_e('Leave blank to use the main image prompt.', 'gpt3-ai-content-generator'); ?>"></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-prompt-type="featured_image"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{post_title}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{excerpt}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code>
                </span>
            </div>
        </div>
    </div>
</div>
