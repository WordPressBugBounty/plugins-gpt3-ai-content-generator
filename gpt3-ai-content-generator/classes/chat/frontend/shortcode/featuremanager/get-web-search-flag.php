<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/shortcode/featuremanager/get-web-search-flag.php
// Status: NEW FILE

namespace WPAICG\Chat\Frontend\Shortcode\FeatureManagerMethods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Determines the 'allowWebSearchTool' feature flag.
 *
 * @param array $settings Bot settings array (needs 'provider').
 * @param bool $allow_openai_web_search_tool_setting Intermediate OpenAI flag value from core flags.
 * @param bool $allow_claude_web_search_tool_setting Intermediate Claude flag value from core flags.
 * @return array An array containing the 'allowWebSearchTool' flag.
 */
function get_web_search_flag_logic(
    array $settings,
    bool $allow_openai_web_search_tool_setting,
    bool $allow_claude_web_search_tool_setting
): array {
    $provider = $settings['provider'] ?? 'OpenAI';
    $allow_web_search_tool = false;
    if ($provider === 'OpenAI') {
        $allow_web_search_tool = $allow_openai_web_search_tool_setting;
    } elseif ($provider === 'Claude') {
        $allow_web_search_tool = $allow_claude_web_search_tool_setting;
    }

    return [
        'allowWebSearchTool' => $allow_web_search_tool,
    ];
}
