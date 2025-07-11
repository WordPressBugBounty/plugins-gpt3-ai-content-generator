<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/seo/aioseo/get-focus-keyword.php
// Status: NEW FILE

namespace WPAICG\SEO\AIOSEO;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Logic to get the AIOSEO keyphrase for a post.
 *
 * @param int $post_id The ID of the post.
 * @return string|null The focus keyphrase or null if not set.
 */
function get_focus_keyword_logic(int $post_id): ?string
{
    if (empty($post_id)) {
        return null;
    }
    $keywords_data = get_post_meta($post_id, '_aioseo_keywords', true);
    if (is_string($keywords_data)) {
        $keywords_data = json_decode($keywords_data, true);
    }
    if (is_array($keywords_data) && !empty($keywords_data['focus']['keyphrase'])) {
        return $keywords_data['focus']['keyphrase'];
    }
    return null;
}