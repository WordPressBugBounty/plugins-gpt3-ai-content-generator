<?php
// File: admin/views/modules/chatbot/partials/appearance/custom-theme/reset-theme-button.php
// NEW FILE

/**
 * Partial: Custom Theme - Reset to Default Button
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent script (custom-theme-settings.php):
// $bot_id
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
    <p class="aipkit_form-help" style="margin-bottom: 0;">
        <?php esc_html_e('Customize the appearance of your chatbot.', 'gpt3-ai-content-generator'); ?>
    </p>
    <div style="display:flex; align-items:center;">
        <button
            type="button"
            id="aipkit_reset_custom_theme_btn_<?php echo esc_attr($bot_id); ?>"
            class="aipkit_btn aipkit_btn-small aipkit_btn-secondary aipkit_reset_custom_theme_btn"
            data-bot-id="<?php echo esc_attr($bot_id); ?>"
            title="<?php esc_attr_e('Reset all custom theme settings to their defaults.', 'gpt3-ai-content-generator'); ?>"
        >
            <span class="dashicons dashicons-image-rotate" style="margin-right: 3px;"></span>
            <?php esc_html_e('Reset', 'gpt3-ai-content-generator'); ?>
        </button>
        <span id="aipkit_reset_theme_status_<?php echo esc_attr($bot_id); ?>" class="aipkit_form-help" style="margin-left: 8px; min-width: 120px;"></span>
    </div>
</div>