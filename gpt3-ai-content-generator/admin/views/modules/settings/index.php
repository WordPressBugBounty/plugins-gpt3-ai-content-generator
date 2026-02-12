<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/settings/index.php
// Status: MODIFIED
// I have added hidden divs to store Pinecone and Qdrant data for the Semantic Search UI.
/**
 * AIPKit Global Settings Module
 */

use WPAICG\AIPKIT_AI_Settings;
use WPAICG\AIPKit_Providers;
use WPAICG\aipkit_dashboard;
use WPAICG\Core\Providers\Google\GoogleSettingsHandler;

if (!defined('ABSPATH')) {
    exit;
}

// Ensure GoogleSettingsHandler is loaded before use
$google_settings_handler_path = WPAICG_PLUGIN_DIR . 'classes/core/providers/google/GoogleSettingsHandler.php';
if (!class_exists(GoogleSettingsHandler::class) && file_exists($google_settings_handler_path)) {
    require_once $google_settings_handler_path;
} elseif (!class_exists(GoogleSettingsHandler::class)) {
    echo '<div class="notice notice-error"><p>Error: Google Settings component failed to load. Safety settings cannot be displayed.</p></div>';
}

// --- Variable Definitions ---
$aipkit_options = get_option('aipkit_options', array());
$aipkit_options = is_array($aipkit_options) ? $aipkit_options : [];

// Force the "providers" array to exist
AIPKit_Providers::get_all_providers();

$ai_params = AIPKIT_AI_Settings::get_ai_parameters();
$all_api_keys = AIPKIT_AI_Settings::get_api_keys();
$public_api_key = $all_api_keys['public_api_key'] ?? '';

$is_pro = class_exists('\WPAICG\aipkit_dashboard') && aipkit_dashboard::is_pro_plan();

$safety_settings = class_exists(GoogleSettingsHandler::class) ? GoogleSettingsHandler::get_safety_settings() : [];
$category_thresholds = array();
if (is_array($safety_settings)) {
    foreach ($safety_settings as $setting) {
        if (isset($setting['category'], $setting['threshold'])) {
            $category_thresholds[$setting['category']] = $setting['threshold'];
        }
    }
}

$current_provider = AIPKit_Providers::get_current_provider();

$openai_data     = AIPKit_Providers::get_provider_data('OpenAI');
$openrouter_data = AIPKit_Providers::get_provider_data('OpenRouter');
$google_data     = AIPKit_Providers::get_provider_data('Google');
$azure_data      = AIPKit_Providers::get_provider_data('Azure');
$claude_data     = AIPKit_Providers::get_provider_data('Claude');
$deepseek_data   = AIPKit_Providers::get_provider_data('DeepSeek');
$ollama_data     = AIPKit_Providers::get_provider_data('Ollama');
$pinecone_data   = AIPKit_Providers::get_provider_data('Pinecone');
$qdrant_data     = AIPKit_Providers::get_provider_data('Qdrant');

$temperature       = $ai_params['temperature'];
$top_p             = $ai_params['top_p'];
$openai_store_conversation = isset($openai_data['store_conversation']) ? $openai_data['store_conversation'] : '0';

$safety_thresholds = array(
    'BLOCK_NONE'             => 'Block None',
    'BLOCK_LOW_AND_ABOVE'    => 'Block Few',
    'BLOCK_MEDIUM_AND_ABOVE' => 'Block Some',
    'BLOCK_ONLY_HIGH'        => 'Block Most',
);

$openai_defaults     = AIPKit_Providers::get_provider_defaults('OpenAI');
$openrouter_defaults = AIPKit_Providers::get_provider_defaults('OpenRouter');
$google_defaults     = AIPKit_Providers::get_provider_defaults('Google');
$azure_defaults      = AIPKit_Providers::get_provider_defaults('Azure');
$claude_defaults     = AIPKit_Providers::get_provider_defaults('Claude');
$deepseek_defaults   = AIPKit_Providers::get_provider_defaults('DeepSeek');
$ollama_defaults     = AIPKit_Providers::get_provider_defaults('Ollama');
$pinecone_defaults   = AIPKit_Providers::get_provider_defaults('Pinecone');
$qdrant_defaults     = AIPKit_Providers::get_provider_defaults('Qdrant');

