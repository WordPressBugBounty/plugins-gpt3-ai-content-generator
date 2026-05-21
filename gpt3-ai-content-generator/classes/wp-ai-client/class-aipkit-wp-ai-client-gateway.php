<?php

namespace WPAICG\WP_AI_Client;

use WordPress\AiClient\AiClient;

if (!defined('ABSPATH')) {
    exit;
}

class AIPKit_WP_AI_Client_Gateway
{
    private const PROVIDER_CLASSES = [
        'aipuffer' => AIPKit_WP_AI_Client_Provider_AIPuffer::class,
        'openai' => AIPKit_WP_AI_Client_Provider_OpenAI::class,
        'google' => AIPKit_WP_AI_Client_Provider_Google::class,
        'anthropic' => AIPKit_WP_AI_Client_Provider_Anthropic::class,
        'openrouter' => AIPKit_WP_AI_Client_Provider_OpenRouter::class,
        'azure' => AIPKit_WP_AI_Client_Provider_Azure::class,
        'deepseek' => AIPKit_WP_AI_Client_Provider_DeepSeek::class,
        'xai' => AIPKit_WP_AI_Client_Provider_xAI::class,
        'ollama' => AIPKit_WP_AI_Client_Provider_Ollama::class,
    ];

    public static function register_hooks(): void
    {
        add_action('init', [self::class, 'register_providers'], 1000);
    }

    public static function register_providers(): void
    {
        if (!AIPKit_WP_AI_Client_Settings::is_effectively_managed()) {
            return;
        }

        try {
            $registry = AiClient::defaultRegistry();
        } catch (\Throwable $e) {
            return;
        }

        foreach (self::PROVIDER_CLASSES as $provider_id => $class_name) {
            if (!class_exists($class_name)) {
                continue;
            }

            try {
                self::replace_provider_if_registered($registry, $provider_id, $class_name);
                $registry->registerProvider($class_name);
            } catch (\Throwable $e) {
                continue;
            }
        }
    }

    private static function replace_provider_if_registered($registry, string $provider_id, string $class_name): void
    {
        if (!method_exists($registry, 'hasProvider') || !$registry->hasProvider($provider_id)) {
            return;
        }

        try {
            $existing_class = $registry->getProviderClassName($provider_id);
        } catch (\Throwable $e) {
            return;
        }

        if ($existing_class === $class_name) {
            return;
        }

        try {
            $reflection = new \ReflectionObject($registry);
            foreach (['registeredIdsToClassNames', 'registeredClassNamesToIds', 'providerAuthenticationInstances'] as $property_name) {
                if (!$reflection->hasProperty($property_name)) {
                    return;
                }
            }

            $ids_property = $reflection->getProperty('registeredIdsToClassNames');
            $classes_property = $reflection->getProperty('registeredClassNamesToIds');
            $auth_property = $reflection->getProperty('providerAuthenticationInstances');
            if (PHP_VERSION_ID < 80100) {
                $ids_property->setAccessible(true);
                $classes_property->setAccessible(true);
                $auth_property->setAccessible(true);
            }

            $ids = $ids_property->getValue($registry);
            $classes = $classes_property->getValue($registry);
            $auth = $auth_property->getValue($registry);

            unset($ids[$provider_id], $classes[$existing_class], $auth[$existing_class]);

            $ids_property->setValue($registry, $ids);
            $classes_property->setValue($registry, $classes);
            $auth_property->setValue($registry, $auth);
        } catch (\Throwable $e) {
            // If Core internals change, registerProvider() can still override by ID.
        }
    }
}
