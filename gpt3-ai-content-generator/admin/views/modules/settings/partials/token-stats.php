<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/settings/partials/token-stats.php
// UPDATED FILE - Corrected method name call

/**
 * Partial: Token Usage Statistics
 *
 * Displays the stats overview cards and chart container for the AI Settings page.
 * UPDATED: Displays actual stats data fetched from AIPKit_Stats.
 * UPDATED: Changed chart placeholder to a chart container div.
 * REVISED: Displays stats for overall usage, not just chat. Shows most used module.
 * FIXED: Corrected the method name called to get the most used module.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use WPAICG\Stats\AIPKit_Stats; // Use renamed stats class

// Variables passed from parent settings/index.php:
// $stats_error_message (string|null)
// $stats_data (array|WP_Error) - contains ['days_period', 'total_tokens', 'total_interactions', 'module_counts']

$days_period = 30; // Default
$total_interactions = 0;

if (is_array($stats_data)) {
    $days_period = $stats_data['days_period'] ?? 30;
    $total_interactions = $stats_data['total_interactions'] ?? 0;

} elseif (is_wp_error($stats_data)) {
     // Error message handled below
     $stats_error_message = $stats_data->get_error_message();
}

?>
<div class="aipkit_settings_column aipkit_settings_column-right">
    <section class="aipkit_settings_card aipkit_settings_card--stats">
        <div class="aipkit_settings_card_header">
            <h3 class="aipkit_settings_card_title">
                <?php echo esc_html__('Usage Overview', 'gpt3-ai-content-generator'); ?>
            </h3>
        </div>
        <div class="aipkit_settings_card_body">
        <?php if ($stats_error_message): ?>
            <div class="aipkit_notice aipkit_notice-warning">
            <?php
                // translators: %s is the error message explaining why stats could not be calculated
                echo '<p>' . esc_html( sprintf( __('Error calculating stats: %s', 'gpt3-ai-content-generator'), $stats_error_message ) ) . '</p>';
                ?>
            </div>
        <?php else: ?>
            <?php if (empty($stats_notice_message)) : ?>
            <div class="aipkit_stats_period_row">
                <p class="aipkit_form-help aipkit_stats_period_text">
                    <?php esc_html_e('Total token usage statistics across all modules for the last', 'gpt3-ai-content-generator'); ?>
                    <span class="aipkit_stats_period_select">
                        <select id="aipkit_stats_period_select" class="aipkit_select">
                            <?php $period_options = [3, 7, 14, 30, 90]; $current = (int) ($days_period ?: 3); foreach ($period_options as $opt): ?>
                                <?php /* translators: %d: number of days */ ?>
                                <option value="<?php echo (int) $opt; ?>" <?php selected($current, $opt); ?>><?php echo esc_html( sprintf(_n('%d day', '%d days', $opt, 'gpt3-ai-content-generator'), $opt) ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </span>
                    .
                    <?php if ($total_interactions > 10000): ?>
                        <span class="aipkit_stats_period_note"><?php esc_html_e('(Stats calculation might be slow due to high volume)', 'gpt3-ai-content-generator'); ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
            <?php if (!empty($stats_notice_message)) : ?>
                <div class="aipkit_notice aipkit_notice-info">
                    <?php echo esc_html($stats_notice_message); ?>
                    <a href="<?php echo esc_url('https://docs.aipower.org/docs/logs#auto-delete-logs-pruning'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Read more', 'gpt3-ai-content-generator'); ?></a>
                </div>
            <?php else: ?>


            <!-- Chart Container -->
            <div id="aipkit_token_usage_chart_container" class="aipkit_token_usage_chart_container" data-default-days="<?php echo (int) ($days_period ?: 3); ?>">
                <!-- Chart will be rendered here by JS -->
                <div class="aipkit_chart_loading_placeholder">
                     <span class="aipkit_spinner" style="display:inline-block;"></span>
                     <?php esc_html_e('Loading chart data...', 'gpt3-ai-content-generator'); ?>
                 </div>
                 <div class="aipkit_chart_error_placeholder" style="display: none;"></div>
                 <div class="aipkit_chart_nodata_placeholder" style="display: none;"></div>
            </div>
            <?php endif; ?>
        <?php endif; // end error check ?>
        </div>
    </section>
</div>
