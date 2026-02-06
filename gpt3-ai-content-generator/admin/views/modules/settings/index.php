<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/settings/index.php
// Status: MODIFIED
// I have added hidden divs to store Pinecone and Qdrant data for the Semantic Search UI.
/**
 * AIPKit Global Settings Module
 */

use WPAICG\AIPKIT_AI_Settings;
use WPAICG\AIPKit_Providers;
use WPAICG\Stats\AIPKit_Stats;
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
$deepseek_defaults   = AIPKit_Providers::get_provider_defaults('DeepSeek');
$ollama_defaults     = AIPKit_Providers::get_provider_defaults('Ollama');
$pinecone_defaults   = AIPKit_Providers::get_provider_defaults('Pinecone');
$qdrant_defaults     = AIPKit_Providers::get_provider_defaults('Qdrant');


$stats_error_message = null;
$stats_data = null;
// Default to a smaller period to keep memory low
$aipkit_stats_default_days = 3;
if (class_exists('\\WPAICG\\Stats\\AIPKit_Stats')) {
    $stats_calculator = new AIPKit_Stats();
    $stats_data = $stats_calculator->get_token_stats_last_days($aipkit_stats_default_days);
    if (is_wp_error($stats_data)) {
        if ($stats_data->get_error_code() === 'stats_volume_too_large') {
            $err = $stats_data; // keep reference to error for volume data
            // Fallback to quick stats (interactions + module counts only)
            $quick = $stats_calculator->get_quick_interaction_stats($aipkit_stats_default_days);
            if (!is_wp_error($quick)) {
                $stats_data = [
                    'days_period' => $quick['days_period'] ?? $aipkit_stats_default_days,
                    'total_tokens' => null,
                    'total_interactions' => $quick['total_interactions'] ?? 0,
                    'avg_tokens_per_interaction' => null,
                    'module_counts' => $quick['module_counts'] ?? [],
                ];
                // Show a friendly notice rather than an error
                $err_data = is_wp_error($err) ? $err->get_error_data() : null;
                $rows = is_array($err_data) && isset($err_data['rows']) ? (int)$err_data['rows'] : 0;
                $bytes = is_array($err_data) && isset($err_data['bytes']) ? (int)$err_data['bytes'] : 0;
                $stats_notice_message = sprintf(
                    /* translators: 1: rows, 2: bytes */
                    __('Usage data for the selected period is very large (rows: %1$s, size: %2$s). Token metrics are not shown. Consider deleting logs.', 'gpt3-ai-content-generator'),
                    number_format_i18n($rows),
                    size_format($bytes)
                );
                $stats_notice_link = admin_url('admin.php?page=wpaicg#stats');
                $stats_error_message = null;
            } else {
                $stats_error_message = $stats_data->get_error_message();
                $stats_data = null;
            }
        } else {
            $stats_error_message = $stats_data->get_error_message();
            $stats_data = null;
        }
    }
} else {
    $stats_error_message = __('Statistics component is unavailable.', 'gpt3-ai-content-generator');
}


$integrations_tab_visible = true;


$providers = ['OpenAI', 'OpenRouter', 'Google', 'Azure', 'DeepSeek', 'Ollama'];
$grouped_openai_models = AIPKit_Providers::get_openai_models();
$openrouter_model_list = AIPKit_Providers::get_openrouter_models();
$google_model_list     = AIPKit_Providers::get_google_models();
$azure_deployment_list = AIPKit_Providers::get_azure_all_models_grouped();
$deepseek_model_list   = AIPKit_Providers::get_deepseek_models();
$ollama_model_list     = AIPKit_Providers::get_ollama_models();
$active_provider_label = $current_provider ?: __('Not set', 'gpt3-ai-content-generator');
$active_model_value = '';
switch ($current_provider) {
    case 'OpenAI':
        $active_model_value = $openai_data['model'] ?? '';
        break;
    case 'OpenRouter':
        $active_model_value = $openrouter_data['model'] ?? '';
        break;
    case 'Google':
        $active_model_value = $google_data['model'] ?? '';
        break;
    case 'Azure':
        $active_model_value = $azure_data['model'] ?? '';
        break;
    case 'DeepSeek':
        $active_model_value = $deepseek_data['model'] ?? '';
        break;
    case 'Ollama':
        $active_model_value = $ollama_data['model'] ?? '';
        break;
}
$active_model_value = is_string($active_model_value) ? trim($active_model_value) : '';
if ($current_provider === 'Google' && strpos($active_model_value, 'models/') === 0) {
    $active_model_value = substr($active_model_value, 7);
}
$active_model_display = $active_model_value !== '' ? $active_model_value : __('Not selected', 'gpt3-ai-content-generator');

