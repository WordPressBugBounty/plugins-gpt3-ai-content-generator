<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/asset_registrars/class-chat-core-asset-registrar.php
// Status: MODIFIED

namespace WPAICG\Chat\Frontend\AssetRegistrars;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Chat_Core_Asset_Registrar
{
    public static function register(string $version, string $plugin_base_url, string $plugin_dir, string $public_js_url): array
    {
        $core_handles = [];
        $public_chat_utils_js_url = $public_js_url . 'chat/utils/';

        // These handles represent chat core modules bundled into public-main.
        $chat_util_scripts = [
            'auto-resize-textarea'       => ['aipkit-chat-util-auto-resize', $public_chat_utils_js_url . 'auto-resize-textarea.js', []],
            'generate-client-message-id' => ['aipkit-chat-util-gen-id', $public_chat_utils_js_url . 'generate-client-message-id.js', []],
            'toggle-web-search'          => ['aipkit-chat-util-toggle-web-search', $public_chat_utils_js_url . 'toggle-web-search.js', []],
            'toggle-google-grounding'    => ['aipkit-chat-util-toggle-google-grounding', $public_chat_utils_js_url . 'toggle-google-grounding.js', []],
        ];
        foreach ($chat_util_scripts as $key => $script_data) {
            list($handle, $path, $deps) = $script_data;
            $core_handles[$key] = $handle;
        }

        $apply_theme_handle = 'aipkit-chat-ui-apply-custom-theme';
        $core_handles['apply-custom-theme'] = $apply_theme_handle;

        $markdown_initiate_handle = 'aipkit-markdown-initiate';
        $core_handles['markdown-initiate'] = $markdown_initiate_handle;

        return $core_handles;
    }
}
