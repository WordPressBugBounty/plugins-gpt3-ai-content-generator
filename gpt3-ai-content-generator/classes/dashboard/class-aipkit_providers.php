<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/class-aipkit_providers.php
// Status: MODIFIED

namespace WPAICG;

use WPAICG\Core\AIPKit_Models_API;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIPKit_Providers
 */
class AIPKit_Providers
{
    private static $provider_defaults = [
        'OpenAI' => [
            'api_key' => '', 'model' => 'gpt-4.1-mini', 'embedding_model' => 'text-embedding-3-small',
            'base_url' => 'https://api.openai.com', 'api_version' => 'v1',
            'store_conversation' => '0',
            'expiration_policy' => 7, // NEW: Default expiration policy in days
        ],
        'OpenRouter' => [
            'api_key' => '', 'model' => '',
            'base_url' => 'https://openrouter.ai/api', 'api_version' => 'v1',
        ],
        'Google' => [
            'api_key' => '', 'model' => '', 'embedding_model' => 'gemini-embedding-exp-03-07',
            'base_url' => 'https://generativelanguage.googleapis.com', 'api_version' => 'v1beta',
            'safety_settings' => []
        ],
        'Azure' => [
            'api_key' => '', 'model' => '', 'endpoint' => '', 'embeddings' => '',
            'api_version_authoring' => '2023-03-15-preview', 'api_version_inference' => '2025-01-01-preview',
        ],
        'DeepSeek' => [
            'api_key' => '', 'model' => '',
            'base_url' => 'https://api.deepseek.com', 'api_version' => 'v1',
        ],
        'ElevenLabs' => [
            'api_key' => '', 'voice_id' => '', 'model_id' => '',
            'base_url' => 'https://api.elevenlabs.io', 'api_version' => 'v1',
        ],
        'Pexels' => [
            'api_key' => '',
        ],
        'Pixabay' => [
            'api_key' => '',
        ],
        'Pinecone' => [
            'api_key' => '',
        ],
        'Qdrant' => [ // Ensure API key is part of defaults as it's now mandatory for cloud
            'api_key' => '', 'url' => '', 'default_collection' => '',
        ],
        'Replicate' => [
            'api_key' => '',
        ],
    ];

    private static $default_model_lists = [
        'OpenAI' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo'],
        'OpenAIEmbedding' => [
            ['id' => 'text-embedding-3-small', 'name' => 'Text Embedding 3 Small (1536)'],
            ['id' => 'text-embedding-3-large', 'name' => 'Text Embedding 3 Large (3072)'],
            ['id' => 'text-embedding-ada-002', 'name' => 'Text Embedding Ada 002 (1536)'],
        ],
        'OpenRouter' => ['anthropic/claude-3-sonnet', 'anthropic/claude-3-opus', 'cohere/command-r-plus', 'google/gemini-pro-1.5', 'meta-llama/llama-3.1-70b-instruct', 'mistralai/mistral-large', 'openai/gpt-4o', 'openai/gpt-4-turbo', 'openai/gpt-3.5-turbo', 'deepseek/deepseek-chat'],
        'Google' => ['gemini-1.5-pro-latest', 'gemini-1.5-flash-latest', 'gemini-pro'],
        'GoogleEmbedding' => [
            ['id' => 'gemini-embedding-exp-03-07', 'name' => 'Gemini Embedding (3072)'],
            ['id' => 'models/text-embedding-004', 'name' => 'Embedding 004 (768)'],
            ['id' => 'models/embedding-001', 'name' => 'Embedding 001 (768)'],
        ],
        'Azure' => [], 'DeepSeek' => ['deepseek-chat', 'deepseek-coder'],
        'ElevenLabs' => [], 'ElevenLabsModels' => ['eleven_multilingual_v2'],
        'OpenAITTS' => [['id' => 'tts-1', 'name' => 'TTS-1'], ['id' => 'tts-1-hd', 'name' => 'TTS-1-HD']],
        'OpenAISTT' => [['id' => 'whisper-1', 'name' => 'Whisper-1']],
        'PineconeIndexes'   => [],
        'QdrantCollections' => [], // Added Qdrant default
        'Replicate' => [],
    ];

