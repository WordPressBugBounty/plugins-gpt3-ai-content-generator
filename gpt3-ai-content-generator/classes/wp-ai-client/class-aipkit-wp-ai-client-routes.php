<?php

namespace WPAICG\WP_AI_Client;

use WPAICG\AIPKit_Providers;
use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;

if (!defined('ABSPATH')) {
    exit;
}

class AIPKit_WP_AI_Client_Routes
{
    public const PROVIDER_ID = 'aipuffer';

    public const ROUTE_TEXT = 'text_generation';
    public const ROUTE_FAST_TEXT = 'fast_text_generation';
    public const ROUTE_IMAGE = 'image_generation';

    public const MODEL_TEXT = 'aipuffer-default-text';
    public const MODEL_FAST_TEXT = 'aipuffer-fast-text';
    public const MODEL_IMAGE = 'aipuffer-default-image';

    private const ROUTE_MODELS = [
        self::MODEL_TEXT => self::ROUTE_TEXT,
        self::MODEL_FAST_TEXT => self::ROUTE_FAST_TEXT,
        self::MODEL_IMAGE => self::ROUTE_IMAGE,
    ];

    public static function get_defaults(): array
    {
        return self::inferred_defaults();
    }

    public static function get_route(string $route): array
    {
        $defaults = self::get_defaults();
        return $defaults[$route] ?? ['provider' => '', 'model' => ''];
    }

    public static function resolve_model_alias(string $model_id): ?array
    {
        $route = self::ROUTE_MODELS[$model_id] ?? '';
        if ($route === '') {
            return null;
        }

        $setting = self::get_route($route);
        return self::normalize_resolved_route($route, $setting);
    }

    public static function available_alias_models(): array
    {
        $models = [];
        foreach (self::ROUTE_MODELS as $alias => $route) {
            $resolved = self::resolve_model_alias($alias);
            if ($resolved === null || !self::route_has_credentials($resolved)) {
                continue;
            }

            $models[] = self::alias_metadata($alias, $route, $resolved);
        }

        return $models;
    }

    public static function get_alias_model_metadata(string $model_id): ModelMetadata
    {
        foreach (self::available_alias_models() as $model_metadata) {
            if ($model_metadata->getId() === $model_id) {
                return $model_metadata;
            }
        }

        throw new InvalidArgumentException(sprintf('Unknown AI Puffer route model "%s".', esc_html($model_id)));
    }

    public static function actual_model_metadata(array $resolved_route): ModelMetadata
    {
        $connector_id = sanitize_key((string) ($resolved_route['provider'] ?? ''));
        $model_id = sanitize_text_field((string) ($resolved_route['model'] ?? ''));
        $internal_provider = AIPKit_WP_AI_Client_Settings::get_internal_provider($connector_id) ?: '';

        if ($connector_id !== '' && $model_id !== '' && $internal_provider !== '') {
            try {
                $directory = new AIPKit_WP_AI_Client_Model_Directory($connector_id, $internal_provider);
                return $directory->getModelMetadata($model_id);
            } catch (\Throwable $e) {
                // Fall back to generic metadata below.
            }
        }

        $capabilities = $resolved_route['route'] === self::ROUTE_IMAGE
            ? [CapabilityEnum::imageGeneration()]
            : [CapabilityEnum::textGeneration(), CapabilityEnum::chatHistory()];

        return new ModelMetadata(
            $model_id !== '' ? $model_id : 'aipuffer-model',
            $model_id !== '' ? $model_id : 'AI Puffer Model',
            $capabilities,
            self::generic_supported_options(
                $resolved_route['route'] === self::ROUTE_IMAGE,
                $resolved_route['route'] !== self::ROUTE_IMAGE && self::provider_accepts_image_input($internal_provider)
            )
        );
    }

