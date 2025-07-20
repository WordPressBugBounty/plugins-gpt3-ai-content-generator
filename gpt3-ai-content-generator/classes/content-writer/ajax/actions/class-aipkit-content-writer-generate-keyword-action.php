<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/class-aipkit-content-writer-generate-keyword-action.php
// Status: NEW FILE

namespace WPAICG\ContentWriter\Ajax\Actions;

use WPAICG\ContentWriter\Ajax\AIPKit_Content_Writer_Base_Ajax_Action;
use WPAICG\ContentWriter\Prompt\AIPKit_Content_Writer_Summarizer;
use WPAICG\ContentWriter\Prompt\AIPKit_Content_Writer_Keyword_Prompt_Builder;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the dedicated AJAX action for generating an SEO focus keyword.
 */
class AIPKit_Content_Writer_Generate_Keyword_Action extends AIPKit_Content_Writer_Base_Ajax_Action
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
        $provider_raw = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $model = isset($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $prompt_mode = isset($_POST['prompt_mode']) ? sanitize_key($_POST['prompt_mode']) : 'standard';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $custom_keyword_prompt = isset($_POST['custom_keyword_prompt']) ? sanitize_textarea_field(wp_unslash($_POST['custom_keyword_prompt'])) : null;


        if (empty($generated_content) || empty($final_title) || empty($provider_raw) || empty($model)) {
            $this->send_wp_error(new WP_Error('missing_keyword_data', 'Missing required data for keyword generation.', ['status' => 400]));
            return;
        }

        $provider = match (strtolower($provider_raw)) {
            'openai' => 'OpenAI', 'openrouter' => 'OpenRouter', 'google' => 'Google', 'azure' => 'Azure', 'deepseek' => 'DeepSeek',
            default => $provider_raw
        };

        if (!class_exists(AIPKit_Content_Writer_Summarizer::class) || !class_exists(AIPKit_Content_Writer_Keyword_Prompt_Builder::class) || !$this->get_ai_caller()) {
            $this->send_wp_error(new WP_Error('missing_keyword_dependencies', 'A component required for keyword generation is missing.', ['status' => 500]));
            return;
        }

        $content_summary = AIPKit_Content_Writer_Summarizer::summarize($generated_content);
        $keyword_user_prompt = AIPKit_Content_Writer_Keyword_Prompt_Builder::build($final_title, $content_summary, $prompt_mode, $custom_keyword_prompt);
        $keyword_ai_params = ['max_completion_tokens' => 20, 'temperature' => 0.2];

        $keyword_result = $this->get_ai_caller()->make_standard_call(
            $provider,
            $model,
            [['role' => 'user', 'content' => $keyword_user_prompt]],
            $keyword_ai_params,
            'You are an SEO expert. Your task is to provide the single best focus keyword for a piece of content.'
        );

        if (is_wp_error($keyword_result)) {
            $this->send_wp_error($keyword_result);
            return;
        }

        $focus_keyword = !empty($keyword_result['content']) ? trim(str_replace(['"', "'", '.'], '', $keyword_result['content'])) : null;
        if (empty($focus_keyword)) {
            $this->send_wp_error(new WP_Error('keyword_gen_empty', 'AI did not return a valid focus keyword.', ['status' => 500]));
            return;
        }

        wp_send_json_success(['focus_keyword' => $focus_keyword]);
    }
}
