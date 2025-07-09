<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/autogpt/cron/event-processor/processor/content-enhancement/process-enhancement-item.php
// Status: MODIFIED
// I have corrected the call to build_vector_search_context_logic and added the requested logging.

namespace WPAICG\AutoGPT\Cron\EventProcessor\Processor\ContentEnhancement;

use WPAICG\Core\AIPKit_AI_Caller;
use WPAICG\SEO\AIPKit_SEO_Helper;
use WP_Error;
// --- ADDED: Dependencies for vector context ---
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WPAICG\Core\Stream\Vector as VectorContextBuilder;

if (!defined('ABSPATH')) {
    exit;
}

// --- ADDED: Dependency loader for vector context functions ---
$vector_logic_base_path = WPAICG_PLUGIN_DIR . 'classes/core/stream/vector/';
if (file_exists($vector_logic_base_path . 'fn-build-vector-search-context.php')) {
    require_once $vector_logic_base_path . 'fn-build-vector-search-context.php';
}

/**
 * Processes a single "enhance_existing_content" queue item.
 *
 * @param array $item The queue item from the database.
 * @param array $item_config The decoded item_config from the queue item.
 * @return array ['status' => 'success'|'error', 'message' => '...']
 */
function process_enhancement_item_logic(array $item, array $item_config): array
{
    $post_id = absint($item['target_identifier']);
    $post = get_post($post_id);

    if (!$post || $post->post_status !== 'publish') {
        return ['status' => 'success', 'message' => "Skipped post #{$post_id}: Post not found or is not published."];
    }

    $ai_caller = new AIPKit_AI_Caller();
    $ai_params = ['temperature' => floatval($item_config['ai_temperature'] ?? 1.0)];
    $user_max_tokens = absint($item_config['content_max_tokens'] ?? 250);
    $system_instruction = 'You are an expert SEO copywriter. You follow instructions precisely. Your response must contain ONLY the generated text, with no introductory phrases, labels, or quotation marks.';
    $changes_made = [];

    // --- START: Gather all possible placeholders ---
    $original_meta = get_post_meta($post_id, '_yoast_wpseo_metadesc', true) ?: (get_post_meta($post_id, '_aioseo_description', true) ?: '');
    $placeholders = [
        '{original_title}' => $post->post_title,
        '{original_content}' => wp_strip_all_tags($post->post_content),
        '{original_excerpt}' => $post->post_excerpt,
        '{original_meta_description}' => $original_meta,
    ];

    if ($post->post_type === 'product' && class_exists('WooCommerce')) {
        $product = wc_get_product($post->ID);
        if ($product) {
            $placeholders['{price}'] = $product->get_price();
            $placeholders['{regular_price}'] = $product->get_regular_price();
            $placeholders['{sale_price}'] = $product->get_sale_price();
            $placeholders['{sku}'] = $product->get_sku();
            $placeholders['{stock_quantity}'] = $product->get_stock_quantity() ?? 'N/A';
            $placeholders['{stock_status}'] = $product->get_stock_status();
            $placeholders['{weight}'] = $product->get_weight();
            $placeholders['{length}'] = $product->get_length();
            $placeholders['{width}'] = $product->get_width();
            $placeholders['{height}'] = $product->get_height();
            $placeholders['{short_description}'] = wp_strip_all_tags($product->get_short_description());
            $placeholders['{purchase_note}'] = $product->get_purchase_note();

            $attributes = $product->get_attributes();
            $attribute_string = '';
            foreach ($attributes as $attribute) {
                if ($attribute->is_taxonomy()) {
                    $terms = wp_get_post_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']);
                    if (!is_wp_error($terms) && !empty($terms)) {
                        $attribute_string .= wc_attribute_label($attribute->get_name()) . ': ' . implode(', ', $terms) . '; ';
                    }
                } else {
                    $attribute_string .= wc_attribute_label($attribute->get_name()) . ': ' . implode(', ', $attribute->get_options()) . '; ';
                }
            }
            $placeholders['{attributes}'] = rtrim($attribute_string, '; ');
        }
    }
    // --- END: Gather placeholders ---

    // --- NEW: Vector Context Logic ---
    $vector_context = '';
    if (($item_config['enable_vector_store'] ?? '0') === '1' && class_exists(AIPKit_Vector_Store_Manager::class) && function_exists('\WPAICG\Core\Stream\Vector\build_vector_search_context_logic')) {
        $vector_store_manager = new AIPKit_Vector_Store_Manager();
        $vector_context = VectorContextBuilder\build_vector_search_context_logic(
            $ai_caller,
            $vector_store_manager,
            $post->post_title, // Use post title as the query
            $item_config,      // Pass the whole task config
            $item_config['ai_provider'],
            null, // frontend_active_openai_vs_id
            $item_config['pinecone_index_name'] ?? null,
            null, // frontend_active_pinecone_namespace
            $item_config['qdrant_collection_name'] ?? null,
            null // No frontend context in cron jobs
        );
        if (!empty($vector_context)) {
            $system_instruction = "## Relevant information from knowledge base:\n" . trim($vector_context) . "\n##\n\n" . $system_instruction;
        }
    }
    // --- END: Vector Context Logic ---

    // 1. Enhance Title
    if (isset($item_config['update_title']) && $item_config['update_title'] === '1' && !empty($item_config['title_prompt'])) {
        $prompt = str_replace(array_keys($placeholders), array_values($placeholders), $item_config['title_prompt']);

        $ai_params_title = array_merge($ai_params, ['max_completion_tokens' => min($user_max_tokens, 150)]);
        $title_result = $ai_caller->make_standard_call($item_config['ai_provider'], $item_config['ai_model'], [['role' => 'user', 'content' => $prompt]], $ai_params_title, $system_instruction);

        if (!is_wp_error($title_result) && !empty($title_result['content'])) {
            $new_title = trim(str_replace('"', '', $title_result['content']));
            wp_update_post(['ID' => $post_id, 'post_title' => $new_title]);
            $changes_made[] = 'title';
        } else {
            error_log("AIPKit Content Enhancement: Title generation failed for Post #{$post_id}. Error: " . (is_wp_error($title_result) ? $title_result->get_error_message() : 'Empty response.'));
        }
    }

    // 2. Enhance Excerpt
    if (isset($item_config['update_excerpt']) && $item_config['update_excerpt'] === '1' && !empty($item_config['excerpt_prompt'])) {
        $prompt = str_replace(array_keys($placeholders), array_values($placeholders), $item_config['excerpt_prompt']);

        $ai_params_excerpt = array_merge($ai_params, ['max_completion_tokens' => min($user_max_tokens, 300)]);
        $excerpt_result = $ai_caller->make_standard_call($item_config['ai_provider'], $item_config['ai_model'], [['role' => 'user', 'content' => $prompt]], $ai_params_excerpt, $system_instruction);

        if (!is_wp_error($excerpt_result) && !empty($excerpt_result['content'])) {
            $new_excerpt = trim(str_replace('"', '', $excerpt_result['content']));
            wp_update_post(['ID' => $post_id, 'post_excerpt' => $new_excerpt]);
            $changes_made[] = 'excerpt';
        } else {
            error_log("AIPKit Content Enhancement: Excerpt generation failed for Post #{$post_id}. Error: " . (is_wp_error($excerpt_result) ? $excerpt_result->get_error_message() : 'Empty response.'));
        }
    }

    // 3. Enhance Content
    if (isset($item_config['update_content']) && $item_config['update_content'] === '1' && !empty($item_config['content_prompt'])) {
        $original_content = $post->post_content;
        $media_tags = [];
        $placeholder_prefix = '[AIPKIT_MEDIA_PLACEHOLDER_';
        $placeholder_suffix = ']';

        $content_for_ai = preg_replace_callback(
            '/(<img[^>]+>|<video[^>]*>.*?<\/video>|<figure[^>]*>.*?<\/figure>|<iframe[^>]*>.*?<\/iframe>|\[.*?\])/is',
            function ($matches) use (&$media_tags, $placeholder_prefix, $placeholder_suffix) {
                $media_tags[] = $matches[0];
                $index = count($media_tags) - 1;
                return $placeholder_prefix . $index . $placeholder_suffix;
            },
            $original_content
        );

        $content_for_ai = wp_strip_all_tags($content_for_ai);

        $prompt_placeholders = $placeholders;
        $prompt_placeholders['{original_content}'] = $content_for_ai;
        $prompt = str_replace(array_keys($prompt_placeholders), array_values($prompt_placeholders), $item_config['content_prompt']);

        $system_instruction_for_content = 'You are an expert editor. Your task is to rewrite and improve the provided article content while preserving any special placeholders like [AIPKIT_MEDIA_PLACEHOLDER_...]. Return only the full, revised article content, formatted with standard HTML paragraph tags (<p> and </p>). Do not use any other HTML tags.';

        $ai_params_content = array_merge($ai_params, ['max_completion_tokens' => min($user_max_tokens, 4000)]);
        $content_result = $ai_caller->make_standard_call($item_config['ai_provider'], $item_config['ai_model'], [['role' => 'user', 'content' => $prompt]], $ai_params_content, $system_instruction_for_content);

        if (!is_wp_error($content_result) && !empty($content_result['content'])) {
            $enhanced_content = $content_result['content'];

            if (!empty($media_tags)) {
                foreach ($media_tags as $index => $tag) {
                    $placeholder_to_find = $placeholder_prefix . $index . $placeholder_suffix;
                    $enhanced_content = str_ireplace($placeholder_to_find, $tag, $enhanced_content);
                }
            }

            $new_content = wp_kses_post($enhanced_content);
            wp_update_post(['ID' => $post_id, 'post_content' => $new_content]);
            $changes_made[] = 'content';
        } else {
            error_log("AIPKit Content Enhancement: Content rewrite failed for Post #{$post_id}. Error: " . (is_wp_error($content_result) ? $content_result->get_error_message() : 'Empty response.'));
        }
    }

    // 4. Enhance Meta Description
    if (isset($item_config['update_meta']) && $item_config['update_meta'] === '1' && !empty($item_config['meta_prompt'])) {
        $prompt = str_replace(array_keys($placeholders), array_values($placeholders), $item_config['meta_prompt']);

        $ai_params_meta = array_merge($ai_params, ['max_completion_tokens' => min($user_max_tokens, 250)]);
        $meta_result = $ai_caller->make_standard_call($item_config['ai_provider'], $item_config['ai_model'], [['role' => 'user', 'content' => $prompt]], $ai_params_meta, $system_instruction);

        if (!is_wp_error($meta_result) && !empty($meta_result['content'])) {
            $new_meta = trim(str_replace('"', '', $meta_result['content']));
            AIPKit_SEO_Helper::update_meta_description($post_id, $new_meta);
            $changes_made[] = 'meta description';
        } else {
            error_log("AIPKit Content Enhancement: Meta description generation failed for Post #{$post_id}. Error: " . (is_wp_error($meta_result) ? $meta_result->get_error_message() : 'Empty response.'));
        }
    }

    if (empty($changes_made)) {
        return ['status' => 'success', 'message' => "No enhancements were enabled or all AI calls failed for post #{$post_id}."];
    } else {
        return ['status' => 'success', 'message' => "Post #{$post_id} enhanced. Updated: " . implode(', ', $changes_made) . "."];
    }
}