$providers = ['OpenAI', 'Google', 'Claude', 'OpenRouter', 'Azure', 'Ollama', 'DeepSeek'];
$provider_select_options = class_exists('\\WPAICG\\AIPKit_Provider_Model_List_Builder')
    ? \WPAICG\AIPKit_Provider_Model_List_Builder::get_provider_options($providers, $is_pro)
    : [];
?>
<div class="aipkit_settings_main_container" id="aipkit_settings_container">
    <section class="aipkit_settings_simple_panel aipkit_settings_pages_shell">
        <div class="aipkit_settings_page_nav_row">
            <nav class="aipkit_settings_page_nav" aria-label="<?php esc_attr_e('Settings sections', 'gpt3-ai-content-generator'); ?>">
                <button type="button" class="aipkit_settings_page_nav_link is-active" data-aipkit-settings-page-link="ai">
                    <?php esc_html_e('AI', 'gpt3-ai-content-generator'); ?>
                </button>
                <button type="button" class="aipkit_settings_page_nav_link" data-aipkit-settings-page-link="rest-api">
                    <?php esc_html_e('REST API', 'gpt3-ai-content-generator'); ?>
                </button>
                <button type="button" class="aipkit_settings_page_nav_link" data-aipkit-settings-page-link="others">
                    <?php esc_html_e('Others', 'gpt3-ai-content-generator'); ?>
                </button>
            </nav>
            <div id="aipkit_settings_global_messages" class="aipkit_settings_messages" role="status" aria-live="polite"></div>
        </div>

        <div class="aipkit_settings_pages">
            <section class="aipkit_settings_page_section" data-aipkit-settings-page="ai">
                <header class="aipkit_settings_page_header">
                    <h3 class="aipkit_settings_page_title"><?php esc_html_e('AI Settings', 'gpt3-ai-content-generator'); ?></h3>
                    <p class="aipkit_settings_page_helper"><?php esc_html_e('Set your default provider, model, API key, and advanced options.', 'gpt3-ai-content-generator'); ?></p>
                </header>

                <div class="aipkit_settings_simple_form" id="aipkit_settings_provider_panel">
                    <?php include __DIR__ . '/partials/settings-provider-select.php'; ?>
                    <?php include __DIR__ . '/partials/settings-api-keys.php'; ?>
                    <?php include __DIR__ . '/partials/settings-models.php'; ?>
                    <?php include __DIR__ . '/partials/settings-advanced-panel.php'; ?>
                </div>
            </section>

            <section class="aipkit_settings_page_section" data-aipkit-settings-page="rest-api" hidden>
                <header class="aipkit_settings_page_header">
                    <h3 class="aipkit_settings_page_title"><?php esc_html_e('REST API Settings', 'gpt3-ai-content-generator'); ?></h3>
                    <p class="aipkit_settings_page_helper"><?php esc_html_e('Configure external access to AIPKit via REST API key.', 'gpt3-ai-content-generator'); ?></p>
                </header>

                <div class="aipkit_settings_simple_form aipkit_settings_simple_form--rest-api">
                    <?php include __DIR__ . '/partials/settings-rest-api-page.php'; ?>
                </div>
            </section>

            <section class="aipkit_settings_page_section" data-aipkit-settings-page="others" hidden>
                <header class="aipkit_settings_page_header">
                    <h3 class="aipkit_settings_page_title"><?php esc_html_e('Other Settings', 'gpt3-ai-content-generator'); ?></h3>
                    <p class="aipkit_settings_page_helper"><?php esc_html_e('Manage backups and maintenance actions.', 'gpt3-ai-content-generator'); ?></p>
                </header>

                <div class="aipkit_settings_simple_form aipkit_settings_simple_form--others">
                    <?php include __DIR__ . '/partials/settings-others-page.php'; ?>
                </div>
            </section>
        </div>
    </section>
</div>

<!-- Hidden div for storing synced Google TTS Voices -->
<div id="aipkit_google_tts_voices_json_main" style="display:none;" data-voices="<?php
    $google_voices_main = class_exists(GoogleSettingsHandler::class) ? GoogleSettingsHandler::get_synced_google_tts_voices() : [];
echo esc_attr(wp_json_encode($google_voices_main ?: []));
?>"></div>
