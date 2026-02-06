<?php
/**
 * Partial: AutoGPT Task Schedule Card
 *
 * Expected variables:
 * - $frequencies (array) Optional
 * - $aipkit_status_label (string) Optional
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_status_label = isset($aipkit_status_label)
    ? (string) $aipkit_status_label
    : __('Schedule', 'gpt3-ai-content-generator');

$cw_post_statuses = isset($cw_post_statuses) && is_array($cw_post_statuses)
    ? $cw_post_statuses
    : [];
?>
<div
    class="aipkit_cw_setting_chip aipkit_task_schedule_card"
    id="aipkit_autogpt_task_status_card"
    data-status="active"
    role="group"
    aria-label="<?php echo esc_attr($aipkit_status_label); ?>"
>
    <div class="aipkit_task_schedule_header">
        <span class="aipkit_cw_setting_chip_label"><?php echo esc_html($aipkit_status_label); ?></span>
        <span class="aipkit_task_status_toggle">
            <span class="aipkit_cw_setting_chip_value aipkit_task_status_value" id="aipkit_autogpt_task_status_label">Active</span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_autogpt_task_status_toggle"
                    class="aipkit_toggle_switch"
                    checked
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </span>
        <input type="hidden" id="aipkit_autogpt_task_status_input" name="task_status" value="active">
    </div>
    <div class="aipkit_task_schedule_publishing">
        <div class="aipkit_post_status_row" data-aipkit-task-publishing>
            <label class="aipkit_post_settings_label aipkit_cw_setting_chip_label" for="aipkit_task_cw_post_status">
                <?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?>
            </label>
            <select id="aipkit_task_cw_post_status" name="post_status" class="aipkit_post_settings_select">
                <?php foreach ($cw_post_statuses as $status_val => $status_label): ?>
                    <option value="<?php echo esc_attr($status_val); ?>" <?php selected($status_val, 'publish'); ?>><?php echo esc_html($status_label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php include __DIR__ . '/task-frequency.php'; ?>

        <div id="aipkit_task_cw_schedule_options_wrapper" class="aipkit_task_schedule_options" data-aipkit-task-publishing style="display: none;">
            <div class="aipkit_post_smart_schedule_options">
                <label class="aipkit_post_schedule_radio">
                    <input type="radio" name="schedule_mode" value="immediate" checked>
                    <span class="aipkit_post_schedule_radio_text"><?php esc_html_e('Publish Immediately', 'gpt3-ai-content-generator'); ?></span>
                </label>
                <label class="aipkit_post_schedule_radio">
                    <input type="radio" name="schedule_mode" value="smart">
                    <span class="aipkit_post_schedule_radio_text"><?php esc_html_e('Smart Schedule', 'gpt3-ai-content-generator'); ?></span>
                </label>
                <label class="aipkit_post_schedule_radio aipkit_schedule_from_input_option aipkit_task_schedule_from_input_option">
                    <input type="radio" name="schedule_mode" value="from_input">
                    <span class="aipkit_post_schedule_radio_text"><?php esc_html_e('Use Dates from Input', 'gpt3-ai-content-generator'); ?></span>
                    <span
                        class="aipkit_popover_warning is-visible"
                        data-tooltip="<?php echo esc_attr__("Use when your input includes a publish date (Bulk/CSV or Google Sheets).\n\nAccepted formats: YYYY-MM-DD HH:MM[:SS], YYYY/MM/DD HH:MM, MM/DD/YYYY HH:MM, DD/MM/YYYY HH:MM, or ISO 8601.\n\nTimes use the site timezone unless an offset/Z is provided.", 'gpt3-ai-content-generator'); ?>"
                        tabindex="0"
                        aria-label="<?php echo esc_attr__('Show date format help', 'gpt3-ai-content-generator'); ?>"
                    >
                        <span class="dashicons dashicons-info" aria-hidden="true"></span>
                    </span>
                </label>
            </div>

                <div id="aipkit_task_cw_smart_schedule_fields" class="aipkit_post_smart_schedule_fields" style="display: none;">
                    <div class="aipkit_post_smart_schedule_field">
                    <label class="aipkit_post_settings_label aipkit_cw_setting_chip_label" for="aipkit_task_cw_smart_schedule_start_datetime">
                        <?php esc_html_e('Start Date/Time', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="datetime-local" id="aipkit_task_cw_smart_schedule_start_datetime" name="smart_schedule_start_datetime" class="aipkit_post_settings_input">
                </div>
                <div class="aipkit_post_smart_schedule_field">
                    <label class="aipkit_post_settings_label aipkit_cw_setting_chip_label" for="aipkit_task_cw_smart_schedule_interval_value">
                        <?php esc_html_e('Publish one post every', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div class="aipkit_post_smart_schedule_interval">
                        <input type="number" id="aipkit_task_cw_smart_schedule_interval_value" name="smart_schedule_interval_value" value="1" min="1" class="aipkit_post_settings_input aipkit_post_settings_input--number">
                        <select id="aipkit_task_cw_smart_schedule_interval_unit" name="smart_schedule_interval_unit" class="aipkit_post_settings_select">
                            <option value="hours"><?php esc_html_e('Hours', 'gpt3-ai-content-generator'); ?></option>
                            <option value="days"><?php esc_html_e('Days', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
