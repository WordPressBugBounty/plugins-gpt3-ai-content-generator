<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/class-aipkit-content-writer-prompts.php
// Status: MODIFIED
// I have added a new method to get the default prompt for generating an excerpt.

namespace WPAICG\ContentWriter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Centralized class for defining default prompts used in the Content Writer module.
 * @since NEXT_VERSION
 */
class AIPKit_Content_Writer_Prompts
{
    /**
     * @return string The default prompt for generating a new title.
     */
    public static function get_default_title_prompt(): string
    {
        return __('You are an expert SEO copywriter. Generate the single best and most compelling title based on the provided information. Analyze the topic and keywords to determine the most effective angle, and synthesize this into one optimal title. Instructions: The title must be SEO-friendly and under 60 characters. Prioritize placing the main keyword near the beginning. Return ONLY the single new title text. Do not include any introduction, explanation, or quotation marks. Topic: {topic} Keywords: {keywords}', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating main content.
     */
    public static function get_default_content_prompt(): string
    {
        return __('Write an article about the topic: "{topic}". Do not repeat the title at the beginning of the article content. Start directly with the first paragraph. Please incorporate the following keywords naturally: {keywords}. Ensure the content is well-structured, informative, and engaging. Use clear headings and paragraphs. Avoid overly promotional language unless specifically requested. Focus on providing value to the reader. Generate the full article now.', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an SEO meta description.
     */
    public static function get_default_meta_prompt(): string
    {
        return __('You are an SEO expert. Write a meta description for a blog post with the title "{topic}" and keywords "{keywords}". The description must be under 156 characters, written in an active voice, and include a clear call-to-action. Your response must be ONLY the raw text of the meta description, without any labels, quotation marks, or markdown formatting. Here is a summary of the content for context:\n\n{content_summary}', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an SEO focus keyword.
     */
    public static function get_default_keyword_prompt(): string
    {
        return __('You are an SEO expert. Your task is to identify the single most important and relevant focus keyphrase for the following article. The keyphrase should be concise (ideally 2-4 words) and must be present within the provided article summary.\n\nReturn ONLY the keyphrase. Do not add any explanation, labels, or quotation marks.\n\nArticle Title: "{topic}"\nArticle Summary:\n{content_summary}', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an excerpt.
     */
    public static function get_default_excerpt_prompt(): string
    {
        return __('You are an expert copywriter. Rewrite the post excerpt to be more compelling and engaging based on the information provided. Use a friendly tone and aim for 1–2 concise sentences. Return ONLY the new excerpt without any explanation or formatting.\n\nPost title: "{topic}"\nPost content summary: "{content_summary}"', 'gpt3-ai-content-generator');
    }


    /**
     * @return string The default prompt for generating an in-content image.
     */
    public static function get_default_image_prompt(): string
    {
        return __('A high-quality, relevant image for an article about: {topic}', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating a featured image.
     */
    public static function get_default_featured_image_prompt(): string
    {
        return __('An eye-catching, high-quality featured image for an article about: {topic}. Keywords: {keywords}.', 'gpt3-ai-content-generator');
    }
}
