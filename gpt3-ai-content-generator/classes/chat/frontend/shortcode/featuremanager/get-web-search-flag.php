<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/shortcode/featuremanager/get-web-search-flag.php
// Status: NEW FILE

namespace WPAICG\Chat\Frontend\Shortcode\FeatureManagerMethods;

use function WPAICG\Core\Providers\OpenRouter\Methods\resolve_model_capabilities_logic;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Determines the 'allowWebSearchTool' feature flag.
 *
 * @param array $settings Bot settings array (needs 'provider').
 * @param bool $allow_openai_web_search_tool_setting Intermediate OpenAI flag value from core flags.
 * @param bool $allow_claude_web_search_tool_setting Intermediate Claude flag value from core flags.
 * @param bool $allow_openrouter_web_search_tool_setting Intermediate OpenRouter flag value from core flags.
 * @return array An array containing the 'allowWebSearchTool' flag.
 */
function get_web_search_flag_logic(
    array $settings,
    bool $allow_openai_web_search_tool_setting,
    bool $allow_claude_web_search_tool_setting,
    bool $allow_openrouter_web_search_tool_setting
): array {
    $provider = $settings['provider'] ?? 'OpenAI';
    $allow_web_search_tool = false;
    if ($provider === 'OpenAI') {
        $allow_web_search_tool = $allow_openai_web_search_tool_setting;
    } elseif ($provider === 'Claude') {
        $allow_web_search_tool = $allow_claude_web_search_tool_setting;
    } elseif ($provider === 'OpenRouter') {
        $allow_web_search_tool = $allow_openrouter_web_search_tool_setting;
        $model = isset($settings['model']) ? sanitize_text_field((string) $settings['model']) : '';
        if ($allow_web_search_tool && $model !== '') {
            $resolver_fn = 'WPAICG\\Core\\Providers\\OpenRouter\\Methods\\resolve_model_capabilities_logic';
            if (!function_exists($resolver_fn)) {
                $capability_file = WPAICG_PLUGIN_DIR . 'classes/core/providers/openrouter/capabilities.php';
                if (file_exists($capability_file)) {
                    require_once $capability_file;
                }
            }
            if (function_exists($resolver_fn)) {
                $capabilities = resolve_model_capabilities_logic($model);
                $allow_web_search_tool = !empty($capabilities['web_search_plugin']);
            }
        }
    }

    return [
        'allowWebSearchTool' => $allow_web_search_tool,
    ];
}
