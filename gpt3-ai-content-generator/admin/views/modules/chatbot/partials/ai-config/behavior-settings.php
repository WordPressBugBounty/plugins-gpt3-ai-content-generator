<?php
use WPAICG\Chat\Storage\BotSettingsManager;
use WPAICG\Core\AIPKit_OpenAI_Reasoning;

$bot_id = $initial_active_bot_id;
$bot_settings = $active_bot_settings;

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
$reasoning_index = array_search($reasoning_effort, $reasoning_options, true);
if ($reasoning_index === false) {
    $reasoning_index = 0;
}

$saved_temperature = max(0.0, min($saved_temperature, 2.0));
$saved_max_tokens = max(1, min($saved_max_tokens, 128000));
$saved_max_messages = max(1, min($saved_max_messages, 1024));
?>
<div class="aipkit_popover_options_list">
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span
                class="aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Display responses word by word in real-time.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Streaming', 'gpt3-ai-content-generator'); ?>
            </span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_stream_enabled_popover"
                    name="stream_enabled"
                    class="aipkit_stream_enable_toggle"
                    value="1"
                    <?php checked($saved_stream_enabled, '1'); ?>
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>
    </div>
    <div
        class="aipkit_popover_option_row aipkit_stateful_convo_group"
        style="<?php echo ($current_provider_for_this_bot === 'OpenAI') ? '' : 'display:none;'; ?>"
    >
        <div class="aipkit_popover_option_main">
            <span
                class="aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Use OpenAI server-side memory.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Stateful memory', 'gpt3-ai-content-generator'); ?>
            </span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_conversation_state_enabled_popover"
                    name="openai_conversation_state_enabled"
                    class="aipkit_openai_conversation_state_enable_toggle aipkit_stateful_convo_checkbox"
                    value="1"
                    <?php checked($openai_conversation_state_enabled_val, '1'); ?>
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_params_list">
            <div class="aipkit_popover_param_row">
                <span class="aipkit_popover_param_label">
                    <?php esc_html_e('Temperature', 'gpt3-ai-content-generator'); ?>
                </span>
                <div class="aipkit_popover_param_slider">
                    <input
                        type="range"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_temperature"
                        name="temperature"
                        class="aipkit_form-input aipkit_range_slider aipkit_popover_slider"
                        min="0" max="2" step="0.1"
                        value="<?php echo esc_attr($saved_temperature); ?>"
                    />
                    <span
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_temperature_value"
                        class="aipkit_popover_param_value"
                    >
                        <?php echo esc_html($saved_temperature); ?>
                    </span>
                </div>
            </div>
            <div class="aipkit_popover_param_row">
                <span class="aipkit_popover_param_label">
                    <?php esc_html_e('Max tokens', 'gpt3-ai-content-generator'); ?>
                </span>
                <div class="aipkit_popover_param_slider">
                    <input
                        type="range"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_completion_tokens"
                        name="max_completion_tokens"
                        class="aipkit_form-input aipkit_range_slider aipkit_popover_slider"
                        min="1" max="128000" step="1"
                        value="<?php echo esc_attr($saved_max_tokens); ?>"
                    />
                    <span
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_completion_tokens_value"
                        class="aipkit_popover_param_value"
                    >
                        <?php echo esc_html($saved_max_tokens); ?>
                    </span>
                </div>
            </div>
            <div class="aipkit_popover_param_row">
                <span class="aipkit_popover_param_label">
                    <?php esc_html_e('Context messages', 'gpt3-ai-content-generator'); ?>
                </span>
                <div class="aipkit_popover_param_slider">
                    <input
                        type="range"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_messages"
                        name="max_messages"
                        class="aipkit_form-input aipkit_range_slider aipkit_popover_slider"
                        min="1" max="1024" step="1"
                        value="<?php echo esc_attr($saved_max_messages); ?>"
                    />
                    <span
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_messages_value"
                        class="aipkit_popover_param_value"
                    >
                        <?php echo esc_html($saved_max_messages); ?>
                    </span>
                </div>
            </div>
            <div class="aipkit_popover_param_row aipkit_reasoning_effort_field">
                <span
                    class="aipkit_popover_param_label"
                    data-tooltip="<?php echo esc_attr__('Controls thinking depth for reasoning models.', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Reasoning effort', 'gpt3-ai-content-generator'); ?>
                </span>
                <div class="aipkit_popover_param_slider">
                    <input
                        type="range"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_reasoning_effort"
                        name="reasoning_effort_range"
                        class="aipkit_form-input aipkit_range_slider aipkit_popover_slider aipkit_reasoning_effort_slider"
                        min="0" max="<?php echo esc_attr(count($reasoning_options) - 1); ?>" step="1"
                        value="<?php echo esc_attr($reasoning_index); ?>"
                        data-reasoning-values="<?php echo esc_attr(wp_json_encode($reasoning_options)); ?>"
                        data-reasoning-labels="<?php echo esc_attr(wp_json_encode($reasoning_labels)); ?>"
                    />
                    <span
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_reasoning_effort_value"
                        class="aipkit_popover_param_value aipkit_reasoning_effort_label"
                    >
                        <?php echo esc_html($reasoning_labels[$reasoning_index]); ?>
                    </span>
                </div>
                <input
                    type="hidden"
                    name="reasoning_effort"
                    class="aipkit_reasoning_effort_value"
                    value="<?php echo esc_attr($reasoning_effort); ?>"
                />
            </div>
        </div>
    </div>
</div>
<div class="aipkit_popover_flyout_footer">
    <span class="aipkit_popover_flyout_footer_text">
        <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
    </span>
    <a
        class="aipkit_popover_flyout_footer_link"
        href="<?php echo esc_url('https://docs.aipower.org/docs/ai-configuration'); ?>"
        target="_blank"
        rel="noopener noreferrer"
    >
        <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
    </a>
</div>
