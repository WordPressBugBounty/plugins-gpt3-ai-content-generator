<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/image-generator/index.php
/**
 * AIPKit Image Generator Module - Admin View
 * REVISED: Moved controls into the container header and settings into a popover.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<?php
$aipkit_notice_id = 'aipkit_provider_notice_image_generator';
include WPAICG_PLUGIN_DIR . 'admin/views/shared/provider-key-notice.php';
?>

<div class="aipkit_container aipkit_module_image_generator" id="aipkit_image_generator_container">
    <div class="aipkit_container-header">
        <div class="aipkit_container-header-left">
            <div class="aipkit_container-title"><?php esc_html_e('Image Generator', 'gpt3-ai-content-generator'); ?></div>
            <span id="aipkit_image_generator_status" class="aipkit_training_status aipkit_global_status_area" aria-live="polite"></span>
        </div>
        <div class="aipkit_container-actions">
            <div class="aipkit_tabs_module_controls">
                <div class="aipkit_image_generator_top_bar">
                    <div class="aipkit_top_bar_left_group">
                        <div class="aipkit_shortcode_display_top_wrapper">
                            <code id="aipkit_image_generator_shortcode_snippet" title="<?php esc_attr_e('Click to copy shortcode', 'gpt3-ai-content-generator'); ?>">
                                [aipkit_image_generator]
                            </code>
                            <button type="button" id="aipkit_image_generator_shortcode_settings_toggle" class="aipkit_icon_btn" title="<?php esc_attr_e('Configure Shortcode Options', 'gpt3-ai-content-generator'); ?>" aria-expanded="false" aria-controls="aipkit_image_generator_shortcode_configurator">
                                <span class="dashicons dashicons-admin-settings"></span>
                            </button>
                        </div>
                    </div>
                    <button type="button" id="aipkit_theme_switcher_toggle_btn" class="aipkit_icon_btn" title="<?php esc_attr_e('Switch Preview Theme', 'gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-lightbulb"></span>
                    </button>
                </div>

                <div
                    class="aipkit_shortcode_configurator aipkit_model_settings_popover_panel"
                    id="aipkit_image_generator_shortcode_configurator"
                    style="display: none;"
                    role="dialog"
                    aria-modal="false"
                    aria-labelledby="aipkit_image_generator_shortcode_title"
                >
                    <div class="aipkit_model_settings_popover_header">
                        <span class="aipkit_model_settings_popover_title" id="aipkit_image_generator_shortcode_title">
                            <?php esc_html_e('Shortcode Settings', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <button
                            type="button"
                            class="aipkit_model_settings_popover_close"
                            aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                        >
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                    <div class="aipkit_model_settings_popover_body">
                        <div class="aipkit_popover_options_list">
                            <div class="aipkit_popover_option_row">
                                <div class="aipkit_popover_option_main">
                                    <span class="aipkit_popover_option_label"><?php esc_html_e('Show Provider Select', 'gpt3-ai-content-generator'); ?></span>
                                    <label class="aipkit_switch">
                                        <input type="checkbox" name="cfg_show_provider" class="aipkit_toggle_switch aipkit_config_input" value="1" checked>
                                        <span class="aipkit_switch_slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="aipkit_popover_option_row">
                                <div class="aipkit_popover_option_main">
                                    <span class="aipkit_popover_option_label"><?php esc_html_e('Show Model Select', 'gpt3-ai-content-generator'); ?></span>
                                    <label class="aipkit_switch">
                                        <input type="checkbox" name="cfg_show_model" class="aipkit_toggle_switch aipkit_config_input" value="1" checked>
                                        <span class="aipkit_switch_slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="aipkit_popover_option_row">
                                <div class="aipkit_popover_option_main">
                                    <span class="aipkit_popover_option_label"><?php esc_html_e('Show User History (if logged in)', 'gpt3-ai-content-generator'); ?></span>
                                    <label class="aipkit_switch">
                                        <input type="checkbox" name="cfg_show_history" class="aipkit_toggle_switch aipkit_config_input" value="1">
                                        <span class="aipkit_switch_slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="aipkit_popover_option_row">
                                <div class="aipkit_popover_option_main">
                                    <label class="aipkit_popover_option_label" for="aipkit_image_generator_shortcode_theme"><?php esc_html_e('Theme', 'gpt3-ai-content-generator'); ?></label>
                                    <div class="aipkit_popover_option_actions">
                                        <select id="aipkit_image_generator_shortcode_theme" name="cfg_theme" class="aipkit_popover_option_select aipkit_config_input">
                                            <option value="light"><?php esc_html_e('Light', 'gpt3-ai-content-generator'); ?></option>
                                            <option value="dark" selected><?php esc_html_e('Dark', 'gpt3-ai-content-generator'); ?></option>
                                            <option value="custom"><?php esc_html_e('Custom CSS', 'gpt3-ai-content-generator'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="aipkit_popover_flyout_footer">
                        <span class="aipkit_popover_flyout_footer_text">
                            <?php esc_html_e('Need help?', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <a
                            class="aipkit_popover_flyout_footer_link"
                            href="https://docs.aipower.org/docs/image-generator"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <button
                type="button"
                class="aipkit_btn aipkit_btn-secondary"
                id="aipkit_image_generator_settings_trigger"
                data-aipkit-popover-target="aipkit_image_generator_settings_popover"
                data-aipkit-popover-placement="bottom"
                aria-controls="aipkit_image_generator_settings_popover"
                aria-expanded="false"
            >
                <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                <?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>

    <div class="aipkit_container-body">
        <div class="aipkit_image_generator_admin_preview_wrapper">
            <?php
            echo do_shortcode('[aipkit_image_generator history="true"]');
            ?>
        </div>
    </div>

    <div
        class="aipkit_model_settings_popover aipkit_image_generator_settings_popover"
        id="aipkit_image_generator_settings_popover"
        aria-hidden="true"
    >
        <div class="aipkit_model_settings_popover_panel" role="dialog" aria-modal="false" aria-labelledby="aipkit_image_generator_settings_title">
            <div class="aipkit_model_settings_popover_header">
                <div class="aipkit_model_settings_popover_header_start">
                    <button
                        type="button"
                        class="aipkit_model_settings_popover_back"
                        aria-label="<?php esc_attr_e('Back', 'gpt3-ai-content-generator'); ?>"
                        hidden
                    >
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <span class="aipkit_model_settings_popover_title" id="aipkit_image_generator_settings_title">
                        <?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <div class="aipkit_model_settings_popover_header_end">
                    <button
                        type="button"
                        class="aipkit_model_settings_popover_close"
                        aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                    >
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            </div>
            <div class="aipkit_model_settings_popover_body">
                <?php include __DIR__ . '/partials/settings-image-generator.php'; ?>
            </div>
        </div>
    </div>
</div><!-- /.aipkit_container -->
