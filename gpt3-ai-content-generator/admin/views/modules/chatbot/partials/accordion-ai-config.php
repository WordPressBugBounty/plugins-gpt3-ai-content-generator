<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/chatbot/partials/accordion-ai-config.php
/**
 * Partial: Chatbot AI Configuration Accordion Content

 */
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Chat\Storage\BotSettingsManager; // Use new class for constants

// Variables available from parent script (chatbot/index.php):
// $bot_id, $providers, $saved_provider, $grouped_openai_models, $saved_model,
// $openrouter_model_list, $google_model_list,
// $azure_deployment_list, $deepseek_model_list
// $bot_settings (contains stream_enabled, temperature, max_completion_tokens, max_messages etc.)

// --- NEW: Get value for Instructions ---
$saved_instructions = isset($bot_settings['instructions']) ? $bot_settings['instructions'] : '';
// --- END NEW ---

// Get current provider for this bot for conditional display
$current_provider_for_this_bot = $bot_settings['provider'] ?? 'OpenAI';
// Get OpenAI Conversation State setting value
$openai_conversation_state_enabled_val = $bot_settings['openai_conversation_state_enabled']
                                          ?? BotSettingsManager::DEFAULT_OPENAI_CONVERSATION_STATE_ENABLED;
// Get OpenAI Web Search settings
$openai_web_search_enabled_val = $bot_settings['openai_web_search_enabled']
                                  ?? BotSettingsManager::DEFAULT_OPENAI_WEB_SEARCH_ENABLED;
$openai_web_search_context_size_val = $bot_settings['openai_web_search_context_size']
                                      ?? BotSettingsManager::DEFAULT_OPENAI_WEB_SEARCH_CONTEXT_SIZE;
$openai_web_search_loc_type_val = $bot_settings['openai_web_search_loc_type']
                                  ?? BotSettingsManager::DEFAULT_OPENAI_WEB_SEARCH_LOC_TYPE;
$openai_web_search_loc_country_val = $bot_settings['openai_web_search_loc_country'] ?? '';
$openai_web_search_loc_city_val = $bot_settings['openai_web_search_loc_city'] ?? '';
$openai_web_search_loc_region_val = $bot_settings['openai_web_search_loc_region'] ?? '';
$openai_web_search_loc_timezone_val = $bot_settings['openai_web_search_loc_timezone'] ?? '';

// --- NEW: Get Google Search Grounding settings ---
$google_search_grounding_enabled_val = $bot_settings['google_search_grounding_enabled']
                                     ?? BotSettingsManager::DEFAULT_GOOGLE_SEARCH_GROUNDING_ENABLED;
$google_grounding_mode_val = $bot_settings['google_grounding_mode']
                             ?? BotSettingsManager::DEFAULT_GOOGLE_GROUNDING_MODE;
$google_grounding_dynamic_threshold_val = isset($bot_settings['google_grounding_dynamic_threshold'])
                                          ? floatval($bot_settings['google_grounding_dynamic_threshold'])
                                          : BotSettingsManager::DEFAULT_GOOGLE_GROUNDING_DYNAMIC_THRESHOLD;
// Ensure threshold is within bounds (0.0 to 1.0)
$google_grounding_dynamic_threshold_val = max(0.0, min($google_grounding_dynamic_threshold_val, 1.0));
// --- END NEW ---

// --- NEW: Get Image Upload setting for icon button state ---
$enable_image_upload = isset($bot_settings['enable_image_upload'])
                        ? $bot_settings['enable_image_upload']
                        : \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_ENABLE_IMAGE_UPLOAD;
// --- END NEW ---

// --- NEW: Get voice input setting value ---
$enable_voice_input = isset($bot_settings['enable_voice_input'])
                      ? $bot_settings['enable_voice_input']
                      : \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_ENABLE_VOICE_INPUT;
// --- END NEW ---
?>
<div class="aipkit_accordion">
    <div class="aipkit_accordion-header">
        <span class="dashicons dashicons-arrow-right-alt2"></span>
        <?php esc_html_e('General', 'gpt3-ai-content-generator'); ?>
    </div>
    <div class="aipkit_accordion-content">

        <?php
        // Include the split partials for Provider/Model and Parameters
        include __DIR__ . '/ai-config/provider-model.php';
        ?>

        <!-- NEW: Instructions moved here -->
        <div class="aipkit_form-group">
            <label
                class="aipkit_form-label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_instructions"
            >
                <?php esc_html_e('Instructions', 'gpt3-ai-content-generator'); ?>
            </label>
            <textarea
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_instructions"
                name="instructions"
                class="aipkit_form-input"
                rows="5"
                placeholder="<?php esc_attr_e('e.g., You are a helpful AI Assistant. Please be friendly.', 'gpt3-ai-content-generator'); ?>"
            ><?php echo esc_textarea($saved_instructions); ?></textarea>
        </div>
        <!-- END NEW -->
        
        <?php
        include __DIR__ . '/ai-config/parameters.php';
        ?>

    </div><!-- /.aipkit_accordion-content -->
</div><!-- /.aipkit_accordion -->