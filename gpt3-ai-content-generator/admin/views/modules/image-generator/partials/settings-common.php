<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/image-generator/partials/settings-common.php
// NEW FILE

/**
 * Partial: Image Generator Settings - Common Settings
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Variables required from parent settings-image-generator.php:
// $settings_data (array containing common settings)

$custom_css = $settings_data['common']['custom_css'] ?? '';

$default_css_template = "/* --- AIPKit Image Generator Custom CSS Example (Dark Theme Base) --- */
.aipkit_image_generator_public_wrapper.aipkit-theme-custom {
    background-color: #1f2937; /* Dark grey background */
    border: 1px solid #4b5563; /* Slightly lighter border */
    color: #e5e7eb; /* Light text */
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    margin: 1em 0;
    padding: 20px;
}

.aipkit_image_generator_public_wrapper.aipkit-theme-custom .aipkit_image_generator_input_bar {
    background-color: #374151; /* Slightly lighter than container bg */
    border: 1px solid #4b5563;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* ... Add more rules as needed ... */
";

?>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
        <textarea
            id="aipkit_image_generator_custom_css"
            name="custom_css"
            class="aipkit_popover_option_textarea aipkit_popover_option_textarea--code aipkit_autosave_trigger"
            rows="15"
            placeholder="<?php echo esc_attr($default_css_template); ?>"
        ><?php echo esc_textarea($custom_css ?: $default_css_template); ?></textarea>
        <span class="aipkit_popover_option_helper">
            <?php esc_html_e('Add CSS rules for [aipkit_image_generator theme="custom"].', 'gpt3-ai-content-generator'); ?>
        </span>
    </div>
</div>
