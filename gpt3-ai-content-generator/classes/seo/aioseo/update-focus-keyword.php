<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/seo/aioseo/update-focus-keyword.php
// Status: NEW FILE

namespace WPAICG\SEO\AIOSEO;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Logic to update the AIOSEO keyphrase for a post.
 *
 * @param int $post_id The ID of the post.
 * @param string $keyword The new focus keyphrase. AIOSEO stores it in a serialized array.
 * @return bool True on success, false on failure.
 */
function update_focus_keyword_logic(int $post_id, string $keyword): bool
{
    if (empty($post_id) || !is_string($keyword)) {
        return false;
    }
    // AIOSEO v4 stores the focus keyphrase in a serialized array.
    // We'll create a simple structure that mimics a single keyword entry.
    $sanitized_keyword = sanitize_text_field($keyword);
    $keyphrase_data = [
        'focus' => [
            'keyphrase' => $sanitized_keyword,
            'analysis' => []
        ]
    ];
    // Note: AIOSEO uses wp_slash internally on update_post_meta, so we don't need to double-slash.
    $result = update_post_meta($post_id, '_aioseo_keywords', $keyphrase_data);
    return $result !== false;
}