    public static function actual_provider_metadata(array $resolved_route): ProviderMetadata
    {
        $connector_id = sanitize_key((string) ($resolved_route['provider'] ?? ''));
        $config = AIPKit_WP_AI_Client_Settings::get_provider_config($connector_id) ?: [];
        $is_keyless = !empty($config['keyless']);

        return new ProviderMetadata(
            $connector_id !== '' ? $connector_id : self::PROVIDER_ID,
            ($config['name'] ?? __('AI Puffer', 'gpt3-ai-content-generator')) . ' via AI Puffer',
            $is_keyless ? ProviderTypeEnum::server() : ProviderTypeEnum::cloud(),
            !empty($config['credentials_url']) ? $config['credentials_url'] : null,
            $is_keyless ? null : RequestAuthenticationMethod::apiKey(),
            $config['description'] ?? __('AI provider managed by AI Puffer.', 'gpt3-ai-content-generator')
        );
    }

    public static function provider_has_any_route(): bool
    {
        foreach (array_keys(self::ROUTE_MODELS) as $alias) {
            $route = self::resolve_model_alias($alias);
            if ($route !== null && self::route_has_credentials($route)) {
                return true;
            }
        }

        return false;
    }

    public static function route_labels(): array
    {
        return [
            self::ROUTE_TEXT => __('Default text model', 'gpt3-ai-content-generator'),
            self::ROUTE_IMAGE => __('Default image model', 'gpt3-ai-content-generator'),
            self::ROUTE_FAST_TEXT => __('Fast text model', 'gpt3-ai-content-generator'),
        ];
    }

    public static function model_alias_accepts_image_input(string $model_id): bool
    {
        $resolved = self::resolve_model_alias($model_id);
        if ($resolved === null) {
            return false;
        }

        return self::provider_accepts_image_input((string) ($resolved['internal_provider'] ?? ''));
    }

    private static function normalize_resolved_route(string $route, array $setting): ?array
    {
        $provider = sanitize_key((string) ($setting['provider'] ?? ''));
        $model = sanitize_text_field((string) ($setting['model'] ?? ''));
        if ($provider === '' || $model === '') {
            return null;
        }

        $internal_provider = AIPKit_WP_AI_Client_Settings::get_internal_provider($provider);
        if (!$internal_provider) {
            return null;
        }

        return [
            'route' => sanitize_key($route),
            'provider' => $provider,
            'internal_provider' => $internal_provider,
            'model' => $model,
        ];
    }

    private static function route_has_credentials(array $resolved_route): bool
    {
        return AIPKit_WP_AI_Client_Settings::provider_has_credentials((string) ($resolved_route['provider'] ?? ''));
    }

    private static function alias_metadata(string $alias, string $route, array $resolved): ModelMetadata
    {
        $actual = self::actual_model_metadata($resolved);
        $labels = self::route_labels();
        $provider_config = AIPKit_WP_AI_Client_Settings::get_provider_config((string) ($resolved['provider'] ?? '')) ?: [];
        $provider_name = (string) ($provider_config['name'] ?? $resolved['provider'] ?? 'AI Puffer');
        $name = sprintf(
            '%s: %s / %s',
            $labels[$route] ?? __('AI Puffer route', 'gpt3-ai-content-generator'),
            $provider_name,
            $actual->getName()
        );

        $is_image_route = $route === self::ROUTE_IMAGE;
        $capabilities = $is_image_route
            ? [CapabilityEnum::imageGeneration()]
            : [CapabilityEnum::textGeneration(), CapabilityEnum::chatHistory()];

        return new ModelMetadata(
            $alias,
            $name,
            $capabilities,
            self::generic_supported_options(
                $is_image_route,
                !$is_image_route && self::provider_accepts_image_input((string) ($resolved['internal_provider'] ?? ''))
            )
        );
    }

    private static function inferred_defaults(): array
    {
        $text = self::infer_text_route(false);
        $image = self::infer_image_route();
        $fast = self::infer_text_route(true);

        return [
            self::ROUTE_TEXT => $text,
            self::ROUTE_IMAGE => $image,
            self::ROUTE_FAST_TEXT => $fast ?: $text,
        ];
    }

