<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/shortcode/featuremanager/get-upload-flags.php
// Status: MODIFIED

namespace WPAICG\Chat\Frontend\Shortcode\FeatureManagerMethods;

use WPAICG\aipkit_dashboard; // ADDED for Pro check
use function WPAICG\Core\Providers\OpenRouter\Methods\resolve_model_capabilities_logic;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Determines file/image upload related feature flags.
 *
 * @param array $core_flags An array of intermediate flags from get_core_flag_values_logic.
 *                          Expected keys: 'provider', 'enable_file_upload_setting', 'enable_image_upload_setting'.
 * @return array An array of upload feature flags:
 *               'file_upload_ui_enabled', 'image_upload_ui_enabled', 'input_action_button_enabled'.
 */
function get_upload_flags_logic(array $core_flags): array {
    $upload_flags = [];
    $is_pro = false;
    // Ensure aipkit_dashboard class is loaded before calling its static methods
    if (!class_exists(aipkit_dashboard::class)) {
        $dashboard_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_dashboard.php';
        if (file_exists($dashboard_path)) {
            require_once $dashboard_path;
        }
    }

    if (class_exists(aipkit_dashboard::class)) {
        $is_pro = aipkit_dashboard::is_pro_plan();
    }

    $provider = isset($core_flags['provider']) ? sanitize_text_field((string) $core_flags['provider']) : 'OpenAI';
    $model = isset($core_flags['model']) ? sanitize_text_field((string) $core_flags['model']) : '';
    $vector_store_provider = isset($core_flags['vector_store_provider']) ? sanitize_key((string) $core_flags['vector_store_provider']) : 'openai';
    $image_upload_supported_providers = ['OpenAI', 'Claude', 'OpenRouter'];
    $is_image_upload_supported_provider = in_array($provider, $image_upload_supported_providers, true);
    $claude_files_compatible = !($vector_store_provider === 'claude_files' && $provider !== 'Claude');

    if ($provider === 'OpenRouter' && $is_image_upload_supported_provider && $model !== '') {
        $resolver_fn = 'WPAICG\\Core\\Providers\\OpenRouter\\Methods\\resolve_model_capabilities_logic';
        if (!function_exists($resolver_fn)) {
            $capability_file = WPAICG_PLUGIN_DIR . 'classes/core/providers/openrouter/capabilities.php';
            if (file_exists($capability_file)) {
                require_once $capability_file;
            }
        }
        if (function_exists($resolver_fn)) {
            $capabilities = resolve_model_capabilities_logic($model);
            $is_image_upload_supported_provider = !empty($capabilities['image_input']);
        }
    }

    // File upload UI is enabled if the setting is on AND it's a Pro feature
    $upload_flags['file_upload_ui_enabled'] = ($core_flags['enable_file_upload_setting'] ?? false) && $is_pro && $claude_files_compatible;
    // Image upload UI is enabled only for providers with image-analysis support.
    $upload_flags['image_upload_ui_enabled'] = ($core_flags['enable_image_upload_setting'] ?? false) && $is_image_upload_supported_provider;

    $upload_flags['input_action_button_enabled'] = $upload_flags['file_upload_ui_enabled'] ||
                                                 $upload_flags['image_upload_ui_enabled'];

    return $upload_flags;
}
