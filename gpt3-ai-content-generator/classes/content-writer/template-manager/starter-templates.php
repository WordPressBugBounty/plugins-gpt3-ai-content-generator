<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/template-manager/starter-templates.php
// Status: NEW FILE

namespace WPAICG\ContentWriter\TemplateManagerMethods;

use WP_Error;
use WPAICG\AIPKit_Providers;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Returns the user meta key for starter template IDs.
 */
function get_cw_starter_templates_meta_key(): string
{
    return '_aipkit_cw_starter_template_ids';
}

/**
 * Returns the user meta key storing starter template seed version.
 */
function get_cw_starter_templates_seeded_meta_key(): string
{
    return '_aipkit_cw_starter_templates_seeded';
}

/**
 * Returns the current starter templates seed version.
 */
function get_cw_starter_templates_seeded_version(): int
{
    return 12;
}

/**
 * Gets the seeded version for a user.
 *
 * @param int $user_id
 * @return int
 */
function get_cw_starter_templates_seeded_version_for_user(int $user_id): int
{
    if (!$user_id) {
        return 0;
    }
    $seeded = get_user_meta($user_id, get_cw_starter_templates_seeded_meta_key(), true);
    if ($seeded === '' || $seeded === null) {
        return 0;
    }
    if (is_numeric($seeded)) {
        return (int)$seeded;
    }
    return $seeded ? 1 : 0;
}

/**
 * Stores the seeded version for a user.
 *
 * @param int $user_id
 * @param int $version
 * @return void
 */
function set_cw_starter_templates_seeded_version_for_user(int $user_id, int $version): void
{
    if (!$user_id) {
        return;
    }
    update_user_meta($user_id, get_cw_starter_templates_seeded_meta_key(), $version);
}

/**
 * Fetches starter template IDs for a user.
 *
 * @param int $user_id
 * @return array
 */
function get_cw_starter_template_ids_for_user(int $user_id): array
{
    if (!$user_id) {
        return [];
    }
    $ids = get_user_meta($user_id, get_cw_starter_templates_meta_key(), true);
    if (!is_array($ids)) {
        return [];
    }
    $ids = array_map('absint', $ids);
    $ids = array_filter($ids, static fn($id) => $id > 0);
    return array_values(array_unique($ids));
}

/**
 * Stores starter template IDs for a user.
 *
 * @param int $user_id
 * @param array $ids
 * @return void
 */
function set_cw_starter_template_ids_for_user(int $user_id, array $ids): void
{
    if (!$user_id) {
        return;
    }
    $ids = array_map('absint', $ids);
    $ids = array_filter($ids, static fn($id) => $id > 0);
    update_user_meta($user_id, get_cw_starter_templates_meta_key(), array_values(array_unique($ids)));
}

/**
 * Removes a starter template ID from a user's meta.
 *
 * @param int $user_id
 * @param int $template_id
 * @return void
 */
function remove_cw_starter_template_id_for_user(int $user_id, int $template_id): void
{
    if (!$user_id || !$template_id) {
        return;
    }
    $ids = get_cw_starter_template_ids_for_user($user_id);
    if (empty($ids)) {
        return;
    }
    $ids = array_filter($ids, static fn($id) => (int)$id !== (int)$template_id);
    set_cw_starter_template_ids_for_user($user_id, $ids);
}

/**
 * Ensures the first starter template is the default and removes legacy default templates.
 *
 * @param \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance
 * @return void
 */
