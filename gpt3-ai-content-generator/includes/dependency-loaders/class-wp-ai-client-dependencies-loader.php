<?php

namespace WPAICG\Includes\DependencyLoaders;

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Client_Dependencies_Loader
{
    public static function load(): void
    {
        $base_path = WPAICG_PLUGIN_DIR . 'classes/wp-ai-client/';

        $settings_path = $base_path . 'class-aipkit-wp-ai-client-settings.php';
        if (file_exists($settings_path)) {
            require_once $settings_path;
        }

        if (!class_exists(\WPAICG\WP_AI_Client\AIPKit_WP_AI_Client_Settings::class)
            || !\WPAICG\WP_AI_Client\AIPKit_WP_AI_Client_Settings::is_supported()
        ) {
            return;
        }

        $wp_ai_files = [
            'class-aipkit-wp-ai-client-connectors.php',
            'class-aipkit-wp-ai-client-availability.php',
            'class-aipkit-wp-ai-client-model-directory.php',
            'class-aipkit-wp-ai-client-routes.php',
            'class-aipkit-wp-ai-client-approval-compatibility.php',
            'class-aipkit-wp-ai-client-gateway-model.php',
            'class-aipkit-wp-ai-client-providers.php',
            'class-aipkit-wp-ai-client-gateway.php',
            'class-aipkit-wp-ai-client-wordpress-ai-compatibility.php',
        ];

        foreach ($wp_ai_files as $file) {
            $path = $base_path . $file;
            if (file_exists($path)) {
                require_once $path;
            }
        }

        $integration_path = $base_path . 'class-aipkit-wp-ai-client-integration.php';
        if (file_exists($integration_path)) {
            require_once $integration_path;
        }

        if (class_exists(\WPAICG\WP_AI_Client\AIPKit_WP_AI_Client_Integration::class)) {
            \WPAICG\WP_AI_Client\AIPKit_WP_AI_Client_Integration::register_hooks();
        }
    }
}
