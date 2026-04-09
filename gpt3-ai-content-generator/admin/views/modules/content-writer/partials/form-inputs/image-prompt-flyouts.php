<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

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
