<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/save-post/insert-post.php
// Status: MODIFIED
// I have added a preg_replace call to convert markdown-style links into HTML <a> tags before the content is saved.

namespace WPAICG\ContentWriter\Ajax\Actions\SavePost;

use WPAICG\ContentWriter\TemplateManagerMethods as CwTemplateMethods;
use WP_Error;
use WPAICG\Utils\AIPKit_TOC_Generator;
// --- ADDED: Image Injector Dependency ---
use WPAICG\ContentWriter\AIPKit_Image_Injector;
use WPAICG\ContentWriter\SEO\AIPKit_Content_Writer_Smart_SEO_Image_Alt_Helper;
use WPAICG\Images\AIPKit_Image_Storage_Helper;

// --- END ADDED ---

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

// Ensure dependencies are loaded
if (!function_exists('WPAICG\ContentWriter\TemplateManagerMethods\calculate_schedule_datetime_logic')) {
    $path = WPAICG_PLUGIN_DIR . 'classes/content-writer/template-manager/calculate-schedule-datetime.php';
    if (file_exists($path)) {
        require_once $path;
    }
}
if (!class_exists('\WPAICG\Utils\AIPKit_TOC_Generator')) {
    $toc_generator_path = WPAICG_PLUGIN_DIR . 'includes/utils/class-aipkit-toc-generator.php';
    if (file_exists($toc_generator_path)) {
        require_once $toc_generator_path;
    }
}
if (!class_exists('\WPAICG\SEO\AIPKit_SEO_Helper')) {
    $seo_helper_path = WPAICG_PLUGIN_DIR . 'classes/seo/seo-helper.php';
    if (file_exists($seo_helper_path)) {
        require_once $seo_helper_path;
    }
}
// --- ADDED: Ensure Image Injector is loaded ---
if (!class_exists(AIPKit_Image_Injector::class)) {
    $injector_path = WPAICG_PLUGIN_DIR . 'classes/content-writer/class-aipkit-image-injector.php';
    if (file_exists($injector_path)) {
        require_once $injector_path;
    }
}
if (!class_exists(AIPKit_Image_Storage_Helper::class)) {
    $storage_path = WPAICG_PLUGIN_DIR . 'classes/images/class-aipkit-image-storage-helper.php';
    if (file_exists($storage_path)) {
        require_once $storage_path;
    }
}
if (!class_exists(AIPKit_Content_Writer_Smart_SEO_Image_Alt_Helper::class)) {
    $image_alt_helper_path = WPAICG_LIB_DIR . 'content-writer/seo/class-aipkit-content-writer-smart-seo-image-alt-helper.php';
    if (file_exists($image_alt_helper_path)) {
        require_once $image_alt_helper_path;
    }
}
// --- END ADDED ---

/**
 * Inserts the generated content as a new post.
 *
 * @param array      $postarr           The final prepared post array.
 * @param string|null $excerpt           Optional post excerpt.
 * @param array|null $image_data        Optional data for generated images.
 * @param string     $image_alignment   Optional alignment for injected images.
 * @param string     $image_size        Optional display size for injected images.
 * @param string|null $focus_keyword     Optional focus keyword for SEO-aware image alt fallback.
 * @return int|WP_Error The new post ID or a WP_Error on failure.
 */
