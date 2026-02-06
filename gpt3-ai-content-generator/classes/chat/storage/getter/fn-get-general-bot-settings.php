<?php

// File: classes/chat/storage/getter/fn-get-general-bot-settings.php
// Status: MODIFIED

namespace WPAICG\Chat\Storage\GetterMethods;

use WPAICG\Chat\Storage\BotSettingsManager; // For default constants

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Retrieves general bot settings like greeting and instructions.
 * MODIFIED: Explicitly adds 'bot_id' and 'name' to the settings array.
 *
 * @param int $bot_id The ID of the bot post.
 * @param string $bot_name The name of the bot.
 * @param callable $get_meta_fn A function to retrieve post meta.
 * @return array Associative array of general settings.
 */
function get_general_bot_settings_logic(int $bot_id, string $bot_name, callable $get_meta_fn): array
{
    $settings = [];

    // Ensure BotSettingsManager is loaded for constants
    if (!class_exists(BotSettingsManager::class)) {
        $bsm_path = dirname(__DIR__) . '/class-aipkit_bot_settings_manager.php'; // Path relative to getter directory
        if (file_exists($bsm_path)) {
            require_once $bsm_path;
        }
    }

    // --- ADDED: Explicitly add bot_id and name ---
    $settings['bot_id'] = $bot_id;
    $settings['name'] = $bot_name;
    // --- END ADDED ---

    $default_greeting = __('Hello there!', 'gpt3-ai-content-generator');
    $default_subgreeting = __('How can I help you today?', 'gpt3-ai-content-generator');
    $settings['greeting'] = $get_meta_fn('_aipkit_greeting_message', $default_greeting);
    $settings['subgreeting'] = $get_meta_fn('_aipkit_subgreeting_message', $default_subgreeting);

    $default_instructions = __("You are a helpful AI Assistant. Please be friendly. Today's date is [date].", 'gpt3-ai-content-generator');
    $settings['instructions'] = $get_meta_fn('_aipkit_instructions', $default_instructions);

    return $settings;
}