    private static function infer_text_route(bool $prefer_fast): array
    {
        $current_internal = class_exists(AIPKit_Providers::class) ? AIPKit_Providers::get_current_provider() : 'OpenAI';
        $current_connector = self::connector_for_internal_provider($current_internal);
        $ordered_connectors = array_values(array_unique(array_filter(array_merge(
            [$current_connector],
            ['openai', 'google', 'openrouter', 'anthropic', 'deepseek', 'xai', 'ollama', 'azure']
        ))));

        foreach ($ordered_connectors as $connector_id) {
            $config = AIPKit_WP_AI_Client_Settings::get_provider_config($connector_id);
            if (!$config) {
                continue;
            }
            if (!AIPKit_WP_AI_Client_Settings::provider_has_credentials($connector_id)) {
                continue;
            }
            $models = self::normalize_model_rows(self::model_rows_for_text((string) ($config['aipkit_provider'] ?? '')));
            if (empty($models)) {
                continue;
            }

            $provider_data = AIPKit_Providers::get_provider_data((string) ($config['aipkit_provider'] ?? ''));
            $preferred_model = isset($provider_data['model']) ? sanitize_text_field((string) $provider_data['model']) : '';
            if ($prefer_fast) {
                $preferred_model = self::choose_fast_model($models, $preferred_model);
            }
            $model = self::model_or_first($models, $preferred_model);
            if ($model !== '') {
                return ['provider' => $connector_id, 'model' => $model];
            }
        }

        return ['provider' => '', 'model' => ''];
    }

    private static function infer_image_route(): array
    {
        $ordered_connectors = ['openai', 'google', 'xai', 'openrouter', 'azure'];
        foreach ($ordered_connectors as $connector_id) {
            $config = AIPKit_WP_AI_Client_Settings::get_provider_config($connector_id);
            if (!$config) {
                continue;
            }
            if (!AIPKit_WP_AI_Client_Settings::provider_has_credentials($connector_id)) {
                continue;
            }

            $models = self::normalize_model_rows(self::model_rows_for_image((string) ($config['aipkit_provider'] ?? '')));
            if (empty($models)) {
                continue;
            }

            $preferred_model = match ((string) ($config['aipkit_provider'] ?? '')) {
                'OpenAI' => AIPKit_Providers::get_default_openai_image_model(),
                'Google' => AIPKit_Providers::get_default_google_image_model(),
                'xAI' => AIPKit_Providers::get_default_xai_image_model(),
                default => '',
            };
            $model = self::model_or_first($models, $preferred_model);
            if ($model !== '') {
                return ['provider' => $connector_id, 'model' => $model];
            }
        }

        return ['provider' => '', 'model' => ''];
    }

    private static function connector_for_internal_provider(string $internal_provider): string
    {
        foreach (AIPKit_WP_AI_Client_Settings::providers() as $connector_id => $config) {
            if (($config['aipkit_provider'] ?? '') === $internal_provider) {
                return $connector_id;
            }
        }

        return '';
    }

    private static function model_or_first(array $models, string $preferred_model): string
    {
        if ($preferred_model !== '') {
            foreach ($models as $model) {
                if (($model['id'] ?? '') === $preferred_model) {
                    return $preferred_model;
                }
            }
        }

        return (string) ($models[0]['id'] ?? '');
    }

    private static function choose_fast_model(array $models, string $fallback): string
    {
        $patterns = ['fast', 'flash-lite', 'flash', 'mini', 'nano', 'lite', 'haiku'];
        foreach ($patterns as $pattern) {
            foreach ($models as $model) {
                $haystack = strtolower((string) ($model['id'] ?? '') . ' ' . (string) ($model['name'] ?? ''));
                if (strpos($haystack, $pattern) !== false) {
                    return (string) $model['id'];
                }
            }
        }

        return $fallback;
    }

    private static function model_rows_for_text(string $internal_provider): array
    {
        return match ($internal_provider) {
            'OpenAI' => AIPKit_Providers::get_openai_models(),
            'Google' => AIPKit_Providers::get_google_models(),
            'Claude' => AIPKit_Providers::get_claude_models(),
            'OpenRouter' => AIPKit_Providers::get_openrouter_models(),
            'Azure' => AIPKit_Providers::get_azure_deployments(),
            'DeepSeek' => AIPKit_Providers::get_deepseek_models(),
            'xAI' => AIPKit_Providers::get_xai_models(),
            'Ollama' => AIPKit_Providers::get_ollama_models(),
            default => [],
        };
    }