    private static $model_list_options = [
        'OpenAI'           => 'aipkit_openai_model_list',
        'OpenAIEmbedding'  => 'aipkit_openai_embedding_model_list',
        'OpenRouter'       => 'aipkit_openrouter_model_list',
        'Google'           => 'aipkit_google_model_list',
        'GoogleEmbedding'  => 'aipkit_google_embedding_model_list',
        'Azure'            => 'aipkit_azure_deployment_list',
        'DeepSeek'         => 'aipkit_deepseek_model_list',
        'ElevenLabs'       => 'aipkit_elevenlabs_voice_list',
        'ElevenLabsModels' => 'aipkit_elevenlabs_model_list',
        'OpenAITTS'        => 'aipkit_openai_tts_model_list',
        'OpenAISTT'        => 'aipkit_openai_stt_model_list',
        'PineconeIndexes'   => 'aipkit_pinecone_index_list',
        'QdrantCollections' => 'aipkit_qdrant_collection_list', // Added Qdrant option
        'Replicate' => 'aipkit_replicate_model_list',
    ];

    /** @var array Holds request-level cache for model lists */
    private static $cached_model_lists = [];
    public const MODEL_LIST_TRANSIENT_TTL = 5 * MINUTE_IN_SECONDS;


    public static function get_current_provider()
    {
        $opts = get_option('aipkit_options', array());
        return isset($opts['provider']) ? $opts['provider'] : 'OpenAI';
    }

    public static function get_all_providers()
    {
        $opts = get_option('aipkit_options', array());
        $providers_changed = false;
        if (!isset($opts['providers']) || !is_array($opts['providers'])) {
            $opts['providers'] = array();
            $providers_changed = true;
        }
        $providers = & $opts['providers'];
        foreach (self::$provider_defaults as $provider_name => $defaults) {
            if (!isset($providers[$provider_name]) || !is_array($providers[$provider_name])) {
                $providers[$provider_name] = $defaults;
                $providers_changed = true;
            } else {
                // Ensure all default keys are present and prune obsolete ones
                $merged = array_merge($defaults, $providers[$provider_name]);
                $final_settings_for_provider = array_intersect_key($merged, $defaults); // Use keys from defaults as the master list
                if ($final_settings_for_provider !== $providers[$provider_name]) {
                    $providers[$provider_name] = $final_settings_for_provider;
                    $providers_changed = true;
                }
            }
        }
        if ($providers_changed) {
            update_option('aipkit_options', $opts, 'no');
        }
        return $providers;
    }

    public static function get_provider_data($provider)
    {
        $all = self::get_all_providers();
        $defaults = self::$provider_defaults[$provider] ?? [];
        return isset($all[$provider]) ? array_merge($defaults, $all[$provider]) : $defaults;
    }

    public static function get_default_provider_config()
    {
        $currentProvider = self::get_current_provider();
        $provData = self::get_provider_data($currentProvider);
        $all_possible_keys = [];
        foreach (self::$provider_defaults as $def_val) {
            $all_possible_keys = array_merge($all_possible_keys, array_keys($def_val));
        }
        $all_possible_keys = array_unique($all_possible_keys);
        $result = array_fill_keys($all_possible_keys, '');
        $result['provider'] = $currentProvider;
        foreach ($result as $key => $value) {
            if (isset($provData[$key])) {
                $result[$key] = $provData[$key];
            }
        }
        return $result;
    }

    public static function get_provider_defaults($provider)
    {
        return self::$provider_defaults[$provider] ?? [];
    }
    public static function get_provider_defaults_all(): array
    {
        return self::$provider_defaults;
    }

