<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/generate-title/build-title-prompt.php
// Status: MODIFIED

namespace WPAICG\ContentWriter\Ajax\Actions\GenerateTitle;

use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds the system instruction and user prompt for title generation.
 * UPDATED: Simplified to only use the custom title prompt, as guided mode is removed.
 *
 * @param array $validated_params The validated parameters from the request.
 * @return array An array containing 'system_instruction' and 'user_prompt'.
 */
function build_title_prompt_logic(array $validated_params): array
{
    $system_instruction = "You are an expert copywriter specializing in crafting engaging headlines.";

    // Use the custom prompt from settings, or the central default if empty.
    $user_prompt_template = $validated_params['custom_title_prompt'] ?? AIPKit_Content_Writer_Prompts::get_default_title_prompt();
    if (empty(trim($user_prompt_template))) {
        $user_prompt_template = AIPKit_Content_Writer_Prompts::get_default_title_prompt();
    }

    // Replace placeholders
    $final_title_for_prompt = $validated_params['content_title'] ?? '';
    $final_keywords_for_prompt = !empty($validated_params['inline_keywords']) ? $validated_params['inline_keywords'] : ($validated_params['content_keywords'] ?? '');

    $rss_description = $validated_params['rss_description'] ?? '';
    $url_content_context = $validated_params['url_content_context'] ?? '';
    $source_url = $validated_params['source_url'] ?? '';

    $user_prompt = str_replace('{topic}', $final_title_for_prompt, $user_prompt_template);
    $user_prompt = str_replace('{keywords}', $final_keywords_for_prompt, $user_prompt);
    $user_prompt = str_replace('{description}', $rss_description, $user_prompt);
    $user_prompt = str_replace('{url_content}', $url_content_context, $user_prompt);
    $user_prompt = str_replace('{source_url}', $source_url, $user_prompt);

    return [
        'user_prompt' => $user_prompt,
        'system_instruction' => $system_instruction,
    ];
}
