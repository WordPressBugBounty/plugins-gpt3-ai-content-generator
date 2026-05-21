<?php

namespace WPAICG\WP_AI_Client;

use WPAICG\AIPKit_Providers;
use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;

if (!defined('ABSPATH')) {
    exit;
}

class AIPKit_WP_AI_Client_Model_Directory implements ModelMetadataDirectoryInterface
{
    private string $connector_id;
    private string $internal_provider;

    public function __construct(string $connector_id, string $internal_provider)
    {
        $this->connector_id = sanitize_key($connector_id);
        $this->internal_provider = $internal_provider;
    }

    public function listModelMetadata(): array
    {
        return array_values($this->build_model_map());
    }

    public function hasModelMetadata(string $modelId): bool
    {
        $models = $this->build_model_map();
        return isset($models[$modelId]);
    }

    public function getModelMetadata(string $modelId): ModelMetadata
    {
        $models = $this->build_model_map();
        if (!isset($models[$modelId])) {
            throw new InvalidArgumentException(sprintf('Unknown AI Puffer model "%s" for provider "%s".', esc_html($modelId), esc_html($this->connector_id)));
        }

        return $models[$modelId];
    }

    private function build_model_map(): array
    {
        if (!class_exists(AIPKit_Providers::class)) {
            return [];
        }

        $rows = [];
        foreach ($this->flatten_model_rows($this->text_model_rows()) as $row) {
            $this->merge_model_row($rows, $row, [CapabilityEnum::textGeneration(), CapabilityEnum::chatHistory()]);
        }
        foreach ($this->flatten_model_rows($this->image_model_rows()) as $row) {
            $this->merge_model_row($rows, $row, [CapabilityEnum::imageGeneration()]);
        }
        $rows = $this->sort_rows_by_preference($rows);

        $metadata = [];
        foreach ($rows as $id => $row) {
            $metadata[$id] = new ModelMetadata(
                $id,
                $row['name'] ?: $id,
                $this->unique_capabilities($row['capabilities']),
                $this->supported_options($row['capabilities'])
            );
        }

        return $metadata;
    }

    private function sort_rows_by_preference(array $rows): array
    {
        $preferred = array_flip($this->preferred_model_ids());
        uksort($rows, static function (string $a, string $b) use ($preferred): int {
            $a_rank = $preferred[$a] ?? 9999;
            $b_rank = $preferred[$b] ?? 9999;
            if ($a_rank !== $b_rank) {
                return $a_rank <=> $b_rank;
            }

            return strcasecmp($a, $b);
        });

        return $rows;
    }

    private function preferred_model_ids(): array
    {
        $ids = [];
        if (class_exists(AIPKit_WP_AI_Client_Routes::class)) {
            foreach (AIPKit_WP_AI_Client_Routes::get_defaults() as $route) {
                $route_provider = isset($route['provider']) ? sanitize_key((string) $route['provider']) : '';
                if (AIPKit_WP_AI_Client_Settings::get_internal_provider($route_provider) !== $this->internal_provider) {
                    continue;
                }
                if (!empty($route['model']) && is_string($route['model'])) {
                    $ids[] = trim($route['model']);
                }
            }
        }

        $provider_data = AIPKit_Providers::get_provider_data($this->internal_provider);
        if (!empty($provider_data['model']) && is_string($provider_data['model'])) {
            $ids[] = trim($provider_data['model']);
        }

        if ($this->internal_provider === 'OpenAI') {
            $ids[] = AIPKit_Providers::get_default_openai_image_model();
        } elseif ($this->internal_provider === 'Google') {
            $ids[] = AIPKit_Providers::get_default_google_image_model();
        } elseif ($this->internal_provider === 'xAI') {
            $ids[] = AIPKit_Providers::get_default_xai_image_model();
        }

        return array_values(array_filter(array_unique($ids), static fn(string $id): bool => $id !== ''));
    }

