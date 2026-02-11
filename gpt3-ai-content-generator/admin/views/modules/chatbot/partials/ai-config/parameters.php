<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/chatbot/partials/ai-config/parameters.php
// Status: MODIFIED

/**
 * Partial: AI Config - AI Parameter Sliders
 */
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Chat\Storage\BotSettingsManager; // Use new class for constants
use WPAICG\Core\AIPKit_OpenAI_Reasoning;

// Variables required from parent script (accordion-ai-config.php):
// $bot_id, $bot_settings, $openai_conversation_state_enabled_val, $current_provider_for_this_bot
// $openai_web_search_enabled_val, $openai_web_search_context_size_val, $openai_web_search_loc_type_val, etc.
// $google_search_grounding_enabled_val, $google_grounding_mode_val, etc.
// $reasoning_effort_val (NEW)
// --- NEW: $enable_image_upload variable passed from accordion-ai-config.php
$enable_image_upload = isset($bot_settings['enable_image_upload'])
                        ? $bot_settings['enable_image_upload']
                        : \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_ENABLE_IMAGE_UPLOAD;
// --- NEW: $enable_voice_input variable passed from accordion-ai-config.php
$enable_voice_input = isset($bot_settings['enable_voice_input'])
                      ? $bot_settings['enable_voice_input']
                      : \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_ENABLE_VOICE_INPUT;
// --- END NEW ---

// Extract AI param values from bot_settings with defaults from BotSettingsManager
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

// Ensure they are clamped
$saved_temperature = max(0.0, min($saved_temperature, 2.0));
$saved_max_tokens = max(1, min($saved_max_tokens, 128000));
$saved_max_messages = max(1, min($saved_max_messages, 1024));

?>
    <div
        class="aipkit_popover_parameters_flyout"
        id="aipkit_parameters_flyout"
        aria-hidden="true"
        role="dialog"
        aria-label="<?php esc_attr_e('Parameters', 'gpt3-ai-content-generator'); ?>"
    >
        <div class="aipkit_popover_flyout_header">
            <span class="aipkit_popover_flyout_title">
                <?php esc_html_e('AI Parameters', 'gpt3-ai-content-generator'); ?>
            </span>
            <button
                type="button"
                class="aipkit_popover_flyout_close"
                aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit_popover_flyout_body aipkit_popover_parameters_body">
            <div class="aipkit_popover_params_list">
                <!-- Temperature -->
                <div class="aipkit_popover_param_row">
                    <span class="aipkit_popover_param_label"><?php esc_html_e('Creativity', 'gpt3-ai-content-generator'); ?></span>
                    <div class="aipkit_popover_param_slider">
                        <input
                            type="range"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_temperature"
                            name="temperature"
                            class="aipkit_form-input aipkit_range_slider aipkit_popover_slider"
                            min="0" max="2" step="0.1"
                            value="<?php echo esc_attr($saved_temperature); ?>"
                        />
                        <span id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_temperature_value" class="aipkit_popover_param_value"><?php echo esc_html($saved_temperature); ?></span>
                    </div>
                </div>

                <!-- Max Tokens -->
                <div class="aipkit_popover_param_row">
                    <span class="aipkit_popover_param_label"><?php esc_html_e('Response length', 'gpt3-ai-content-generator'); ?></span>
                    <div class="aipkit_popover_param_slider">
                        <input
                            type="range"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_completion_tokens"
                            name="max_completion_tokens"
                            class="aipkit_form-input aipkit_range_slider aipkit_popover_slider"
                            min="1" max="128000" step="1"
                            value="<?php echo esc_attr($saved_max_tokens); ?>"
                        />
                        <span id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_completion_tokens_value" class="aipkit_popover_param_value"><?php echo esc_html($saved_max_tokens); ?></span>
                    </div>
                </div>

                <!-- Max Messages -->
                <div class="aipkit_popover_param_row">
                    <span class="aipkit_popover_param_label"><?php esc_html_e('Memory', 'gpt3-ai-content-generator'); ?></span>
                    <div class="aipkit_popover_param_slider">
                        <input
                            type="range"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_messages"
                            name="max_messages"
                            class="aipkit_form-input aipkit_range_slider aipkit_popover_slider"
                            min="1" max="1024" step="1"
                            value="<?php echo esc_attr($saved_max_messages); ?>"
                        />
                        <span id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_messages_value" class="aipkit_popover_param_value"><?php echo esc_html($saved_max_messages); ?></span>
                    </div>
                </div>

                <div class="aipkit_popover_param_row aipkit_reasoning_effort_field">
                    <span
                        class="aipkit_popover_param_label"
                        data-tooltip="<?php echo esc_attr__('Controls thinking depth for reasoning models.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('Reasoning', 'gpt3-ai-content-generator'); ?>
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
                        <span id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_reasoning_effort_value" class="aipkit_popover_param_value aipkit_reasoning_effort_label"><?php echo esc_html($reasoning_labels[$reasoning_index]); ?></span>
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
        <div class="aipkit_popover_flyout_footer">
            <span class="aipkit_popover_flyout_footer_text">
                <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
            </span>
            <a
                class="aipkit_popover_flyout_footer_link"
                href="<?php echo esc_url('https://docs.aipower.org/docs/ai-configuration#ai-parameters'); ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
            </a>
        </div>
    </div>
