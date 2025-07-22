<?php
// File: classes/chat/storage/getter/fn-get-voice-agent-config.php

namespace WPAICG\Chat\Storage\GetterMethods;

use WPAICG\Chat\Storage\BotSettingsManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Retrieves Realtime Voice Agent configuration settings.
 *
 * @param int $bot_id The ID of the bot post.
 * @param callable $get_meta_fn A function to retrieve post meta.
 * @return array Associative array of voice agent settings.
 */
function get_voice_agent_config_logic(int $bot_id, callable $get_meta_fn): array
{
    $settings = [];

    // Ensure BotSettingsManager is loaded for constants, or provide fallbacks.
    if (!class_exists(BotSettingsManager::class)) {
        $bsm_path = dirname(__DIR__) . '/class-aipkit_bot_settings_manager.php';
        if (file_exists($bsm_path)) {
            require_once $bsm_path;
        }
    }

    $default_enable_realtime = '0';
    $default_realtime_model = 'gpt-4o-realtime-preview';
    $default_realtime_voice = 'alloy';
    $default_turn_detection = 'server_vad';
    $default_speed = 1.0;
    $default_input_audio_format = 'pcm16';
    $default_output_audio_format = 'pcm16';
    $default_noise_reduction = '1';

    $settings['enable_realtime_voice'] = $get_meta_fn('_aipkit_enable_realtime_voice', $default_enable_realtime);
    $settings['realtime_model'] = $get_meta_fn('_aipkit_realtime_model', $default_realtime_model);
    $settings['realtime_voice'] = $get_meta_fn('_aipkit_realtime_voice', $default_realtime_voice);
    $settings['turn_detection'] = $get_meta_fn('_aipkit_turn_detection', $default_turn_detection);
    $settings['speed'] = floatval($get_meta_fn('_aipkit_speed', $default_speed));
    $settings['input_audio_format'] = $get_meta_fn('_aipkit_input_audio_format', $default_input_audio_format);
    $settings['output_audio_format'] = $get_meta_fn('_aipkit_output_audio_format', $default_output_audio_format);
    $settings['input_audio_noise_reduction'] = $get_meta_fn('_aipkit_input_audio_noise_reduction', $default_noise_reduction);
    
    // Validate values to be safe
    if (!in_array($settings['realtime_model'], ['gpt-4o-realtime-preview', 'gpt-4o-mini-realtime'])) {
        $settings['realtime_model'] = $default_realtime_model;
    }
    if (!in_array($settings['realtime_voice'], ['alloy', 'ash', 'ballad', 'coral', 'echo', 'fable', 'onyx', 'nova', 'shimmer', 'verse'])) {
        $settings['realtime_voice'] = $default_realtime_voice;
    }
    if (!in_array($settings['turn_detection'], ['none', 'server_vad', 'semantic_vad'])) {
        $settings['turn_detection'] = $default_turn_detection;
    }
    $settings['speed'] = max(0.25, min(1.5, $settings['speed']));

    return $settings;
}