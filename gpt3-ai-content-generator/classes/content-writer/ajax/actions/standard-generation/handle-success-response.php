<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/standard-generation/handle-success-response.php
// Status: MODIFIED
// I have updated this file to also generate and include the excerpt in the success response.

namespace WPAICG\ContentWriter\Ajax\Actions\StandardGeneration;

use WPAICG\ContentWriter\Ajax\Actions\AIPKit_Content_Writer_Standard_Generation_Action;
use WPAICG\AIPKit\Addons\AIPKit_IP_Anonymization;
use WPAICG\ContentWriter\Prompt\AIPKit_Content_Writer_Summarizer;
use WPAICG\ContentWriter\Prompt\AIPKit_Content_Writer_Meta_Prompt_Builder;
use WPAICG\ContentWriter\Prompt\AIPKit_Content_Writer_Keyword_Prompt_Builder;
use WPAICG\ContentWriter\Prompt\AIPKit_Content_Writer_Excerpt_Prompt_Builder;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Image_Handler; // Added for images
use WP_Error;
use WPAICG\Chat\Storage\LogStorage;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles a successful response from the AI call by logging it and sending a JSON success response.
 * MODIFIED: Now generates and includes SEO meta description and focus keyword if requested.
 * MODIFIED: Now generates and includes AI images if requested.
 *
 * @param AIPKit_Content_Writer_Standard_Generation_Action $handler The handler instance.
 * @param array $result The successful result array from the AI call.
 * @param array $validated_params The validated parameters from the request.
 * @param string $conversation_uuid The UUID of this interaction.
 * @return void
 */
