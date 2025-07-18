<?php
// File: admin/views/modules/chatbot/partials/appearance/custom-theme/general-appearance-settings.php
// NEW FILE

/**
 * Partial: Custom Theme - General Appearance Settings
 * (Font Family, Bubble Border Radius)
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent script (custom-theme-settings.php):
// $bot_id, $get_cts_val, $esc_cts_val_attr, $font_families, $custom_theme_defaults
?>
<h5 style="margin-top:15px;"><?php esc_html_e('General Appearance', 'gpt3-ai-content-generator'); ?></h5>
<div class="aipkit_form-row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_font_family_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Font Family', 'gpt3-ai-content-generator'); ?>
        </label>
        <select id="cts_font_family_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[font_family]" class="aipkit_form-input">
            <?php foreach($font_families as $name => $stack): ?>
                <option value="<?php echo esc_attr($stack); ?>" <?php selected($get_cts_val('font_family'), $stack); ?>>
                    <?php echo esc_html(is_string($name) ? $name : $stack); // Use key as name if available, else value ?>
                </option>
            <?php endforeach; ?>
             <option value="inherit" <?php selected($get_cts_val('font_family'), 'inherit'); ?>>
                <?php esc_html_e('Inherit from Page', 'gpt3-ai-content-generator'); ?>
            </option>
        </select>
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_bubble_border_radius_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Bubble Border Radius (px)', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="number"
            id="cts_bubble_border_radius_<?php echo esc_attr($bot_id); ?>"
            name="custom_theme_settings[bubble_border_radius]"
            class="aipkit_form-input"
            value="<?php echo $esc_cts_val_attr('bubble_border_radius'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: $esc_cts_val_attr is a helper closure that retrieves and escapes the value. ?>"
            min="0" max="50" step="1"
            placeholder="<?php echo esc_attr($custom_theme_defaults['bubble_border_radius_placeholder']); ?>"
        />
    </div>
</div>