    private static function model_rows_for_image(string $internal_provider): array
    {
        return match ($internal_provider) {
            'OpenAI' => AIPKit_Providers::get_openai_image_models(),
            'Google' => AIPKit_Providers::get_google_image_models(),
            'OpenRouter' => AIPKit_Providers::get_openrouter_image_models(),
            'Azure' => AIPKit_Providers::get_azure_image_models(),
            'xAI' => AIPKit_Providers::get_xai_image_models(),
            default => [],
        };
    }

    private static function normalize_model_rows(array $rows): array
    {
        $normalized = [];
        foreach (self::flatten_model_rows($rows) as $row) {
            $model = self::normalize_model_row($row);
            if ($model === null) {
                continue;
            }
            $normalized[$model['id']] = $model;
        }

        return array_values($normalized);
    }

    private static function flatten_model_rows(array $rows): array
    {
        $flat = [];
        foreach ($rows as $row) {
            if (is_array($row) && !isset($row['id']) && !isset($row['model']) && !isset($row['name'])) {
                foreach ($row as $nested_row) {
                    $flat[] = $nested_row;
                }
                continue;
            }
            $flat[] = $row;
        }

        return $flat;
    }

    private static function normalize_model_row($row): ?array
    {
        if (is_string($row)) {
            $id = trim($row);
            return $id === '' ? null : ['id' => $id, 'name' => $id];
        }
        if (!is_array($row)) {
            return null;
        }

        $id = '';
        foreach (['id', 'model', 'name'] as $key) {
            if (!empty($row[$key]) && is_string($row[$key])) {
                $id = trim($row[$key]);
                break;
            }
        }
        if ($id === '') {
            return null;
        }

        $name = isset($row['name']) && is_string($row['name']) ? trim($row['name']) : $id;
        return ['id' => $id, 'name' => $name !== '' ? $name : $id];
    }

    private static function provider_accepts_image_input(string $internal_provider): bool
    {
        return in_array($internal_provider, ['OpenAI', 'Google', 'Claude', 'OpenRouter', 'xAI'], true);
    }

    private static function generic_supported_options(bool $image, bool $accepts_image_input = false): array
    {
        $methods = $image
            ? ['candidateCount', 'outputFileType', 'outputMimeType', 'outputMediaAspectRatio', 'outputMediaOrientation']
            : ['candidateCount', 'systemInstruction', 'maxTokens', 'temperature', 'topP', 'topK', 'stopSequences', 'presencePenalty', 'frequencyPenalty', 'outputMimeType', 'outputSchema'];

        $supported = [];
        foreach ($methods as $method) {
            try {
                $supported_values = (!$image && $method === 'candidateCount') ? [1, 2, 3, 4] : null;
                $supported[] = new SupportedOption(OptionEnum::$method(), $supported_values);
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            $input_modalities = [[ModalityEnum::text()]];
            if ($accepts_image_input) {
                $input_modalities[] = [ModalityEnum::text(), ModalityEnum::image()];
            }
            $supported[] = new SupportedOption(OptionEnum::inputModalities(), $input_modalities);
        } catch (\Throwable $e) {
            // Older builds may not expose these enum values.
        }

        try {
            $supported[] = new SupportedOption(
                OptionEnum::outputModalities(),
                [[$image ? ModalityEnum::image() : ModalityEnum::text()]]
            );
        } catch (\Throwable $e) {
            // Older builds may not expose these enum values.
        }

        return $supported;
    }
}

class AIPKit_WP_AI_Client_Route_Model_Directory implements ModelMetadataDirectoryInterface
{
    public function listModelMetadata(): array
    {
        return AIPKit_WP_AI_Client_Routes::available_alias_models();
    }

    public function hasModelMetadata(string $modelId): bool
    {
        foreach ($this->listModelMetadata() as $model_metadata) {
            if ($model_metadata->getId() === $modelId) {
                return true;
            }
        }

        return false;
    }

    public function getModelMetadata(string $modelId): ModelMetadata
    {
        return AIPKit_WP_AI_Client_Routes::get_alias_model_metadata($modelId);
    }
}

class AIPKit_WP_AI_Client_Route_Availability implements ProviderAvailabilityInterface
{
    public function isConfigured(): bool
    {
        return AIPKit_WP_AI_Client_Settings::is_effectively_managed()
            && AIPKit_WP_AI_Client_Routes::provider_has_any_route();
    }
}