function set_cw_short_starter_as_default(\WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance): void
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $starter_ids = get_cw_starter_template_ids_for_user($user_id);
    if (empty($starter_ids)) {
        return;
    }

    $short_template_id = (int) $starter_ids[0];
    if ($short_template_id <= 0) {
        return;
    }

    $wpdb = $managerInstance->get_wpdb();
    $table_name = $managerInstance->get_table_name();

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Reason: Direct query to a custom table. Caches will be invalidated.
    $default_template = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, template_name FROM {$table_name} WHERE user_id = %d AND template_type = 'content_writer' AND is_default = 1 LIMIT 1",
            $user_id
        ),
        ARRAY_A
    );

    $default_id = isset($default_template['id']) ? (int) $default_template['id'] : 0;
    if ($default_id !== $short_template_id) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reason: Direct update to a custom table. Caches will be invalidated.
        $wpdb->update(
            $table_name,
            ['is_default' => 0],
            ['user_id' => $user_id, 'template_type' => 'content_writer'],
            ['%d'],
            ['%d', '%s']
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reason: Direct update to a custom table. Caches will be invalidated.
        $wpdb->update(
            $table_name,
            ['is_default' => 1],
            ['id' => $short_template_id, 'user_id' => $user_id],
            ['%d'],
            ['%d', '%d']
        );
    }

    $default_names = array_values(array_unique([
        __('Default Template', 'gpt3-ai-content-generator'),
        'Default Template',
    ]));
    $default_names = array_filter($default_names, static fn($name) => $name !== '');
    if (!empty($default_names)) {
        $placeholders = implode(', ', array_fill(0, count($default_names), '%s'));
        $query = "DELETE FROM {$table_name} WHERE user_id = %d AND template_type = 'content_writer' AND template_name IN ({$placeholders}) AND id != %d";
        $prepared = $wpdb->prepare($query, array_merge([$user_id], $default_names, [$short_template_id]));
        if ($prepared) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reason: Direct delete to a custom table. Caches will be invalidated.
            $wpdb->query($prepared);
        }
    }
}

/**
 * Returns the first available model ID from a provider model list.
 *
 * @param array $models
 * @return string
 */
function get_cw_first_image_model_id(array $models): string
{
    foreach ($models as $model) {
        if (is_array($model)) {
            $model_id = $model['id'] ?? ($model['name'] ?? '');
            if (!empty($model_id)) {
                return (string) $model_id;
            }
        } elseif (is_string($model)) {
            $model_id = trim($model);
            if ($model_id !== '') {
                return $model_id;
            }
        }
    }
    return '';
}

/**
 * Attempts to resolve the Imagen 4 Ultra (Preview) model ID from Google image models.
 *
 * @param array $models
 * @return string
 */
function get_cw_google_ultra_image_model_id(array $models): string
{
    $preferred_id = 'imagen-4.0-ultra-generate-preview-06-06';
    $fallback_match = '';

    foreach ($models as $model) {
        if (!is_array($model)) {
            continue;
        }
        $model_id = isset($model['id']) ? (string) $model['id'] : '';
        $model_name = isset($model['name']) ? (string) $model['name'] : '';
        if ($model_id === $preferred_id) {
            return $preferred_id;
        }
        $combined = strtolower($model_id . ' ' . $model_name);
        if (strpos($combined, 'imagen 4 ultra') !== false || strpos($combined, 'imagen-4.0-ultra') !== false) {
            $fallback_match = $model_id ?: $fallback_match;
        }
    }

    if (!empty($fallback_match)) {
        return $fallback_match;
    }

    return get_cw_first_image_model_id($models) ?: $preferred_id;
}

/**
 * Resolves default image provider + model based on the main dashboard provider.
 *
 * @param string $main_provider
 * @return array{provider: string, model: string}
 */
function get_cw_starter_template_image_defaults(string $main_provider): array
{
    $fallback = [
        'provider' => 'openai',
        'model' => 'dall-e-3',
    ];

    if (!class_exists(AIPKit_Providers::class)) {
        return $fallback;
    }

    $provider_key = strtolower($main_provider);

    if ($provider_key === 'google') {
        $google_models = AIPKit_Providers::get_google_image_models();
        $model_id = get_cw_google_ultra_image_model_id($google_models);
        return [
            'provider' => 'google',
            'model' => $model_id,
        ];
    }

    if ($provider_key === 'azure') {
        $azure_models = AIPKit_Providers::get_azure_image_models();
        $model_id = get_cw_first_image_model_id($azure_models);
        return [
            'provider' => 'azure',
            'model' => $model_id,
        ];
    }

    if ($provider_key === 'openai') {
        return $fallback;
    }

    return $fallback;
}

/**
 * Returns the starter template definitions with prompt and config overrides.
 *
 * @param array $base_config
 * @return array
 */
