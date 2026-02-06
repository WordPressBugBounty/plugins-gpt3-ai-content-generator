<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/stats/index.php
// Status: NEW

if (!defined('ABSPATH')) {
    exit;
}

$stats_default_days = 30;
$log_settings = get_option('aipkit_log_settings', [
    'enable_pruning' => false,
    'retention_period_days' => 90,
]);
$log_settings_enabled = !empty($log_settings['enable_pruning']);
$log_settings_retention = isset($log_settings['retention_period_days'])
    ? (int) $log_settings['retention_period_days']
    : 90;
$user_credits_nonce = wp_create_nonce('aipkit_user_credits_nonce');
$retention_options = class_exists('\\WPAICG\\Chat\\Utils\\LogConfig')
    ? \WPAICG\Chat\Utils\LogConfig::get_retention_periods()
    : [
        7 => __('7 Days', 'gpt3-ai-content-generator'),
        15 => __('15 Days', 'gpt3-ai-content-generator'),
        30 => __('30 Days', 'gpt3-ai-content-generator'),
        60 => __('60 Days', 'gpt3-ai-content-generator'),
        90 => __('90 Days', 'gpt3-ai-content-generator'),
    ];
$is_pro = class_exists('\\WPAICG\\aipkit_dashboard')
    ? \WPAICG\aipkit_dashboard::is_pro_plan()
    : false;
$upgrade_url = admin_url('admin.php?page=wpaicg-pricing');
$cron_hook = class_exists('\\WPAICG\\Chat\\Storage\\LogCronManager')
    ? \WPAICG\Chat\Storage\LogCronManager::HOOK_NAME
    : 'aipkit_prune_logs_cron';
$next_scheduled = wp_next_scheduled($cron_hook);
$is_cron_active = $next_scheduled !== false;
$last_run_option = get_option('aipkit_log_pruning_last_run', '');
$last_run_label = $last_run_option
    ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_run_option))
    : __('Never', 'gpt3-ai-content-generator');
$cron_state = 'disabled';
$cron_text = __('Disabled', 'gpt3-ai-content-generator');
if ($log_settings_enabled) {
    if ($is_cron_active) {
        $cron_state = 'scheduled';
        $cron_text = __('Scheduled', 'gpt3-ai-content-generator');
    } else {
        $cron_state = 'not-scheduled';
        $cron_text = __('Not Scheduled', 'gpt3-ai-content-generator');
    }
}