$connection_label = __('API Key', 'gpt3-ai-content-generator');
$connection_status_text = __('Missing', 'gpt3-ai-content-generator');
$connection_status_class = 'aipkit_status-warning';

if ($current_provider === 'Azure') {
    $connection_label = __('API Key & Endpoint', 'gpt3-ai-content-generator');
    $azure_key_set = !empty($azure_data['api_key']);
    $azure_endpoint_set = !empty($azure_data['endpoint']);
    if ($azure_key_set && $azure_endpoint_set) {
        $connection_status_text = __('Set', 'gpt3-ai-content-generator');
        $connection_status_class = 'aipkit_status-success';
    } else {
        $connection_status_text = __('Incomplete', 'gpt3-ai-content-generator');
    }
} elseif ($current_provider === 'Ollama') {
    $connection_label = __('Base URL', 'gpt3-ai-content-generator');
    if (!empty($ollama_data['base_url'])) {
        $connection_status_text = __('Set', 'gpt3-ai-content-generator');
        $connection_status_class = 'aipkit_status-success';
    }
} else {
    $current_provider_key = '';
    if ($current_provider === 'OpenAI') {
        $current_provider_key = $openai_data['api_key'] ?? '';
    } elseif ($current_provider === 'OpenRouter') {
        $current_provider_key = $openrouter_data['api_key'] ?? '';
    } elseif ($current_provider === 'Google') {
        $current_provider_key = $google_data['api_key'] ?? '';
    } elseif ($current_provider === 'DeepSeek') {
        $current_provider_key = $deepseek_data['api_key'] ?? '';
    }
    if (!empty($current_provider_key)) {
        $connection_status_text = __('Set', 'gpt3-ai-content-generator');
        $connection_status_class = 'aipkit_status-success';
    }
}

?>
<div class="aipkit_settings_main_container" id="aipkit_settings_container">
    <div class="aipkit_settings_layout">

            <div class="aipkit_settings_column aipkit_settings_column-left">
                <section class="aipkit_settings_card" id="aipkit_settings_provider_card">
                    <div class="aipkit_settings_card_header">
                        <h3 class="aipkit_settings_card_title"><?php esc_html_e('Provider & Model', 'gpt3-ai-content-generator'); ?></h3>
                        <div id="aipkit_settings_messages" class="aipkit_settings_messages"></div>
                    </div>
                    <div class="aipkit_settings_card_body">
                        <div class="aipkit_form-row aipkit_settings-form-row--provider-model">
                            <div class="aipkit_form-group aipkit_form-col aipkit_settings-form-col--provider-select">
                                <?php include __DIR__ . '/partials/settings-provider-select.php'; ?>
                            </div>
                            <div class="aipkit_form-group aipkit_form-col aipkit_settings-form-col--model-select">
                                <?php include __DIR__ . '/partials/settings-models.php'; ?>
                            </div>
                        </div>
                        <div class="aipkit_settings-form-col--full-width">
                            <?php include __DIR__ . '/partials/settings-api-keys.php'; ?>
                        </div>
                    </div>
                </section>

                <?php if ($integrations_tab_visible): ?>
                    <section class="aipkit_settings_card" id="aipkit_settings_integrations_card">
                        <div class="aipkit_settings_card_header">
                            <h3 class="aipkit_settings_card_title"><?php esc_html_e('Integrations', 'gpt3-ai-content-generator'); ?></h3>
                        </div>
                        <div class="aipkit_settings_card_body">
                            <?php include __DIR__ . '/partials/settings-advanced-integrations.php'; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <?php include __DIR__ . '/partials/token-stats.php'; ?>

    </div>

    <div
        class="aipkit_model_settings_popover"
        id="aipkit_settings_advanced_popover"
        aria-hidden="true"
    >
        <div
            class="aipkit_model_settings_popover_panel"
            role="dialog"
            aria-modal="false"
            aria-labelledby="aipkit_settings_advanced_title"
        >
            <div class="aipkit_model_settings_popover_header">
                <span class="aipkit_model_settings_popover_title" id="aipkit_settings_advanced_title">
                    <?php esc_html_e('Advanced settings', 'gpt3-ai-content-generator'); ?>
                </span>
                <button
                    type="button"
                    class="aipkit_model_settings_popover_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit_model_settings_popover_body">
                <?php include __DIR__ . '/partials/settings-parameters.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Hidden div for storing synced Google TTS Voices -->
<div id="aipkit_google_tts_voices_json_main" style="display:none;" data-voices="<?php
    $google_voices_main = class_exists(GoogleSettingsHandler::class) ? GoogleSettingsHandler::get_synced_google_tts_voices() : [];
echo esc_attr(wp_json_encode($google_voices_main ?: []));
?>"></div>
