<?php
use WPAICG\Chat\Storage\BotSettingsManager;
use WPAICG\Core\AIPKit_OpenAI_Reasoning;

$bot_id = $initial_active_bot_id;
$bot_settings = $active_bot_settings;
$saved_stream_enabled = isset($bot_settings['stream_enabled'])
    ? $bot_settings['stream_enabled']
    : BotSettingsManager::DEFAULT_STREAM_ENABLED;

$saved_temperature = isset($bot_settings['temperature'])
    ? floatval($bot_settings['temperature'])
    : BotSettingsManager::DEFAULT_TEMPERATURE;
$saved_max_tokens = isset($bot_settings['max_completion_tokens'])
    ? absint($bot_settings['max_completion_tokens'])
    : BotSettingsManager::DEFAULT_MAX_COMPLETION_TOKENS;
$saved_max_messages = isset($bot_settings['max_messages'])
    ? absint($bot_settings['max_messages'])
    : BotSettingsManager::DEFAULT_MAX_MESSAGES;
$reasoning_effort = isset($bot_settings['reasoning_effort'])
    ? sanitize_text_field($bot_settings['reasoning_effort'])
    : BotSettingsManager::DEFAULT_REASONING_EFFORT;
$reasoning_effort = AIPKit_OpenAI_Reasoning::sanitize_effort($reasoning_effort);
$reasoning_options = ['none', 'low', 'medium', 'high', 'xhigh'];
$reasoning_labels = [
    __('none', 'gpt3-ai-content-generator'),
    __('low', 'gpt3-ai-content-generator'),
    __('med', 'gpt3-ai-content-generator'),
    __('high', 'gpt3-ai-content-generator'),
    __('xhigh', 'gpt3-ai-content-generator'),
];
if (!in_array($reasoning_effort, $reasoning_options, true)) {
    $reasoning_effort = BotSettingsManager::DEFAULT_REASONING_EFFORT;
}

$saved_temperature = max(0.0, min($saved_temperature, 2.0));
$saved_max_tokens = max(1, min($saved_max_tokens, 128000));
$saved_max_messages = max(1, min($saved_max_messages, 1024));
?>
<div class="aipkit_popover_options_list aipkit_behavior_compact_options">
    <div class="aipkit_behavior_compact_row">
        <div class="aipkit_behavior_compact_cell">
            <label
                class="aipkit_popover_option_label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_stream_enabled_select"
                data-tooltip="<?php echo esc_attr__('Display responses word by word in real-time.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Streaming', 'gpt3-ai-content-generator'); ?>
            </label>
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_stream_enabled_select"
                name="stream_enabled"
                class="aipkit_form-input aipkit_popover_option_select aipkit_stream_enable_toggle"
            >
                <option value="1" <?php selected($saved_stream_enabled, '1'); ?>>
                    <?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?>
                </option>
                <option value="0" <?php selected($saved_stream_enabled, '0'); ?>>
                    <?php esc_html_e('No', 'gpt3-ai-content-generator'); ?>
                </option>
            </select>
        </div>
        <div
            class="aipkit_behavior_compact_cell aipkit_stateful_convo_group"
            style="<?php echo ($current_provider_for_this_bot === 'OpenAI') ? '' : 'display:none;'; ?>"
        >
            <label
                class="aipkit_popover_option_label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_conversation_state_enabled_select"
                data-tooltip="<?php echo esc_attr__('Use OpenAI server-side memory.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Session memory', 'gpt3-ai-content-generator'); ?>
            </label>
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_conversation_state_enabled_select"
                name="openai_conversation_state_enabled"
                class="aipkit_form-input aipkit_popover_option_select aipkit_openai_conversation_state_enable_toggle aipkit_stateful_convo_checkbox"
            >
                <option value="1" <?php selected($openai_conversation_state_enabled_val, '1'); ?>>
                    <?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?>
                </option>
                <option value="0" <?php selected($openai_conversation_state_enabled_val, '0'); ?>>
                    <?php esc_html_e('No', 'gpt3-ai-content-generator'); ?>
                </option>
            </select>
        </div>
        <div class="aipkit_behavior_compact_cell">
            <label class="aipkit_popover_option_label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_temperature">
                <?php esc_html_e('Creativity', 'gpt3-ai-content-generator'); ?>
            </label>
            <input
                type="number"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_temperature"
                name="temperature"
                class="aipkit_form-input"
                min="0"
                max="2"
                step="0.1"
                value="<?php echo esc_attr($saved_temperature); ?>"
            />
        </div>
        <div class="aipkit_behavior_compact_cell">
            <label class="aipkit_popover_option_label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_completion_tokens">
                <?php esc_html_e('Response length', 'gpt3-ai-content-generator'); ?>
            </label>
            <input
                type="number"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_completion_tokens"
                name="max_completion_tokens"
                class="aipkit_form-input"
                min="1"
                max="128000"
                step="1"
                value="<?php echo esc_attr($saved_max_tokens); ?>"
            />
        </div>
        <div class="aipkit_behavior_compact_cell">
            <label class="aipkit_popover_option_label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_messages">
                <?php esc_html_e('Memory', 'gpt3-ai-content-generator'); ?>
            </label>
            <input
                type="number"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_messages"
                name="max_messages"
                class="aipkit_form-input"
                min="1"
                max="1024"
                step="1"
                value="<?php echo esc_attr($saved_max_messages); ?>"
            />
        </div>
        <div class="aipkit_behavior_compact_cell aipkit_reasoning_effort_field">
            <label
                class="aipkit_popover_option_label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_reasoning_effort"
                data-tooltip="<?php echo esc_attr__('Controls thinking depth for reasoning models.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Reasoning', 'gpt3-ai-content-generator'); ?>
            </label>
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_reasoning_effort"
                name="reasoning_effort"
                class="aipkit_form-input aipkit_popover_option_select aipkit_reasoning_effort_value"
            >
                <?php foreach ($reasoning_options as $option_index => $option_value) : ?>
                    <option value="<?php echo esc_attr($option_value); ?>" <?php selected($reasoning_effort, $option_value); ?>>
                        <?php echo esc_html($reasoning_labels[$option_index]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>