function insert_post_logic(array $postarr, ?string $excerpt = null, ?array $image_data = null, string $image_alignment = 'none', string $image_size = 'large', ?string $focus_keyword = null): int|WP_Error
{
    if (class_exists(\WPAICG\ContentWriter\AIPKit_Content_Writer_Output_Cleaner::class)) {
        $postarr['post_title'] = \WPAICG\ContentWriter\AIPKit_Content_Writer_Output_Cleaner::clean_title(
            (string) ($postarr['post_title'] ?? ''),
            (string) $focus_keyword
        );
    }

    // --- START: Convert markdown to HTML ---
    $html_content = $postarr['post_content'];
    if (class_exists(\WPAICG\ContentWriter\AIPKit_Content_Writer_Output_Cleaner::class)) {
        $html_content = \WPAICG\ContentWriter\AIPKit_Content_Writer_Output_Cleaner::clean_article_content((string) $html_content, (string) $focus_keyword);
    }

    // Convert markdown block elements like headings
    $html_content = preg_replace('/^#\s+(.*)$/m', '<h1>$1</h1>', $html_content);
    $html_content = preg_replace('/^##\s+(.*)$/m', '<h2>$1</h2>', $html_content);
    $html_content = preg_replace('/^###\s+(.*)$/m', '<h3>$1</h3>', $html_content);
    $html_content = preg_replace('/^####\s+(.*)$/m', '<h4>$1</h4>', $html_content);

    // Convert inline markdown elements like bold and italic.
    $html_content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $html_content);
    $html_content = preg_replace('/(?<!\*)\*(?!\*|_)(.*?)(?<!\*|_)\*(?!\*)/s', '<em>$1</em>', $html_content);
    // Convert links: [text](url) -> <a href="url">text</a>
    $html_content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html_content);

    // Update the post content in the array with the converted HTML
    $postarr['post_content'] = $html_content;
    // --- END: Convert markdown to HTML ---

    if (is_array($image_data) && class_exists(AIPKit_Content_Writer_Smart_SEO_Image_Alt_Helper::class)) {
        AIPKit_Content_Writer_Smart_SEO_Image_Alt_Helper::maybe_prepare_rank_math_image_alt($image_data, (string) $focus_keyword);
    }

    // --- ADDED: Normalize image data if attachment IDs are missing ---
    if (!empty($image_data['in_content_images']) && class_exists(AIPKit_Image_Storage_Helper::class)) {
        $normalized_images = [];
        foreach ($image_data['in_content_images'] as $image_item) {
            $image_alt = class_exists(AIPKit_Content_Writer_Smart_SEO_Image_Alt_Helper::class)
                ? AIPKit_Content_Writer_Smart_SEO_Image_Alt_Helper::resolve_image_alt_text($image_item)
                : '';
            if (empty($image_item['attachment_id'])) {
                $fallback_url = $image_item['media_library_url'] ?? ($image_item['url'] ?? ($image_item['src'] ?? ($image_item['image_url'] ?? null)));
                if (!empty($fallback_url)) {
                    $image_payload = ['url' => $fallback_url];
                    if ($image_alt !== '') {
                        $image_payload['alt'] = $image_alt;
                    }
                    $attachment_id = AIPKit_Image_Storage_Helper::save_image_to_media_library(
                        $image_payload,
                        $postarr['post_title'],
                        [],
                        absint($postarr['post_author'])
                    );
                    if (!is_wp_error($attachment_id) && $attachment_id) {
                        $image_item['attachment_id'] = $attachment_id;
                        $image_item['media_library_url'] = wp_get_attachment_url($attachment_id);
                    }
                }
            }
            if (!empty($image_item['attachment_id']) && $image_alt !== '') {
                $attachment_id = absint($image_item['attachment_id']);
                if ($attachment_id > 0 && trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true)) === '') {
                    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image_alt);
                    update_post_meta($attachment_id, '_aipkit_image_alt_text', $image_alt);
                }
            }
            $normalized_images[] = $image_item;
        }
        $image_data['in_content_images'] = $normalized_images;
    }
    if (empty($image_data['featured_image_id']) && !empty($image_data['featured_image_url']) && class_exists(AIPKit_Image_Storage_Helper::class)) {
        $featured_attachment_id = AIPKit_Image_Storage_Helper::save_image_to_media_library(
            ['url' => $image_data['featured_image_url']],
            $postarr['post_title'],
            [],
            absint($postarr['post_author'])
        );
        if (!is_wp_error($featured_attachment_id) && $featured_attachment_id) {
            $image_data['featured_image_id'] = $featured_attachment_id;
        }
    }

    // --- ADDED: Image Injector logic before ToC generation ---
    if (!empty($image_data['in_content_images']) && class_exists(AIPKit_Image_Injector::class)) {
        $image_injector = new AIPKit_Image_Injector();
        $postarr['post_content'] = $image_injector->inject_images(
            $postarr['post_content'],
            $image_data['in_content_images'],
            $image_data['placement_settings']['placement'] ?? 'after_first_h2',
            absint($image_data['placement_settings']['param_x'] ?? 2),
            $image_alignment,
            $image_size
        );
    }
    // --- END ADDED ---

    // Generate ToC after images have been placed
    if (isset($postarr['generate_toc']) && $postarr['generate_toc'] === '1' && class_exists(AIPKit_TOC_Generator::class)) {
        $toc_result = AIPKit_TOC_Generator::generate($postarr['post_content'], [
            'rank_math_compatible' => class_exists('\\WPAICG\\SEO\\AIPKit_SEO_Helper')
                && sanitize_key((string) (\WPAICG\SEO\AIPKit_SEO_Helper::get_active_plugin_profile()['profile'] ?? '')) === 'rank_math',
        ]);
        if (!empty($toc_result['toc'])) {
            // Prepend ToC to the modified content
            $postarr['post_content'] = $toc_result['toc'] . $toc_result['content'];
        }
    }
    // Unset the custom key before passing to wp_insert_post
    unset($postarr['generate_toc']);

    // Add excerpt if provided
    if (!empty($excerpt)) {
        $postarr['post_excerpt'] = $excerpt;
    }

    $post_id_or_error = wp_insert_post($postarr, true);

    if (is_wp_error($post_id_or_error)) {
        return $post_id_or_error;
    }

    // --- ADDED: Set Featured Image after post insertion ---
    if (!empty($image_data['featured_image_id'])) {
        set_post_thumbnail($post_id_or_error, $image_data['featured_image_id']);
    }
    // --- END ADDED ---

    return $post_id_or_error;
}
