<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/moderation/AIPKit_OpenAI_Moderation_Checker.php
// Status: NEW FILE

namespace WPAICG\Core\Moderation;

use WPAICG\Lib\Addons\AIPKit_OpenAI_Moderation as ProOpenAIModerationFacade;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_OpenAI_Moderation_Checker
 *
 * Checks if OpenAI Moderation should be performed and, if so, calls the Pro addon helper.
 */
class AIPKit_OpenAI_Moderation_Checker {

    /**
     * Checks if text should be moderated by OpenAI and performs the check if applicable.
     *
     * @param string $text The text content to check.
     * @param array $bot_settings Current request settings/context (used for provider selection and moderation execution).
     * @return WP_Error|null WP_Error if flagged by OpenAI, null otherwise or if not applicable.
     */
    public static function check(string $text, array $bot_settings): ?WP_Error {
        // 1. Check if the Pro OpenAI Moderation Facade class exists
        if (!class_exists(ProOpenAIModerationFacade::class)) {
            // This means the Pro addon files (in /lib/) are not loaded. This is normal for free version.
            // No error_log needed here, as it's an expected state in free version.
            return null;
        }

        $global_moderation_settings = class_exists(AIPKit_Global_Security_Settings::class)
            ? AIPKit_Global_Security_Settings::get_openai_moderation_settings()
            : [
                'enabled' => '0',
                'message' => __('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator'),
            ];
        $moderation_settings = $bot_settings;
        $moderation_settings['openai_moderation_enabled'] = $global_moderation_settings['enabled'] ?? '0';
        $moderation_settings['openai_moderation_message'] = $global_moderation_settings['message']
            ?? __('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator');

        // 2. Require global OpenAI moderation to be enabled
        $moderation_enabled = $moderation_settings['openai_moderation_enabled'] ?? '0';
        if (!($moderation_enabled === '1' || $moderation_enabled === 1 || $moderation_enabled === true)) {
            return null;
        }

        // 3. Determine the AI provider for the current context
        $provider_from_bot = $moderation_settings['provider'] ?? null;
        $global_default_provider = null;
        if (class_exists(\WPAICG\AIPKit_Providers::class)) { // Ensure Providers class is loaded
            $global_default_provider = \WPAICG\AIPKit_Providers::get_current_provider();
        }
        $current_provider = $provider_from_bot ?: $global_default_provider;

        // 4. OpenAI Moderation only applies if the selected provider is OpenAI
        if ($current_provider !== 'OpenAI') {
            return null;
        }

        // 5. Use the Pro Facade's perform_moderation method.
        // This method internally calls ProOpenAIModerationFacade::is_required() and then the executor.
        $moderation_result = ProOpenAIModerationFacade::perform_moderation($text, $moderation_settings);

        // 6. Analyze the result from the Pro Facade:
        // - null: Moderation wasn't required by Pro Facade's internal checks, or an API error occurred (Pro Facade handles logging).
        // - false: Moderation check passed.
        // - string: Moderation flagged the message, and the string is the user-facing message.
        if (is_string($moderation_result)) {
            // Message was flagged by the Pro Facade. The result is the user-facing message.
            return new WP_Error('content_flagged_by_openai', $moderation_result, ['status' => 400]); // Bad Request
        }

        // If null (not required/API error) or false (passed), return null from this checker.
        return null;
    }
}
