<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/storage/getter/fn-get-trigger-config.php
// Status: MODIFIED

namespace WPAICG\Chat\Storage\GetterMethods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Retrieves trigger configuration for a bot.
 * Returns the raw JSON string from post meta, or an empty array string '[]' if not set or invalid.
 *
 * @param int $bot_id The ID of the bot post.
 * @param callable $get_meta_fn A function to retrieve post meta (used to get the trigger meta key).
 * @return array Associative array containing 'triggers_json'.
 */
function get_trigger_config_logic(int $bot_id, callable $get_meta_fn): array
{
    $settings = [];

    // --- MODIFIED: Conditional access to Trigger Storage META_KEY ---
    $trigger_meta_key = '_aipkit_chatbot_triggers'; // Fallback key
    $trigger_storage_class_name = '\WPAICG\Lib\Chat\Triggers\AIPKit_Trigger_Storage'; // New Pro location/namespace

    if (class_exists($trigger_storage_class_name)) {
        $trigger_meta_key = $trigger_storage_class_name::META_KEY;
    }
    // --- END MODIFICATION ---

    // Use the passed $get_meta_fn to retrieve the value for the determined meta key
    // Default to an empty JSON array string if the meta key is not found or is empty.
    $triggers_json_string = $get_meta_fn($trigger_meta_key, '[]');

    // Ensure $triggers_json_string is a string before trying to decode
    if (!is_string($triggers_json_string) || trim($triggers_json_string) === '') {
        $triggers_json_string = '[]';
    }

    $decoded = json_decode($triggers_json_string, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        // Attempt a targeted repair for unescaped quotes in body_template (common cause of invalid JSON).
        $repaired = preg_replace_callback(
            '/("body_template"\s*:\s*")(.+?)(")\s*,\s*"timeout_seconds"/s',
            static function (array $matches): string {
                $value = $matches[2];
                $value = str_replace('\\', '\\\\', $value);
                $value = str_replace('"', '\\"', $value);
                return $matches[1] . $value . $matches[3] . ', "timeout_seconds"';
            },
            $triggers_json_string,
            -1,
            $repair_count
        );
        if (!empty($repair_count)) {
            $decoded = json_decode($repaired, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $triggers_json_string = $repaired;
            }
        }
    }

    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $settings['triggers_json'] = wp_json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        $settings['triggers_json'] = '[]';
    }
    return $settings;
}