    public static function save_provider_data($provider, $data)
    {
        $opts = get_option('aipkit_options', array());
        if (!isset($opts['providers']) || !is_array($opts['providers'])) {
            $opts['providers'] = array();
        }
        if (!isset($opts['providers'][$provider]) || !is_array($opts['providers'][$provider])) {
            $opts['providers'][$provider] = self::$provider_defaults[$provider] ?? [];
        }

        $current_provider_settings_ref = & $opts['providers'][$provider];
        $defaults_for_provider = self::$provider_defaults[$provider] ?? [];
        $changed_for_this_provider = false;

        foreach ($defaults_for_provider as $key => $default_value) {
            if (array_key_exists($key, $data)) { // Only process keys that were sent in $data
                $new_value = $data[$key]; // Use the value from $data
                // Sanitize based on key
                if (in_array($key, ['base_url', 'endpoint', 'url'], true)) {
                    $new_value = esc_url_raw($new_value);
                } elseif ($key === 'store_conversation') {
                    $new_value = ($new_value === '1' ? '1' : '0');
                } elseif ($key === 'expiration_policy') {
                    $new_value = absint($new_value);
                } // Sanitize new field
                else {
                    $new_value = sanitize_text_field($new_value);
                }

                if (!isset($current_provider_settings_ref[$key]) || $current_provider_settings_ref[$key] !== $new_value) {
                    $current_provider_settings_ref[$key] = $new_value;
                    $changed_for_this_provider = true;
                }
            } elseif (isset($current_provider_settings_ref[$key]) && !isset($data[$key])) {
                if ($key === 'store_conversation') {
                    $current_provider_settings_ref[$key] = '0';
                    $changed_for_this_provider = true;
                }
            }
        }
        if ($changed_for_this_provider) {
            update_option('aipkit_options', $opts, 'no');
        }
    }

    public static function save_current_provider($provider)
    {
        $opts = get_option('aipkit_options', array());
        $valid_providers = array_keys(self::$provider_defaults);
        if (!in_array($provider, $valid_providers, true) || in_array($provider, ['ElevenLabs', 'Pinecone', 'Qdrant'])) {
            $provider = 'OpenAI';
        }
        if (!isset($opts['provider']) || $opts['provider'] !== $provider) {
            $opts['provider'] = $provider;
            if (!isset($opts['providers']) || !is_array($opts['providers'])) {
                $opts['providers'] = array();
            }
            if (!isset($opts['providers'][$provider]) || !is_array($opts['providers'][$provider])) {
                $opts['providers'][$provider] = self::$provider_defaults[$provider] ?? [];
            }
            update_option('aipkit_options', $opts, 'no');
        }
    }

