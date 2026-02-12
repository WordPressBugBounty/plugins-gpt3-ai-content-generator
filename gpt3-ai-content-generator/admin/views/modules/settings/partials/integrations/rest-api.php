<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/settings/partials/integrations/rest-api.php
// Status: NEW

/**
 * Partial: REST API Access
 * Displayed under Integrations.
 */
if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="aipkit_accordion">
    <div class="aipkit_accordion-header">
        <span class="dashicons dashicons-arrow-right-alt2"></span>
        <?php echo esc_html__('REST API', 'gpt3-ai-content-generator'); ?>
    </div>
    <div class="aipkit_accordion-content">
        <div class="aipkit_form-group">
            <label class="aipkit_form-label" for="aipkit_public_api_key">
                <?php esc_html_e('REST API Key', 'gpt3-ai-content-generator'); ?>
            </label>
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
        </div>
        <p class="aipkit_form-help">
            <a href="https://docs.aipower.org/docs/api-reference" target="_blank" rel="noopener noreferrer"><?php esc_html_e('REST API documentation', 'gpt3-ai-content-generator'); ?></a>
        </p>
    </div>
</div>
