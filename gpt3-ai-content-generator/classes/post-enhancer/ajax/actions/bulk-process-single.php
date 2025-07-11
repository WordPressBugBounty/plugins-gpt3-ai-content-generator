<?php

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
            error_log("AIPKit Bulk Enhancer: Adding OpenAI vector_store_tool_config to AI params for Post #{$post->ID}.");
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
            } else {
                error_log('AIPKit Bulk Enhancer: Vector Store Manager class not found.');
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
            error_log("AIPKit Bulk Enhancer: Added vector context to system prompt for Post #{$post->ID}.");
        }
        // --- END: Vector Context Logic ---

        // Loop through enhancements
        foreach (array_keys($item_config) as $field_to_enhance) {
            if (empty($item_config[$field_to_enhance]['prompt'])) {
                continue;
            }

            $prompt = str_replace(array_keys($placeholders), array_values($placeholders), $item_config[$field_to_enhance]['prompt']);
            $current_ai_params = $ai_params;

            $instruction_context = ['post_id' => $post->ID];

            $ai_result = $ai_caller->make_standard_call($provider, $model, [['role' => 'user', 'content' => $prompt]], $current_ai_params, $system_instruction, $instruction_context);

            if (is_wp_error($ai_result) || empty($ai_result['content'])) {
                continue;
            }

            $new_value = trim(str_replace('"', '', $ai_result['content']));

            switch ($field_to_enhance) {
                case 'title':
                    wp_update_post(['ID' => $post->ID, 'post_title' => sanitize_text_field($new_value)]);
                    $changes_made[] = 'title';
                    break;
                case 'excerpt':
                    wp_update_post(['ID' => $post->ID, 'post_excerpt' => wp_kses_post($new_value)]);
                    $changes_made[] = 'excerpt';
                    break;
                case 'content':
                    // --- START: Convert markdown to HTML ---
                    $html_content = $new_value;
                    $html_content = preg_replace('/^#\s+(.*)$/m', '<h1>$1</h1>', $html_content);
                    $html_content = preg_replace('/^##\s+(.*)$/m', '<h2>$1</h2>', $html_content);
                    $html_content = preg_replace('/^###\s+(.*)$/m', '<h3>$1</h3>', $html_content);
                    $html_content = preg_replace('/^####\s+(.*)$/m', '<h4>$1</h4>', $html_content);
                    $html_content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $html_content);
                    $html_content = preg_replace('/(?<!\*)\*(?!\*|_)(.*?)(?<!\*|_)\*(?!\*)/s', '<em>$1</em>', $html_content);
                    // --- END: Convert markdown to HTML ---
                    wp_update_post(['ID' => $post->ID, 'post_content' => wp_kses_post($html_content)]);
                    $changes_made[] = 'content';
                    break;
                case 'tags':
                    wp_set_post_tags($post->ID, sanitize_text_field($new_value), false);
                    $changes_made[] = 'tags';
                    break;
                case 'meta':
                    AIPKit_SEO_Helper::update_meta_description($post->ID, sanitize_text_field($new_value));
                    $changes_made[] = 'meta';
                    break;
                case 'keyword':
                    AIPKit_SEO_Helper::update_focus_keyword($post->ID, sanitize_text_field($new_value));
                    $changes_made[] = 'keyword';
                    break;
            }
        }

        if (empty($changes_made)) {
            $this->send_error_response(new WP_Error('enhancement_failed', 'AI failed to generate any valid enhancements for this post.', ['status' => 500]));
        } else {
            wp_send_json_success(['message' => 'Post ' . $post->ID . ' enhanced successfully. Fields updated: ' . implode(', ', $changes_made)]);
        }
    }
}
