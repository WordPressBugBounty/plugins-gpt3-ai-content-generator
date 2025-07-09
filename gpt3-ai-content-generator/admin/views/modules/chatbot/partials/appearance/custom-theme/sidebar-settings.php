<?php
// File: admin/views/modules/chatbot/partials/appearance/custom-theme/sidebar-settings.php
// NEW FILE

/**
 * Partial: Custom Theme - Sidebar Color Settings
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent script (custom-theme-settings.php):
// $bot_id, $esc_cts_val_attr
?>
<h5 style="margin-top:15px;"><?php esc_html_e('Sidebar (if enabled)', 'gpt3-ai-content-generator'); ?></h5>
<div class="aipkit_form-row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_sidebar_bg_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Background', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_sidebar_bg_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[sidebar_bg_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo esc_attr($esc_cts_val_attr('sidebar_bg_color')); ?>">
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_sidebar_text_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Text Color', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_sidebar_text_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[sidebar_text_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo esc_attr($esc_cts_val_attr('sidebar_text_color')); ?>">
    </div>
     <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_sidebar_border_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Border Color', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_sidebar_border_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[sidebar_border_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo esc_attr($esc_cts_val_attr('sidebar_border_color')); ?>">
    </div>
</div>
 <div class="aipkit_form-row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_sidebar_active_bg_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Active Item Background', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_sidebar_active_bg_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[sidebar_active_bg_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo esc_attr($esc_cts_val_attr('sidebar_active_bg_color')); ?>">
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_sidebar_active_text_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Active Item Text Color', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_sidebar_active_text_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[sidebar_active_text_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo esc_attr($esc_cts_val_attr('sidebar_active_text_color')); ?>">
    </div>
</div>