function handle_success_response_logic(AIPKit_Content_Writer_Standard_Generation_Action $handler, array $result, array $validated_params, string $conversation_uuid): void
{
    $content = $result['content'] ?? '';
    $usage = $result['usage'] ?? null;
    $request_payload_log_for_success = $result['request_payload_log'] ?? null;
    $meta_description = null;
    $focus_keyword = null;
    $excerpt = null;
    $image_data = null; // New variable for image data

    // Log main content generation
    if ($handler->log_storage) {
        $handler->log_storage->log_message([
            'bot_id'            => null,
            'user_id'           => get_current_user_id(),
            'session_id'        => null,
            'conversation_uuid' => $conversation_uuid,
            'module'            => 'content_writer',
            'is_guest'          => 0,
            'role'              => implode(', ', wp_get_current_user()->roles),
            'ip_address'        => AIPKit_IP_Anonymization::maybe_anonymize($_SERVER['REMOTE_ADDR'] ?? null),
            'message_role'      => 'bot',
            'message_content'   => $content,
            'timestamp'         => time(),
            'ai_provider'       => $validated_params['provider'],
            'ai_model'          => $validated_params['model'],
            'usage'             => $usage,
            'request_payload'   => $request_payload_log_for_success,
        ]);
    }

    // --- Generate Images ---
    if (($validated_params['generate_images_enabled'] ?? '0') === '1' && !empty($content) && class_exists(AIPKit_Content_Writer_Image_Handler::class)) {
        $image_handler = new AIPKit_Content_Writer_Image_Handler();
        $final_title = $validated_params['content_title'] ?? ''; // This is the original topic
        $final_keywords = !empty($validated_params['inline_keywords']) ? $validated_params['inline_keywords'] : ($validated_params['content_keywords'] ?? '');
        $image_result = $image_handler->generate_and_prepare_images($validated_params, $final_title, $final_keywords, $final_title);

        if (is_wp_error($image_result)) {
            error_log('AIPKit Standard Gen: Image generation failed. Error: ' . $image_result->get_error_message());
        } else {
            $image_data = $image_result;
        }
    }
    // --- End Image Generation ---

    // --- Generate SEO Data if requested ---
    $generate_meta = ($validated_params['generate_meta_description'] ?? '0') === '1';
    $generate_keyword = ($validated_params['generate_focus_keyword'] ?? '0') === '1';
    $generate_excerpt = ($validated_params['generate_excerpt'] ?? '0') === '1';
    $prompt_mode = $validated_params['prompt_mode'] ?? 'standard';
    $should_generate_seo = ($generate_meta || $generate_keyword || $generate_excerpt) && !empty($content);


    if ($should_generate_seo) {
        $content_summary = AIPKit_Content_Writer_Summarizer::summarize($content);
        $final_title = $validated_params['content_title'] ?? '';
        $final_keywords = !empty($validated_params['inline_keywords']) ? $validated_params['inline_keywords'] : ($validated_params['content_keywords'] ?? '');

        if ($generate_excerpt && class_exists(AIPKit_Content_Writer_Excerpt_Prompt_Builder::class)) {
            $custom_excerpt_prompt = $validated_params['custom_excerpt_prompt'] ?? null;
            $excerpt_user_prompt = AIPKit_Content_Writer_Excerpt_Prompt_Builder::build($final_title, $content_summary, $prompt_mode, $custom_excerpt_prompt);
            $excerpt_system_instruction = 'You are an expert copywriter. Your task is to provide an engaging excerpt for a piece of content.';
            $excerpt_ai_params = ['max_completion_tokens' => 200, 'temperature' => 1];

            $excerpt_result = $handler->get_ai_caller()->make_standard_call(
                $validated_params['provider'],
                $validated_params['model'],
                [['role' => 'user', 'content' => $excerpt_user_prompt]],
                $excerpt_ai_params,
                $excerpt_system_instruction
            );
            if (!is_wp_error($excerpt_result) && !empty($excerpt_result['content'])) {
                $excerpt = trim(str_replace(['"', "'"], '', $excerpt_result['content']));
            } else {
                error_log('AIPKit Standard Gen: Excerpt generation failed. Error: ' . (is_wp_error($excerpt_result) ? $excerpt_result->get_error_message() : 'Empty response.'));
            }
        }


        if ($generate_meta && class_exists(AIPKit_Content_Writer_Meta_Prompt_Builder::class)) {
            $custom_meta_prompt = $validated_params['custom_meta_prompt'] ?? null;
            $meta_user_prompt = AIPKit_Content_Writer_Meta_Prompt_Builder::build($final_title, $content_summary, $final_keywords, $prompt_mode, $custom_meta_prompt);
            $meta_system_instruction = 'You are an SEO expert specializing in writing meta descriptions.';
            $meta_ai_params = ['max_completion_tokens' => 100, 'temperature' => 1];

            $meta_result = $handler->get_ai_caller()->make_standard_call(
                $validated_params['provider'],
                $validated_params['model'],
                [['role' => 'user', 'content' => $meta_user_prompt]],
                $meta_ai_params,
                $meta_system_instruction
            );

            if (!is_wp_error($meta_result) && !empty($meta_result['content'])) {
                $meta_description = trim(str_replace(['"', "'"], '', $meta_result['content']));
                $meta_usage = $meta_result['usage'] ?? null;
            } else {
                error_log('AutoGPT Meta Gen Failed: ' . (is_wp_error($meta_result) ? $meta_result->get_error_message() : 'Empty response'));
            }
        }

        if ($generate_keyword) {
            if (!empty($final_keywords)) {
                list($first_keyword) = array_map('trim', explode(',', $final_keywords, 2));
                $focus_keyword = $first_keyword;
            } else {
                if (class_exists(AIPKit_Content_Writer_Keyword_Prompt_Builder::class)) {
                    $custom_keyword_prompt = $validated_params['custom_keyword_prompt'] ?? null;
                    $keyword_user_prompt = \WPAICG\ContentWriter\Prompt\AIPKit_Content_Writer_Keyword_Prompt_Builder::build($final_title, $content_summary, $prompt_mode, $custom_keyword_prompt);
                    $keyword_ai_params = ['max_completion_tokens' => 20, 'temperature' => 0.2];

                    $keyword_result = $handler->get_ai_caller()->make_standard_call(
                        $validated_params['provider'],
                        $validated_params['model'],
                        [['role' => 'user', 'content' => $keyword_user_prompt]],
                        $keyword_ai_params,
                        'You are an SEO expert. Your task is to provide the single best focus keyword for a piece of content.'
                    );

                    if (!is_wp_error($keyword_result) && !empty($keyword_result['content'])) {
                        $focus_keyword = trim(str_replace(['"', "'", '.'], '', $keyword_result['content']));
                        $keyword_usage = $keyword_result['usage'] ?? null;
                    } else {
                        error_log('AutoGPT Focus Keyword Gen Failed: ' . (is_wp_error($keyword_result) ? $keyword_result->get_error_message() : 'Empty response'));
                    }
                }
            }
        }
    }
    // --- END Generate SEO Data ---


    wp_send_json_success([
        'content' => $content,
        'usage' => $usage,
        'meta_description' => $meta_description,
        'focus_keyword' => $focus_keyword,
        'excerpt' => $excerpt,
        'image_data' => $image_data
    ]);
}
