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
    $original_meta = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true) ?: (get_post_meta($post->ID, '_aioseo_description', true) ?: '');
    $original_focus_keyword = AIPKit_SEO_Helper::get_focus_keyword($post->ID);
    $placeholders = [
        '{original_title}' => $post->post_title,
        '{original_content}' => wp_strip_all_tags($post->post_content),
        '{original_excerpt}' => $post->post_excerpt,
        '{original_meta_description}' => $original_meta,
        '{original_focus_keyword}' => $original_focus_keyword ?: '',
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

    // --- Vector Context Logic ---
    $vector_context = '';
    $vector_store_manager = null;
    if (($item_config['enable_vector_store'] ?? '0') === '1') {
        if (class_exists(AIPKit_Vector_Store_Manager::class)) {
            $vector_store_manager = new AIPKit_Vector_Store_Manager();
        } else {
            error_log('AIPKit Bulk Enhancer: Vector Store Manager class not found.');
        }

        if ($vector_store_manager && function_exists('\WPAICG\Core\Stream\Vector\build_vector_search_context_logic')) {
            $vector_context = VectorContextBuilder\build_vector_search_context_logic(
                $ai_caller,
                $vector_store_manager,
                $post->post_title, // Use post title as the query
                $item_config,      // Pass the whole task config
                $item_config['ai_provider'],         // Main AI provider
                null,
                null,
                null,
                null,
                null // No frontend context in bulk enhancer
            );
        }
    }
    if (!empty($vector_context)) {
        $system_instruction = "## Relevant information from knowledge base:\n" . trim($vector_context) . "\n##\n\n" . $system_instruction;
        error_log("AIPKit Bulk Enhancer: Added vector context to system prompt for Post #{$post->ID}.");
    }
    // --- END: Vector Context Logic ---

    // --- REORDERED LOGIC START ---
    $fields_to_enhance = [];
    if (isset($item_config['update_title']) && $item_config['update_title'] === '1') {
        $fields_to_enhance[] = 'title';
    }
    if (isset($item_config['update_excerpt']) && $item_config['update_excerpt'] === '1') {
        $fields_to_enhance[] = 'excerpt';
    }
    if (isset($item_config['update_content']) && $item_config['update_content'] === '1') {
        $fields_to_enhance[] = 'content';
    }
    if (isset($item_config['update_meta']) && $item_config['update_meta'] === '1') {
        $fields_to_enhance[] = 'meta';
    }
    if (isset($item_config['update_keyword']) && $item_config['update_keyword'] === '1') {
        $fields_to_enhance[] = 'keyword';
    } // Assuming 'update_keyword' is the form field name

    // 1. Process keyword first if requested
    if (in_array('keyword', $fields_to_enhance) && !empty($item_config['keyword_prompt'])) {
        $prompt = str_replace(array_keys($placeholders), array_values($placeholders), $item_config['keyword_prompt']);
        $ai_params_kw = array_merge($ai_params, ['max_completion_tokens' => min($user_max_tokens, 20)]);
        $keyword_result = $ai_caller->make_standard_call($item_config['ai_provider'], $item_config['ai_model'], [['role' => 'user', 'content' => $prompt]], $ai_params_kw, $system_instruction);

        if (!is_wp_error($keyword_result) && !empty($keyword_result['content'])) {
            $new_keyword = trim(str_replace('"', '', $keyword_result['content']));
            AIPKit_SEO_Helper::update_focus_keyword($post->ID, $new_keyword);
            $placeholders['{original_focus_keyword}'] = $new_keyword; // UPDATE placeholder for subsequent calls
            $changes_made[] = 'focus keyword';
        }
    }

    // 2. Process other fields
    foreach ($fields_to_enhance as $field) {
        if ($field === 'keyword') {
            continue; // Already processed
        }

        if (!empty($item_config[$field]['prompt'])) {
            $prompt = str_replace(array_keys($placeholders), array_values($placeholders), $item_config[$field]['prompt']);
            $current_ai_params = $ai_params;

            if ($field === 'title') {
                $current_ai_params['max_completion_tokens'] = min($user_max_tokens, 150);
            }
            if ($field === 'excerpt') {
                $current_ai_params['max_completion_tokens'] = min($user_max_tokens, 300);
            }
            if ($field === 'meta') {
                $current_ai_params['max_completion_tokens'] = min($user_max_tokens, 250);
            }

            $ai_result = $ai_caller->make_standard_call($item_config['ai_provider'], $item_config['ai_model'], [['role' => 'user', 'content' => $prompt]], $current_ai_params, $system_instruction);

            if (!is_wp_error($ai_result) && !empty($ai_result['content'])) {
                $new_value = trim(str_replace('"', '', $ai_result['content']));
                switch ($field) {
                    case 'title':
                        wp_update_post(['ID' => $post->ID, 'post_title' => sanitize_text_field($new_value)]);
                        $changes_made[] = 'title';
                        break;
                    case 'excerpt':
                        wp_update_post(['ID' => $post->ID, 'post_excerpt' => wp_kses_post($new_value)]);
                        $changes_made[] = 'excerpt';
                        break;
                    case 'content':
                        wp_update_post(['ID' => $post->ID, 'post_content' => wp_kses_post($new_value)]);
                        $changes_made[] = 'content';
                        break;
                    case 'meta':
                        AIPKit_SEO_Helper::update_meta_description($post->ID, sanitize_text_field($new_value));
                        $changes_made[] = 'meta description';
                        break;
                }
            }
        }
    }
    // --- REORDERED LOGIC END ---

    if (empty($changes_made)) {
        return ['status' => 'success', 'message' => "No enhancements were enabled or all AI calls failed for post #{$post_id}."];
    } else {
        return ['status' => 'success', 'message' => "Post #{$post_id} enhanced. Updated: " . implode(', ', $changes_made) . "."];
    }
}
