<?php
/**
 * Partial: Image Generator Settings - Replicate Provider
 * Settings specific to the Replicate image generation provider.
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables required from parent settings-image-generator.php:
// $settings_data (array containing replicate settings)
// $current_replicate_api_key (string)
$replicate_settings = $settings_data['replicate'] ?? [];
$disable_safety_checker = $replicate_settings['disable_safety_checker'] ?? true;
?>

<div class="aipkit_popover_option_group">
    <div class="aipkit_popover_option_row aipkit_popover_option_row--force-divider">
        <div class="aipkit_popover_option_main">
            <label
                class="aipkit_popover_option_label"
                for="aipkit_replicate_api_key"
            >
                <?php esc_html_e('API key', 'gpt3-ai-content-generator'); ?>
            </label>
            <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                <input
                    type="password"
                    id="aipkit_replicate_api_key"
                    name="replicate_api_key"
                    class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                    value="<?php echo esc_attr($current_replicate_api_key ?? ''); ?>"
                    placeholder="<?php esc_attr_e('Enter your Replicate API key', 'gpt3-ai-content-generator'); ?>"
                    autocomplete="new-password"
                    data-lpignore="true"
                    data-1p-ignore="true"
                    data-form-type="other"
                />
                <span class="aipkit_api-key-toggle">
                    <span class="dashicons dashicons-visibility"></span>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="aipkit_popover_option_group">
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
            <div class="aipkit_popover_option_header">
                <label class="aipkit_popover_option_label" for="aipkit_replicate_disable_safety_checker">
                    <?php esc_html_e('Disable safety checker', 'gpt3-ai-content-generator'); ?>
                </label>
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_replicate_disable_safety_checker"
                        name="replicate_disable_safety_checker"
                        class="aipkit_toggle_switch aipkit_autosave_trigger"
                        value="1"
                        <?php checked($disable_safety_checker, true); ?>
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
            </div>
        </div>
    </div>
</div>