    private function merge_model_row(array &$rows, $row, array $capabilities): void
    {
        $normalized = $this->normalize_model_row($row);
        if ($normalized === null) {
            return;
        }

        $id = $normalized['id'];
        if (!isset($rows[$id])) {
            $rows[$id] = [
                'name' => $normalized['name'],
                'capabilities' => [],
            ];
        }

        if ($rows[$id]['name'] === $id && $normalized['name'] !== $id) {
            $rows[$id]['name'] = $normalized['name'];
        }

        foreach ($capabilities as $capability) {
            $rows[$id]['capabilities'][] = $capability;
        }
    }

    private function normalize_model_row($row): ?array
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

    private function flatten_model_rows(array $rows): array
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

    private function text_model_rows(): array
    {
        return match ($this->internal_provider) {
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

    private function image_model_rows(): array
    {
        return match ($this->internal_provider) {
            'OpenAI' => AIPKit_Providers::get_openai_image_models(),
            'Google' => AIPKit_Providers::get_google_image_models(),
            'OpenRouter' => AIPKit_Providers::get_openrouter_image_models(),
            'Azure' => AIPKit_Providers::get_azure_image_models(),
            'xAI' => AIPKit_Providers::get_xai_image_models(),
            default => [],
        };
    }

    private function unique_capabilities(array $capabilities): array
    {
        $seen = [];
        $unique = [];
        foreach ($capabilities as $capability) {
            if (!is_object($capability)) {
                continue;
            }
            $value = (string) $capability->value;
            if (isset($seen[$value])) {
                continue;
            }
            $seen[$value] = true;
            $unique[] = $capability;
        }

        return $unique;
    }

    private function supported_options(array $capabilities): array
    {
        $has_text = false;
        $has_image = false;
        foreach ($capabilities as $capability) {
            $value = is_object($capability) ? (string) $capability->value : '';
            if ($value === CapabilityEnum::TEXT_GENERATION) {
                $has_text = true;
            }
            if ($value === CapabilityEnum::IMAGE_GENERATION) {
                $has_image = true;
            }
        }

        $option_methods = ['customOptions'];
        if ($has_text) {
            $option_methods = array_merge($option_methods, [
                'candidateCount',
                'systemInstruction',
                'maxTokens',
                'temperature',
                'topP',
                'topK',
                'stopSequences',
                'presencePenalty',
                'frequencyPenalty',
                'outputMimeType',
                'outputSchema',
            ]);
        }
        if ($has_image) {
            $option_methods = array_merge($option_methods, [
                'candidateCount',
                'outputFileType',
                'outputMimeType',
                'outputMediaAspectRatio',
                'outputMediaOrientation',
            ]);
        }

        $supported = [];
        $seen = [];
        foreach ($option_methods as $method) {
            try {
                $option = OptionEnum::$method();
                if (isset($seen[$option->value])) {
                    continue;
                }
                $seen[$option->value] = true;
                $supported_values = ($has_text && $method === 'candidateCount') ? [1, 2, 3, 4] : null;
                $supported[] = new SupportedOption($option, $supported_values);
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            $input_modalities = [[ModalityEnum::text()]];
            if ($this->provider_accepts_image_input()) {
                $input_modalities[] = [ModalityEnum::text(), ModalityEnum::image()];
            }
            $supported[] = new SupportedOption(OptionEnum::inputModalities(), $input_modalities);
        } catch (\Throwable $e) {
            // Older builds may not expose these enum values.
        }

        try {
            $output_modalities = [];
            if ($has_text) {
                $output_modalities[] = [ModalityEnum::text()];
            }
            if ($has_image) {
                $output_modalities[] = [ModalityEnum::image()];
            }
            if (!empty($output_modalities)) {
                $supported[] = new SupportedOption(OptionEnum::outputModalities(), $output_modalities);
            }
        } catch (\Throwable $e) {
            // Older builds may not expose these enum values.
        }

        return $supported;
    }

    private function provider_accepts_image_input(): bool
    {
        return in_array($this->internal_provider, ['OpenAI', 'Google', 'Claude', 'OpenRouter', 'xAI'], true);
    }
}