function get_cw_starter_template_definitions(array $base_config): array
{
    if (empty($base_config)) {
        return [];
    }

    $default_provider = $base_config['ai_provider'] ?? 'OpenAI';
    $default_model = $base_config['ai_model'] ?? '';
    if (strtolower($default_provider) === 'google') {
        $default_model = 'gemini-2.5-flash';
    }
    $image_defaults = get_cw_starter_template_image_defaults($default_provider);
    $default_image_provider = $image_defaults['provider'] ?? 'openai';
    $default_image_model = $image_defaults['model'] ?? 'gpt-image-1';
    $default_image_count = $base_config['image_count'] ?? '1';
    $default_image_size = $base_config['image_size'] ?? 'large';
    $default_image_alignment = $base_config['image_alignment'] ?? 'none';
    $default_image_placement = $base_config['image_placement'] ?? 'after_first_h2';
    $default_image_prompt = AIPKit_Content_Writer_Prompts::get_default_image_prompt();
    $default_featured_image_prompt = AIPKit_Content_Writer_Prompts::get_default_featured_image_prompt();
    $default_meta_prompt = AIPKit_Content_Writer_Prompts::get_default_meta_prompt();
    $default_keyword_prompt = AIPKit_Content_Writer_Prompts::get_default_keyword_prompt();
    $default_excerpt_prompt = AIPKit_Content_Writer_Prompts::get_default_excerpt_prompt();
    $default_tags_prompt = AIPKit_Content_Writer_Prompts::get_default_tags_prompt();

    return [
        [
            'template_name' => __('Short (600-800 words)', 'gpt3-ai-content-generator'),
            'introduced_in' => 12,
            'config' => [
                'ai_provider' => $default_provider,
                'ai_model' => $default_model,
                'content_length' => 'short',
                'generate_meta_description' => '1',
                'generate_focus_keyword' => '1',
                'generate_excerpt' => '1',
                'generate_tags' => '1',
                'generate_images_enabled' => '1',
                'generate_featured_image' => '1',
                'image_provider' => $default_image_provider,
                'image_model' => $default_image_model,
                'image_count' => $default_image_count,
                'image_size' => $default_image_size,
                'image_alignment' => $default_image_alignment,
                'image_placement' => $default_image_placement,
                'image_prompt' => $default_image_prompt,
                'featured_image_prompt' => $default_featured_image_prompt,
                'template_scope' => 'prompts_only',
                'prompt_mode' => 'custom',
                'custom_title_prompt' => __('Write a clear, SEO-friendly title that includes the main keyword. Keep it concise and suitable for search results (about 8-12 words). Return only the title text with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                'custom_content_prompt' => __('Write a short blog post of about 600-800 words. Use headings, short paragraphs, and include the focus keyword naturally. Format the article in proper Markdown with clear H2/H3 headings, lists when helpful, and no extra commentary.

Return only the Markdown article.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                'custom_meta_prompt' => $default_meta_prompt,
                'custom_keyword_prompt' => $default_keyword_prompt,
                'custom_excerpt_prompt' => $default_excerpt_prompt,
                'custom_tags_prompt' => $default_tags_prompt,
            ],
        ],
        [
            'template_name' => __('Medium (1200-1600 words)', 'gpt3-ai-content-generator'),
            'introduced_in' => 12,
            'config' => [
                'ai_provider' => $default_provider,
                'ai_model' => $default_model,
                'content_length' => 'medium',
                'generate_meta_description' => '1',
                'generate_focus_keyword' => '1',
                'generate_excerpt' => '1',
                'generate_tags' => '1',
                'generate_images_enabled' => '1',
                'generate_featured_image' => '1',
                'image_provider' => $default_image_provider,
                'image_model' => $default_image_model,
                'image_count' => $default_image_count,
                'image_size' => $default_image_size,
                'image_alignment' => $default_image_alignment,
                'image_placement' => $default_image_placement,
                'image_prompt' => $default_image_prompt,
                'featured_image_prompt' => $default_featured_image_prompt,
                'template_scope' => 'prompts_only',
                'prompt_mode' => 'custom',
                'custom_title_prompt' => __('Write a clear, SEO-friendly title that includes the main keyword. Keep it concise and suitable for search results (about 8-12 words). Return only the title text with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                'custom_content_prompt' => __('Write a medium-length blog post of about 1200-1600 words. Use clear headings, examples, and a short conclusion. Format the article in proper Markdown with clear H2/H3 headings, lists when helpful, and no extra commentary.

Return only the Markdown article.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                'custom_meta_prompt' => $default_meta_prompt,
                'custom_keyword_prompt' => $default_keyword_prompt,
                'custom_excerpt_prompt' => $default_excerpt_prompt,
                'custom_tags_prompt' => $default_tags_prompt,
            ],
        ],
        [
            'template_name' => __('Long (2000-2500 words)', 'gpt3-ai-content-generator'),
            'introduced_in' => 12,
            'config' => [
                'ai_provider' => $default_provider,
                'ai_model' => $default_model,
                'content_length' => 'long',
                'generate_meta_description' => '1',
                'generate_focus_keyword' => '1',
                'generate_excerpt' => '1',
                'generate_tags' => '1',
                'generate_images_enabled' => '1',
                'generate_featured_image' => '1',
                'image_provider' => $default_image_provider,
                'image_model' => $default_image_model,
                'image_count' => $default_image_count,
                'image_size' => $default_image_size,
                'image_alignment' => $default_image_alignment,
                'image_placement' => $default_image_placement,
                'image_prompt' => $default_image_prompt,
                'featured_image_prompt' => $default_featured_image_prompt,
                'template_scope' => 'prompts_only',
                'prompt_mode' => 'custom',
                'custom_title_prompt' => __('Write a clear, SEO-friendly title that includes the main keyword. Keep it concise and suitable for search results (about 8-12 words). Return only the title text with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                'custom_content_prompt' => __('Write a long-form blog post of about 2000-2500 words. Use clear headings, examples, and a concise conclusion. Format the article in proper Markdown with clear H2/H3 headings, lists when helpful, and no extra commentary.

Return only the Markdown article.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                'custom_meta_prompt' => $default_meta_prompt,
                'custom_keyword_prompt' => $default_keyword_prompt,
                'custom_excerpt_prompt' => $default_excerpt_prompt,
                'custom_tags_prompt' => $default_tags_prompt,
            ],
        ],
    ];
}

/**
 * Ensures starter templates exist for the current user.
 *
 * @param \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance
 * @return void
 */
function ensure_starter_templates_exist_logic(\WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance): void
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $seeded_version = get_cw_starter_templates_seeded_version_for_user($user_id);
    $target_version = get_cw_starter_templates_seeded_version();
    if ($seeded_version >= $target_version) {
        set_cw_short_starter_as_default($managerInstance);
        return;
    }

    $existing_ids = get_cw_starter_template_ids_for_user($user_id);
    if (!empty($existing_ids)) {
        foreach ($existing_ids as $template_id) {
            delete_template_logic($managerInstance, (int)$template_id);
        }
    }
    delete_user_meta($user_id, get_cw_starter_templates_meta_key());
    delete_user_meta($user_id, get_cw_starter_templates_seeded_meta_key());

    $base_config = get_cw_base_template_config($user_id);
    if (empty($base_config)) {
        return;
    }

    $definitions = get_cw_starter_template_definitions($base_config);
    if (empty($definitions)) {
        return;
    }

    $created_ids = [];
    $existing_ids = [];
    foreach ($definitions as $definition) {
        $template_name = $definition['template_name'] ?? '';
        $config = $definition['config'] ?? [];
        $introduced_in = isset($definition['introduced_in']) ? (int)$definition['introduced_in'] : 1;
        if ($introduced_in <= $seeded_version) {
            continue;
        }
        if (!$template_name || empty($config)) {
            continue;
        }

        $result = create_template_logic($managerInstance, $template_name, $config, 'content_writer');
        if (!is_wp_error($result)) {
            $created_ids[] = (int)$result;
        }
    }

    $combined_ids = array_merge($existing_ids, $created_ids);
    if (!empty($combined_ids)) {
        set_cw_starter_template_ids_for_user($user_id, $combined_ids);
    }
    set_cw_starter_templates_seeded_version_for_user($user_id, $target_version);
    set_cw_short_starter_as_default($managerInstance);
}

/**
 * Resets starter templates for the current user.
 *
 * @param \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance
 * @return array|WP_Error
 */
function reset_starter_templates_logic(\WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance): array|WP_Error
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('not_logged_in', __('User must be logged in to reset starter templates.', 'gpt3-ai-content-generator'));
    }

    $starter_ids = get_cw_starter_template_ids_for_user($user_id);
    if (!empty($starter_ids)) {
        foreach ($starter_ids as $template_id) {
            delete_template_logic($managerInstance, (int)$template_id);
        }
    }

    delete_user_meta($user_id, get_cw_starter_templates_meta_key());
    delete_user_meta($user_id, get_cw_starter_templates_seeded_meta_key());

    ensure_starter_templates_exist_logic($managerInstance);

    return get_cw_starter_template_ids_for_user($user_id);
}
