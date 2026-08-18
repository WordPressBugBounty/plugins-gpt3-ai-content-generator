<?php

namespace WPAICG\STT;

use WPAICG\AIPKit_Providers;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsSttAdapter;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gemini speech-to-text strategy backed by the Google Interactions API.
 */
class AIPKit_STT_Google_Provider_Strategy extends AIPKit_STT_Base_Provider_Strategy
{
    /**
     * @return string|WP_Error
     */
    public function transcribe_audio(string $audio_data, string $audio_format, array $api_params, array $options = [])
    {
        $api_key = isset($api_params['api_key']) ? trim((string) $api_params['api_key']) : '';
        if ($api_key === '') {
            return new WP_Error(
                'google_stt_missing_key',
                __('Google API Key is required for transcription.', 'gpt3-ai-content-generator'),
                ['status' => 400, 'status_code' => 400]
            );
        }

        $model = AIPKit_Providers::normalize_google_stt_model($options['stt_model'] ?? '');
        $result = GoogleInteractionsSttAdapter::transcribe(
            [
                'api_key' => $api_key,
                'base_url' => $api_params['base_url'] ?? '',
                'api_version' => 'v1beta',
                'timeout' => 120,
            ],
            $model,
            $audio_data,
            $audio_format,
            isset($options['language']) ? sanitize_text_field((string) $options['language']) : ''
        );
        if (is_wp_error($result)) {
            return $result;
        }

        return trim((string) ($result['content'] ?? ''));
    }

    public function get_supported_formats(): array
    {
        return GoogleInteractionsSttAdapter::supported_formats();
    }
}
