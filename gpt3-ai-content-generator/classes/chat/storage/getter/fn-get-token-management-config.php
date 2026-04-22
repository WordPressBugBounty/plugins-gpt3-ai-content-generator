<?php

// File: classes/chat/storage/getter/fn-get-token-management-config.php
// Status: NEW FILE

namespace WPAICG\Chat\Storage\GetterMethods;

use WPAICG\Chat\Storage\BotSettingsManager; // For default constants

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Retrieves token management configuration settings.
 *
 * @param int $bot_id The ID of the bot post.
 * @param callable $get_meta_fn A function to retrieve post meta.
 * @return array Associative array of token management settings.
 */
function get_token_management_config_logic(int $bot_id, callable $get_meta_fn): array
{
    $settings = [];

    if (!class_exists(BotSettingsManager::class)) {
        $bsm_path = dirname(__DIR__) . '/class-aipkit_bot_settings_manager.php';
        if (file_exists($bsm_path)) {
            require_once $bsm_path;
        }
    }

    $settings['token_limit_mode'] = $get_meta_fn('_aipkit_token_limit_mode', BotSettingsManager::DEFAULT_TOKEN_LIMIT_MODE);
    if (!in_array($settings['token_limit_mode'], ['general', 'role_based'])) {
        $settings['token_limit_mode'] = BotSettingsManager::DEFAULT_TOKEN_LIMIT_MODE;
    }

    $guest_limit_raw = $get_meta_fn('_aipkit_token_guest_limit', BotSettingsManager::DEFAULT_TOKEN_GUEST_LIMIT);
    $settings['token_guest_limit'] = ($guest_limit_raw === '') ? null : (($guest_limit_raw === '0') ? 0 : absint($guest_limit_raw));

    $user_limit_raw = $get_meta_fn('_aipkit_token_user_limit', BotSettingsManager::DEFAULT_TOKEN_USER_LIMIT);
    $settings['token_user_limit'] = ($user_limit_raw === '') ? null : (($user_limit_raw === '0') ? 0 : absint($user_limit_raw));

    $role_limits_json = $get_meta_fn('_aipkit_token_role_limits', '[]');
    $decoded_roles = json_decode($role_limits_json, true);
    $settings['token_role_limits'] = is_array($decoded_roles) ? $decoded_roles : [];

    $settings['token_reset_period'] = $get_meta_fn('_aipkit_token_reset_period', BotSettingsManager::DEFAULT_TOKEN_RESET_PERIOD);
    if (!in_array($settings['token_reset_period'], ['never', 'daily', 'weekly', 'monthly'])) {
        $settings['token_reset_period'] = BotSettingsManager::DEFAULT_TOKEN_RESET_PERIOD;
    }

    $default_limit_message = class_exists(BotSettingsManager::class)
        ? BotSettingsManager::get_default_token_limit_message()
        : __('You have reached your quota for this period.', 'gpt3-ai-content-generator');
    $settings['token_limit_message'] = $get_meta_fn('_aipkit_token_limit_message', $default_limit_message);
    if (empty($settings['token_limit_message'])) {
        $settings['token_limit_message'] = $default_limit_message;
    }

    $valid_action_types = class_exists(BotSettingsManager::class)
        ? BotSettingsManager::get_token_limit_action_types()
        : ['none', 'dashboard_usage', 'dashboard_credits', 'dashboard_purchases', 'buy_credits', 'custom_url'];
    $default_action_settings = class_exists(BotSettingsManager::class)
        ? BotSettingsManager::get_default_token_limit_action_settings()
        : [
            'primary_type' => 'dashboard_usage',
            'primary_label' => __('View usage', 'gpt3-ai-content-generator'),
            'primary_url' => '',
            'secondary_type' => 'buy_credits',
            'secondary_label' => __('Buy credits', 'gpt3-ai-content-generator'),
            'secondary_url' => '',
        ];

    $settings['token_limit_primary_action_type'] = (string) $get_meta_fn(
        '_aipkit_token_limit_primary_action_type',
        $default_action_settings['primary_type']
    );
    if (!in_array($settings['token_limit_primary_action_type'], $valid_action_types, true)) {
        $settings['token_limit_primary_action_type'] = $default_action_settings['primary_type'];
    }
    $settings['token_limit_primary_action_label'] = trim((string) $get_meta_fn(
        '_aipkit_token_limit_primary_action_label',
        $default_action_settings['primary_label']
    ));
    if ($settings['token_limit_primary_action_type'] === 'none') {
        $settings['token_limit_primary_action_label'] = '';
    } elseif ($settings['token_limit_primary_action_label'] === '') {
        $settings['token_limit_primary_action_label'] = class_exists(BotSettingsManager::class)
            ? BotSettingsManager::get_token_limit_action_default_label($settings['token_limit_primary_action_type'])
            : $default_action_settings['primary_label'];
    }
    $settings['token_limit_primary_action_url'] = esc_url_raw((string) $get_meta_fn(
        '_aipkit_token_limit_primary_action_url',
        $default_action_settings['primary_url']
    ));

    $settings['token_limit_secondary_action_type'] = (string) $get_meta_fn(
        '_aipkit_token_limit_secondary_action_type',
        $default_action_settings['secondary_type']
    );
    if (!in_array($settings['token_limit_secondary_action_type'], $valid_action_types, true)) {
        $settings['token_limit_secondary_action_type'] = $default_action_settings['secondary_type'];
    }
    $settings['token_limit_secondary_action_label'] = trim((string) $get_meta_fn(
        '_aipkit_token_limit_secondary_action_label',
        $default_action_settings['secondary_label']
    ));
    if ($settings['token_limit_secondary_action_type'] === 'none') {
        $settings['token_limit_secondary_action_label'] = '';
    } elseif ($settings['token_limit_secondary_action_label'] === '') {
        $settings['token_limit_secondary_action_label'] = class_exists(BotSettingsManager::class)
            ? BotSettingsManager::get_token_limit_action_default_label($settings['token_limit_secondary_action_type'])
            : $default_action_settings['secondary_label'];
    }
    $settings['token_limit_secondary_action_url'] = esc_url_raw((string) $get_meta_fn(
        '_aipkit_token_limit_secondary_action_url',
        $default_action_settings['secondary_url']
    ));

    return $settings;
}
