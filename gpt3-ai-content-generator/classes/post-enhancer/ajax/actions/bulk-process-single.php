<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/post-enhancer/ajax/actions/bulk-process-single.php
// Status: MODIFIED

namespace WPAICG\PostEnhancer\Ajax\Actions;

use WPAICG\PostEnhancer\Ajax\Base\AIPKit_Post_Enhancer_Base_Ajax_Action;
use WPAICG\Core\AIPKit_AI_Caller;
use WPAICG\AIPKit_Providers;
use WPAICG\AIPKIT_AI_Settings;
use WPAICG\SEO\AIPKit_SEO_Helper;
use WP_Error;
// --- ADDED: Dependencies for vector context ---
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WPAICG\Core\Stream\Vector as VectorContextBuilder;

// --- END ADDED ---

use function WPAICG\PostEnhancer\Ajax\Base\get_post_full_content;

if (!defined('ABSPATH')) {
    exit;
}

// --- ADDED: Dependency loader for vector context functions ---
$vector_logic_base_path = WPAICG_PLUGIN_DIR . 'classes/core/stream/vector/';
if (file_exists($vector_logic_base_path . 'fn-build-vector-search-context.php')) {
    require_once $vector_logic_base_path . 'fn-build-vector-search-context.php';
}

class AIPKit_PostEnhancer_Bulk_Process_Single extends AIPKit_Post_Enhancer_Base_Ajax_Action
{
    public function handle(): void
    {
        $permission_check = $this->check_permissions('aipkit_generate_title_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_error_response($permission_check);
            return;
        }

        $post = $this->get_post();
        if (is_wp_error($post)) {
            $this->send_error_response($post);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Reason: Nonce is checked in check_permissions.
        $item_config_json = isset($_POST['enhancements']) ? wp_unslash($_POST['enhancements']) : '{}';
        $item_config = json_decode($item_config_json, true);

        if (empty($item_config) || !is_array($item_config)) {
            $this->send_error_response(new WP_Error('no_enhancements_config', __('No enhancements were selected.', 'gpt3-ai-content-generator'), ['status' => 400]));
            return;
        }

        // AI setup
        $ai_caller = new AIPKit_AI_Caller();
        $global_config = AIPKit_Providers::get_default_provider_config();
        $global_ai_params = AIPKIT_AI_Settings::get_ai_parameters();

        // --- MODIFIED: Use AI config from the request, with fallback to globals ---
        $provider_raw = $item_config['ai_provider'] ?? $global_config['provider'];
        $provider = match(strtolower($provider_raw)) {
            'openai' => 'OpenAI', 'openrouter' => 'OpenRouter', 'google' => 'Google', 'azure' => 'Azure', 'deepseek' => 'DeepSeek',
            default => ucfirst(strtolower($provider_raw))
        };
        $model = $item_config['ai_model'] ?? $global_config['model'];
        $ai_params = [
            'temperature' => isset($item_config['temperature']) ? floatval($item_config['temperature']) : ($global_ai_params['temperature'] ?? 1.0),
            'max_completion_tokens' => isset($item_config['max_tokens']) ? absint($item_config['max_tokens']) : ($global_ai_params['max_completion_tokens'] ?? 4000),
        ];
        // --- END MODIFICATION ---

        // --- NEW: Extract Vector Store Settings ---
        $vector_store_enabled = ($item_config['enable_vector_store'] ?? '0') === '1';
        $vector_store_provider = $item_config['vector_store_provider'] ?? null;
        $vector_store_top_k = isset($item_config['vector_store_top_k']) ? absint($item_config['vector_store_top_k']) : 3;
        $openai_vector_store_ids = $item_config['openai_vector_store_ids'] ?? [];
        $pinecone_index_name = $item_config['pinecone_index_name'] ?? null;
        $qdrant_collection_name = $item_config['qdrant_collection_name'] ?? null;
        $vector_embedding_provider = $item_config['vector_embedding_provider'] ?? null;
        $vector_embedding_model = $item_config['vector_embedding_model'] ?? null;
        // --- END NEW ---

        // --- MODIFIED: Prepare OpenAI vector tools parameter if needed ---
        if ($vector_store_enabled && $provider === 'OpenAI' && $vector_store_provider === 'openai' && !empty($openai_vector_store_ids)) {
            $ai_params['vector_store_tool_config'] = [
                'type'             => 'file_search',
                'vector_store_ids' => $openai_vector_store_ids,
                'max_num_results'  => $vector_store_top_k,
            ];
        }
        // --- END MODIFICATION ---

        $system_instruction = 'You are an expert SEO copywriter. You follow instructions precisely. Your response must contain ONLY the generated text, with no introductory phrases, labels, or quotation marks.';
        $changes_made = [];

        // --- Start: Gather all possible placeholders ---
        $original_meta = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true) ?: (get_post_meta($post->ID, '_aioseo_description', true) ?: '');
        $original_focus_keyword = AIPKit_SEO_Helper::get_focus_keyword($post->ID);
        $placeholders = [
            '{original_title}' => $post->post_title,
            '{original_content}' => get_post_full_content($post),
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
                $category_terms = get_the_terms($post->ID, 'product_cat');
                if (!is_wp_error($category_terms) && !empty($category_terms)) {
                    $category_names = wp_list_pluck($category_terms, 'name');
                    $placeholders['{product_categories}'] = implode(', ', $category_names);
                } else {
                    $placeholders['{product_categories}'] = '';
                }

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
        $vector_store_manager = null;
        if ($vector_store_enabled) {
            if (class_exists(AIPKit_Vector_Store_Manager::class)) {
                $vector_store_manager = new AIPKit_Vector_Store_Manager();
            }

            if ($vector_store_manager && function_exists('\WPAICG\Core\Stream\Vector\build_vector_search_context_logic')) {
                $vector_context = VectorContextBuilder\build_vector_search_context_logic(
                    $ai_caller,
                    $vector_store_manager,
                    $post->post_title, // Use post title as the query
                    $item_config,      // Pass the whole config as it contains all vector settings
                    $provider,         // Main AI provider
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
        }
        // --- END: Vector Context Logic ---

        // --- REORDERED LOGIC START ---
        $fields_to_enhance = [];
        // Determine which fields are selected for enhancement based on the presence of their prompt.
        foreach (array_keys($item_config) as $field_to_enhance) {
            if (in_array($field_to_enhance, ['title', 'excerpt', 'content', 'meta', 'keyword', 'tags']) && !empty($item_config[$field_to_enhance]['prompt'])) {
                $fields_to_enhance[] = $field_to_enhance;
            }
        }

        // 1. Process keyword first if requested
        if (in_array('keyword', $fields_to_enhance) && !empty($item_config['keyword']['prompt'])) {
            $prompt = str_replace(array_keys($placeholders), array_values($placeholders), $item_config['keyword']['prompt']);
            $ai_params_kw = array_merge($ai_params, ['max_completion_tokens' => 20]);
            $keyword_result = $ai_caller->make_standard_call($provider, $model, [['role' => 'user', 'content' => $prompt]], $ai_params_kw, $system_instruction, ['post_id' => $post->ID]);

            if (is_wp_error($keyword_result)) {
                $this->send_error_response($keyword_result);
                return;
            }

            if (!empty($keyword_result['content'])) {
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

                // Adjust tokens for shorter fields
                if ($field === 'title') {
                    $current_ai_params['max_completion_tokens'] = 150;
                }
                if ($field === 'excerpt') {
                    $current_ai_params['max_completion_tokens'] = 300;
                }
                if ($field === 'meta') {
                    $current_ai_params['max_completion_tokens'] = 250;
                }
                if ($field === 'tags') {
                    $current_ai_params['max_completion_tokens'] = 100;
                }

                $ai_result = $ai_caller->make_standard_call($provider, $model, [['role' => 'user', 'content' => $prompt]], $current_ai_params, $system_instruction, ['post_id' => $post->ID]);

                if (is_wp_error($ai_result)) {
                    $this->send_error_response($ai_result);
                    return;
                }

                if (!empty($ai_result['content'])) {
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
                            $html_content = $new_value;
                            $html_content = preg_replace('/^#\s+(.*)$/m', '<h1>$1</h1>', $html_content);
                            $html_content = preg_replace('/^##\s+(.*)$/m', '<h2>$1</h2>', $html_content);
                            $html_content = preg_replace('/^###\s+(.*)$/m', '<h3>$1</h3>', $html_content);
                            $html_content = preg_replace('/^####\s+(.*)$/m', '<h4>$1</h4>', $html_content);
                            $html_content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $html_content);
                            $html_content = preg_replace('/(?<!\*)\*(?!\*|_)(.*?)(?<!\*|_)\*(?!\*)/s', '<em>$1</em>', $html_content);
                            $html_content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html_content);
                            wp_update_post(['ID' => $post->ID, 'post_content' => wp_kses_post($html_content)]);
                            $changes_made[] = 'content';
                            break;
                        case 'tags':
                            if (class_exists('\\WPAICG\\SEO\\AIPKit_SEO_Helper')) {
                                $result = \WPAICG\SEO\AIPKit_SEO_Helper::update_tags($post->ID, sanitize_text_field($new_value));
                                if ($result) {
                                    $changes_made[] = 'tags';
                                }
                            }
                            break;
                        case 'meta':
                            AIPKit_SEO_Helper::update_meta_description($post->ID, sanitize_text_field($new_value));
                            $changes_made[] = 'meta';
                            break;
                    }
                }
            }
        }
        // --- REORDERED LOGIC END ---

        // --- NEW: Update Slug after all other changes ---
        if (!empty($changes_made) && isset($item_config['generate_seo_slug']) && $item_config['generate_seo_slug'] === '1') {
            if (class_exists('\\WPAICG\\SEO\\AIPKit_SEO_Helper')) {
                \WPAICG\SEO\AIPKit_SEO_Helper::update_post_slug_for_seo($post->ID);
            }
        }
        // --- END NEW ---

        if (empty($changes_made)) {
            $this->send_error_response(new WP_Error('enhancement_failed', 'AI failed to generate any valid enhancements for this post.', ['status' => 500]));
        } else {
            wp_send_json_success(['message' => 'Post ' . $post->ID . ' enhanced successfully. Fields updated: ' . implode(', ', $changes_made)]);
        }
    }
}