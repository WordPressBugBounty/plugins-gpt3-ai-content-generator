<?php

namespace WPAICG\ContentWriter\SEO;

use WPAICG\aipkit_dashboard;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalizes and gates Content Writer SEO improvement settings.
 */
class AIPKit_Content_Writer_SEO_Config
{
    public const KEY_ENABLED = 'seo_score_improvement_enabled';
    public const KEY_CONTINUE = 'seo_score_continue_until_target';
    public const KEY_TARGET = 'seo_score_target';
    public const KEY_MAX_PASSES = 'seo_score_max_passes';
    public const KEY_PROFILE = 'seo_score_profile';

    public static function is_pro_plan(): bool
    {
        return class_exists(aipkit_dashboard::class) && aipkit_dashboard::is_pro_plan();
    }

    public static function normalize(array $config, bool $enforce_plan = true, bool $add_defaults = true): array
    {
        $has_seo_config = self::has_any_seo_config($config);
        if (!$add_defaults && !$has_seo_config) {
            return $config;
        }

        if ($add_defaults || array_key_exists(self::KEY_ENABLED, $config)) {
            $config[self::KEY_ENABLED] = self::normalize_binary($config[self::KEY_ENABLED] ?? '0', '0');
        }

        if ($add_defaults || $has_seo_config) {
            $config[self::KEY_CONTINUE] = '1';
            $config[self::KEY_TARGET] = '100';
            $config[self::KEY_MAX_PASSES] = '3';
            $config[self::KEY_PROFILE] = 'auto';
        }

        if ($enforce_plan && !self::is_pro_plan() && ($add_defaults || array_key_exists(self::KEY_ENABLED, $config))) {
            $config[self::KEY_ENABLED] = '0';
        }

        return $config;
    }

    public static function require_pro_for_improvement(array $config): bool|WP_Error
    {
        if (!self::is_enabled($config) || self::is_pro_plan()) {
            return true;
        }

        return new WP_Error(
            'pro_feature_required',
            self::message('Continuous SEO score improvement is a Pro feature. Please upgrade.'),
            ['status' => 403]
        );
    }

    public static function is_enabled(array $config): bool
    {
        return self::normalize_binary($config[self::KEY_ENABLED] ?? '0', '0') === '1';
    }

    private static function has_any_seo_config(array $config): bool
    {
        foreach ([self::KEY_ENABLED, self::KEY_CONTINUE, self::KEY_TARGET, self::KEY_MAX_PASSES, self::KEY_PROFILE] as $key) {
            if (array_key_exists($key, $config)) {
                return true;
            }
        }

        return false;
    }

    private static function normalize_binary(mixed $value, string $default): string
    {
        if ($value === '1' || $value === 1 || $value === true) {
            return '1';
        }

        if ($value === '0' || $value === 0 || $value === false) {
            return '0';
        }

        return $default;
    }

    private static function message(string $text): string
    {
        return function_exists('__') ? __($text, 'gpt3-ai-content-generator') : $text;
    }
}
