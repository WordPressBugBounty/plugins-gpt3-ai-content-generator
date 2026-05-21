<?php
/**
 * Partial: WordPress AI Client connector management.
 */
if (!defined('ABSPATH')) {
    exit;
}

$aipkit_wpai_settings_class = '\\WPAICG\\WP_AI_Client\\AIPKit_WP_AI_Client_Settings';

if (!class_exists($aipkit_wpai_settings_class) || !$aipkit_wpai_settings_class::is_supported()) {
    return;
}

$aipkit_wpai_managed = $aipkit_wpai_settings_class::is_effectively_managed();
$aipkit_wpai_toggle_mode = $aipkit_wpai_managed ? 'observe' : 'managed';
$aipkit_wpai_toggle_label = $aipkit_wpai_managed
    ? __('Stop managing', 'gpt3-ai-content-generator')
    : __('Enable', 'gpt3-ai-content-generator');
$aipkit_wpai_toggle_class = $aipkit_wpai_managed ? 'aipkit_btn-danger' : 'aipkit_btn-primary';
$aipkit_wpai_read_more_url = apply_filters('aipkit_wp_ai_client_learn_more_url', 'https://docs.aipower.org/wordpress-ai-connectors');
?>
<div
    class="aipkit_form-group aipkit_settings_simple_row"
    id="aipkit_settings_wp_ai_client_row"
    data-aipkit-wpai-control
    data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
    data-nonce="<?php echo esc_attr(wp_create_nonce('aipkit_wp_ai_client_set_mode')); ?>"
    data-mode="<?php echo esc_attr($aipkit_wpai_managed ? 'managed' : 'observe'); ?>"
>
    <label class="aipkit_form-label" for="aipkit_settings_wp_ai_client_toggle">
        <?php esc_html_e('WordPress AI Connectors', 'gpt3-ai-content-generator'); ?>
        <span class="aipkit_form-label-helper"><?php esc_html_e('Let AI Puffer manage WP AI Client.', 'gpt3-ai-content-generator'); ?></span>
    </label>
    <div class="aipkit_input-with-button aipkit_settings_wpai_control_group">
        <div class="aipkit_settings_action_buttons aipkit_settings_wpai_actions">
            <button
                type="button"
                id="aipkit_settings_wp_ai_client_toggle"
                class="aipkit_btn <?php echo esc_attr($aipkit_wpai_toggle_class); ?>"
                data-aipkit-wpai-toggle
                data-aipkit-wpai-mode="<?php echo esc_attr($aipkit_wpai_toggle_mode); ?>"
            >
                <?php echo esc_html($aipkit_wpai_toggle_label); ?>
            </button>
            <a
                class="aipkit_btn aipkit_btn-secondary"
                href="<?php echo esc_url(admin_url('options-connectors.php')); ?>"
                data-aipkit-wpai-connectors
            >
                <?php esc_html_e('Open Connectors', 'gpt3-ai-content-generator'); ?>
            </a>
        </div>
        <a
            href="<?php echo esc_url($aipkit_wpai_read_more_url); ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="aipkit_get_key_btn aipkit_settings_get_key_link"
        >
            <?php esc_html_e('Read more', 'gpt3-ai-content-generator'); ?>
        </a>
        <span class="aipkit_input-button-spacer"></span>
    </div>
</div>
