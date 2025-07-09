<?php
/**
 * Partial: Chatbot Appearance Settings Accordion Content
 * UPDATED: Includes new custom-theme-settings.php partial.
 * UPDATED: Merged theme select into text-inputs.php
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\aipkit_dashboard; // Use the dashboard class
use WPAICG\Chat\Storage\BotSettingsManager;

// Check if Conversation Starters addon is active
$starters_addon_active = aipkit_dashboard::is_addon_active('conversation_starters');

// Variables available from parent script:
// $bot_id, $bot_settings
// Note: These variables are now used within the included partials.

$enable_voice_input = isset($bot_settings['enable_voice_input'])
    ? $bot_settings['enable_voice_input']
    : \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_ENABLE_VOICE_INPUT;
$stt_provider = isset($bot_settings['stt_provider'])
                ? $bot_settings['stt_provider']
                : \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_STT_PROVIDER;
// Get saved OpenAI STT model
$stt_openai_model_id = isset($bot_settings['stt_openai_model_id'])
                        ? $bot_settings['stt_openai_model_id']
                        : \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_STT_OPENAI_MODEL_ID;
// NEW: Get saved Azure STT model
$stt_azure_model_id = isset($bot_settings['stt_azure_model_id'])
                        ? $bot_settings['stt_azure_model_id']
                        : \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_STT_AZURE_MODEL_ID;

// Get synced OpenAI STT models
$openai_stt_models = \WPAICG\AIPKit_Providers::get_openai_stt_models();
?>
<div class="aipkit_accordion">
    <div class="aipkit_accordion-header">
        <span class="dashicons dashicons-arrow-right-alt2"></span>
        <?php esc_html_e('Appearance', 'gpt3-ai-content-generator'); ?>
    </div>
    <div class="aipkit_accordion-content">

        <?php
        // Include the split partials
        include __DIR__ . '/appearance/text-inputs.php'; // This now includes Theme select
        include __DIR__ . '/appearance/custom-theme-settings.php'; // Custom theme fields
        include __DIR__ . '/appearance/feature-toggles.php';
        include __DIR__ . '/appearance/conversation-starters.php'; // Includes its own conditional check
        ?>

        <!-- Conditionally displayed STT Provider and Model settings -->
         <div
             class="aipkit_stt_provider_conditional_row" <?php // Added class for JS targeting ?>
             style="display: <?php echo $enable_voice_input === '1' ? 'none' : 'none'; ?>; margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--aipkit_container-border);"
         >
             <!-- STT Provider Selection -->
            <div class="aipkit_form-group aipkit_stt_provider_group">
                <label
                    class="aipkit_form-label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_stt_provider"
                >
                    <?php esc_html_e('Voice Input Provider (STT)', 'gpt3-ai-content-generator'); ?>
                </label>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_stt_provider"
                    name="stt_provider"
                    class="aipkit_form-input aipkit_stt_provider_select" <?php // Added class for JS targeting ?>
                    style="max-width: 200px;"
                >
                    <option value="OpenAI" <?php selected($stt_provider, 'OpenAI'); ?>>
                        <?php esc_html_e('OpenAI', 'gpt3-ai-content-generator'); ?>
                    </option>
                    <?php // Add other STT providers here in the future ?>
                </select>
                 <div class="aipkit_form-help">
                    <?php esc_html_e('Select the service used to transcribe voice input.', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>

            <!-- OpenAI STT Model Selection (Conditionally displayed based on provider) -->
             <div
                 class="aipkit_form-group aipkit_stt_model_field"
                 data-stt-provider="OpenAI" <?php // Identify provider for JS ?>
                 style="display: <?php echo $stt_provider === 'OpenAI' ? 'block' : 'none'; ?>; margin-top: 10px;"
             >
                <label
                    class="aipkit_form-label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_stt_openai_model_id"
                >
                    <?php esc_html_e('OpenAI STT Model', 'gpt3-ai-content-generator'); ?>
                </label>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_stt_openai_model_id"
                    name="stt_openai_model_id"
                    class="aipkit_form-input"
                    style="max-width: 200px;"
                >
                     <?php
                     $foundCurrentSTT = false;
                     if (!empty($openai_stt_models)) {
                        foreach ($openai_stt_models as $model) {
                            $model_id_val = $model['id'] ?? '';
                            $model_name_val = $model['name'] ?? $model_id_val;
                            if ($model_id_val === $stt_openai_model_id) $foundCurrentSTT = true;
                            echo '<option value="' . esc_attr($model_id_val) . '" ' . selected($stt_openai_model_id, $model_id_val, false) . '>' . esc_html($model_name_val) . '</option>';
                        }
                     }
                     // Add saved value if not in list
                     if (!$foundCurrentSTT && !empty($stt_openai_model_id)) {
                         echo '<option value="'.esc_attr($stt_openai_model_id).'" selected>'.esc_html($stt_openai_model_id).' (Manual/Not Synced)</option>';
                     } elseif (empty($openai_stt_models) && empty($stt_openai_model_id)) {
                         // Fallback if no models synced and nothing saved
                          echo '<option value="whisper-1" selected>whisper-1 (Default)</option>';
                     }
                     ?>
                </select>
                 <div class="aipkit_form-help">
                    <?php esc_html_e('Select the OpenAI model for speech-to-text. Ensure models are synced in main AI Settings.', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>
         </div>
         <!-- END Conditional STT Settings -->

    </div><!-- /.aipkit_accordion-content -->
</div><!-- /.aipkit_accordion -->