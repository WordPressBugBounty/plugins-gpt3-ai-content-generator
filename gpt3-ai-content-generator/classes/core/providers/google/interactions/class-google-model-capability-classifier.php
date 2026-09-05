<?php

namespace WPAICG\Core\Providers\Google\Interactions;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleModelCapabilityClassifier
{
    public const ROUTE_INTERACTIONS = 'interactions';
    public const ROUTE_EMBEDDINGS = 'embeddings';
    public const ROUTE_SPECIALIZED = 'specialized';
    public const ROUTE_UNSUPPORTED = 'unsupported';

    /**
     * File Search is model-specific; Interactions support alone is not enough.
     * Keep this list aligned with Google's File Search documentation.
     */
    private const FILE_SEARCH_MODELS = [
        'gemini-3.8-flash',
        'gemini-3.7-flash',
        'gemini-3.6-flash',
        'gemini-3.5-flash-lite',
        'gemini-3.5-flash',
        'gemini-3.1-pro-preview',
        'gemini-3.1-flash-lite',
        'gemini-3-flash-preview',
        'gemini-2.5-pro',
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
    ];

    /**
     * Classify a Google model without treating supportedGenerationMethods as an
     * Interactions capability declaration. Google's model list currently does
     * not advertise the Interactions endpoint there.
     *
     * @param array<string, mixed>|string $model Raw /models item or model ID.
     * @return array<string, mixed>
     */
    public static function classify($model): array
    {
        $raw_model = is_array($model) ? $model : [];
        $model_id = is_string($model)
            ? $model
            : (string) ($raw_model['raw_id'] ?? $raw_model['id'] ?? $raw_model['name'] ?? '');
        $model_id = self::normalize_model_id($model_id);
        $model_lower = strtolower($model_id);
        $raw_methods = $raw_model['supportedGenerationMethods'] ?? $raw_model['supported_generation_methods'] ?? [];
        $methods = is_array($raw_methods)
            ? array_values(array_filter($raw_methods, 'is_string'))
            : [];

        $classification = [
            'id' => $model_id,
            'family' => 'unknown',
            'route' => self::ROUTE_UNSUPPORTED,
            'api_version' => null,
            'supports_interactions' => false,
            'capabilities' => [],
            'supported_generation_methods' => $methods,
        ];

        if ($model_id === '') {
            return $classification;
        }

        if (strpos($model_lower, 'embedding') !== false || in_array('embedContent', $methods, true)) {
            return self::with($classification, 'embedding', self::ROUTE_EMBEDDINGS, null, ['embeddings']);
        }

        if (strpos($model_lower, 'veo-') === 0 || in_array('predictLongRunning', $methods, true)) {
            return self::with($classification, 'video_generation', self::ROUTE_SPECIALIZED, null, ['video_generation']);
        }

        if (strpos($model_lower, 'imagen-') === 0) {
            return self::with($classification, 'deprecated_image_generation', self::ROUTE_UNSUPPORTED, null, []);
        }

        if (strpos($model_lower, 'image') !== false || strpos($model_lower, 'nano-banana') === 0) {
            return self::with($classification, 'image_generation', self::ROUTE_INTERACTIONS, 'v1beta', ['image_generation', 'image_editing']);
        }

        if (strpos($model_lower, 'tts') !== false) {
            return self::with($classification, 'tts', self::ROUTE_INTERACTIONS, 'v1beta', ['audio_generation']);
        }

        if (strpos($model_lower, 'omni') !== false) {
            return self::with(
                $classification,
                'omni',
                self::ROUTE_INTERACTIONS,
                'v1beta',
                ['text_generation', 'multimodal_input', 'audio_input', 'multimodal_generation']
            );
        }

        if (strpos($model_lower, 'native-audio') !== false || in_array('bidiGenerateContent', $methods, true)) {
            return self::with($classification, 'live_audio', self::ROUTE_UNSUPPORTED, null, ['realtime_audio']);
        }

        if (strpos($model_lower, 'deep-research') !== false || strpos($model_lower, 'computer-use') !== false || strpos($model_lower, 'antigravity') !== false) {
            return self::with($classification, 'agent', self::ROUTE_UNSUPPORTED, null, ['agent']);
        }

        if (strpos($model_lower, 'robotics') !== false) {
            return self::with($classification, 'robotics', self::ROUTE_UNSUPPORTED, null, ['robotics']);
        }

        if (strpos($model_lower, 'lyria') !== false) {
            return self::with($classification, 'music_generation', self::ROUTE_UNSUPPORTED, null, ['audio_generation']);
        }

        if ($model_lower === 'aqa' || in_array('generateAnswer', $methods, true)) {
            return self::with($classification, 'answering', self::ROUTE_UNSUPPORTED, null, ['answering']);
        }

        if (strpos($model_lower, 'gemini-') === 0 || strpos($model_lower, 'gemma-') === 0) {
            $api_version = self::is_preview_model($model_lower) ? 'v1beta' : 'v1';
            $capabilities = ['text_generation', 'multimodal_input', 'streaming', 'google_search'];
            if (strpos($model_lower, 'gemini-') === 0) {
                $capabilities[] = 'audio_input';
            }
            if (self::supports_file_search($model_id)) {
                $capabilities[] = 'file_search';
            }
            return self::with(
                $classification,
                'text',
                self::ROUTE_INTERACTIONS,
                $api_version,
                $capabilities
            );
        }

        return $classification;
    }

    public static function supports_file_search(string $model_id): bool
    {
        $model_id = strtolower(self::normalize_model_id($model_id));
        return $model_id !== '' && in_array($model_id, self::get_file_search_models(), true);
    }

    /**
     * Whether the model can accept recorded audio through Interactions.
     *
     * @param array<string, mixed>|string $model Raw /models item or model ID.
     */
    public static function supports_audio_input($model): bool
    {
        $classification = self::classify($model);
        return $classification['supports_interactions'] === true
            && in_array('audio_input', $classification['capabilities'], true);
    }

    /**
     * @return array<int, string>
     */
    public static function get_file_search_models(): array
    {
        $models = apply_filters('aipkit_google_file_search_supported_models', self::FILE_SEARCH_MODELS);
        if (!is_array($models)) {
            $models = self::FILE_SEARCH_MODELS;
        }

        return array_values(array_unique(array_filter(array_map(
            static function ($model): string {
                return strtolower(self::normalize_model_id((string) $model));
            },
            $models
        ))));
    }

    /**
     * @param array<string, mixed> $classification
     * @param array<int, string>   $capabilities
     * @return array<string, mixed>
     */
    private static function with(array $classification, string $family, string $route, ?string $api_version, array $capabilities): array
    {
        $classification['family'] = $family;
        $classification['route'] = $route;
        $classification['api_version'] = $api_version;
        $classification['supports_interactions'] = $route === self::ROUTE_INTERACTIONS;
        $classification['capabilities'] = $capabilities;
        return $classification;
    }

    private static function normalize_model_id(string $model_id): string
    {
        $model_id = trim($model_id);
        if (strpos($model_id, 'models/') === 0) {
            $model_id = (string) substr($model_id, 7);
        }

        return trim($model_id);
    }

    private static function is_preview_model(string $model_id): bool
    {
        return strpos($model_id, 'preview') !== false || strpos($model_id, '-eap') !== false;
    }
}
