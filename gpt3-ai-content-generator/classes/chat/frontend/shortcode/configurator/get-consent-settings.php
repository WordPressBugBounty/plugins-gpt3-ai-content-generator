<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/shortcode/configurator/get-consent-settings.php
// Status: NEW FILE

namespace WPAICG\Chat\Frontend\Shortcode\ConfiguratorMethods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Prepares the consent-related text fields.
 *
 * @param array $settings Bot settings.
 * @return array An array containing consent_title, consent_message, and consent_button texts.
 */
function get_consent_settings_logic(array $settings): array {
    $default_title = __('Consent Required', 'gpt3-ai-content-generator');
    $default_message = __('Before starting the conversation, please agree to our Terms of Service and Privacy Policy.', 'gpt3-ai-content-generator');
    $default_button = __('I Agree', 'gpt3-ai-content-generator');

    $consent_title = $settings['consent_title'] ?? '';
    $consent_message = $settings['consent_message'] ?? '';
    $consent_button = $settings['consent_button'] ?? '';

    return [
        'consent_title' => $consent_title !== '' ? $consent_title : $default_title,
        'consent_message' => $consent_message !== '' ? $consent_message : $default_message,
        'consent_button' => $consent_button !== '' ? $consent_button : $default_button,
    ];
}
