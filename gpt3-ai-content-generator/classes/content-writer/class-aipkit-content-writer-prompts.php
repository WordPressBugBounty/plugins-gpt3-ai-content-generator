<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/prompt/class-aipkit-content-writer-prompts.php
// Status: MODIFIED

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
        return __('You are an expert SEO copywriter. Write a powerful and engaging SEO title that:
- Starts with the main focus keyword
- Stays concise and fits typical search-result length (about 8-12 words)
- Includes at least one power word (e.g., Stunning, Must-Have, Exclusive)
- Includes a positive or negative sentiment word (e.g., Best, Effortless, Affordable)

Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating new content.
     */
    public static function get_default_content_prompt(): string
    {
        return __('Write a full article based on the topic and keywords below. The article must:
- Be at least 600 words long
- Include the focus keyword in one or more subheadings (H2, H3, etc.)
- Start the first paragraph with the focus keyword
- Be informative, structured, and engaging
- Use natural tone and clear formatting
- Avoid repeating the title in the content

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an SEO meta description.
     */
    public static function get_default_meta_prompt(): string
    {
        return __('Write a meta description (under 155 characters) for a page about the following topic. The description must:
- Begin with or include the focus keyword early
- Use active voice and a clear call-to-action
- Be concise and engaging

Return ONLY the plain meta description without any quotation marks, labels, or formatting.

Topic: "{topic}"
Keywords: "{keywords}"
Summary: "{content_summary}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an SEO focus keyword.
     */
    public static function get_default_keyword_prompt(): string
    {
        return __('Identify the single most important and relevant SEO focus keyphrase for the article based on the title and summary. The keyphrase must:
- Be 2–4 words
- Be naturally found in the content
- Be suitable for SEO targeting

Return ONLY the keyphrase, with no labels, formatting, or quotation marks.

Title: "{topic}"
Summary:
{content_summary}', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an excerpt.
     */
    public static function get_default_excerpt_prompt(): string
    {
        return __('Write a short excerpt (1–2 engaging sentences) for the following article. Use a friendly, clear tone. Include the focus keyword naturally.

Return ONLY the excerpt, without any formatting or explanation.

Title: "{topic}"
Keywords: "{keywords}"
Summary: "{content_summary}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating tags.
     */
    public static function get_default_tags_prompt(): string
    {
        return __('Generate 5–10 relevant SEO tags for a blog post about the following topic. Tags must reflect key themes and keywords.

Return ONLY a comma-separated list of tags. Do not include any explanation, numbering, or formatting.

Title: "{topic}"
Keywords: "{keywords}"
Summary:
{content_summary}', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an in-content image.
     */
    public static function get_default_image_prompt(): string
    {
        return __('Generate a high-quality, relevant image prompt for an article about: {topic}', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating a featured image.
     */
    public static function get_default_featured_image_prompt(): string
    {
        return __('Generate an eye-catching, high-quality featured image prompt for a blog post about: {topic}. Keywords: {keywords}.', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an image title.
     */
    public static function get_default_image_title_prompt(): string
    {
        return __('Write a concise image title (under 8 words) based on the information below. Keep it clear and descriptive.

Return ONLY the title text without quotation marks or extra text.

Topic: "{topic}"
Keywords: "{keywords}"
Post title: "{post_title}"
Excerpt: "{excerpt}"
File name: "{file_name}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating image alt text.
     */
    public static function get_default_image_alt_text_prompt(): string
    {
        return __('Write clear alt text (under 125 characters) that describes the image for accessibility.

Return ONLY the alt text with no extra text.

Topic: "{topic}"
Keywords: "{keywords}"
Post title: "{post_title}"
Excerpt: "{excerpt}"
File name: "{file_name}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an image caption.
     */
    public static function get_default_image_caption_prompt(): string
    {
        return __('Write a short, friendly caption (1 sentence) for the image.

Return ONLY the caption text with no extra text.

Topic: "{topic}"
Keywords: "{keywords}"
Post title: "{post_title}"
Excerpt: "{excerpt}"
File name: "{file_name}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for generating an image description.
     */
    public static function get_default_image_description_prompt(): string
    {
        return __('Write a brief image description (1–2 sentences) suitable for the media library.

Return ONLY the description with no extra text.

Topic: "{topic}"
Keywords: "{keywords}"
Post title: "{post_title}"
Excerpt: "{excerpt}"
File name: "{file_name}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for updating an image title (existing attachments).
     */
    public static function get_default_image_title_prompt_update(): string
    {
        return __('Write a concise image title (under 8 words) based on the attachment details below. Keep it literal and clear.

Return ONLY the title text without quotation marks or extra text.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for updating image alt text (existing attachments).
     */
    public static function get_default_image_alt_text_prompt_update(): string
    {
        return __('Write clear alt text (under 125 characters) that describes the image for accessibility. Include a keyword only if it fits naturally.

Return ONLY the alt text.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for updating an image caption (existing attachments).
     */
    public static function get_default_image_caption_prompt_update(): string
    {
        return __('Write a short, friendly caption (1 sentence, under 20 words) that matches the attachment content.

Return ONLY the caption.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return string The default prompt for updating an image description (existing attachments).
     */
    public static function get_default_image_description_prompt_update(): string
    {
        return __('Write a brief media library description (1-2 sentences, under 240 characters) for the image.

Return ONLY the description.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator');
    }

    /**
     * @return array<string, array<int, array{label:string,prompt:string}>>
     */
    public static function get_prompt_library(): array
    {
        static $library = null;
        if ($library !== null) {
            return $library;
        }

        $library = [
            'title' => [
                [
                    'label' => __('Primary SEO Title (Strict)', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a single, SEO-optimized article title based on the topic and keywords below.
The title must:
- Clearly represent the core topic of the article
- Naturally incorporate the most relevant keywords where appropriate
- Be under 60 characters
- Accurately reflect search intent
- Avoid clickbait, exaggeration, or misleading language
- Be suitable for Google search results and WordPress post titles
- Sound natural, professional, and human-written
- Avoid quotation marks, emojis, or special characters
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('High-CTR Search Result Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a compelling, high click-through-rate SEO title.
The title must:
- Encourage clicks by clearly communicating value or outcome
- Use keywords naturally without forcing them
- Spark curiosity while remaining honest and accurate
- Be easily understood at a glance in search results
- Stay under 60 characters
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Authoritative Guide Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an authoritative, expert-level title that positions the article as a comprehensive and trustworthy resource.
The title must:
- Convey depth, clarity, and credibility
- Integrate relevant keywords naturally
- Avoid hype, sales language, or exaggerated claims
- Be suitable for professional blogs, agencies, or educational websites
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('How-To Practical Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a clear, practical how-to style title.
The title must:
- Clearly indicate actionable or instructional value
- Reflect what the reader will learn or achieve
- Use keywords naturally
- Be concise, direct, and evergreen
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Problem-Solution Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a problem-solution focused title based on the topic below.
The title must:
- Reference a real or common problem related to the topic
- Imply a clear solution, improvement, or outcome
- Incorporate keywords naturally
- Sound helpful and trustworthy, not sensational
- Stay under 65 characters
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Beginner-Friendly Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create a beginner-friendly article title designed for users new to the topic.
The title must:
- Use simple, clear language
- Avoid jargon or overly technical terms
- Communicate what the article covers at a glance
- Integrate keywords naturally
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Advanced Audience Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a title aimed at experienced users or professionals.
The title must:
- Signal depth, strategy, or advanced insight
- Avoid beginner-level wording
- Maintain a serious, authoritative tone
- Use keywords naturally and sparingly
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('List-Style SEO Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list-based SEO title.
The title must:
- Include a number between 5 and 15
- Clearly describe what the list contains
- Use keywords naturally
- Avoid generic list phrasing
- Stay under 65 characters
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Question-Based Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a question-style title that closely matches user search intent.
The title must:
- Be phrased as a natural, human question
- Clearly relate to the topic
- Use keywords naturally where relevant
- Be suitable for informational queries and featured snippets
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Evergreen SEO Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate an evergreen SEO title designed for long-term relevance.
The title must:
- Avoid years, trends, or time-sensitive wording
- Clearly communicate the article subject
- Use keywords naturally
- Be neutral, professional, and durable over time
- Stay under 60 characters
Return ONLY the title text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'content' => [
                [
                    'label' => __('Core SEO Long-Form Article', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a full, original article based on the topic and keywords below.
The article must:
- Be at least 600 words long
- Clearly introduce the topic in the opening paragraph
- Naturally include important keywords early in the content
- Use clear H2 and H3 subheadings for structure
- Be informative, well-organized, and engaging
- Use a natural, professional, human-like tone
- Avoid repeating the article title verbatim in the content
- Avoid keyword stuffing or unnatural phrasing

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Topical Authority Content', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an in-depth, authoritative article that establishes topical authority.
The article must:
- Fully explain the topic with expert-level clarity
- Cover related concepts and subtopics where relevant
- Use structured H2 and H3 headings
- Integrate keywords naturally throughout the article
- Be written as if by an experienced subject-matter expert
- Avoid promotional or sales-focused language

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Search-Intent Content', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an SEO-optimized article with primary focus on user search intent.
The article must:
- Clearly answer the main intent behind the topic
- Address common questions users may have
- Provide clear explanations before advanced details
- Use keywords naturally where relevant
- Maintain logical flow and strong readability

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Structured Educational Article', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an educational, well-structured article suitable for learning purposes.
The article must:
- Break the topic into logical sections
- Use H2 for main ideas and H3 for explanations
- Include examples where helpful
- Maintain a clear, explanatory tone
- Use keywords naturally without forcing them

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Conversational SEO Content', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a natural-sounding, conversational article that remains SEO-compliant.
The article must:
- Feel human-written and easy to read
- Avoid robotic or repetitive phrasing
- Use short paragraphs for scannability
- Integrate keywords smoothly and contextually
- Maintain a friendly but professional tone

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Problem-Solving Content', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a practical, solution-oriented article focused on real-world problems related to the topic.
The article must:
- Identify common challenges or pain points
- Explain why these issues occur
- Offer clear, actionable guidance or solutions
- Use headings and bullet points where appropriate
- Include keywords naturally within context

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Evergreen SEO Content', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an evergreen SEO article designed to remain relevant over time.
The article must:
- Avoid time-sensitive references such as years or trends
- Focus on foundational knowledge and best practices
- Use keywords naturally across headings and body text
- Maintain a neutral, professional tone

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Advanced Professional Content', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an advanced-level article targeting experienced readers or professionals.
The article must:
- Assume baseline knowledge of the topic
- Focus on strategy, optimization, or deeper insights
- Avoid beginner-style explanations
- Maintain a confident, authoritative tone
- Use keywords naturally without overuse

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Readability-Optimized SEO Content', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an SEO-optimized article with strong emphasis on readability.
The article must:
- Use short paragraphs and clear sentence structure
- Avoid overly complex or long sentences
- Use bullet points and lists where helpful
- Integrate keywords smoothly
- Be easy to scan and skim

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Informational Soft Conversion', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an informative, trust-building article that subtly encourages reader engagement.
The article must:
- Educate the reader thoroughly without sales pressure
- Build credibility and clarity around the topic
- Use a calm, professional tone
- Include keywords naturally
- End with a neutral, informative conclusion

Topic: "{topic}"
Keywords: "{keywords}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'meta' => [
                [
                    'label' => __('Primary SEO Meta Description', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a single, SEO-optimized meta description based on the topic, keywords, and content summary below.
The meta description must:
- Accurately summarize the article content
- Naturally incorporate relevant keywords without stuffing
- Be under 160 characters
- Match informational search intent
- Sound natural, clear, and human-written
- Avoid quotation marks, emojis, or promotional hype
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('High-CTR Meta Description', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a compelling, high click-through-rate meta description.
The meta description must:
- Encourage clicks by clearly communicating value
- Reflect what the reader will gain from the article
- Use keywords naturally where appropriate
- Stay under 160 characters
- Avoid clickbait, exaggeration, or misleading claims
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Informational Intent Meta', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an informational meta description aligned with user search intent.
The meta description must:
- Clearly explain what the article covers
- Answer the implied "what is / how / why" intent
- Integrate keywords naturally
- Be concise, neutral, and accurate
- Remain under 160 characters
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Benefit-Driven Meta', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a benefit-focused meta description for an SEO article.
The meta description must:
- Highlight the main benefit or outcome for the reader
- Stay truthful to the article’s actual content
- Use keywords naturally
- Be clear, specific, and concise
- Stay under 160 characters
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Question-Style Meta', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a question-style meta description that encourages curiosity.
The meta description must:
- Be phrased as a natural question
- Reflect the article’s core topic accurately
- Use keywords only where relevant
- Avoid being vague or misleading
- Remain under 160 characters
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Professional Editorial Meta', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a professional, editorial-style meta description suitable for agencies or authoritative blogs.
The meta description must:
- Sound neutral, trustworthy, and informative
- Avoid marketing or sales language
- Accurately reflect the article’s scope
- Use keywords naturally
- Stay under 160 characters
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Beginner-Friendly Meta', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create a beginner-friendly meta description for users new to the topic.
The meta description must:
- Use simple, clear language
- Explain what the article helps the reader understand
- Avoid jargon or technical phrasing
- Use keywords naturally
- Remain under 160 characters
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Short & Punchy Meta', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a short, punchy meta description optimized for quick scanning in search results.
The meta description must:
- Be concise and impactful
- Clearly describe the article’s focus
- Use keywords sparingly and naturally
- Stay under 140 characters
- Avoid filler words
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Problem-Solution Meta', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a problem-solution oriented meta description.
The meta description must:
- Reference a common problem related to the topic
- Hint at a clear solution provided by the article
- Stay accurate to the content summary
- Use keywords naturally
- Remain under 160 characters
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Evergreen SEO Meta', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate an evergreen SEO meta description designed for long-term relevance.
The meta description must:
- Avoid time-sensitive language
- Clearly communicate what the article is about
- Use keywords naturally
- Sound neutral and professional
- Stay under 160 characters
Return ONLY the meta description text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'keyword' => [
                [
                    'label' => __('Primary SEO Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one primary focus keyword based on the topic and content summary below.
The focus keyword must:
- Accurately represent the core subject of the article
- Match the main informational search intent
- Be a natural search query a real user would type
- Avoid being too broad or overly generic
- Avoid being branded unless clearly required by the topic
- Be suitable for on-page SEO optimization
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Long-Tail Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one long-tail focus keyword based on the topic and content summary below.
The focus keyword must:
- Be more specific than a generic head term
- Clearly reflect the article’s unique angle or depth
- Match informational or problem-solving search intent
- Sound natural and human-written
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Search-Intent Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one focus keyword optimized specifically for search intent.
The focus keyword must:
- Reflect what the user is actually trying to learn or solve
- Be phrased as a natural search query
- Avoid keyword stuffing or unnatural structure
- Closely align with the article’s primary purpose
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Beginner-Friendly Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one beginner-friendly focus keyword based on the topic and content summary.
The focus keyword must:
- Use simple, commonly searched wording
- Avoid advanced or technical jargon
- Reflect how a beginner would phrase the search
- Accurately match the article’s content
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Professional Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one advanced or professional-level focus keyword.
The focus keyword must:
- Reflect deeper or more specialized search intent
- Be suitable for experienced users or professionals
- Avoid beginner-style phrasing
- Remain natural and realistic as a search query
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Problem-Oriented Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one problem-oriented focus keyword based on the article content.
The focus keyword must:
- Clearly express a problem, challenge, or pain point
- Match problem-solving or informational intent
- Be phrased as a natural user search
- Align closely with the solutions discussed in the article
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Question-Style Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one question-style focus keyword.
The focus keyword must:
- Be phrased as a natural question a user might search
- Directly relate to the topic and content summary
- Avoid being too broad or vague
- Be suitable for featured snippets where possible
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Evergreen Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one evergreen focus keyword designed for long-term SEO value.
The focus keyword must:
- Avoid time-sensitive terms or trends
- Reflect stable, long-term search interest
- Accurately represent the article’s main subject
- Be suitable for ongoing organic traffic
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Conversion-Adjacent Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one focus keyword that sits close to conversion intent without being transactional.
The focus keyword must:
- Indicate deeper interest or evaluation
- Avoid direct buying terms unless required by the topic
- Match the article’s informational nature
- Sound natural and realistic as a search query
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Balanced SEO Focus Keyword', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate one balanced focus keyword that combines clarity, relevance, and search intent.
The focus keyword must:
- Not be too broad or too narrow
- Represent the article accurately
- Be suitable as the primary on-page keyword
- Be something a real user would search
Return ONLY the focus keyword text on a single line with no extra text or annotations.

Topic: "{topic}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'excerpt' => [
                [
                    'label' => __('Primary SEO Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a concise, SEO-friendly excerpt based on the topic, keywords, and content summary below.
The excerpt must:
- Accurately summarize the article’s main idea
- Be between 2–3 sentences
- Use keywords naturally without stuffing
- Match informational search intent
- Sound neutral, clear, and professional
- Avoid promotional language or hype
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Hook-Driven Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a hook-driven excerpt designed to encourage readers to continue reading.
The excerpt must:
- Capture interest without clickbait
- Clearly indicate what the article covers
- Use keywords naturally where relevant
- Be concise and engaging
- Stay faithful to the content summary
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Benefit-Focused Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a benefit-focused article excerpt.
The excerpt must:
- Clearly explain what the reader will learn or gain
- Remain accurate to the article’s content
- Avoid exaggerated or sales-oriented phrasing
- Use keywords naturally
- Be suitable for blog listings and archive pages
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Informational Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an informative, educational excerpt suitable for explanatory content.
The excerpt must:
- Focus on clarity and understanding
- Briefly describe the subject matter
- Use simple, clear language
- Integrate keywords naturally
- Avoid marketing tone
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Problem-Solution Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a problem-solution style excerpt.
The excerpt must:
- Reference a common challenge related to the topic
- Hint at a solution or guidance offered in the article
- Stay accurate to the content summary
- Use keywords naturally
- Be concise and informative
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Beginner-Friendly Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a beginner-friendly excerpt for readers new to the topic.
The excerpt must:
- Use approachable, simple language
- Clearly explain what the article is about
- Avoid technical jargon
- Use keywords naturally
- Feel welcoming and easy to understand
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Professional Editorial Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a professional, editorial-style excerpt suitable for agencies or authoritative blogs.
The excerpt must:
- Sound neutral, trustworthy, and polished
- Reflect depth and seriousness of the topic
- Avoid promotional or casual language
- Use keywords naturally
- Be appropriate for high-quality publications
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Short Teaser Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a short teaser excerpt for blog listings.
The excerpt must:
- Be 1–2 sentences only
- Spark curiosity while remaining accurate
- Clearly relate to the topic
- Use keywords sparingly and naturally
- Avoid vague phrasing
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Readability-Optimized Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an excerpt optimized for readability and scanning.
The excerpt must:
- Use short sentences
- Be easy to understand at a glance
- Clearly communicate article focus
- Use keywords naturally
- Avoid unnecessary words
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Evergreen SEO Excerpt', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write an evergreen SEO excerpt designed for long-term relevance.
The excerpt must:
- Avoid time-sensitive language
- Clearly summarize the article’s subject
- Use keywords naturally
- Maintain a neutral, professional tone
- Be suitable for long-term content archives
Return ONLY the excerpt text on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'tags' => [
                [
                    'label' => __('Primary SEO Tags', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list of relevant SEO tags based on the topic, keywords, and content summary below.
The tags must:
- Accurately reflect the main subjects of the article
- Be closely related to the topic
- Use natural, commonly searched phrases
- Avoid overly generic or irrelevant terms
- Be suitable for WordPress post tags
- Return 5–10 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Long-Tail SEO Tags', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list of long-tail SEO tags for the article.
The tags must:
- Be more specific than generic head terms
- Reflect deeper aspects of the topic
- Match informational or problem-solving intent
- Be phrased naturally as real search queries
- Return 5–10 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Semantic Topic Tags', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list of semantically related tags that support topical authority.
The tags must:
- Represent closely related concepts and subtopics
- Expand semantic coverage without duplication
- Avoid keyword stuffing
- Be relevant to the article’s actual content
- Return 6–12 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Beginner-Friendly Tags', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list of beginner-friendly tags for readers new to the topic.
The tags must:
- Use simple, commonly understood terms
- Avoid technical or advanced jargon
- Reflect how beginners might search
- Remain accurate to the article’s content
- Return 5–10 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Professional Tags', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list of advanced or professional-level tags.
The tags must:
- Reflect deeper knowledge or strategic aspects
- Be suitable for experienced users or professionals
- Avoid beginner-style phrasing
- Remain natural and realistic search terms
- Return 5–10 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Question-Based Tags', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list of question-based SEO tags related to the article.
The tags must:
- Be phrased as natural search questions
- Reflect common user curiosities
- Match the informational intent of the article
- Avoid being overly long or vague
- Return 5–8 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Problem-Oriented Tags', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list of problem-oriented tags based on the article content.
The tags must:
- Clearly express challenges, issues, or pain points
- Match problem-solving search intent
- Align closely with solutions discussed in the article
- Be realistic user search phrases
- Return 5–10 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Evergreen SEO Tags', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list of evergreen SEO tags suitable for long-term use.
The tags must:
- Avoid time-sensitive or trend-based terms
- Represent stable, ongoing search interest
- Accurately reflect the article topic
- Be usable across multiple related articles
- Return 5–10 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Short & High-Impact Tags', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a list of short, high-impact SEO tags.
The tags must:
- Be concise (1–3 words where possible)
- Represent core concepts of the article
- Avoid redundancy
- Remain meaningful and searchable
- Return 5–10 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Balanced SEO Tag Set', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a balanced set of SEO tags for the article.
The tags must:
- Include a mix of broad, specific, and semantic terms
- Reflect different user search intents
- Stay accurate to the content summary
- Avoid duplication or near-duplicates
- Return 8–12 tags as a comma-separated list
Return ONLY the tags as a comma-separated list on a single line with no extra text or annotations.

Topic: "{topic}"
Keywords: "{keywords}"
Content Summary: "{content_summary}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'image' => [
                [
                    'label' => __('Contextual Illustration Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a contextual illustration based on the topic, keywords, and excerpt below.
The image must:
- Visually support a key concept discussed in the article
- Be relevant to the written content, not decorative
- Avoid text, logos, or branding
- Use a clean, modern illustration style
- Be suitable for placement inside a blog article

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Educational Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create an educational-style image that helps explain the topic visually.
The image must:
- Represent an idea, process, or concept from the article
- Be easy to understand at a glance
- Avoid unnecessary visual complexity
- Use a neutral, instructional design style

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Abstract Concept Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate an abstract conceptual image inspired by the article topic.
The image must:
- Symbolize the core idea rather than literal objects
- Use abstract shapes, metaphors, or visual themes
- Avoid text and branding
- Be appropriate for professional blogs

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Minimalist Supporting Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create a minimalist supporting image for in-content use.
The image must:
- Be simple, clean, and uncluttered
- Support the article without overpowering the text
- Use neutral colors and modern aesthetics
- Avoid text overlays

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Modern Flat Design Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a modern flat-design style image based on the article.
The image must:
- Use flat shapes and clear visual hierarchy
- Represent ideas related to the topic
- Be suitable for SEO and content marketing blogs
- Avoid logos and readable text

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Professional Business Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create a professional, business-appropriate image.
The image must:
- Be suitable for agency or corporate blogs
- Use realistic or semi-realistic visuals
- Avoid cartoonish or playful styles
- Support the article’s informational tone

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Data/Process Visualization Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a visual representation of a process, flow, or system related to the article.
The image must:
- Visually communicate structure or progression
- Avoid readable text
- Be clear and well-balanced
- Be appropriate for embedding mid-article

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Neutral SEO-Friendly Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create a neutral, SEO-friendly image that complements the article content.
The image must:
- Be broadly applicable and non-controversial
- Avoid strong opinions or emotional cues
- Fit well across different industries

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Illustrative Blog Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate an illustrative blog image designed specifically for content sections.
The image must:
- Match modern blog aesthetics
- Support the written explanation
- Avoid distracting elements
- Be visually consistent with professional blogs

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Concept + Keyword Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create an image inspired by the topic and keywords combined.
The image must:
- Reflect multiple concepts subtly
- Avoid literal keyword text
- Feel intentional and content-driven
- Be suitable for SEO content

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'image_title' => [
                [
                    'label' => __('Concise Image Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a concise image title (4-8 words) describing the image.
Keep it literal and clear.

Return ONLY the title text.

Topic: "{topic}"
Keywords: "{keywords}"
Post Title: "{post_title}"
Excerpt: "{excerpt}"
File Name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Keyword-Aware Image Title', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a short image title (under 8 words) that includes one relevant keyword when natural.

Return ONLY the title text.

Topic: "{topic}"
Keywords: "{keywords}"
Post Title: "{post_title}"
Excerpt: "{excerpt}"
File Name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'image_title_update' => [
                [
                    'label' => __('Attachment Title (Concise)', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a concise attachment title (4-8 words) based on the image details.
Keep it literal and clear.

Return ONLY the title text.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Attachment Title (Short)', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a short attachment title (under 8 words) that reflects the image content.

Return ONLY the title text.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'image_alt_text' => [
                [
                    'label' => __('Accessibility-First Alt Text', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write clear alt text (under 125 characters) that describes what is in the image for accessibility.
Include a keyword only if it fits naturally.

Return ONLY the alt text.

Topic: "{topic}"
Keywords: "{keywords}"
Post Title: "{post_title}"
Excerpt: "{excerpt}"
File Name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Short Descriptive Alt Text', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write short alt text (under 100 characters) that focuses on the main subject and setting.

Return ONLY the alt text.

Topic: "{topic}"
Keywords: "{keywords}"
Post Title: "{post_title}"
Excerpt: "{excerpt}"
File Name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'image_alt_text_update' => [
                [
                    'label' => __('Alt Text (Accessibility)', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write clear alt text (under 125 characters) that describes the image for accessibility.
Keep it factual and specific.

Return ONLY the alt text.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Alt Text (Short)', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write short alt text (under 100 characters) focusing on the main subject and setting.

Return ONLY the alt text.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'image_caption' => [
                [
                    'label' => __('Short Caption', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a short, friendly caption (1 sentence, under 20 words) that matches the article tone.

Return ONLY the caption.

Topic: "{topic}"
Keywords: "{keywords}"
Post Title: "{post_title}"
Excerpt: "{excerpt}"
File Name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Informative Caption', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a concise caption that adds context related to the topic.

Return ONLY the caption.

Topic: "{topic}"
Keywords: "{keywords}"
Post Title: "{post_title}"
Excerpt: "{excerpt}"
File Name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'image_caption_update' => [
                [
                    'label' => __('Caption (Short)', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a short, friendly caption (1 sentence, under 20 words) that matches the image.

Return ONLY the caption.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Caption (Context)', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a concise caption that adds helpful context about the image.

Return ONLY the caption.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'image_description' => [
                [
                    'label' => __('Media Library Description', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a brief media library description (1-2 sentences, under 240 characters) describing the image.

Return ONLY the description.

Topic: "{topic}"
Keywords: "{keywords}"
Post Title: "{post_title}"
Excerpt: "{excerpt}"
File Name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Detailed Image Description', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a clear description (2 sentences max) that describes the image and its relevance to the article.

Return ONLY the description.

Topic: "{topic}"
Keywords: "{keywords}"
Post Title: "{post_title}"
Excerpt: "{excerpt}"
File Name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'image_description_update' => [
                [
                    'label' => __('Description (Media Library)', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a brief media library description (1-2 sentences, under 240 characters) describing the image.

Return ONLY the description.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Description (Detailed)', 'gpt3-ai-content-generator'),
                    'prompt' => __('Write a clear description (2 sentences max) that describes the image.

Return ONLY the description.

Attachment title: "{original_title}"
Caption: "{original_caption}"
Description: "{original_description}"
Alt text: "{original_alt}"
File name: "{file_name}"', 'gpt3-ai-content-generator'),
                ],
            ],
            'featured_image' => [
                [
                    'label' => __('Hero Featured Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a strong hero-style featured image for the article.
The image must:
- Represent the overall topic clearly
- Be visually striking at large sizes
- Avoid text overlays and logos
- Work well as a blog header image

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Clean Blog Header Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create a clean, modern featured image suitable for a blog header.
The image must:
- Be wide-format friendly
- Use balanced composition
- Avoid clutter and small details

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Editorial Featured Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate an editorial-style featured image inspired by online magazines.
The image must:
- Feel professional and high-quality
- Convey authority and credibility
- Avoid sensational or exaggerated visuals

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Minimal Text-Free Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create a minimalist featured image with no text.
The image must:
- Rely purely on visuals to convey meaning
- Use clean colors and composition
- Be suitable for long-term evergreen content

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('High-Contrast Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a high-contrast featured image designed to stand out in blog grids.
The image must:
- Be visually bold without being aggressive
- Maintain professional tone
- Avoid unnecessary detail

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Modern Web Aesthetic', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create a featured image aligned with modern web design aesthetics.
The image must:
- Feel current and polished
- Work well with modern WordPress themes
- Avoid dated or stock-photo clichés

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Abstract Featured Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate an abstract featured image related to the article topic.
The image must:
- Represent ideas symbolically
- Avoid literal illustrations
- Feel intentional and professional

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Illustrated Featured Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create a custom illustration-style featured image.
The image must:
- Be unique and non-stock-like
- Use consistent illustration style
- Be suitable for branded blog content

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Brand-Safe Featured Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Generate a brand-safe featured image suitable for agencies and businesses.
The image must:
- Avoid controversial or emotional imagery
- Be neutral and professional
- Work across different brand styles

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
                [
                    'label' => __('Evergreen Featured Image', 'gpt3-ai-content-generator'),
                    'prompt' => __('Create an evergreen featured image designed for long-term use.
The image must:
- Avoid trends or time-specific visuals
- Clearly relate to the article topic
- Remain relevant for years

Topic: "{topic}"
Keywords: "{keywords}"
Excerpt: "{excerpt}"
Post Title: "{post_title}"', 'gpt3-ai-content-generator'),
                ],
            ],
        ];

        return $library;
    }
}
