<?php

namespace WPAICG\WP_AI_Client;

if (!defined('ABSPATH')) {
    exit;
}

class AIPKit_WP_AI_Client_WordPress_AI_Compatibility
{
    private static bool $registered = false;

    public static function register_hooks(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        add_filter('wpai_preferred_text_models', [self::class, 'prefer_default_text_model'], 1000);
        add_filter('wpai_preferred_image_models', [self::class, 'prefer_default_image_model'], 1000);
        add_filter('wpai_preferred_vision_models', [self::class, 'prefer_default_vision_model'], 1000);
    }

    public static function prefer_default_text_model(array $preferred_models): array
    {
        return self::prepend_route_alias($preferred_models, AIPKit_WP_AI_Client_Routes::MODEL_TEXT);
    }

    public static function prefer_default_image_model(array $preferred_models): array
    {
        return self::prepend_route_alias($preferred_models, AIPKit_WP_AI_Client_Routes::MODEL_IMAGE);
    }

    public static function prefer_default_vision_model(array $preferred_models): array
    {
        if (!self::should_filter() || !AIPKit_WP_AI_Client_Routes::model_alias_accepts_image_input(AIPKit_WP_AI_Client_Routes::MODEL_TEXT)) {
            return $preferred_models;
        }

        return self::prepend_route_alias($preferred_models, AIPKit_WP_AI_Client_Routes::MODEL_TEXT);
    }

    private static function prepend_route_alias(array $preferred_models, string $model_alias): array
    {
        if (!self::should_filter() || !self::route_alias_is_available($model_alias)) {
            return $preferred_models;
        }

        return self::prepend_preferences($preferred_models, [
            [AIPKit_WP_AI_Client_Routes::PROVIDER_ID, $model_alias],
        ]);
    }

    private static function should_filter(): bool
    {
        return class_exists(AIPKit_WP_AI_Client_Settings::class)
            && class_exists(AIPKit_WP_AI_Client_Routes::class)
            && AIPKit_WP_AI_Client_Settings::is_effectively_managed();
    }

    private static function route_alias_is_available(string $model_alias): bool
    {
        try {
            AIPKit_WP_AI_Client_Routes::get_alias_model_metadata($model_alias);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function prepend_preferences(array $preferred_models, array $new_preferences): array
    {
        $merged = [];
        $seen = [];

        foreach (array_merge($new_preferences, $preferred_models) as $preference) {
            $key = self::preference_key($preference);
            if ($key !== '' && isset($seen[$key])) {
                continue;
            }
            if ($key !== '') {
                $seen[$key] = true;
            }
            $merged[] = $preference;
        }

        return $merged;
    }

    private static function preference_key($preference): string
    {
        if (is_array($preference) && count($preference) === 2) {
            $provider = sanitize_key((string) $preference[0]);
            $model = sanitize_text_field((string) $preference[1]);

            return $provider . '|' . $model;
        }

        if (is_string($preference)) {
            return 'model|' . sanitize_text_field($preference);
        }

        if (is_object($preference)) {
            return 'object|' . spl_object_hash($preference);
        }

        return '';
    }
}
