<?php
/**
 * Shared Partial: Provider API Key Notice
 *
 * Expected variables:
 * - $aipkit_notice_id or $notice_id (string) Unique HTML id for the notice element.
 * - $aipkit_notice_class or $notice_class (string, optional) Additional CSS classes.
 */

if (!defined('ABSPATH')) {
    exit;
}

$notice_id = isset($notice_id) ? (string) $notice_id : '';
$notice_class = isset($notice_class) ? (string) $notice_class : '';
$aipkit_notice_id = isset($aipkit_notice_id) ? (string) $aipkit_notice_id : $notice_id;
$aipkit_notice_class = isset($aipkit_notice_class) ? (string) $aipkit_notice_class : $notice_class;

if ($aipkit_notice_id === '') {
    return;
}

$aipkit_settings_url = admin_url('admin.php?page=wpaicg');
?>
<div
    id="<?php echo esc_attr($aipkit_notice_id); ?>"
    class="aipkit_notification_bar aipkit_notification_bar--warning aipkit_provider_key_notice aipkit_provider_notice--hidden <?php echo esc_attr($aipkit_notice_class); ?>"
    data-aipkit-provider-notice="1"
    data-message-openai="<?php echo esc_attr__('OpenAI API key is missing. Add it in AI Settings.', 'gpt3-ai-content-generator'); ?>"
    data-message-openrouter="<?php echo esc_attr__('OpenRouter API key is missing. Add it in AI Settings.', 'gpt3-ai-content-generator'); ?>"
    data-message-google="<?php echo esc_attr__('Google API key is missing. Add it in AI Settings.', 'gpt3-ai-content-generator'); ?>"
    data-message-azure="<?php echo esc_attr__('Azure requires an API key and endpoint. Add them in AI Settings.', 'gpt3-ai-content-generator'); ?>"
    data-message-deepseek="<?php echo esc_attr__('DeepSeek API key is missing. Add it in AI Settings.', 'gpt3-ai-content-generator'); ?>"
    data-message-ollama="<?php echo esc_attr__('Ollama base URL is not set. Add it in AI Settings.', 'gpt3-ai-content-generator'); ?>"
    data-message-replicate="<?php echo esc_attr__('Replicate API key is missing. Add it in AI Settings.', 'gpt3-ai-content-generator'); ?>"
    data-message-default="<?php echo esc_attr__('API key is missing for the selected provider. Add it in AI Settings.', 'gpt3-ai-content-generator'); ?>"
>
    <div class="aipkit_notification_bar__icon" aria-hidden="true">
        <span class="dashicons dashicons-warning"></span>
    </div>
    <div class="aipkit_notification_bar__content">
        <p>
            <strong><?php esc_html_e('API setup required', 'gpt3-ai-content-generator'); ?></strong>
            <span class="aipkit_provider_notice_message">
                <?php esc_html_e('Select a provider to see the requirement.', 'gpt3-ai-content-generator'); ?>
            </span>
        </p>
    </div>
    <div class="aipkit_notification_bar__actions">
        <a
            href="<?php echo esc_url($aipkit_settings_url); ?>"
            class="aipkit_btn aipkit_btn-secondary aipkit_provider_notice_settings_link"
            data-aipkit-load-module="settings"
        >
            <?php esc_html_e('Open Settings', 'gpt3-ai-content-generator'); ?>
        </a>
    </div>
</div>
