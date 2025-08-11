<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/logs/partials/logs-settings.php
// Status: MODIFIED
/**
 * Partial: Logs Settings
 *
 * Provides UI for configuring log pruning.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure required classes are loaded
$log_status_renderer_path = WPAICG_PLUGIN_DIR . 'classes/chat/utils/class-log-status-renderer.php';
if (file_exists($log_status_renderer_path)) {
    require_once $log_status_renderer_path;
}

$log_config_path = WPAICG_PLUGIN_DIR . 'classes/chat/utils/class-log-config.php';
if (file_exists($log_config_path)) {
    require_once $log_config_path;
}

use WPAICG\aipkit_dashboard;
use WPAICG\Chat\Utils\LogStatusRenderer; // For cron status display
use WPAICG\Chat\Utils\LogConfig; // For centralized configuration

// Get saved settings using centralized config
$log_settings = LogConfig::get_log_settings();
$enable_pruning = $log_settings['enable_pruning'];
$retention_period_days = $log_settings['retention_period_days'];

$is_pro = aipkit_dashboard::is_pro_plan();

// Get period options from centralized config
$period_options = LogConfig::get_retention_periods();

?>
<div id="aipkit_log_settings_container">
    <form id="aipkit_log_settings_form" onsubmit="return false;">
        <?php wp_nonce_field('aipkit_save_log_settings_nonce'); ?>

        <!-- Unified Settings Panel -->
        <div class="aipkit_chip_settings_panel" style="display: block !important;" <?php echo !$is_pro ? 'data-disabled="true"' : ''; ?>>
            <h4><?php esc_html_e('Auto-Delete Logs', 'gpt3-ai-content-generator'); ?></h4>
            <p class="aipkit_form-help">
                <?php esc_html_e('Automatically delete old log entries to keep your database size manageable. This process runs once daily in the background.', 'gpt3-ai-content-generator'); ?>
            </p>

            <?php if (!$is_pro): ?>
            <div class="aipkit_pro_feature_notice">
                <p>
                    <?php esc_html_e('Auto-Delete Logs is a Pro feature.', 'gpt3-ai-content-generator'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpaicg-pricing')); ?>"><?php esc_html_e('Upgrade to Pro to enable.', 'gpt3-ai-content-generator'); ?></a>
                </p>
            </div>
            <?php endif; ?>

            <div class="aipkit_chip_settings">
                <!-- Single Combined Row: Enabled, Retention Period, and Cron Status -->
                <div class="aipkit_chip_row aipkit_chip_row_combined" style="display: flex !important;">
                    <!-- Enabled Setting Container - Always visible -->
                    <div class="aipkit_setting_container" style="display: flex !important;">
                        <span class="aipkit_chip_label"><?php esc_html_e('Enabled:', 'gpt3-ai-content-generator'); ?></span>
                        <button type="button" 
                                class="aipkit_value_chip <?php echo $enable_pruning ? 'chip-on' : 'chip-off'; ?>" 
                                data-setting="enable_pruning"
                                data-value="<?php echo $enable_pruning ? '1' : '0'; ?>"
                                <?php echo !$is_pro ? 'disabled' : ''; ?>>
                            <?php echo $enable_pruning ? esc_html__('Yes', 'gpt3-ai-content-generator') : esc_html__('No', 'gpt3-ai-content-generator'); ?>
                        </button>
                        
                        <!-- Popover for Auto-Delete Toggle -->
                        <div class="aipkit_chip_popover" data-setting="enable_pruning">
                            <button type="button" class="aipkit_popover_option" data-value="1">
                                <?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?>
                            </button>
                            <button type="button" class="aipkit_popover_option" data-value="0">
                                <?php esc_html_e('No', 'gpt3-ai-content-generator'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Retention Period Setting Container -->
                    <div class="aipkit_setting_container">
                        <span class="aipkit_chip_label"><?php esc_html_e('Delete logs older than:', 'gpt3-ai-content-generator'); ?></span>
                        <button type="button" 
                                class="aipkit_value_chip chip-neutral" 
                                data-setting="retention_period_days"
                                data-value="<?php echo esc_attr($retention_period_days); ?>"
                                <?php echo !$is_pro ? 'disabled' : ''; ?>>
                            <?php echo esc_html($period_options[$retention_period_days] ?? sprintf(__('%d Days', 'gpt3-ai-content-generator'), $retention_period_days)); ?>
                        </button>
                        
                        <!-- Popover for Retention Period -->
                        <div class="aipkit_chip_popover" data-setting="retention_period_days">
                            <?php foreach ($period_options as $value => $label): ?>
                            <button type="button" class="aipkit_popover_option" data-value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Cron Status Setting Container -->
                    <?php echo LogStatusRenderer::render_cron_status_panel(); ?>
                </div>
            </div>

            <!-- Log Prune Actions -->
            <div class="aipkit_log_prune_actions">
                <button type="button" id="aipkit_prune_logs_now_btn" class="aipkit_btn aipkit_btn-secondary" <?php disabled(!$is_pro); ?>>
                    <span class="aipkit_btn-text"><?php esc_html_e('Delete Logs Now', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_spinner" style="display:none;"></span>
                </button>
                <span class="aipkit_form-help">
                    <?php esc_html_e('Immediately delete logs based on current retention settings.', 'gpt3-ai-content-generator'); ?>
                </span>
            </div>
        </div>

        <div class="aipkit_log_buttons_container">
            <button type="submit" id="aipkit_save_log_settings_btn" class="aipkit_btn aipkit_btn-primary">
                <span class="aipkit_btn-text"><?php esc_html_e('Save', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
            <span id="aipkit_log_settings_status" class="aipkit_save_status_container"></span>
        </div>
    </form>
</div>