    public static function get_model_list(string $provider_key): array
    {
        if (!isset(self::$model_list_options[$provider_key])) {
            return [];
        }

        // 1. Check static request-level cache
        if (isset(self::$cached_model_lists[$provider_key])) {
            return self::$cached_model_lists[$provider_key];
        }

        // 2. Check transient cache
        $transient_key = 'aipkit_' . strtolower($provider_key) . '_models_cache';
        $cached_value = get_transient($transient_key);
        if ($cached_value !== false) {
            self::$cached_model_lists[$provider_key] = $cached_value;
            return $cached_value;
        }

        // 3. Fetch from options (database)
        $option_name = self::$model_list_options[$provider_key];
        $model_list_from_option = get_option($option_name, []);
        $processed_model_list = [];
        $use_defaults = (empty($model_list_from_option) || !is_array($model_list_from_option));

        if ($use_defaults) {
            $default_list_raw = self::$default_model_lists[$provider_key] ?? [];
            if (in_array($provider_key, ['Google', 'Azure', 'DeepSeek'])) {
                $processed_model_list = array_map(fn ($id) => ['id' => $id, 'name' => $id], $default_list_raw);
            } elseif ($provider_key === 'OpenRouter') {
                $processed_model_list = array_map(function ($id) {
                    $name = ucfirst(str_replace(['-', '_'], ' ', preg_replace('/^[^\/]+\//', '', $id)));
                    return ['id' => $id, 'name' => $name];
                }, $default_list_raw);
            } elseif ($provider_key === 'OpenAI') {
                $formatted_list = array_map(fn ($id) => ['id' => $id, 'name' => $id], $default_list_raw);
                if (class_exists(AIPKit_Models_API::class)) {
                    $processed_model_list = AIPKit_Models_API::group_openai_models($formatted_list);
                } else {
                    $fb_groups = ['GPT-4o' => [], 'GPT-4 Turbo' => [], 'GPT-4' => [], 'GPT-3.5' => [], 'Other' => []];
                    foreach ($formatted_list as $item) {
                        $idL = strtolower($item['id']);
                        if (strpos($idL, 'gpt-4o') !== false) {
                            $fb_groups['GPT-4o'][] = $item;
                        } elseif (strpos($idL, 'gpt-4-turbo') !== false || strpos($idL, 'gpt-4-1106') !== false || strpos($idL, 'gpt-4-0125') !== false) {
                            $fb_groups['GPT-4 Turbo'][] = $item;
                        } elseif (strpos($idL, 'gpt-4') !== false) {
                            $fb_groups['GPT-4'][] = $item;
                        } elseif (strpos($idL, 'gpt-3.5') !== false) {
                            $fb_groups['GPT-3.5'][] = $item;
                        } else {
                            $fb_groups['Other'][] = $item;
                        }
                    }
                    $processed_model_list = array_filter($fb_groups);
                }
            } else {
                $processed_model_list = $default_list_raw;
            }
        } else {
            $processed_model_list = $model_list_from_option; // Already in correct format (or empty array)
        }
        $processed_model_list = is_array($processed_model_list) ? $processed_model_list : [];

        // 4. Store in caches
        self::$cached_model_lists[$provider_key] = $processed_model_list;
        set_transient($transient_key, $processed_model_list, self::MODEL_LIST_TRANSIENT_TTL);

        return $processed_model_list;
    }

    /**
     * Clears all model list caches (static and transient).
     * Called after a model sync operation.
     */
    public static function clear_model_caches(): void
    {
        self::$cached_model_lists = []; // Clear static cache
        foreach (array_keys(self::$model_list_options) as $provider_key) {
            $transient_key = 'aipkit_' . strtolower($provider_key) . '_models_cache';
            delete_transient($transient_key);
        }
    }


    public static function get_openai_models(): array
    {
        return self::get_model_list('OpenAI');
    }
    public static function get_openai_embedding_models(): array
    {
        return self::get_model_list('OpenAIEmbedding');
    }
    public static function get_openrouter_models(): array
    {
        return self::get_model_list('OpenRouter');
    }
    public static function get_google_models(): array
    {
        return self::get_model_list('Google');
    }
    public static function get_google_embedding_models(): array
    {
        return self::get_model_list('GoogleEmbedding');
    }
    public static function get_azure_deployments(): array
    {
        return self::get_model_list('Azure');
    }
    public static function get_deepseek_models(): array
    {
        return self::get_model_list('DeepSeek');
    }
    public static function get_elevenlabs_voices(): array
    {
        return self::get_model_list('ElevenLabs');
    }
    public static function get_elevenlabs_models(): array
    {
        return self::get_model_list('ElevenLabsModels');
    }
    public static function get_openai_tts_models(): array
    {
        return self::get_model_list('OpenAITTS');
    }
    public static function get_openai_stt_models(): array
    {
        return self::get_model_list('OpenAISTT');
    }
    public static function get_pinecone_indexes(): array
    {
        return self::get_model_list('PineconeIndexes');
    }
    public static function get_qdrant_collections(): array
    {
        return self::get_model_list('QdrantCollections');
    }
    public static function get_replicate_models(): array
    {
        return self::get_model_list('Replicate');
    }
}
