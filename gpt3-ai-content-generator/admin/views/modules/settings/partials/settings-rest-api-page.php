<?php
/**
 * Partial: REST API Settings Page
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="aipkit_form-group aipkit_settings_simple_row" id="aipkit_settings_rest_api_panel">
    <label class="aipkit_form-label" for="aipkit_public_api_key">
        <?php esc_html_e('REST API Key', 'gpt3-ai-content-generator'); ?>
        <span class="aipkit_form-label-helper"><?php esc_html_e('Use this for external API requests.', 'gpt3-ai-content-generator'); ?></span>
    </label>
    <div class="aipkit_input-with-button">
        <div class="aipkit_api-key-wrapper">
            <input
                type="password"
                id="aipkit_public_api_key"
                name="public_api_key"
                class="aipkit_form-input aipkit_autosave_trigger"
                value="<?php echo esc_attr($public_api_key); ?>"
                placeholder="<?php esc_attr_e('Leave blank to disable REST API access', 'gpt3-ai-content-generator'); ?>"
            />
            <span class="aipkit_api-key-toggle"><span class="dashicons dashicons-visibility"></span></span>
        </div>
        <a href="https://docs.aipower.org/docs/api-reference" target="_blank" rel="noopener noreferrer" class="aipkit_get_key_btn aipkit_settings_get_key_link">
            <?php esc_html_e('REST API docs', 'gpt3-ai-content-generator'); ?>
        </a>
        <span class="aipkit_input-button-spacer"></span>
    </div>
</div>
