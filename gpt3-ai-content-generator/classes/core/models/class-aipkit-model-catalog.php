<?php

namespace WPAICG\Core\Models;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Static definitions for the universal AI catalog.
 *
 * This class owns bootstrapped model/resource data and the mapping from legacy
 * provider-specific options into canonical catalog keys. Runtime and UI code
 * should query AIPKit_Model_Registry instead of reading these definitions.
 */
final class AIPKit_Model_Catalog
{
    /**
     * Provider preference for fresh text-generation configurations.
     *
     * A configured main provider may override this order. Keeping the fallback
     * here lets PHP and browser consumers share the same product decision.
     */
    private const PROVIDER_PRIORITY = [
        'text_generation' => [
            'OpenAI',
            'Claude',
            'Google',
            'OpenRouter',
            'Ollama',
            'Azure',
            'DeepSeek',
            'xAI',
        ],
    ];

    /**
     * General-purpose models supported by the chatbot's file transcription flow.
     * Keep this list focused on stable aliases; synced snapshots remain available
     * in the universal registry without cluttering the chatbot setting.
     */
    private const OPENAI_FILE_TRANSCRIPTION_MODELS = [
        ['id' => 'gpt-transcribe', 'name' => 'GPT Transcribe'],
        ['id' => 'gpt-4o-transcribe', 'name' => 'GPT-4o Transcribe'],
        [
            'id' => 'gpt-4o-mini-transcribe',
            'name' => 'GPT-4o Mini Transcribe',
            'roles' => ['realtime_transcription'],
        ],
        ['id' => 'whisper-1', 'name' => 'Whisper-1'],
    ];

