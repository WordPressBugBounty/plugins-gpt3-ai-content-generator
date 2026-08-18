<?php

namespace WPAICG\Core\Providers\Google\Interactions;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleInteractionsTtsAdapter
{
    /**
     * Generate one audio interaction. A single retry covers Google's documented
     * rare preview-model 500 response without retrying client errors.
     *
     * @param array<string, mixed> $connection Google connection values.
     * @return array<string, mixed>|WP_Error
     */
    public static function generate(array $connection, string $model, string $text, string $voice)
    {
        $connection['api_version'] = 'v1beta';
        $request_options = [
            'store' => false,
            'response_format' => ['type' => 'audio'],
            'generation_config' => [
                'speech_config' => [
                    ['voice' => $voice],
                ],
            ],
        ];
        $input = "Read the following transcript aloud exactly as written. Do not add, remove, or describe anything.\n\n### TRANSCRIPT\n" . $text;
        $client = new GoogleInteractionsClient();

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $result = $client->create($connection, $model, $input, $request_options);
            if (!is_wp_error($result)) {
                return GoogleInteractionsResponseParser::require_audio_result($result);
            }

            $error_data = $result->get_error_data();
            $status = is_array($error_data)
                ? (int) ($error_data['status_code'] ?? $error_data['status'] ?? 0)
                : 0;
            if ($attempt === 1 || $status < 500 || $status >= 600) {
                return $result;
            }
        }

        return new WP_Error(
            'google_interactions_tts_failed',
            __('Google text-to-speech failed.', 'gpt3-ai-content-generator')
        );
    }
}
