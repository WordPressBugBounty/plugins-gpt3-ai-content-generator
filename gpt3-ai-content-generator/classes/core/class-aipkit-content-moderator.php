<?php

namespace WPAICG\Core;

// Use statements for the new checker components
use WPAICG\Core\Moderation\AIPKit_BannedIP_Checker;
use WPAICG\Core\Moderation\AIPKit_BannedWords_Checker;
use WPAICG\Core\Moderation\AIPKit_OpenAI_Moderation_Checker;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Content_Moderator (Facade)
 *
 * Centralized class for handling content moderation checks.
 * Delegates specific checks to specialized checker classes.
 */
class AIPKit_Content_Moderator {
    /**
     * Checks the provided text and context against configured moderation rules.
     *
     * @param string $text The text content to check (e.g., user message).
     * @param array $context Associative array containing context information.
     *                      Expected keys:
     *                      - 'client_ip': (string) The IP address of the user making the request.
     *                      - 'bot_settings': (array) Settings of the current bot (needed for OpenAI provider check).
     *                      - 'banned_ips_settings': (array) Optional override for banned IP settings.
     *                      - 'banned_words_settings': (array) Optional override for banned words settings.
     *                      - 'skip_banned_checks': (bool) Optional flag to skip banned words/IP checks.
     * @return WP_Error|null Returns a WP_Error if the content is flagged (with user-facing message),
     *                       or null if the content passes all checks or moderation is not applicable/failed internally.
     */
    public static function check_content(string $text, array $context = []): ?WP_Error {
        $client_ip = $context['client_ip'] ?? null;
        $bot_settings = $context['bot_settings'] ?? [];
        $skip_banned_checks = !empty($context['skip_banned_checks']);

        if (!$skip_banned_checks) {
            $banned_ips_settings = $context['banned_ips_settings']
                ?? [
                    'ips' => $bot_settings['banned_ips'] ?? '',
                    'message' => $bot_settings['banned_ips_message'] ?? '',
                ];
            if (!is_array($banned_ips_settings)) {
                $banned_ips_settings = ['ips' => '', 'message' => ''];
            }

            $ip_check_result = AIPKit_BannedIP_Checker::check($client_ip, $banned_ips_settings);
            if (is_wp_error($ip_check_result)) {
                return $ip_check_result;
            }

            $banned_words_settings = $context['banned_words_settings']
                ?? [
                    'words' => $bot_settings['banned_words'] ?? '',
                    'message' => $bot_settings['banned_words_message'] ?? '',
                ];
            if (!is_array($banned_words_settings)) {
                $banned_words_settings = ['words' => '', 'message' => ''];
            }

            $words_check_result = AIPKit_BannedWords_Checker::check($text, $banned_words_settings);
            if (is_wp_error($words_check_result)) {
                return $words_check_result;
            }
        }

        // OpenAI Moderation API Check (delegates to a checker that uses the Pro Addon Helper)
        $openai_mod_check_result = AIPKit_OpenAI_Moderation_Checker::check($text, $bot_settings);
        if (is_wp_error($openai_mod_check_result)) {
            return $openai_mod_check_result;
        }

        // All checks passed
        return null;
    }
}
