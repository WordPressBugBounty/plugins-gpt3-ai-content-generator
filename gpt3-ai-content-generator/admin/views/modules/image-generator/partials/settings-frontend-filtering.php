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
<p class="aipkit_form-help">
    <?php esc_html_e('Control which AI providers and models are available to users on the frontend Image Generator shortcode. Leave blank to show all available options.', 'gpt3-ai-content-generator'); ?>
</p>
<div class="aipkit_form-group">
    <label class="aipkit_form-label" for="aipkit_image_gen_frontend_providers"><?php esc_html_e('Allowed Providers (comma-separated)', 'gpt3-ai-content-generator'); ?></label>
    <textarea
        id="aipkit_image_gen_frontend_providers"
        name="frontend_providers"
        class="aipkit_form-input aipkit_settings_input"
        rows="2"
        placeholder="<?php esc_attr_e('e.g., OpenAI, Google, Replicate', 'gpt3-ai-content-generator'); ?>"
    ><?php echo esc_textarea($allowed_providers_str); ?></textarea>
     <div class="aipkit_form-help">
        <?php esc_html_e('Enter provider names exactly as they appear in the UI (OpenAI, Google, Replicate).', 'gpt3-ai-content-generator'); ?>
    </div>
</div>
<div class="aipkit_form-group">
    <label class="aipkit_form-label" for="aipkit_image_gen_frontend_models"><?php esc_html_e('Allowed Models (comma-separated)', 'gpt3-ai-content-generator'); ?></label>
    <textarea
        id="aipkit_image_gen_frontend_models"
        name="frontend_models"
        class="aipkit_form-input aipkit_settings_input"
        rows="4"
        placeholder="<?php esc_attr_e('e.g., dall-e-3, imagen-3.0-generate-002, stability-ai/sdxl', 'gpt3-ai-content-generator'); ?>"
    ><?php echo esc_textarea($allowed_models_str); ?></textarea>
     <div class="aipkit_form-help">
        <?php esc_html_e('Enter the exact model IDs. This will filter models across all selected providers.', 'gpt3-ai-content-generator'); ?>
    </div>
</div>