    private const GOOGLE_TTS_VOICES = [
        ['id' => 'Kore', 'name' => 'Kore', 'style' => 'Firm'],
        ['id' => 'Zephyr', 'name' => 'Zephyr', 'style' => 'Bright'],
        ['id' => 'Puck', 'name' => 'Puck', 'style' => 'Upbeat'],
        ['id' => 'Charon', 'name' => 'Charon', 'style' => 'Informative'],
        ['id' => 'Fenrir', 'name' => 'Fenrir', 'style' => 'Excitable'],
        ['id' => 'Leda', 'name' => 'Leda', 'style' => 'Youthful'],
        ['id' => 'Orus', 'name' => 'Orus', 'style' => 'Firm'],
        ['id' => 'Aoede', 'name' => 'Aoede', 'style' => 'Breezy'],
        ['id' => 'Callirrhoe', 'name' => 'Callirrhoe', 'style' => 'Easy-going'],
        ['id' => 'Autonoe', 'name' => 'Autonoe', 'style' => 'Bright'],
        ['id' => 'Enceladus', 'name' => 'Enceladus', 'style' => 'Breathy'],
        ['id' => 'Iapetus', 'name' => 'Iapetus', 'style' => 'Clear'],
        ['id' => 'Umbriel', 'name' => 'Umbriel', 'style' => 'Easy-going'],
        ['id' => 'Algieba', 'name' => 'Algieba', 'style' => 'Smooth'],
        ['id' => 'Despina', 'name' => 'Despina', 'style' => 'Smooth'],
        ['id' => 'Erinome', 'name' => 'Erinome', 'style' => 'Clear'],
        ['id' => 'Algenib', 'name' => 'Algenib', 'style' => 'Gravelly'],
        ['id' => 'Rasalgethi', 'name' => 'Rasalgethi', 'style' => 'Informative'],
        ['id' => 'Laomedeia', 'name' => 'Laomedeia', 'style' => 'Upbeat'],
        ['id' => 'Achernar', 'name' => 'Achernar', 'style' => 'Soft'],
        ['id' => 'Alnilam', 'name' => 'Alnilam', 'style' => 'Firm'],
        ['id' => 'Schedar', 'name' => 'Schedar', 'style' => 'Even'],
        ['id' => 'Gacrux', 'name' => 'Gacrux', 'style' => 'Mature'],
        ['id' => 'Pulcherrima', 'name' => 'Pulcherrima', 'style' => 'Forward'],
        ['id' => 'Achird', 'name' => 'Achird', 'style' => 'Friendly'],
        ['id' => 'Zubenelgenubi', 'name' => 'Zubenelgenubi', 'style' => 'Casual'],
        ['id' => 'Vindemiatrix', 'name' => 'Vindemiatrix', 'style' => 'Gentle'],
        ['id' => 'Sadachbia', 'name' => 'Sadachbia', 'style' => 'Lively'],
        ['id' => 'Sadaltager', 'name' => 'Sadaltager', 'style' => 'Knowledgeable'],
        ['id' => 'Sulafat', 'name' => 'Sulafat', 'style' => 'Warm'],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function get_definitions(): array
    {
        return [
            'OpenAI' => self::model_definition(
                'OpenAI',
                'text',
                'text_generation',
                'aipkit_openai_model_list',
                'gpt-5.6-luna',
                [
                    'gpt-5.6-sol',
                    'gpt-5.6-luna',
                    'gpt-5.6-terra',
                ],
                ['legacy_grouped' => true]
            ),
            'OpenAIImage' => self::model_definition(
                'OpenAI',
                'image',
                'image_generation',
                '',
                'gpt-image-2',
                [
                    ['id' => 'gpt-image-2', 'name' => 'GPT Image 2'],
                    ['id' => 'gpt-image-1.5', 'name' => 'GPT Image 1.5'],
                ],
                [
                    'capabilities' => ['image_generation' => true, 'image_editing' => true],
                ]
            ),
            'OpenAIEmbedding' => self::model_definition(
                'OpenAI',
                'embedding',
                'embeddings',
                'aipkit_openai_embedding_model_list',
                'text-embedding-3-small',
                [
                    ['id' => 'text-embedding-3-small', 'name' => 'Text Embedding 3 Small (1536)', 'dimensions' => 1536],
                    ['id' => 'text-embedding-3-large', 'name' => 'Text Embedding 3 Large (3072)', 'dimensions' => 3072],
                ]
            ),
            'OpenAITTS' => self::model_definition(
                'OpenAI',
                'audio',
                'tts',
                'aipkit_openai_tts_model_list',
                'tts-1',
                [
                    ['id' => 'tts-1', 'name' => 'TTS-1'],
                    ['id' => 'tts-1-hd', 'name' => 'TTS-1-HD'],
                ]
            ),
            'OpenAISTT' => self::model_definition(
                'OpenAI',
                'audio',
                'stt',
                'aipkit_openai_stt_model_list',
                'whisper-1',
                self::OPENAI_FILE_TRANSCRIPTION_MODELS
            ),
            'OpenAIRealtime' => self::model_definition(
                'OpenAI',
                'audio',
                'realtime',
                '',
                'gpt-realtime-2.1',
                [
                    ['id' => 'gpt-realtime-2.1', 'name' => 'GPT Realtime 2.1'],
                    ['id' => 'gpt-realtime-2.1-mini', 'name' => 'GPT Realtime 2.1 Mini'],
                ]
            ),
            'OpenAIVoices' => self::resource_definition(
                'OpenAI',
                'voice',
                '',
                [
                    ['id' => 'alloy', 'name' => 'Alloy', 'gender' => 'neutral'],
                    ['id' => 'echo', 'name' => 'Echo', 'gender' => 'male'],
                    ['id' => 'fable', 'name' => 'Fable', 'gender' => 'male'],
                    ['id' => 'onyx', 'name' => 'Onyx', 'gender' => 'male'],
                    ['id' => 'nova', 'name' => 'Nova', 'gender' => 'female'],
                    ['id' => 'shimmer', 'name' => 'Shimmer', 'gender' => 'female'],
                ]
            ),
            'OpenAIRealtimeVoices' => self::resource_definition(
                'OpenAI',
                'voice',
                '',
                [
                    'alloy', 'ash', 'ballad', 'coral', 'echo', 'sage', 'shimmer', 'verse', 'marin', 'cedar',
                ]
            ),
            'OpenAIVectorStores' => self::resource_definition(
                'OpenAI',
                'vector_target',
                '',
                []
            ),
            'OpenRouter' => self::model_definition(
                'OpenRouter',
                'text',
                'text_generation',
                'aipkit_openrouter_model_list',
                'openai/gpt-5.6-luna',
                [
                    ['id' => 'openai/gpt-5.6-luna', 'name' => 'GPT-5.6 Luna'],
                    ['id' => 'deepseek/deepseek-v4-flash-0731', 'name' => 'DeepSeek V4 Flash 0731'],
                    ['id' => 'anthropic/claude-sonnet-5', 'name' => 'Claude Sonnet 5'],
                ]
            ),
            'OpenRouterEmbedding' => self::model_definition(
                'OpenRouter',
                'embedding',
                'embeddings',
                'aipkit_openrouter_embedding_model_list',
                '',
                []
            ),
            'Google' => self::model_definition(
                'Google',
                'text',
                'text_generation',
                'aipkit_google_model_list',
                'gemini-3.7-flash',
                [
                    ['id' => 'gemini-3.7-flash', 'name' => 'Gemini 3.7 Flash'],
                    ['id' => 'gemini-3.5-flash-lite', 'name' => 'Gemini 3.5 Flash-Lite'],
                ],
                [
                    'deprecated_ids' => [
                        'gemini-pro',
                        'gemini-1.5-pro-latest',
                        'gemini-1.5-flash-latest',
                        'gemini-2.0-flash',
                        'gemini-2.0-flash-lite',
                    ],
                ]
            ),
            'GoogleImage' => self::model_definition(
                'Google',
                'image',
                'image_generation',
                'aipkit_google_image_model_list',
                'gemini-3.1-flash-image',
                [
                    ['id' => 'gemini-3.1-flash-image', 'name' => 'Gemini 3.1 Flash Image (Nano Banana 2)'],
                    ['id' => 'gemini-3.1-flash-lite-image', 'name' => 'Gemini 3.1 Flash Lite Image (Nano Banana 2 Lite)'],
                    ['id' => 'gemini-3-pro-image', 'name' => 'Gemini 3 Pro Image (Nano Banana Pro)'],
                    ['id' => 'gemini-2.5-flash-image', 'name' => 'Gemini 2.5 Flash Image (Nano Banana)'],
                ]
            ),
            'GoogleVideo' => self::model_definition(
                'Google',
                'video',
                'video_generation',
                'aipkit_google_video_model_list',
                '',
                []
            ),
            'GoogleEmbedding' => self::model_definition(
                'Google',
                'embedding',
                'embeddings',
                'aipkit_google_embedding_model_list',
                'gemini-embedding-2-preview',
                [
                    ['id' => 'gemini-embedding-2-preview', 'name' => 'Gemini Embedding 2 Preview (3072)', 'dimensions' => 3072],
                    ['id' => 'gemini-embedding-001', 'name' => 'Gemini Embedding 001 (3072)', 'dimensions' => 3072],
                ]
            ),
            'GoogleFileSearchStores' => self::resource_definition(
                'Google',
                'knowledge_target',
                'aipkit_google_file_search_store_list',
                []
            ),
            'GoogleTTS' => self::model_definition(
                'Google',
                'audio',
                'tts',
                'aipkit_google_tts_model_list',
                'gemini-3.1-flash-tts-preview',
                [
                    ['id' => 'gemini-3.1-flash-tts-preview', 'name' => 'Gemini 3.1 Flash TTS Preview'],
                    ['id' => 'gemini-2.5-flash-preview-tts', 'name' => 'Gemini 2.5 Flash Preview TTS'],
                    ['id' => 'gemini-2.5-pro-preview-tts', 'name' => 'Gemini 2.5 Pro Preview TTS'],
                ]
            ),
            'GoogleTTSVoices' => self::resource_definition(
                'Google',
                'voice',
                '',
                self::GOOGLE_TTS_VOICES
            ),
            'Azure' => self::model_definition(
                'Azure',
                'text',
                'text_generation',
                'aipkit_azure_deployment_list',
                '',
                [],
                ['is_deployment' => true]
            ),
            'AzureImage' => self::model_definition(
                'Azure',
                'image',
                'image_generation',
                'aipkit_azure_image_model_list',
                '',
                [],
                ['is_deployment' => true, 'capabilities' => ['image_generation' => true, 'image_editing' => true]]
            ),
            'AzureEmbedding' => self::model_definition(
                'Azure',
                'embedding',
                'embeddings',
                'aipkit_azure_embedding_model_list',
                '',
                [],
                ['is_deployment' => true]
            ),
            'Claude' => self::model_definition(
                'Claude',
                'text',
                'text_generation',
                'aipkit_claude_model_list',
                'claude-sonnet-5',
                [
                    ['id' => 'claude-sonnet-5', 'name' => 'Claude Sonnet 5'],
                    ['id' => 'claude-haiku-4-5', 'name' => 'Claude Haiku 4.5'],
                    ['id' => 'claude-opus-5', 'name' => 'Claude Opus 5'],
                ]
            ),
            'DeepSeek' => self::model_definition(
                'DeepSeek',
                'text',
                'text_generation',
                'aipkit_deepseek_model_list',
                'deepseek-v4-flash',
                [
                    ['id' => 'deepseek-v4-flash', 'name' => 'DeepSeek V4 Flash'],
                    ['id' => 'deepseek-v4-pro', 'name' => 'DeepSeek V4 Pro'],
                ],
                ['deprecated_ids' => ['deepseek-chat', 'deepseek-reasoner']]
            ),
            'xAI' => self::model_definition(
                'xAI',
                'text',
                'text_generation',
                'aipkit_xai_model_list',
                'grok-4.3',
                [
                    ['id' => 'grok-4.3', 'name' => 'Grok 4.3'],
                    ['id' => 'grok-4.5', 'name' => 'Grok 4.5'],
                ],
                [
                    'deprecated_ids' => [
                        'grok-4-1-fast-reasoning',
                        'grok-4-1-fast-non-reasoning',
                        'grok-4-fast-reasoning',
                        'grok-4-fast-non-reasoning',
                        'grok-4-0709',
                        'grok-code-fast-1',
                        'grok-3',
                    ],
                ]
            ),
            'xAIImage' => self::model_definition(
                'xAI',
                'image',
                'image_generation',
                'aipkit_xai_image_model_list',
                'grok-imagine-image-quality',
                [
                    ['id' => 'grok-imagine-image-quality', 'name' => 'Grok Imagine Image Quality'],
                    ['id' => 'grok-imagine-image', 'name' => 'Grok Imagine Image'],
                ],
                [
                    'capabilities' => ['image_generation' => true, 'image_editing' => true],
                    'deprecated_ids' => ['grok-imagine-image-pro'],
                ]
            ),
            'Ollama' => self::model_definition(
                'Ollama',
                'text',
                'text_generation',
                'aipkit_ollama_model_list',
                '',
                [],
                [
                    'requires_pro' => true,
                ]
            ),
            'OllamaEmbedding' => self::model_definition(
                'Ollama',
                'embedding',
                'embeddings',
                'aipkit_ollama_embedding_model_list',
                '',
                []
            ),
            'OllamaVision' => self::model_definition(
                'Ollama',
                'text',
                'image_input',
                'aipkit_ollama_vision_model_list',
                '',
                [],
                ['capabilities' => ['text_generation' => true, 'image_input' => true, 'vision' => true]]
            ),
            'OllamaCapabilities' => self::resource_definition(
                'Ollama',
                'model_metadata',
                'aipkit_ollama_model_capability_list',
                []
            ),
            'ElevenLabsModels' => self::model_definition(
                'ElevenLabs',
                'audio',
                'tts',
                'aipkit_elevenlabs_model_list',
                'eleven_multilingual_v2',
                ['eleven_multilingual_v2']
            ),
            'ElevenLabs' => self::resource_definition(
                'ElevenLabs',
                'voice',
                'aipkit_elevenlabs_voice_list',
                []
            ),
            'Replicate' => self::model_definition(
                'Replicate',
                'image',
                'image_generation',
                'aipkit_replicate_model_list',
                '',
                []
            ),
            'PineconeIndexes' => self::resource_definition(
                'Pinecone',
                'vector_target',
                'aipkit_pinecone_index_list',
                []
            ),
            'QdrantCollections' => self::resource_definition(
                'Qdrant',
                'vector_target',
                'aipkit_qdrant_collection_list',
                []
            ),
            'ChromaCollections' => self::resource_definition(
                'Chroma',
                'vector_target',
                'aipkit_chroma_collection_list',
                []
            ),
        ];
    }

    /**
     * Return the model-only targets used by the Settings "Sync all models" action.
     *
     * A target may use a different connection provider when one provider exposes
     * multiple sync scopes, such as ElevenLabs voices and TTS models. Provider
     * resources such as voices and vector-store targets are intentionally absent.
     *
     * @return array<int, array{provider:string,connection_provider:string,label:string}>
     */
    public static function get_bulk_model_sync_targets(): array
    {
        $targets = [
            ['provider' => 'OpenAI', 'connection_provider' => 'OpenAI', 'label' => 'OpenAI'],
            ['provider' => 'Claude', 'connection_provider' => 'Claude', 'label' => 'Anthropic'],
            ['provider' => 'Google', 'connection_provider' => 'Google', 'label' => 'Google'],
            ['provider' => 'OpenRouter', 'connection_provider' => 'OpenRouter', 'label' => 'OpenRouter'],
            ['provider' => 'Ollama', 'connection_provider' => 'Ollama', 'label' => 'Ollama'],
            ['provider' => 'Azure', 'connection_provider' => 'Azure', 'label' => 'Azure'],
            ['provider' => 'DeepSeek', 'connection_provider' => 'DeepSeek', 'label' => 'DeepSeek'],
            ['provider' => 'xAI', 'connection_provider' => 'xAI', 'label' => 'xAI'],
            ['provider' => 'ElevenLabsModels', 'connection_provider' => 'ElevenLabs', 'label' => 'ElevenLabs'],
            ['provider' => 'Replicate', 'connection_provider' => 'Replicate', 'label' => 'Replicate'],
        ];

        $filtered = apply_filters('aipkit_bulk_model_sync_targets', $targets);
        return is_array($filtered) ? array_values($filtered) : $targets;
    }

    /**
     * @return array<string, mixed>
     */
    public static function get_definition(string $catalog_key): array
    {
        $definitions = self::get_definitions();
        $normalized_key = self::normalize_catalog_key($catalog_key);
        return $definitions[$normalized_key] ?? [];
    }

    public static function normalize_catalog_key(string $catalog_key): string
    {
        $catalog_key = trim($catalog_key);
        if ($catalog_key === '') {
            return '';
        }

        foreach (array_keys(self::get_definitions()) as $known_key) {
            if (strtolower($known_key) === strtolower($catalog_key)) {
                return $known_key;
            }
        }

        $aliases = [
            'xai_image' => 'xAIImage',
            'xai-image' => 'xAIImage',
            'openai_image' => 'OpenAIImage',
            'openai-image' => 'OpenAIImage',
            'openai_embedding' => 'OpenAIEmbedding',
            'openrouter_embedding' => 'OpenRouterEmbedding',
            'google_embedding' => 'GoogleEmbedding',
            'google_image' => 'GoogleImage',
            'google_video' => 'GoogleVideo',
            'google_file_search_stores' => 'GoogleFileSearchStores',
            'google-file-search-stores' => 'GoogleFileSearchStores',
            'azure_embedding' => 'AzureEmbedding',
            'azure_image' => 'AzureImage',
            'ollama_embedding' => 'OllamaEmbedding',
            'ollama_vision' => 'OllamaVision',
            'openai_vector_stores' => 'OpenAIVectorStores',
            'openai-vector-stores' => 'OpenAIVectorStores',
        ];

        return $aliases[strtolower($catalog_key)] ?? $catalog_key;
    }

    /**
     * @return array<int, mixed>
     */
    public static function get_seed_rows(string $catalog_key): array
    {
        $definition = self::get_definition($catalog_key);
        return isset($definition['seeds']) && is_array($definition['seeds'])
            ? $definition['seeds']
            : [];
    }

    /**
     * @return array<int, string>
     */
    public static function get_seed_ids(string $catalog_key): array
    {
        $ids = [];
        foreach (self::get_seed_rows($catalog_key) as $seed) {
            $id = is_string($seed)
                ? sanitize_text_field($seed)
                : sanitize_text_field((string) ($seed['id'] ?? ''));
            if ($id !== '') {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Browser-safe defaults and built-in model order from the canonical catalog.
     *
     * @return array{
     *   defaults:array<string,string>,
     *   seeds:array<string,array<int,string>>,
     *   providerCatalogs:array<string,array<string,string>>,
     *   providerPriority:array<string,array<int,string>>,
     *   providerAccess:array<string,array<string,mixed>>
     * }
     */
    public static function get_client_config(): array
    {
        $defaults = [];
        $seeds = [];
        $provider_access = [];
        foreach (self::get_definitions() as $catalog_key => $definition) {
            if (($definition['resource_type'] ?? 'model') !== 'model') {
                continue;
            }
            $defaults[$catalog_key] = self::get_default_id($catalog_key);
            $seeds[$catalog_key] = self::get_seed_ids($catalog_key);

            if (empty($definition['requires_pro'])) {
                continue;
            }

            $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
            $provider_key = sanitize_key($provider);
            $capability = sanitize_key((string) ($definition['capability'] ?? ''));
            if ($provider_key === '' || $capability === '') {
                continue;
            }

            if (!isset($provider_access[$provider_key])) {
                $provider_access[$provider_key] = [
                    'provider' => $provider,
                    'requiresPro' => true,
                    'capabilities' => [],
                ];
            }
            $provider_access[$provider_key]['capabilities'][$capability] = true;
        }

        return [
            'defaults' => $defaults,
            'seeds' => $seeds,
            'providerCatalogs' => self::get_provider_catalogs_by_capability(),
            'providerPriority' => self::PROVIDER_PRIORITY,
            'providerAccess' => $provider_access,
        ];
    }

    /**
     * Return the shared provider preference for a model capability.
     *
     * @return array<int, string>
     */
    public static function get_provider_priority(string $capability = 'text_generation'): array
    {
        $capability = sanitize_key($capability);
        return self::PROVIDER_PRIORITY[$capability] ?? [];
    }

    /**
     * Map provider keys and capabilities to their canonical model catalogs.
     *
     * @return array<string, array<string, string>>
     */
    public static function get_provider_catalogs_by_capability(): array
    {
        $catalogs = [];
        foreach (self::get_definitions() as $catalog_key => $definition) {
            if (($definition['resource_type'] ?? 'model') !== 'model') {
                continue;
            }

            $provider_key = sanitize_key(strtolower((string) ($definition['provider'] ?? '')));
            if ($provider_key === '') {
                continue;
            }

            foreach ((array) ($definition['capabilities'] ?? []) as $capability => $supported) {
                $capability = sanitize_key((string) $capability);
                if ($capability === '' || !$supported || isset($catalogs[$provider_key][$capability])) {
                    continue;
                }
                $catalogs[$provider_key][$capability] = $catalog_key;
            }
        }

        return $catalogs;
    }

    public static function get_model_id_by_role(string $role): string
    {
        $role = sanitize_key($role);
        if ($role === '') {
            return '';
        }

        foreach (self::get_definitions() as $definition) {
            foreach ((array) ($definition['seeds'] ?? []) as $seed) {
                if (!is_array($seed) || !in_array($role, (array) ($seed['roles'] ?? []), true)) {
                    continue;
                }
                return sanitize_text_field((string) ($seed['id'] ?? ''));
            }
        }

        return '';
    }

    public static function get_default_id(string $catalog_key): string
    {
        $definition = self::get_definition($catalog_key);
        $default_id = isset($definition['default']) ? sanitize_text_field((string) $definition['default']) : '';
        if ($default_id !== '') {
            return $default_id;
        }

        foreach (self::get_seed_rows($catalog_key) as $seed) {
            $candidate = is_string($seed)
                ? sanitize_text_field($seed)
                : sanitize_text_field((string) ($seed['id'] ?? ''));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * @return array<int, string>
     */
    public static function get_recommended_ids(string $catalog_key): array
    {
        return self::get_seed_ids($catalog_key);
    }

    /**
     * Whether a model can be used by the chatbot's POST /audio/transcriptions
     * request shape. Stable aliases are preferred in the UI; dated snapshots of
     * those aliases remain valid for existing saved configurations.
     */
    public static function is_openai_file_transcription_model(string $model_id): bool
    {
        $model_id = strtolower(trim($model_id));
        if ($model_id === '') {
            return false;
        }

        if (in_array($model_id, self::get_seed_ids('OpenAISTT'), true)) {
            return true;
        }

        return preg_match(
            '/^(?:gpt-transcribe|gpt-4o-transcribe|gpt-4o-mini-transcribe)-\d{4}-\d{2}-\d{2}$/',
            $model_id
        ) === 1;
    }

    /**
     * Normalize an OpenAI chatbot STT selection to a compatible file
     * transcription model.
     */
    public static function sanitize_openai_file_transcription_model(string $model_id): string
    {
        $model_id = sanitize_text_field($model_id);
        return self::is_openai_file_transcription_model($model_id)
            ? $model_id
            : self::get_default_id('OpenAISTT');
    }

    /**
     * Classify a model into a provider-specific display family.
     *
     * Families are deliberately separate from model kinds. A kind describes
     * what a model can do (text, image, audio, embedding, and so on); a family
     * only organizes compatible models inside one provider's picker view.
     *
     * @param array<string, mixed> $row Raw or normalized model row.
     * @return array{key:string,label:string,order:int,collapsed:bool}
     */
    public static function get_model_family(string $catalog_key, array $row): array
    {
        $catalog_key = self::normalize_catalog_key($catalog_key);
        $definition = self::get_definition($catalog_key);
        $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
        $metadata = isset($row['metadata']) && is_array($row['metadata'])
            ? $row['metadata']
            : [];
        $model_id = strtolower(trim((string) (
            $row['raw_id']
            ?? $row['id']
            ?? $row['model_id']
            ?? $row['model']
            ?? $row['name']
            ?? ''
        )));

        if ($provider === 'Azure') {
            $underlying_model = (string) (
                $metadata['model']
                ?? $row['model']
                ?? $metadata['model_name']
                ?? $row['name']
                ?? $model_id
            );
            if ($catalog_key === 'AzureImage') {
                return self::classify_openai_image_family($underlying_model);
            }
            if ($catalog_key === 'AzureEmbedding') {
                return self::classify_openai_embedding_family($underlying_model);
            }
            return self::classify_openai_text_family($underlying_model);
        }

        switch ($catalog_key) {
            case 'OpenAI':
                return self::classify_openai_text_family($model_id);
            case 'OpenAIImage':
                return self::classify_openai_image_family($model_id);
            case 'OpenAIEmbedding':
                return self::classify_openai_embedding_family($model_id);
            case 'OpenAITTS':
                return self::family('text-to-speech', __('Text to speech', 'gpt3-ai-content-generator'), 10);
            case 'OpenAISTT':
                return self::family('speech-to-text', __('Speech to text', 'gpt3-ai-content-generator'), 10);
            case 'OpenAIRealtime':
                return self::family('realtime', __('Realtime', 'gpt3-ai-content-generator'), 10);
            case 'OpenRouter':
            case 'OpenRouterEmbedding':
                return self::classify_publisher_family($model_id, 'OpenRouter');
            case 'Google':
                return self::classify_google_text_family($model_id);
            case 'GoogleImage':
                if (strpos($model_id, 'gemini') !== false || strpos($model_id, 'nano-banana') !== false) {
                    return self::family('gemini-image', __('Gemini Image', 'gpt3-ai-content-generator'), 10);
                }
                return self::other_family();
            case 'GoogleTTS':
                return self::family('gemini-tts', __('Gemini TTS', 'gpt3-ai-content-generator'), 10);
            case 'GoogleVideo':
                return strpos($model_id, 'veo') !== false
                    ? self::family('veo', 'Veo', 10)
                    : self::other_family();
            case 'GoogleEmbedding':
                if (strpos($model_id, 'gemini-embedding') !== false) {
                    return self::family('gemini-embedding', __('Gemini Embedding', 'gpt3-ai-content-generator'), 10);
                }
                if (strpos($model_id, 'text-embedding') !== false) {
                    return self::family('text-embedding', __('Text Embedding', 'gpt3-ai-content-generator'), 20);
                }
                return self::other_family();
            case 'Claude':
                return self::classify_anthropic_family($model_id);
            case 'DeepSeek':
                if (strpos($model_id, 'v4') !== false) {
                    return self::family('deepseek-v4', 'DeepSeek V4', 10);
                }
                if (strpos($model_id, 'v3') !== false || strpos($model_id, 'deepseek-chat') !== false) {
                    return self::family('deepseek-v3', 'DeepSeek V3', 20);
                }
                if (preg_match('/(?:^|[\/_-])r1(?:[\/_-]|$)/', $model_id)) {
                    return self::family('deepseek-r1', 'DeepSeek R1', 30);
                }
                return self::other_family();
            case 'xAI':
                if (strpos($model_id, 'grok-build') !== false) {
                    return self::family('grok-build', __('Grok Build', 'gpt3-ai-content-generator'), 20);
                }
                if (strpos($model_id, 'grok-4') !== false) {
                    return self::family('grok-4', 'Grok 4', 10);
                }
                if (strpos($model_id, 'grok-3') !== false) {
                    return self::family('grok-3', 'Grok 3', 30);
                }
                return self::other_family();
            case 'xAIImage':
                return self::family('grok-imagine', __('Grok Imagine', 'gpt3-ai-content-generator'), 10);
            case 'Ollama':
            case 'OllamaEmbedding':
            case 'OllamaVision':
                return self::classify_ollama_family($model_id, $row, $metadata);
            case 'ElevenLabsModels':
                return self::family('elevenlabs-tts', __('Text to speech', 'gpt3-ai-content-generator'), 10);
            case 'Replicate':
                return self::classify_publisher_family($model_id, 'Replicate');
        }

        return self::other_family();
    }

    /**
     * Group raw rows using the canonical family classifier.
     *
     * @param array<mixed> $rows
     * @return array<string, array<int, mixed>>
     */
    public static function group_rows_by_family(string $catalog_key, array $rows): array
    {
        $families = [];
        foreach ($rows as $row) {
            if (is_string($row)) {
                $row = ['id' => $row, 'name' => $row];
            }
            if (!is_array($row)) {
                continue;
            }
            $family = self::get_model_family($catalog_key, $row);
            $key = (string) ($family['key'] ?? 'other');
            if (!isset($families[$key])) {
                $families[$key] = [
                    'label' => (string) ($family['label'] ?? __('Other', 'gpt3-ai-content-generator')),
                    'order' => (int) ($family['order'] ?? 999),
                    'rows' => [],
                ];
            }
            $families[$key]['rows'][] = $row;
        }

        uasort($families, static function (array $left, array $right): int {
            return ((int) $left['order'] <=> (int) $right['order'])
                ?: strcasecmp((string) $left['label'], (string) $right['label']);
        });

        $grouped = [];
        foreach ($families as $family) {
            $items = $family['rows'];
            usort($items, static function ($left, $right): int {
                $left_id = is_array($left) ? (string) ($left['name'] ?? $left['id'] ?? '') : (string) $left;
                $right_id = is_array($right) ? (string) ($right['name'] ?? $right['id'] ?? '') : (string) $right;
                return strnatcasecmp($left_id, $right_id);
            });
            $grouped[(string) $family['label']] = $items;
        }
        return $grouped;
    }

    /** @return array{key:string,label:string,order:int,collapsed:bool} */
    private static function classify_openai_text_family(string $model_id): array
    {
        $model_id = strtolower(trim($model_id));
        if (strpos($model_id, 'ft:') === 0 || strpos($model_id, ':ft-') !== false || strpos($model_id, ':ft_') !== false) {
            return self::family('fine-tuned', __('Fine-tuned', 'gpt3-ai-content-generator'), 90, true);
        }
        if (strpos($model_id, 'gpt-5') !== false) {
            return self::family('gpt-5', 'GPT-5', 10);
        }
        if (strpos($model_id, 'gpt-4') !== false) {
            return self::family('gpt-4', 'GPT-4', 20);
        }
        if (preg_match('/^o[1-9](?:[.\-_]|$)/', $model_id)) {
            return self::family('o-series', __('o-series', 'gpt3-ai-content-generator'), 30);
        }
        if (
            strpos($model_id, 'gpt-3.5') !== false
            || preg_match('/^(?:ada|babbage|curie|davinci)(?:[.\-_:]|$)/', $model_id)
        ) {
            return self::family('gpt-3-legacy', __('GPT-3.5 & legacy', 'gpt3-ai-content-generator'), 40, true);
        }
        return self::family('specialized-other', __('Specialized & other', 'gpt3-ai-content-generator'), 100, true);
    }

    /** @return array{key:string,label:string,order:int,collapsed:bool} */
    private static function classify_openai_image_family(string $model_id): array
    {
        $model_id = strtolower(trim($model_id));
        if (strpos($model_id, 'gpt-image') !== false || strpos($model_id, 'chatgpt-image') !== false) {
            return self::family('gpt-image', __('GPT Image', 'gpt3-ai-content-generator'), 10);
        }
        if (strpos($model_id, 'dall-e') !== false) {
            return self::family('dall-e', 'DALL·E', 20);
        }
        return self::other_family();
    }

    /** @return array{key:string,label:string,order:int,collapsed:bool} */
    private static function classify_openai_embedding_family(string $model_id): array
    {
        $model_id = strtolower(trim($model_id));
        if (strpos($model_id, 'text-embedding-3') !== false) {
            return self::family('text-embedding-3', __('Text Embedding 3', 'gpt3-ai-content-generator'), 10);
        }
        return self::family('legacy-embeddings', __('Legacy embeddings', 'gpt3-ai-content-generator'), 20, true);
    }

    /** @return array{key:string,label:string,order:int,collapsed:bool} */
    private static function classify_google_text_family(string $model_id): array
    {
        $model_id = strtolower(trim($model_id));
        if (strpos($model_id, 'gemma') !== false) {
            return self::family('gemma', 'Gemma', 50);
        }
        if (strpos($model_id, 'deep-research') !== false || strpos($model_id, 'antigravity') !== false) {
            return self::family('research-agents', __('Research & agents', 'gpt3-ai-content-generator'), 40);
        }
        if (strpos($model_id, 'flash-lite') !== false) {
            return self::family('gemini-flash-lite', __('Gemini Flash-Lite', 'gpt3-ai-content-generator'), 30);
        }
        if (strpos($model_id, 'pro') !== false && strpos($model_id, 'gemini') !== false) {
            return self::family('gemini-pro', __('Gemini Pro', 'gpt3-ai-content-generator'), 10);
        }
        if (strpos($model_id, 'flash') !== false && strpos($model_id, 'gemini') !== false) {
            return self::family('gemini-flash', __('Gemini Flash', 'gpt3-ai-content-generator'), 20);
        }
        return self::family('specialized-other', __('Specialized & other', 'gpt3-ai-content-generator'), 100, true);
    }

    /** @return array{key:string,label:string,order:int,collapsed:bool} */
    private static function classify_anthropic_family(string $model_id): array
    {
        $model_id = strtolower(trim($model_id));
        foreach (['fable', 'opus', 'sonnet', 'haiku'] as $order => $family_key) {
            if (strpos($model_id, $family_key) !== false) {
                return self::family($family_key, ucfirst($family_key), ($order + 1) * 10);
            }
        }
        return self::other_family();
    }

    /** @return array{key:string,label:string,order:int,collapsed:bool} */
    private static function classify_publisher_family(string $model_id, string $fallback_label): array
    {
        $model_id = ltrim(strtolower(trim($model_id)), '~');
        $parts = explode('/', $model_id, 2);
        $publisher = sanitize_key((string) ($parts[0] ?? ''));
        if (count($parts) < 2 || $publisher === '') {
            $publisher = sanitize_key($fallback_label);
        }

        $publisher_aliases = [
            'meta' => 'meta-llama',
            'xai' => 'x-ai',
            'bytedance-seed' => 'bytedance',
        ];
        $publisher = $publisher_aliases[$publisher] ?? $publisher;

        $labels = [
            'openrouter' => 'OpenRouter',
            'openai' => 'OpenAI',
            'ai21' => 'AI21',
            'anthropic' => 'Anthropic',
            'aion-labs' => 'Aion Labs',
            'allenai' => 'AllenAI',
            'arcee-ai' => 'Arcee AI',
            'black-forest-labs' => 'Black Forest Labs',
            'bytedance' => 'ByteDance',
            'cognitivecomputations' => 'Cognitive Computations',
            'deepcogito' => 'Deep Cogito',
            'google' => 'Google',
            'ibm-granite' => 'IBM',
            'inclusionai' => 'Inclusion AI',
            'kwaipilot' => 'KwaiPilot',
            'mistralai' => 'Mistral',
            'meta-llama' => 'Meta',
            'deepseek' => 'DeepSeek',
            'x-ai' => 'xAI',
            'cohere' => 'Cohere',
            'qwen' => 'Qwen',
            'microsoft' => 'Microsoft',
            'amazon' => 'Amazon',
            'minimax' => 'MiniMax',
            'moonshotai' => 'Moonshot AI',
            'nvidia' => 'NVIDIA',
            'nousresearch' => 'Nous Research',
            'rekaai' => 'Reka AI',
            'stepfun' => 'StepFun',
            'thedrummer' => 'The Drummer',
            'thinkingmachines' => 'Thinking Machines',
            'z-ai' => 'Z.ai',
            'replicate' => 'Replicate',
        ];
        $orders = [
            'openrouter' => 5,
            'openai' => 10,
            'anthropic' => 20,
            'google' => 30,
            'mistralai' => 40,
            'meta-llama' => 50,
            'deepseek' => 60,
            'x-ai' => 70,
            'cohere' => 80,
        ];
        $label = $labels[$publisher] ?? self::format_dynamic_family_label($publisher ?: $fallback_label);
        return self::family('publisher-' . ($publisher ?: 'other'), $label, $orders[$publisher] ?? 100);
    }

    /** @return array{key:string,label:string,order:int,collapsed:bool} */
    private static function classify_ollama_family(string $model_id, array $row, array $metadata): array
    {
        $details = [];
        if (isset($metadata['details']) && is_array($metadata['details'])) {
            $details = $metadata['details'];
        } elseif (isset($row['details']) && is_array($row['details'])) {
            $details = $row['details'];
        }
        $family = sanitize_key((string) ($details['family'] ?? ''));
        if ($family === '' && isset($details['families']) && is_array($details['families'])) {
            $family = sanitize_key((string) reset($details['families']));
        }
        if ($family === '') {
            $family = sanitize_key((string) (explode(':', $model_id, 2)[0] ?? 'custom'));
        }
        if ($family === '') {
            $family = 'custom';
        }

        return self::family('ollama-' . $family, self::format_dynamic_family_label($family), 100);
    }

    private static function format_dynamic_family_label(string $value): string
    {
        $value = trim(str_replace(['_', '-'], ' ', $value));
        if ($value === '') {
            return __('Other', 'gpt3-ai-content-generator');
        }
        return ucwords($value);
    }

    /** @return array{key:string,label:string,order:int,collapsed:bool} */
    private static function other_family(): array
    {
        return self::family('other', __('Other', 'gpt3-ai-content-generator'), 999, true);
    }

    /** @return array{key:string,label:string,order:int,collapsed:bool} */
    private static function family(string $key, string $label, int $order, bool $collapsed = false): array
    {
        return [
            'key' => sanitize_key($key),
            'label' => sanitize_text_field($label),
            'order' => $order,
            'collapsed' => $collapsed,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function get_deprecated_ids(string $catalog_key): array
    {
        $definition = self::get_definition($catalog_key);
        $deprecated_ids = isset($definition['deprecated_ids']) && is_array($definition['deprecated_ids'])
            ? $definition['deprecated_ids']
            : [];

        return array_values(array_unique(array_filter(array_map(
            static function ($model_id): string {
                return strtolower(sanitize_text_field((string) $model_id));
            },
            $deprecated_ids
        ))));
    }

    public static function is_deprecated_id(string $catalog_key, string $model_id): bool
    {
        $model_id = strtolower(trim($model_id));
        return $model_id !== '' && in_array($model_id, self::get_deprecated_ids($catalog_key), true);
    }

    /**
     * @return array<string, string>
     */
    public static function get_legacy_option_map(): array
    {
        $option_map = [];
        foreach (self::get_definitions() as $catalog_key => $definition) {
            $option_name = isset($definition['legacy_option'])
                ? sanitize_key((string) $definition['legacy_option'])
                : '';
            if ($option_name !== '') {
                $option_map[$catalog_key] = $option_name;
            }
        }
        return $option_map;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function get_catalog_keys_by_provider(): array
    {
        $by_provider = [];
        foreach (self::get_definitions() as $catalog_key => $definition) {
            $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
            if ($provider === '') {
                continue;
            }
            if (!isset($by_provider[$provider])) {
                $by_provider[$provider] = [];
            }
            $by_provider[$provider][] = $catalog_key;
        }
        return $by_provider;
    }

    /**
     * @param array<int, mixed> $seeds
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private static function model_definition(
        string $provider,
        string $kind,
        string $capability,
        string $legacy_option,
        string $default,
        array $seeds,
        array $extra = []
    ): array {
        $definition = [
            'provider' => $provider,
            'resource_type' => 'model',
            'kind' => $kind,
            'capability' => $capability,
            'capabilities' => [$capability => true],
            'legacy_option' => $legacy_option,
            'default' => $default,
            'seeds' => $seeds,
        ];

        return array_replace_recursive($definition, $extra);
    }

    /**
     * @param array<int, mixed> $seeds
     * @return array<string, mixed>
     */
    private static function resource_definition(
        string $provider,
        string $resource_type,
        string $legacy_option,
        array $seeds
    ): array {
        return [
            'provider' => $provider,
            'resource_type' => $resource_type,
            'kind' => 'resource',
            'capability' => '',
            'capabilities' => [],
            'legacy_option' => $legacy_option,
            'default' => '',
            'seeds' => $seeds,
        ];
    }
}
