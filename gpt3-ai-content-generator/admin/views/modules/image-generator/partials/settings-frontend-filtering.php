<?php
/**
 * Partial: Image Generator Settings - Frontend Filtering
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables required from parent settings-image-generator.php:
// $settings_data (array containing frontend_display settings)
$frontend_display_settings = $settings_data['frontend_display'] ?? [];
$allowed_providers_str = $frontend_display_settings['allowed_providers'] ?? '';
$allowed_models_str = $frontend_display_settings['allowed_models'] ?? '';
?>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
        <label class="aipkit_popover_option_label" for="aipkit_image_gen_frontend_models">
            <?php esc_html_e('Allowed models', 'gpt3-ai-content-generator'); ?>
        </label>
        <textarea
            id="aipkit_image_gen_frontend_models"
            name="frontend_models"
            class="aipkit_popover_option_textarea aipkit_autosave_trigger"
            style="display:none;"
            rows="4"
            placeholder="<?php esc_attr_e('Select models below or leave empty for all', 'gpt3-ai-content-generator'); ?>"
        ><?php echo esc_textarea($allowed_models_str); ?></textarea>
        <div
            id="aipkit_image_gen_models_selector"
            class="aipkit_models_selector"
            data-initial-value="<?php echo esc_attr($allowed_models_str); ?>"
            data-empty-all-selected="<?php echo $allowed_models_str === '' ? '1' : '0'; ?>"
        >
            <div class="aipkit_models_selector-loading">
                <?php esc_html_e('Loading model list…', 'gpt3-ai-content-generator'); ?>
            </div>
        </div>
        <span class="aipkit_popover_option_helper">
            <?php esc_html_e('Pick specific models to show on the frontend. Leave everything unselected to allow all models. Use the per-provider toggle to include entire catalogs quickly.', 'gpt3-ai-content-generator'); ?>
        </span>
    </div>
</div>
