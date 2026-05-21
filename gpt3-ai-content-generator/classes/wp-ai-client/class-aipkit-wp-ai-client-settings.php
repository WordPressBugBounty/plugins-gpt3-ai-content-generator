<?php

namespace WPAICG\WP_AI_Client;

use WPAICG\AIPKit_Providers;

if (!defined('ABSPATH')) {
    exit;
}

class AIPKit_WP_AI_Client_Settings
{
    public const OPTION_MODE = 'aipkit_wp_ai_client_gateway_mode';
    public const OPTION_BANNER_DISMISSED = 'aipkit_wp_ai_client_banner_dismissed';
    public const MODE_OBSERVE = 'observe';
    public const MODE_MANAGED = 'managed';

    private const PROVIDERS = [
        'openai' => [
            'aipkit_provider' => 'OpenAI',
            'name' => 'OpenAI',
            'description' => 'Text and image generation through AI Puffer.',
            'credentials_url' => 'https://platform.openai.com/api-keys',
            'supports_images' => true,
        ],
        'google' => [
            'aipkit_provider' => 'Google',
            'name' => 'Google',
            'description' => 'Text and image generation with Gemini and Imagen through AI Puffer.',
            'credentials_url' => 'https://aistudio.google.com/api-keys',
            'supports_images' => true,
        ],
        'anthropic' => [
            'aipkit_provider' => 'Claude',
            'name' => 'Anthropic',
            'description' => 'Text generation with Claude through AI Puffer.',
            'credentials_url' => 'https://console.anthropic.com/settings/keys',
            'supports_images' => false,
        ],
        'openrouter' => [
            'aipkit_provider' => 'OpenRouter',
            'name' => 'OpenRouter',
            'description' => 'Text and image generation through OpenRouter in AI Puffer.',
            'credentials_url' => 'https://openrouter.ai/keys',
            'supports_images' => true,
        ],
        'azure' => [
            'aipkit_provider' => 'Azure',
            'name' => 'Azure OpenAI',
            'description' => 'Text and image generation through Azure deployments in AI Puffer.',
            'credentials_url' => 'https://portal.azure.com/',
            'supports_images' => true,
        ],
        'deepseek' => [
            'aipkit_provider' => 'DeepSeek',
            'name' => 'DeepSeek',
            'description' => 'Text generation through DeepSeek in AI Puffer.',
            'credentials_url' => 'https://platform.deepseek.com/api_keys',
            'supports_images' => false,
        ],
        'xai' => [
            'aipkit_provider' => 'xAI',
            'name' => 'xAI',
            'description' => 'Text and image generation through xAI in AI Puffer.',
            'credentials_url' => 'https://console.x.ai/',
            'supports_images' => true,
        ],
        'ollama' => [
            'aipkit_provider' => 'Ollama',
            'name' => 'Ollama',
            'description' => 'Local text generation through Ollama in AI Puffer.',
            'credentials_url' => '',
            'supports_images' => false,
            'keyless' => true,
        ],
    ];

    public static function is_supported(): bool
    {
        return function_exists('wp_supports_ai')
            && wp_supports_ai()
            && class_exists('WP_Connector_Registry')
            && class_exists('\WordPress\AiClient\AiClient');
    }

    public static function get_mode(): string
    {
        $mode = get_option(self::OPTION_MODE, self::MODE_OBSERVE);
        $mode = is_string($mode) ? sanitize_key($mode) : self::MODE_OBSERVE;

        return in_array($mode, [self::MODE_OBSERVE, self::MODE_MANAGED], true)
            ? $mode
            : self::MODE_OBSERVE;
    }

    public static function set_mode(string $mode): void
    {
        $mode = sanitize_key($mode);
        if (!in_array($mode, [self::MODE_OBSERVE, self::MODE_MANAGED], true)) {
            $mode = self::MODE_OBSERVE;
        }

        update_option(self::OPTION_MODE, $mode, false);
    }

    public static function is_managed(): bool
    {
        return self::get_mode() === self::MODE_MANAGED;
    }

    public static function is_banner_dismissed(): bool
    {
        return get_option(self::OPTION_BANNER_DISMISSED, '0') === '1';
    }

    public static function set_banner_dismissed(bool $dismissed): void
    {
        update_option(self::OPTION_BANNER_DISMISSED, $dismissed ? '1' : '0', false);
    }

    public static function is_effectively_managed(): bool
    {
        return self::is_supported()
            && self::is_managed();
    }

    public static function providers(): array
    {
        return self::PROVIDERS;
    }

    public static function get_provider_config(string $connector_id): ?array
    {
        $connector_id = sanitize_key($connector_id);
        return self::PROVIDERS[$connector_id] ?? null;
    }

    public static function get_internal_provider(string $connector_id): ?string
    {
        $config = self::get_provider_config($connector_id);
        return $config['aipkit_provider'] ?? null;
    }

    public static function connector_option_name(string $connector_id): string
    {
        return 'connectors_ai_' . str_replace('-', '_', sanitize_key($connector_id)) . '_api_key';
    }

    public static function connector_constant_name(string $connector_id): string
    {
        $sanitized_id = str_replace('-', '_', sanitize_key($connector_id));
        return strtoupper((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', $sanitized_id)) . '_API_KEY';
    }

    public static function provider_has_credentials(string $connector_id): bool
    {
        $config = self::get_provider_config($connector_id);
        if (!$config) {
            return false;
        }

        if (!empty($config['keyless'])) {
            return true;
        }

        $internal_provider = $config['aipkit_provider'];
        if (!class_exists(AIPKit_Providers::class)) {
            return false;
        }

        $data = AIPKit_Providers::get_provider_data($internal_provider);
        if ($internal_provider === 'Azure') {
            return !empty($data['api_key']) && !empty($data['endpoint']);
        }

        return !empty($data['api_key']);
    }

    public static function sync_connector_key_to_provider(string $connector_id, string $api_key): bool
    {
        $config = self::get_provider_config($connector_id);
        if (!$config || !empty($config['keyless'])) {
            return false;
        }

        $provider = $config['aipkit_provider'];
        $opts = get_option('aipkit_options');
        if (!is_array($opts)) {
            $opts = [];
        }
        if (empty($opts['providers']) || !is_array($opts['providers'])) {
            $opts['providers'] = [];
        }
        if (empty($opts['providers'][$provider]) || !is_array($opts['providers'][$provider])) {
            $opts['providers'][$provider] = [];
        }

        $opts['providers'][$provider]['api_key'] = $api_key;
        update_option('aipkit_options', $opts, false);

        return true;
    }

    public static function sync_provider_keys_to_connectors(): void
    {
        if (!class_exists(AIPKit_Providers::class)) {
            return;
        }

        foreach (self::PROVIDERS as $connector_id => $config) {
            if (!empty($config['keyless'])) {
                continue;
            }
            $provider = $config['aipkit_provider'];
            $data = AIPKit_Providers::get_provider_data($provider);
            $api_key = isset($data['api_key']) ? trim((string) $data['api_key']) : '';
            if ($api_key === '') {
                continue;
            }

            $option_name = self::connector_option_name($connector_id);
            if (get_option($option_name, '') !== $api_key) {
                update_option($option_name, $api_key, false);
            }
        }
    }

}
