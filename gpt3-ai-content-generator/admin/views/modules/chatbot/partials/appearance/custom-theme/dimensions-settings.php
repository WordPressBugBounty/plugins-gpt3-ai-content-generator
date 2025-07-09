<?php
// File: admin/views/modules/chatbot/partials/appearance/custom-theme/dimensions-settings.php
// NEW FILE

/**
 * Partial: Custom Theme - Dimensions Settings
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent script (custom-theme-settings.php):
// $bot_id, $esc_cts_val_attr, $custom_theme_defaults
?>
<h5 style="margin-top:15px;"><?php esc_html_e('Dimensions', 'gpt3-ai-content-generator'); ?></h5>
<div class="aipkit_form-row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_container_max_width_<?php echo esc_attr($bot_id); ?>"><?php esc_html_e('Inline Max Width (px)', 'gpt3-ai-content-generator'); ?></label>
        <input type="number" id="cts_container_max_width_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[container_max_width]" class="aipkit_form-input" value="<?php echo esc_attr($esc_cts_val_attr('container_max_width')); ?>" placeholder="<?php echo esc_attr($custom_theme_defaults['container_max_width_placeholder']); ?>" min="200">
        <p class="aipkit_form-help"><?php esc_html_e('Max width for inline chat (e.g., 650).', 'gpt3-ai-content-generator'); ?></p>
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_popup_width_<?php echo esc_attr($bot_id); ?>"><?php esc_html_e('Popup Width (px)', 'gpt3-ai-content-generator'); ?></label>
        <input type="number" id="cts_popup_width_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[popup_width]" class="aipkit_form-input" value="<?php echo esc_attr($esc_cts_val_attr('popup_width')); ?>" placeholder="<?php echo esc_attr($custom_theme_defaults['popup_width_placeholder']); ?>" min="200" max="1000">
        <p class="aipkit_form-help"><?php esc_html_e('Width for popup on desktop (e.g., 400).', 'gpt3-ai-content-generator'); ?></p>
    </div>
</div>
<div class="aipkit_form-row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_container_height_<?php echo esc_attr($bot_id); ?>"><?php esc_html_e('Initial Height (px)', 'gpt3-ai-content-generator'); ?></label>
        <input type="number" id="cts_container_height_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[container_height]" class="aipkit_form-input" value="<?php echo esc_attr($esc_cts_val_attr('container_height')); ?>" placeholder="<?php echo esc_attr($custom_theme_defaults['container_height_placeholder']); ?>" min="100">
        <p class="aipkit_form-help"><?php esc_html_e('Preferred starting height (e.g., 450).', 'gpt3-ai-content-generator'); ?></p>
    </div>
     <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_container_min_height_<?php echo esc_attr($bot_id); ?>"><?php esc_html_e('Min Height (px)', 'gpt3-ai-content-generator'); ?></label>
        <input type="number" id="cts_container_min_height_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[container_min_height]" class="aipkit_form-input" value="<?php echo esc_attr($esc_cts_val_attr('container_min_height')); ?>" placeholder="<?php echo esc_attr($custom_theme_defaults['container_min_height_placeholder']); ?>" min="50">
        <p class="aipkit_form-help"><?php esc_html_e('Minimum height (e.g., 250).', 'gpt3-ai-content-generator'); ?></p>
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_container_max_height_<?php echo esc_attr($bot_id); ?>"><?php esc_html_e('Max Height (Viewport %)', 'gpt3-ai-content-generator'); ?></label>
        <input type="number" id="cts_container_max_height_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[container_max_height]" class="aipkit_form-input" value="<?php echo esc_attr($esc_cts_val_attr('container_max_height')); ?>" placeholder="<?php echo esc_attr($custom_theme_defaults['container_max_height_placeholder']); ?>" min="10" max="100">
        <p class="aipkit_form-help"><?php esc_html_e('Max height as % of window', 'gpt3-ai-content-generator'); ?></p>
    </div>
</div>
<p class="aipkit_form-help">
    <em><?php esc_html_e('For Popup mode, these height settings also apply. Width is controlled by "Popup Width" above.', 'gpt3-ai-content-generator'); ?></em>
</p>