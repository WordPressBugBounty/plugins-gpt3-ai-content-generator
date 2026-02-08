<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/image-generator/partials/settings-image-generator.php
// Status: MODIFIED

/**
 * Partial: Image Generator Settings Content
 * Main settings body for the Image Generator module popover.
 * Uses a settings panel navigation to organize provider settings.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use WPAICG\Images\AIPKit_Image_Settings_Ajax_Handler; // Import the handler class
use WPAICG\AIPKit_Providers;
// Fetch current settings using the handler method
$settings_data = AIPKit_Image_Settings_Ajax_Handler::get_settings();
$replicate_data = AIPKit_Providers::get_provider_data('Replicate');
$current_replicate_api_key = $replicate_data['api_key'] ?? '';

// Prepare nonce for saving
$settings_nonce = wp_create_nonce('aipkit_image_generator_settings_nonce');

?>
<form id="aipkit_image_generator_settings_form" onsubmit="return false;">
    <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr($settings_nonce); ?>">

    <div
        id="aipkit_image_generator_settings_panel"
        data-title-root="<?php esc_attr_e('Settings', 'gpt3-ai-content-generator'); ?>"
        data-title-token-management="<?php esc_attr_e('Token Management', 'gpt3-ai-content-generator'); ?>"
        data-title-replicate="<?php esc_attr_e('Replicate', 'gpt3-ai-content-generator'); ?>"
        data-title-ui-text="<?php esc_attr_e('UI Text', 'gpt3-ai-content-generator'); ?>"
        data-title-custom-css="<?php esc_attr_e('Custom CSS', 'gpt3-ai-content-generator'); ?>"
        data-title-provider-filtering="<?php esc_attr_e('Provider Filtering', 'gpt3-ai-content-generator'); ?>"
    >
        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="root">
            <div class="aipkit_popover_options_list aipkit_popover_options_list--settings-root">
                <div class="aipkit_popover_option_group">
                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                        <button
                            type="button"
                            class="aipkit_popover_option_nav aipkit_image_generator_settings_nav"
                            data-aipkit-panel-target="token-management"
                        >
                            <span class="aipkit_popover_option_label">
                                <span class="aipkit_popover_option_icon dashicons dashicons-chart-bar" aria-hidden="true"></span>
                                <span class="aipkit_popover_option_label_content">
                                    <span class="aipkit_popover_option_label_text">
                                        <?php esc_html_e('Token management', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                    <span class="aipkit_popover_option_hint">
                                        <?php esc_html_e('Usage limits and resets', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                </span>
                            </span>
                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </span>
                        </button>
                    </div>
                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                        <button
                            type="button"
                            class="aipkit_popover_option_nav aipkit_image_generator_settings_nav"
                            data-aipkit-panel-target="replicate"
                        >
                            <span class="aipkit_popover_option_label">
                                <span class="aipkit_popover_option_icon dashicons dashicons-admin-tools" aria-hidden="true"></span>
                                <span class="aipkit_popover_option_label_content">
                                    <span class="aipkit_popover_option_label_text">
                                        <?php esc_html_e('Replicate', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                    <span class="aipkit_popover_option_hint">
                                        <?php esc_html_e('Safety defaults', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                </span>
                            </span>
                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="aipkit_popover_option_group">
                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                        <button
                            type="button"
                            class="aipkit_popover_option_nav aipkit_image_generator_settings_nav"
                            data-aipkit-panel-target="ui-text"
                        >
                            <span class="aipkit_popover_option_label">
                                <span class="aipkit_popover_option_icon dashicons dashicons-editor-textcolor" aria-hidden="true"></span>
                                <span class="aipkit_popover_option_label_content">
                                    <span class="aipkit_popover_option_label_text">
                                        <?php esc_html_e('UI text', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                    <span class="aipkit_popover_option_hint">
                                        <?php esc_html_e('Frontend labels and placeholders', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                </span>
                            </span>
                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </span>
                        </button>
                    </div>
                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                        <button
                            type="button"
                            class="aipkit_popover_option_nav aipkit_image_generator_settings_nav"
                            data-aipkit-panel-target="custom-css"
                        >
                            <span class="aipkit_popover_option_label">
                                <span class="aipkit_popover_option_icon dashicons dashicons-editor-code" aria-hidden="true"></span>
                                <span class="aipkit_popover_option_label_content">
                                    <span class="aipkit_popover_option_label_text">
                                        <?php esc_html_e('Custom CSS', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                    <span class="aipkit_popover_option_hint">
                                        <?php esc_html_e('Theme overrides', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                </span>
                            </span>
                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </span>
                        </button>
                    </div>
                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                        <button
                            type="button"
                            class="aipkit_popover_option_nav aipkit_image_generator_settings_nav"
                            data-aipkit-panel-target="provider-filtering"
                        >
                            <span class="aipkit_popover_option_label">
                                <span class="aipkit_popover_option_icon dashicons dashicons-filter" aria-hidden="true"></span>
                                <span class="aipkit_popover_option_label_content">
                                    <span class="aipkit_popover_option_label_text">
                                        <?php esc_html_e('Provider filtering', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                    <span class="aipkit_popover_option_hint">
                                        <?php esc_html_e('Allowed models', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                </span>
                            </span>
                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="aipkit_popover_flyout_footer">
                <span class="aipkit_popover_flyout_footer_text">
                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/image-generator'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                </a>
            </div>
        </div>

        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="token-management" hidden>
            <div class="aipkit_popover_options_list">
                <?php include __DIR__ . '/settings-token-management.php'; ?>
            </div>
            <div class="aipkit_popover_flyout_footer">
                <span class="aipkit_popover_flyout_footer_text">
                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/image-generator'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                </a>
            </div>
        </div>

        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="replicate" hidden>
            <div class="aipkit_popover_options_list">
                <?php include __DIR__ . '/settings-replicate.php'; ?>
            </div>
            <div class="aipkit_popover_flyout_footer">
                <span class="aipkit_popover_flyout_footer_text">
                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/image-generator#replicate-settings'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                </a>
            </div>
        </div>

        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="ui-text" hidden>
            <div class="aipkit_popover_options_list">
                <?php include __DIR__ . '/settings-ui-text.php'; ?>
            </div>
            <div class="aipkit_popover_flyout_footer">
                <span class="aipkit_popover_flyout_footer_text">
                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/image-generator'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                </a>
            </div>
        </div>

        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="custom-css" hidden>
            <div class="aipkit_popover_options_list">
                <?php include __DIR__ . '/settings-common.php'; ?>
            </div>
            <div class="aipkit_popover_flyout_footer">
                <span class="aipkit_popover_flyout_footer_text">
                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/image-generator'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                </a>
            </div>
        </div>

        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="provider-filtering" hidden>
            <div class="aipkit_popover_options_list">
                <?php include __DIR__ . '/settings-frontend-filtering.php'; ?>
            </div>
            <div class="aipkit_popover_flyout_footer">
                <span class="aipkit_popover_flyout_footer_text">
                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/image-generator'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                </a>
            </div>
        </div>
    </div>
</form>
