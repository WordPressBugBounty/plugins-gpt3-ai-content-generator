<?php
/**
 * Partial: Image Generator Settings - UI Text
 * Frontend label and placeholder overrides for the image generator shortcode.
 */
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Images\AIPKit_Image_Settings_Ajax_Handler;

$ui_text_settings = $settings_data['ui_text'] ?? [];
$ui_text_defaults = AIPKit_Image_Settings_Ajax_Handler::get_default_ui_text_settings();

$get_ui_text_value = static function (string $key) use ($ui_text_settings, $ui_text_defaults): string {
    $value = isset($ui_text_settings[$key]) ? (string) $ui_text_settings[$key] : '';
    if ($value === '' && isset($ui_text_defaults[$key])) {
        return (string) $ui_text_defaults[$key];
    }
    return $value;
};
?>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_generate_label">
            <?php esc_html_e('Generate button label', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_generate_label"
            name="ui_text_generate_label"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('generate_label')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['generate_label']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_edit_label">
            <?php esc_html_e('Edit button label', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_edit_label"
            name="ui_text_edit_label"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('edit_label')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['edit_label']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_mode_generate_label">
            <?php esc_html_e('Mode tab label (Generate)', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_mode_generate_label"
            name="ui_text_mode_generate_label"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('mode_generate_label')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['mode_generate_label']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_mode_edit_label">
            <?php esc_html_e('Mode tab label (Edit)', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_mode_edit_label"
            name="ui_text_mode_edit_label"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('mode_edit_label')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['mode_edit_label']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_generate_placeholder">
            <?php esc_html_e('Generate prompt placeholder', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_generate_placeholder"
            name="ui_text_generate_placeholder"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('generate_placeholder')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['generate_placeholder']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_edit_placeholder">
            <?php esc_html_e('Edit prompt placeholder', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_edit_placeholder"
            name="ui_text_edit_placeholder"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('edit_placeholder')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['edit_placeholder']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_source_image_label">
            <?php esc_html_e('Source image label', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_source_image_label"
            name="ui_text_source_image_label"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('source_image_label')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['source_image_label']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_upload_dropzone_title">
            <?php esc_html_e('Upload dropzone title', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_upload_dropzone_title"
            name="ui_text_upload_dropzone_title"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('upload_dropzone_title')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['upload_dropzone_title']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_upload_dropzone_meta">
            <?php esc_html_e('Upload dropzone meta text', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_upload_dropzone_meta"
            name="ui_text_upload_dropzone_meta"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('upload_dropzone_meta')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['upload_dropzone_meta']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_upload_hint">
            <?php esc_html_e('Upload helper text', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_upload_hint"
            name="ui_text_upload_hint"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('upload_hint')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['upload_hint']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_history_title">
            <?php esc_html_e('History title', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_history_title"
            name="ui_text_history_title"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('history_title')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['history_title']); ?>"
        />
    </div>
</div>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label class="aipkit_popover_option_label" for="aipkit_image_ui_text_results_empty">
            <?php esc_html_e('Results empty text', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_ui_text_results_empty"
            name="ui_text_results_empty"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($get_ui_text_value('results_empty')); ?>"
            placeholder="<?php echo esc_attr($ui_text_defaults['results_empty']); ?>"
        />
    </div>
</div>
