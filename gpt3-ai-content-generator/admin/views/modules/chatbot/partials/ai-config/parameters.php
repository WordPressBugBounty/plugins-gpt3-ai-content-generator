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

// Ensure they are clamped
$saved_temperature = max(0.0, min($saved_temperature, 2.0));
$saved_max_tokens = max(1, min($saved_max_tokens, 128000));
$saved_max_messages = max(1, min($saved_max_messages, 1024));

?>
<?php // NEW WRAPPER DIV for toggling visibility?>
<div class="aipkit_cb_ai_parameters_row" style="display: none; margin-top: 15px;">
    <!-- Row for Temperature, Max Tokens, Max Messages -->
    <div class="aipkit_form-row">
        <!-- Temperature Column -->
        <div class="aipkit_form-group aipkit_form-col">
            <label
                class="aipkit_form-label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_temperature"
            >
                <?php esc_html_e('Temperature', 'gpt3-ai-content-generator'); ?>
            </label>
            <div class="aipkit_slider_wrapper">
                <input
                    type="range"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_temperature"
                    name="temperature"
                    class="aipkit_form-input aipkit_range_slider"
                    min="0" max="2" step="0.1"
                    value="<?php echo esc_attr($saved_temperature); ?>"
                />
                <span
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_temperature_value"
                    class="aipkit_slider_value"
                >
                    <?php echo esc_html($saved_temperature); ?>
                </span>
            </div>
        </div>

        <!-- Max Completion Tokens Column -->
        <div class="aipkit_form-group aipkit_form-col">
            <label
                class="aipkit_form-label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_completion_tokens"
            >
                 <?php esc_html_e('Max Token', 'gpt3-ai-content-generator'); ?>
            </label>
             <div class="aipkit_slider_wrapper">
                <input
                    type="range"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_completion_tokens"
                    name="max_completion_tokens"
                    class="aipkit_form-input aipkit_range_slider"
                    min="1" max="128000" step="1"
                    value="<?php echo esc_attr($saved_max_tokens); ?>"
                />
                <span
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_completion_tokens_value"
                    class="aipkit_slider_value"
                >
                    <?php echo esc_html($saved_max_tokens); ?>
                </span>
            </div>
        </div>

        <!-- Max Messages Column -->
        <div class="aipkit_form-group aipkit_form-col">
             <label
                class="aipkit_form-label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_messages"
            >
                 <?php esc_html_e('Max Messages', 'gpt3-ai-content-generator'); ?>
            </label>
             <div class="aipkit_slider_wrapper">
                <input
                    type="range"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_messages"
                    name="max_messages"
                    class="aipkit_form-input aipkit_range_slider"
                    min="1" max="1024" step="1"
                    value="<?php echo esc_attr($saved_max_messages); ?>"
                />
                <span
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_max_messages_value"
                    class="aipkit_slider_value"
                >
                    <?php echo esc_html($saved_max_messages); ?>
                </span>
            </div>
        </div><!-- /Max Messages Column -->
        
        <!-- NEW: Reasoning Effort (Conditional) -->
        <div class="aipkit_form-group aipkit_form-col aipkit_reasoning_effort_field" style="display: none;">
            <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_reasoning_effort"><?php esc_html_e('Reasoning', 'gpt3-ai-content-generator'); ?></label>
            <select id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_reasoning_effort" name="reasoning_effort" class="aipkit_form-input">
                <option value="minimal" <?php selected($reasoning_effort_val, 'minimal'); ?>>Minimal</option>
                <option value="low" <?php selected($reasoning_effort_val, 'low'); ?>>Low (Default)</option>
                <option value="medium" <?php selected($reasoning_effort_val, 'medium'); ?>>Medium</option>
                <option value="high" <?php selected($reasoning_effort_val, 'high'); ?>>High</option>
            </select>
        </div>
        <!-- END NEW -->

    </div><!-- /AI Params Row -->

    <!-- START: Moved Provider-Specific Sub-Settings -->
    <div class="aipkit_openai_web_search_conditional_settings" style="display: <?php echo ($current_provider_for_this_bot === 'OpenAI' && $openai_web_search_enabled_val === '1') ? 'block' : 'none'; ?>; ">
        <div class="aipkit_form-row">
            <div class="aipkit_form-group aipkit_form-col">
                <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_context_size"><?php esc_html_e('Search Context Size', 'gpt3-ai-content-generator'); ?></label>
                <select id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_context_size" name="openai_web_search_context_size" class="aipkit_form-input">
                    <option value="low" <?php selected($openai_web_search_context_size_val, 'low'); ?>><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
                    <option value="medium" <?php selected($openai_web_search_context_size_val, 'medium'); ?>><?php esc_html_e('Medium (Default)', 'gpt3-ai-content-generator'); ?></option>
                    <option value="high" <?php selected($openai_web_search_context_size_val, 'high'); ?>><?php esc_html_e('High', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
            <div class="aipkit_form-group aipkit_form-col">
                <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_type"><?php esc_html_e('User Location', 'gpt3-ai-content-generator'); ?></label>
                <select id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_type" name="openai_web_search_loc_type" class="aipkit_form-input aipkit_openai_web_search_loc_type_select">
                    <option value="none" <?php selected($openai_web_search_loc_type_val, 'none'); ?>><?php esc_html_e('None (Default)', 'gpt3-ai-content-generator'); ?></option>
                    <option value="approximate" <?php selected($openai_web_search_loc_type_val, 'approximate'); ?>><?php esc_html_e('Approximate', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_openai_web_search_location_details" style="display: <?php echo ($current_provider_for_this_bot === 'OpenAI' && $openai_web_search_enabled_val === '1' && $openai_web_search_loc_type_val === 'approximate') ? 'block' : 'none'; ?>; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #eee;">
            <div class="aipkit_form-row">
                <div class="aipkit_form-group aipkit_form-col"><label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_country"><?php esc_html_e('Country (ISO Code)', 'gpt3-ai-content-generator'); ?></label><input type="text" id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_country" name="openai_web_search_loc_country" class="aipkit_form-input" value="<?php echo esc_attr($openai_web_search_loc_country_val); ?>" placeholder="<?php esc_attr_e('e.g., US, GB', 'gpt3-ai-content-generator'); ?>" maxlength="2"></div>
                <div class="aipkit_form-group aipkit_form-col"><label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_city"><?php esc_html_e('City', 'gpt3-ai-content-generator'); ?></label><input type="text" id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_city" name="openai_web_search_loc_city" class="aipkit_form-input" value="<?php echo esc_attr($openai_web_search_loc_city_val); ?>" placeholder="<?php esc_attr_e('e.g., London', 'gpt3-ai-content-generator'); ?>"></div>
            </div>
            <div class="aipkit_form-row">
                <div class="aipkit_form-group aipkit_form-col"><label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_region"><?php esc_html_e('Region/State', 'gpt3-ai-content-generator'); ?></label><input type="text" id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_region" name="openai_web_search_loc_region" class="aipkit_form-input" value="<?php echo esc_attr($openai_web_search_loc_region_val); ?>" placeholder="<?php esc_attr_e('e.g., California', 'gpt3-ai-content-generator'); ?>"></div>
                <div class="aipkit_form-group aipkit_form-col"><label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_timezone"><?php esc_html_e('Timezone (IANA)', 'gpt3-ai-content-generator'); ?></label><input type="text" id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_timezone" name="openai_web_search_loc_timezone" class="aipkit_form-input" value="<?php echo esc_attr($openai_web_search_loc_timezone_val); ?>" placeholder="<?php esc_attr_e('e.g., America/Chicago', 'gpt3-ai-content-generator'); ?>"></div>
            </div>
            <div class="aipkit_form-help"><?php esc_html_e('Leave location fields blank if not applicable. Country code is 2-letter ISO (e.g., US).', 'gpt3-ai-content-generator'); ?></div>
        </div>
    </div>
    <div class="aipkit_google_search_grounding_conditional_settings" style="display: <?php echo ($current_provider_for_this_bot === 'Google' && $google_search_grounding_enabled_val === '1') ? 'block' : 'none'; ?>; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #eee; ">
        <div class="aipkit_form-row">
            <div class="aipkit_form-group aipkit_form-col">
                <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_mode"><?php esc_html_e('Grounding Mode', 'gpt3-ai-content-generator'); ?></label>
                <select id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_mode" name="google_grounding_mode" class="aipkit_form-input aipkit_google_grounding_mode_select">
                    <option value="DEFAULT_MODE" <?php selected($google_grounding_mode_val, 'DEFAULT_MODE'); ?>><?php esc_html_e('Default (Model Decides/Search as Tool)', 'gpt3-ai-content-generator'); ?></option>
                    <option value="MODE_DYNAMIC" <?php selected($google_grounding_mode_val, 'MODE_DYNAMIC'); ?>><?php esc_html_e('Dynamic Retrieval (Gemini 1.5 Flash only)', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_google_grounding_dynamic_threshold_container" style="display: <?php echo ($current_provider_for_this_bot === 'Google' && $google_search_grounding_enabled_val === '1' && $google_grounding_mode_val === 'MODE_DYNAMIC') ? 'block' : 'none'; ?>; margin-top: 10px;">
            <div class="aipkit_form-group aipkit_form-col">
                <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_dynamic_threshold">
                    <?php esc_html_e('Dynamic Retrieval Threshold', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_slider_wrapper" style="max-width: 400px;"><input type="range" id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_dynamic_threshold" name="google_grounding_dynamic_threshold" class="aipkit_form-input aipkit_range_slider" min="0.0" max="1.0" step="0.01" value="<?php echo esc_attr($google_grounding_dynamic_threshold_val); ?>">
                    <span id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_dynamic_threshold_value" class="aipkit_slider_value"><?php echo esc_html(number_format($google_grounding_dynamic_threshold_val, 2)); ?></span>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Moved Provider-Specific Sub-Settings -->

    <!-- OpenAI Conversation State Setting (HIDDEN) -->
    <div class="aipkit_form-group" style="display: none;">
        <input
            type="checkbox"
            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_conversation_state_enabled"
            name="openai_conversation_state_enabled"
            class="aipkit_toggle_switch aipkit_openai_conversation_state_enable_toggle"
            value="1"
            <?php checked($openai_conversation_state_enabled_val, '1'); ?>
        >
    </div>

     <!-- Image Upload Setting (HIDDEN) -->
     <div class="aipkit_form-group" style="display: none;">
        <input
            type="checkbox"
            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_image_upload"
            name="enable_image_upload"
            class="aipkit_toggle_switch aipkit_image_upload_enable_toggle"
            value="1"
            <?php checked($enable_image_upload, '1'); ?>
        >
    </div>

    <!-- Voice Input Setting (HIDDEN) -->
    <div class="aipkit_form-group" style="display: none;">
        <input
            type="checkbox"
            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_voice_input"
            name="enable_voice_input"
            class="aipkit_toggle_switch aipkit_voice_input_enable_toggle"
            value="1"
            <?php checked($enable_voice_input, '1'); ?>
        >
    </div>

</div>