$chatbot_posts = [];
if (class_exists('\\WPAICG\\Chat\\Admin\\AdminSetup')) {
    $chatbot_posts = get_posts([
        'post_type' => \WPAICG\Chat\Admin\AdminSetup::POST_TYPE,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
}

$module_labels = [
    'chatbot' => __('Chatbot', 'gpt3-ai-content-generator'),
    'content_writer' => __('Content Writer', 'gpt3-ai-content-generator'),
    'image_generator' => __('Image Generator', 'gpt3-ai-content-generator'),
    'ai_forms' => __('AI Forms', 'gpt3-ai-content-generator'),
    'autogpt' => __('Automate', 'gpt3-ai-content-generator'),
    'ai_post_enhancer' => __('Content Assistant', 'gpt3-ai-content-generator'),
    'unknown' => __('Unknown', 'gpt3-ai-content-generator'),
];

$module_options = [];
if (class_exists('\\WPAICG\\Stats\\AIPKit_Stats')) {
    $stats_calculator = new \WPAICG\Stats\AIPKit_Stats();
    $quick_stats = $stats_calculator->get_quick_interaction_stats($stats_default_days);
    if (!is_wp_error($quick_stats) && !empty($quick_stats['module_counts'])) {
        $module_options = array_keys($quick_stats['module_counts']);
    }
}
if (!in_array('chatbot', $module_options, true)) {
    $module_options[] = 'chatbot';
}
$module_options = array_values(array_unique(array_filter($module_options)));
sort($module_options);
?>

<div
    class="aipkit_container aipkit_stats_container"
    id="aipkit_stats_container"
    data-default-days="<?php echo esc_attr($stats_default_days); ?>"
    data-is-pro="<?php echo esc_attr($is_pro ? '1' : '0'); ?>"
    data-module-labels="<?php echo esc_attr(wp_json_encode($module_labels)); ?>"
    data-user-credits-nonce="<?php echo esc_attr($user_credits_nonce); ?>"
>
    <div class="aipkit_container-header">
        <div class="aipkit_container-header-left">
            <div class="aipkit_container-title"><?php esc_html_e('Usage', 'gpt3-ai-content-generator'); ?></div>
            <span id="aipkit_stats_status" class="aipkit_training_status aipkit_global_status_area" aria-live="polite"></span>
        </div>
        <div class="aipkit_container-actions aipkit_stats_header_actions">
            <button
                type="button"
                class="aipkit_btn aipkit_btn-secondary"
                id="aipkit_stats_overview_toggle"
                aria-expanded="false"
                aria-controls="aipkit_stats_overview_panel"
            >
                <span class="dashicons dashicons-chart-line" aria-hidden="true"></span>
                <?php esc_html_e('Overview', 'gpt3-ai-content-generator'); ?>
            </button>
            <button
                type="button"
                class="aipkit_btn aipkit_btn-secondary"
                id="aipkit_stats_settings_trigger"
                data-aipkit-popover-target="aipkit_stats_settings_popover"
                data-aipkit-popover-placement="bottom"
                aria-expanded="false"
                aria-controls="aipkit_stats_settings_popover"
            >
                <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
                <?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>
    <div class="aipkit_container-body">
        <div class="aipkit_stats_overview_panel" id="aipkit_stats_overview_panel" hidden>
            <div class="aipkit_stats_overview_grid" id="aipkit_stats_overview_grid">
                <div class="aipkit_stats_overview_card aipkit_stats_card--conversations">
                    <div class="aipkit_stats_card_icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <div class="aipkit_stats_card_content">
                        <span class="aipkit_stats_overview_label"><?php esc_html_e('Conversations', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_stats_overview_value" id="aipkit_stats_total_conversations">-</span>
                    </div>
                </div>
                <div class="aipkit_stats_overview_card aipkit_stats_card--messages">
                    <div class="aipkit_stats_card_icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </div>
                    <div class="aipkit_stats_card_content">
                        <span class="aipkit_stats_overview_label"><?php esc_html_e('Messages', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_stats_overview_value" id="aipkit_stats_total_messages">-</span>
                    </div>
                </div>
                <div class="aipkit_stats_overview_card aipkit_stats_card--users">
                    <div class="aipkit_stats_card_icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="aipkit_stats_card_content">
                        <span class="aipkit_stats_overview_label"><?php esc_html_e('Unique Users', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_stats_overview_value" id="aipkit_stats_unique_users">-</span>
                    </div>
                </div>
                <div class="aipkit_stats_overview_card aipkit_stats_card--tokens">
                    <div class="aipkit_stats_card_icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="aipkit_stats_card_content">
                        <span class="aipkit_stats_overview_label"><?php esc_html_e('Avg Tokens / Conversation', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_stats_overview_value" id="aipkit_stats_avg_tokens">-</span>
                    </div>
                </div>
            </div>

            <div class="aipkit_stats_charts">
                <div class="aipkit_sub_container aipkit_stats_chart_card">
                    <div class="aipkit_sub_container_header">
                        <div class="aipkit_sub_container_title"><?php esc_html_e('Conversations Over Time', 'gpt3-ai-content-generator'); ?></div>
                    </div>
                    <div class="aipkit_stats_chart_body" id="aipkit_stats_conversation_chart"></div>
                </div>
                <div class="aipkit_sub_container aipkit_stats_chart_card">
                    <div class="aipkit_sub_container_header">
                        <div class="aipkit_sub_container_title"><?php esc_html_e('Token Usage by Module', 'gpt3-ai-content-generator'); ?></div>
                    </div>
                    <div class="aipkit_token_usage_chart_container" id="aipkit_stats_module_chart"></div>
                </div>
            </div>
        </div>

        <div class="aipkit_stats_filters" aria-label="<?php esc_attr_e('Log Filters', 'gpt3-ai-content-generator'); ?>">
            <label class="screen-reader-text" for="aipkit_stats_days_filter"><?php esc_html_e('Date range', 'gpt3-ai-content-generator'); ?></label>
            <select id="aipkit_stats_days_filter" class="aipkit_popover_select">
                <option value="7"><?php esc_html_e('Last 7 days', 'gpt3-ai-content-generator'); ?></option>
                <option value="30" selected><?php esc_html_e('Last 30 days', 'gpt3-ai-content-generator'); ?></option>
                <option value="90"><?php esc_html_e('Last 90 days', 'gpt3-ai-content-generator'); ?></option>
            </select>

            <label class="screen-reader-text" for="aipkit_stats_bot_filter"><?php esc_html_e('Chatbot', 'gpt3-ai-content-generator'); ?></label>
            <select id="aipkit_stats_bot_filter" class="aipkit_popover_select">
                <option value=""><?php esc_html_e('All bots', 'gpt3-ai-content-generator'); ?></option>
                <option value="0"><?php esc_html_e('No bot', 'gpt3-ai-content-generator'); ?></option>
                <?php foreach ($chatbot_posts as $chatbot_post): ?>
                    <option value="<?php echo esc_attr($chatbot_post->ID); ?>">
                        <?php echo esc_html($chatbot_post->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="screen-reader-text" for="aipkit_stats_module_filter"><?php esc_html_e('Module', 'gpt3-ai-content-generator'); ?></label>
            <select id="aipkit_stats_module_filter" class="aipkit_popover_select">
                <option value=""><?php esc_html_e('All modules', 'gpt3-ai-content-generator'); ?></option>
                <?php foreach ($module_options as $module_key): ?>
                    <option value="<?php echo esc_attr($module_key); ?>">
                        <?php echo esc_html($module_labels[$module_key] ?? ucfirst(str_replace('_', ' ', $module_key))); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="screen-reader-text" for="aipkit_stats_search_input"><?php esc_html_e('Search logs', 'gpt3-ai-content-generator'); ?></label>
            <input
                type="search"
                id="aipkit_stats_search_input"
                class="aipkit_stats_search_input"
                placeholder="<?php esc_attr_e('Search logs', 'gpt3-ai-content-generator'); ?>"
            />
        </div>

        <div class="aipkit_stats_layout">
            <div class="aipkit_stats_table_panel">
                <div class="aipkit_stats_table_menu_wrapper">
                    <button
                        type="button"
                        class="aipkit_stats_table_menu_trigger"
                        id="aipkit_stats_table_menu_trigger"
                        aria-expanded="false"
                        aria-controls="aipkit_stats_table_menu"
                    >
                        <span class="dashicons dashicons-ellipsis" aria-hidden="true"></span>
                        <span class="screen-reader-text"><?php esc_html_e('Log actions', 'gpt3-ai-content-generator'); ?></span>
                    </button>
                    <div class="aipkit_stats_table_menu" id="aipkit_stats_table_menu" role="menu" hidden>
                        <button type="button" class="aipkit_stats_table_menu_item" id="aipkit_stats_export_btn" role="menuitem">
                            <span class="dashicons dashicons-download" aria-hidden="true"></span>
                            <?php esc_html_e('Export all', 'gpt3-ai-content-generator'); ?>
                        </button>
                        <button type="button" class="aipkit_stats_table_menu_item aipkit_stats_table_menu_item--danger" id="aipkit_stats_delete_all_btn" role="menuitem">
                            <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                            <?php esc_html_e('Delete all', 'gpt3-ai-content-generator'); ?>
                        </button>
                    </div>
                </div>
                <div class="aipkit_data-table aipkit_stats_table">
                    <table class="aipkit_data-table__table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Date', 'gpt3-ai-content-generator'); ?></th>
                                <th><?php esc_html_e('User', 'gpt3-ai-content-generator'); ?></th>
                                <th><?php esc_html_e('Source', 'gpt3-ai-content-generator'); ?></th>
                                <th><?php esc_html_e('Messages', 'gpt3-ai-content-generator'); ?></th>
                                <th><?php esc_html_e('Tokens', 'gpt3-ai-content-generator'); ?></th>
                                <th><?php esc_html_e('Message', 'gpt3-ai-content-generator'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="aipkit_stats_table_body">
                            <tr>
                                <td colspan="6" class="aipkit_stats_table_placeholder">
                                    <?php esc_html_e('Loading logs...', 'gpt3-ai-content-generator'); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="aipkit_stats_pagination" class="aipkit_logs_pagination_container"></div>
            </div>

            <div class="aipkit_stats_detail_panel" id="aipkit_stats_detail_panel">
                <div class="aipkit_stats_detail_empty">
                    <?php esc_html_e('Select a conversation to view details.', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>
        </div>
    </div>
    <div
        class="aipkit_model_settings_popover aipkit_stats_settings_popover"
        id="aipkit_stats_settings_popover"
        aria-hidden="true"
        data-title-root="<?php esc_attr_e('Log Settings', 'gpt3-ai-content-generator'); ?>"
        data-title-log-retention="<?php esc_attr_e('Log retention', 'gpt3-ai-content-generator'); ?>"
    >
        <div class="aipkit_model_settings_popover_panel" role="dialog" aria-modal="false" aria-labelledby="aipkit_stats_settings_title">
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
                    <span class="aipkit_model_settings_popover_title" id="aipkit_stats_settings_title">
                        <?php esc_html_e('Log Settings', 'gpt3-ai-content-generator'); ?>
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
                <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="root">
                    <div class="aipkit_popover_options_list aipkit_popover_options_list--settings-root">
                        <div class="aipkit_popover_option_group">
                            <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                <button
                                    type="button"
                                    class="aipkit_popover_option_nav aipkit_stats_settings_nav"
                                    data-aipkit-panel-target="log-retention"
                                >
                                    <span class="aipkit_popover_option_label">
                                        <span class="aipkit_popover_option_icon dashicons dashicons-clock" aria-hidden="true"></span>
                                        <span class="aipkit_popover_option_label_content">
                                            <span class="aipkit_popover_option_label_text">
                                                <?php esc_html_e('Log retention', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <span class="aipkit_popover_option_hint">
                                                <?php esc_html_e('Auto-delete and retention', 'gpt3-ai-content-generator'); ?>
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
                                    class="aipkit_popover_option_nav aipkit_stats_settings_nav"
                                    data-aipkit-settings-action="users"
                                >
                                    <span class="aipkit_popover_option_label">
                                        <span class="aipkit_popover_option_icon dashicons dashicons-admin-users" aria-hidden="true"></span>
                                        <span class="aipkit_popover_option_label_content">
                                            <span class="aipkit_popover_option_label_text">
                                                <?php esc_html_e('Users', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <span class="aipkit_popover_option_hint">
                                                <?php esc_html_e('Manage credits and balances', 'gpt3-ai-content-generator'); ?>
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
                            <?php esc_html_e('Need help?', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <a
                            class="aipkit_popover_flyout_footer_link"
                            href="<?php echo esc_url('https://docs.aipower.org/docs/logs'); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                        </a>
                    </div>
                </div>

                <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="log-retention" hidden>
                    <div class="aipkit_popover_options_list">
                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <span class="aipkit_popover_option_label"><?php esc_html_e('Auto-Delete Logs', 'gpt3-ai-content-generator'); ?></span>
                                <label class="aipkit_switch">
                                    <input
                                        type="checkbox"
                                        id="aipkit_stats_auto_delete_toggle"
                                        <?php checked($log_settings_enabled); ?>
                                        <?php disabled(!$is_pro); ?>
                                    />
                                    <span class="aipkit_switch_slider"></span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="aipkit_popover_option_row aipkit_stats_retention_row"
                            data-aipkit-stats-retention-row
                            <?php echo $log_settings_enabled ? '' : 'hidden'; ?>
                        >
                            <div class="aipkit_popover_option_main">
                                <label class="aipkit_popover_option_label" for="aipkit_stats_retention_days">
                                    <?php esc_html_e('Delete logs older than', 'gpt3-ai-content-generator'); ?>
                                </label>
                                <div class="aipkit_popover_option_actions">
                                    <select
                                        id="aipkit_stats_retention_days"
                                        class="aipkit_popover_option_select aipkit_stats_retention_select"
                                        <?php disabled(!$is_pro || !$log_settings_enabled); ?>
                                    >
                                        <?php foreach ($retention_options as $retention_value => $retention_label): ?>
                                            <option value="<?php echo esc_attr($retention_value); ?>" <?php selected($log_settings_retention, $retention_value); ?>>
                                                <?php echo esc_html($retention_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div
                            class="aipkit_popover_option_row aipkit_stats_cron_row"
                            data-aipkit-stats-cron-row
                            <?php echo $log_settings_enabled ? '' : 'hidden'; ?>
                        >
                            <div class="aipkit_popover_option_main">
                                <span class="aipkit_popover_option_label"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></span>
                                <div
                                    class="aipkit_stats_cron_status"
                                    id="aipkit_stats_cron_status"
                                    data-state="<?php echo esc_attr($cron_state); ?>"
                                >
                                    <span class="aipkit_stats_cron_dot" aria-hidden="true"></span>
                                    <span class="aipkit_stats_cron_text" id="aipkit_stats_cron_text"><?php echo esc_html($cron_text); ?></span>
                                    <span class="aipkit_stats_cron_separator" aria-hidden="true">•</span>
                                    <span class="aipkit_stats_cron_last_run" id="aipkit_stats_cron_last_run">
                                        <?php
                                        printf(
                                            /* translators: %s: last run time */
                                            esc_html__('Last run: %s', 'gpt3-ai-content-generator'),
                                            esc_html($last_run_label)
                                        );
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php if (!$is_pro): ?>
                            <div class="aipkit_popover_option_row aipkit_stats_upgrade_row">
                                <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                                    <p class="aipkit_form-help"><?php esc_html_e('Auto-delete logs is available on the Pro plan.', 'gpt3-ai-content-generator'); ?></p>
                                    <a class="aipkit_btn aipkit_btn-primary" href="<?php echo esc_url($upgrade_url); ?>">
                                        <?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_builder_sheet_overlay aipkit_stats_sheet_overlay aipkit_stats_user_sheet" id="aipkit_stats_user_sheet" aria-hidden="true">
        <div
            class="aipkit_builder_sheet_panel"
            id="aipkit_stats_user_sheet_panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="aipkit_stats_user_sheet_title"
            aria-describedby="aipkit_stats_user_sheet_description"
        >
            <div class="aipkit_builder_sheet_header">
                <div>
                    <div class="aipkit_builder_sheet_title_row">
                        <h3 class="aipkit_builder_sheet_title" id="aipkit_stats_user_sheet_title">
                            <?php esc_html_e('User Credits', 'gpt3-ai-content-generator'); ?>
                        </h3>
                    </div>
                    <p class="aipkit_builder_sheet_description" id="aipkit_stats_user_sheet_description">
                        <?php esc_html_e('Review balances and usage details for each user.', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>
                <button type="button" class="aipkit_builder_sheet_close" id="aipkit_stats_user_sheet_close" aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit_builder_sheet_body">
                <div class="aipkit_builder_sheet_section aipkit_stats_user_sheet_section">
                    <div class="aipkit_stats_user_toolbar">
                        <div class="aipkit_stats_user_search_row">
                            <label class="screen-reader-text" for="aipkit_stats_user_search"><?php esc_html_e('Search users', 'gpt3-ai-content-generator'); ?></label>
                            <input
                            type="search"
                            id="aipkit_stats_user_search"
                            class="aipkit_form-input aipkit_stats_user_search_input"
                            placeholder="<?php esc_attr_e('Search by username or email', 'gpt3-ai-content-generator'); ?>"
                        />
                        </div>
                        <div class="aipkit_stats_user_shortcode">
                            <div class="aipkit_stats_shortcode_controls">
                                <code id="aipkit_stats_shortcode_snippet" class="aipkit_stats_shortcode_snippet" title="<?php esc_attr_e('Click to copy shortcode', 'gpt3-ai-content-generator'); ?>">[aipkit_token_usage]</code>
                                <button
                                    type="button"
                                    class="aipkit_btn aipkit_btn-secondary aipkit_btn-small"
                                    id="aipkit_stats_shortcode_toggle"
                                    aria-expanded="false"
                                    aria-controls="aipkit_stats_shortcode_config"
                                >
                                    <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                                    <span class="screen-reader-text"><?php esc_html_e('Options', 'gpt3-ai-content-generator'); ?></span>
                                </button>
                            </div>
                            <div class="aipkit_stats_shortcode_config" id="aipkit_stats_shortcode_config" hidden>
                                <label class="aipkit_checkbox-label">
                                    <input type="checkbox" name="cfg_show_chatbot" class="aipkit_stats_shortcode_option" value="1" checked>
                                    <span><?php esc_html_e('Chatbot', 'gpt3-ai-content-generator'); ?></span>
                                </label>
                                <label class="aipkit_checkbox-label">
                                    <input type="checkbox" name="cfg_show_aiforms" class="aipkit_stats_shortcode_option" value="1" checked>
                                    <span><?php esc_html_e('AI Forms', 'gpt3-ai-content-generator'); ?></span>
                                </label>
                                <label class="aipkit_checkbox-label">
                                    <input type="checkbox" name="cfg_show_imagegenerator" class="aipkit_stats_shortcode_option" value="1" checked>
                                    <span><?php esc_html_e('Image Generator', 'gpt3-ai-content-generator'); ?></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="aipkit_data-table aipkit_stats_user_table">
                        <table>
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('User', 'gpt3-ai-content-generator'); ?></th>
                                    <th><?php esc_html_e('Token Balance', 'gpt3-ai-content-generator'); ?></th>
                                    <th><?php esc_html_e('Periodic Tokens Used', 'gpt3-ai-content-generator'); ?></th>
                                    <th><?php esc_html_e('Usage Details', 'gpt3-ai-content-generator'); ?></th>
                                    <th><?php esc_html_e('Last Reset', 'gpt3-ai-content-generator'); ?></th>
                                    <th><?php esc_html_e('Actions', 'gpt3-ai-content-generator'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="aipkit_stats_user_table_body">
                                <tr>
                                    <td colspan="6" class="aipkit_stats_table_placeholder">
                                        <?php esc_html_e('Loading user credits...', 'gpt3-ai-content-generator'); ?>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot hidden>
                                <tr>
                                    <th colspan="6">
                                        <div class="aipkit_pagination" id="aipkit_stats_user_pagination"></div>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="aipkit_stats_user_no_results" class="aipkit_stats_user_empty" hidden>
                        <?php esc_html_e('No user token data found.', 'gpt3-ai-content-generator'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
