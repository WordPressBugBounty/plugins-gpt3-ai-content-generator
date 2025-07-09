<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/seo/interface-aipkit-seo-handler.php
// Status: NEW FILE

namespace WPAICG\SEO;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface for SEO Plugin Handlers.
 * Defines a contract for all plugin-specific handler classes.
 */
interface AIPKit_SEO_Handler_Interface
{
    /**
     * Updates the meta description for a specific post.
     *
     * @param int $post_id The ID of the post.
     * @param string $description The new meta description.
     * @return bool True on success, false on failure.
     */
    public function update_meta_description(int $post_id, string $description): bool;

    /**
     * Updates the focus keyword(s) for a specific post.
     *
     * @param int $post_id The ID of the post.
     * @param string $keyword The new focus keyword or comma-separated keywords.
     * @return bool True on success, false on failure.
     */
    public function update_focus_keyword(int $post_id, string $keyword): bool;

    // Add other method signatures here as needed, e.g.:
    // public function get_seo_score(int $post_id): ?array;
}
