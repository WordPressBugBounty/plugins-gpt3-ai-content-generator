<?php
/**
 * Partial: AutoGPT Task Frequency
 *
 * Expected variables:
 * - $frequencies (array) Optional
 */

if (!defined('ABSPATH')) {
    exit;
}

$frequencies = isset($frequencies) && is_array($frequencies) ? $frequencies : [];
$default_frequency = array_key_exists('daily', $frequencies)
    ? 'daily'
    : (array_key_first($frequencies) ?: '');
?>
<div class="aipkit_task_schedule_frequency">
    <div class="aipkit_post_status_row aipkit_task_schedule_frequency_row">
        <label class="aipkit_post_settings_label aipkit_cw_setting_chip_label" for="aipkit_automated_task_frequency">
            <?php esc_html_e('Frequency', 'gpt3-ai-content-generator'); ?>
        </label>
        <select id="aipkit_automated_task_frequency" name="task_frequency" class="aipkit_post_settings_select aipkit_task_schedule_frequency_select">
        <?php foreach ($frequencies as $value => $label): ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($value, $default_frequency); ?>><?php echo esc_html($label); ?></option>
        <?php endforeach; ?>
        </select>
    </div>
</div>
