<?php
// File: admin/views/modules/chatbot/partials/appearance/custom-theme/messages-area-settings.php
// NEW FILE

/**
 * Partial: Custom Theme - Messages Area Color Settings
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent script (custom-theme-settings.php):
// $bot_id, $esc_cts_val_attr
?>
<h5 style="margin-top:15px;"><?php esc_html_e('Messages Area', 'gpt3-ai-content-generator'); ?></h5>
<div class="aipkit_form-row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_messages_bg_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Background', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_messages_bg_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[messages_bg_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo $esc_cts_val_attr('messages_bg_color'); ?>">
    </div>
</div>