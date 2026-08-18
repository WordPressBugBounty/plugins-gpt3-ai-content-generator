<?php

namespace WPAICG\Speech;

use WPAICG\AIPKit_Providers;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsTtsAdapter;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gemini text-to-speech strategy backed by the Google Interactions API.
 */
class AIPKit_TTS_Google_Provider_Strategy extends AIPKit_TTS_Base_Provider_Strategy
{
    private const SAMPLE_RATE = 24000;
    private const CHANNELS = 1;
    private const BITS_PER_SAMPLE = 16;

    /**
     * @return string|WP_Error Base64-encoded WAV audio.
     */
    public function generate_speech(string $text, array $api_params, array $options)
    {
        $api_key = isset($api_params['api_key']) ? trim((string) $api_params['api_key']) : '';
        if ($api_key === '') {
            return new WP_Error(
                'google_tts_missing_key',
                __('Google API Key is required for speech generation.', 'gpt3-ai-content-generator')
            );
        }
        if (trim($text) === '') {
            return new WP_Error(
                'google_tts_empty_text',
                __('Text cannot be empty for speech generation.', 'gpt3-ai-content-generator')
            );
        }

        $model = AIPKit_Providers::normalize_google_tts_model($options['model_id'] ?? '');
        $voice = AIPKit_Providers::normalize_google_tts_voice($options['voice'] ?? '');
        $result = GoogleInteractionsTtsAdapter::generate(
            [
                'api_key' => $api_key,
                'base_url' => $api_params['base_url'] ?? '',
                'api_version' => 'v1beta',
                'timeout' => 120,
            ],
            $model,
            $text,
            $voice
        );
        if (is_wp_error($result)) {
            return $result;
        }

        $audio = $result['audio_outputs'][0] ?? [];
        $encoded_audio = is_array($audio) && isset($audio['data'])
            ? trim((string) $audio['data'])
            : '';
        $raw_audio = $encoded_audio !== '' ? base64_decode($encoded_audio, true) : false;
        if ($raw_audio === false || $raw_audio === '') {
            return new WP_Error(
                'google_tts_invalid_audio',
                __('Google returned invalid speech audio data.', 'gpt3-ai-content-generator'),
                ['status' => 502, 'status_code' => 502]
            );
        }

        if (substr($raw_audio, 0, 4) === 'RIFF' && substr($raw_audio, 8, 4) === 'WAVE') {
            return $encoded_audio;
        }

        return base64_encode(self::pcm_to_wav($raw_audio));
    }

    /**
     * Gemini voices are a documented static catalog and do not require a sync request.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_voices(array $api_params)
    {
        return AIPKit_Providers::get_google_tts_voices();
    }

    public function get_supported_formats(): array
    {
        return ['wav'];
    }

    private static function pcm_to_wav(string $pcm): string
    {
        $data_length = strlen($pcm);
        $block_align = self::CHANNELS * (self::BITS_PER_SAMPLE / 8);
        $byte_rate = self::SAMPLE_RATE * $block_align;

        return 'RIFF'
            . pack('V', 36 + $data_length)
            . 'WAVEfmt '
            . pack(
                'VvvVVvv',
                16,
                1,
                self::CHANNELS,
                self::SAMPLE_RATE,
                $byte_rate,
                $block_align,
                self::BITS_PER_SAMPLE
            )
            . 'data'
            . pack('V', $data_length)
            . $pcm;
    }
}
