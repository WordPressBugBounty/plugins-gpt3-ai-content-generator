<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/class-aipkit-content-writer-generate-excerpt-action.php
// Status: NEW FILE

namespace WPAICG\ContentWriter\Ajax\Actions;

use WPAICG\ContentWriter\Ajax\AIPKit_Content_Writer_Base_Ajax_Action;
use WPAICG\ContentWriter\Prompt\AIPKit_Content_Writer_Summarizer;
use WPAICG\ContentWriter\Prompt\AIPKit_Content_Writer_Excerpt_Prompt_Builder;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the dedicated AJAX action for generating an SEO excerpt after main content is created.
 */
class AIPKit_Content_Writer_Generate_Excerpt_Action extends AIPKit_Content_Writer_Base_Ajax_Action
{
    public function handle()
    {
        $permission_check = $this->check_module_access_permissions('content-writer', 'aipkit_content_writer_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $generated_content = isset($_POST['generated_content']) ? wp_kses_post(wp_unslash($_POST['generated_content'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $final_title = isset($_POST['final_title']) ? sanitize_text_field(wp_unslash($_POST['final_title'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $keywords = isset($_POST['keywords']) ? sanitize_text_field(wp_unslash($_POST['keywords'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $provider_raw = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $model = isset($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $prompt_mode = isset($_POST['prompt_mode']) ? sanitize_key($_POST['prompt_mode']) : 'standard';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $custom_excerpt_prompt = isset($_POST['custom_excerpt_prompt']) ? sanitize_textarea_field(wp_unslash($_POST['custom_excerpt_prompt'])) : null;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $content_max_tokens = isset($_POST['content_max_tokens']) ? intval($_POST['content_max_tokens']) : null;


        if (empty($generated_content) || empty($final_title) || empty($provider_raw) || empty($model)) {
            $this->send_wp_error(new WP_Error('missing_excerpt_data', 'Missing required data for excerpt generation.', ['status' => 400]));
            return;
        }

        $provider = match (strtolower($provider_raw)) {
            'openai' => 'OpenAI', 'openrouter' => 'OpenRouter', 'google' => 'Google', 'azure' => 'Azure', 'deepseek' => 'DeepSeek',
            default => $provider_raw
        };

        if (!class_exists(AIPKit_Content_Writer_Summarizer::class) || !class_exists(AIPKit_Content_Writer_Excerpt_Prompt_Builder::class) || !$this->get_ai_caller()) {
            $this->send_wp_error(new WP_Error('missing_excerpt_dependencies', 'A component required for excerpt generation is missing.', ['status' => 500]));
            return;
        }

        $content_summary = AIPKit_Content_Writer_Summarizer::summarize($generated_content);
        $excerpt_user_prompt = AIPKit_Content_Writer_Excerpt_Prompt_Builder::build($final_title, $content_summary, $keywords, $prompt_mode, $custom_excerpt_prompt);
        $excerpt_system_instruction = 'You are an expert copywriter. Your task is to provide an engaging excerpt for a piece of content.';
        
        // Use the max tokens from template/form settings, or default to 200 for excerpt generation
        $max_tokens = isset($content_max_tokens) && $content_max_tokens > 0 ? $content_max_tokens : 4000;
        $excerpt_ai_params = ['max_completion_tokens' => $max_tokens];
        
        $excerpt_result = $this->get_ai_caller()->make_standard_call(
            $provider,
            $model,
            [['role' => 'user', 'content' => $excerpt_user_prompt]],
            $excerpt_ai_params,
            $excerpt_system_instruction
        );

        if (is_wp_error($excerpt_result)) {
            $this->send_wp_error($excerpt_result);
            return;
        }

        $excerpt = !empty($excerpt_result['content']) ? trim(str_replace(['"', "'"], '', $excerpt_result['content'])) : null;
        if (empty($excerpt)) {
            $this->send_wp_error(new WP_Error('excerpt_gen_empty', 'AI did not return a valid excerpt.', ['status' => 500]));
            return;
        }

        wp_send_json_success(['excerpt' => $excerpt]);
    }
}
