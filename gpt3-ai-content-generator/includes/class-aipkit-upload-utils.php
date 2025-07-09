<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/includes/class-aipkit-upload-utils.php
// Status: MODIFIED

namespace WPAICG\Includes;

use WP_Error; // Added for WP_Error usage

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Upload_Utils
 * Utility class for retrieving and formatting WordPress/server upload limits.
 * ADDED: get_vector_upload_allowed_mime_types and validate_vector_upload_file methods.
 */
class AIPKit_Upload_Utils
{
    /**
     * Get various upload limits.
     *
     * @return array An associative array of upload limits with human-readable values.
     *               Keys: 'upload_max_filesize', 'post_max_size', 'memory_limit', 'wp_max_upload_size'.
     */
    public static function get_upload_limits(): array
    {
        $limits = [];

        // From PHP INI
        $limits['upload_max_filesize'] = ini_get('upload_max_filesize');
        $limits['post_max_size'] = ini_get('post_max_size');
        $limits['memory_limit'] = ini_get('memory_limit'); // Less direct, but relevant

        // WordPress calculated max upload size
        $wp_max_bytes = wp_max_upload_size();
        $limits['wp_max_upload_size_bytes'] = $wp_max_bytes;
        $limits['wp_max_upload_size_formatted'] = size_format($wp_max_bytes);

        // Convert INI values to bytes for comparison and consistent formatting if needed later
        $limits['upload_max_filesize_bytes'] = wp_convert_hr_to_bytes($limits['upload_max_filesize']);
        $limits['post_max_size_bytes'] = wp_convert_hr_to_bytes($limits['post_max_size']);
        $limits['memory_limit_bytes'] = wp_convert_hr_to_bytes($limits['memory_limit']);

        // Determine the effective limit considering both php.ini and WP's calculation
        // Often post_max_size is the real constraint if smaller than upload_max_filesize
        $effective_limit_bytes = min($limits['upload_max_filesize_bytes'], $limits['post_max_size_bytes'], $wp_max_bytes);
        $limits['effective_upload_limit_formatted'] = size_format($effective_limit_bytes);
        $limits['effective_upload_limit_bytes'] = $effective_limit_bytes;


        // Provide human-readable versions for direct display
        $limits['upload_max_filesize_hr'] = $limits['upload_max_filesize']; // Already human-readable from ini_get
        $limits['post_max_size_hr'] = $limits['post_max_size'];
        $limits['memory_limit_hr'] = $limits['memory_limit'];

        return $limits;
    }

    /**
     * Gets a concise summary of the most relevant upload limit.
     *
     * @return array ['limit' => int_bytes, 'formatted' => string_human_readable]
     */
    public static function get_effective_upload_limit_summary(): array
    {
        $wp_max_bytes = wp_max_upload_size();
        $upload_max_filesize_bytes = wp_convert_hr_to_bytes(ini_get('upload_max_filesize'));
        $post_max_size_bytes = wp_convert_hr_to_bytes(ini_get('post_max_size'));

        // The smallest of these is usually the effective limit for a single file.
        $effective_limit_bytes = min($wp_max_bytes, $upload_max_filesize_bytes, $post_max_size_bytes);

        return [
            'limit_bytes' => $effective_limit_bytes,
            'formatted'   => size_format($effective_limit_bytes),
        ];
    }

    /**
     * Get allowed MIME types for vector store file uploads.
     * For now, only plain text. Can be extended.
     *
     * @return array List of allowed MIME types.
     */
    public static function get_vector_upload_allowed_mime_types(): array
    {
        return apply_filters('aipkit_vector_upload_allowed_mime_types', [
            'text/plain',       // For .txt files
            'application/pdf',  // For .pdf files
            // Add more types here later, e.g., 'application/msword', etc.
        ]);
    }

    /**
     * Get allowed MIME types for Content Writer CSV file uploads.
     *
     * @return array List of allowed MIME types.
     */
    public static function get_content_writer_allowed_mime_types(): array
    {
        return apply_filters('aipkit_content_writer_allowed_mime_types', [
            'text/csv',
            'text/plain',
        ]);
    }

    /**
     * Validates an uploaded file against allowed MIME types and max size for vector stores.
     *
     * @param array $file_data $_FILES entry for the uploaded file.
     * @param array|null $allowed_mime_types Optional override for allowed MIME types.
     * @param int|null $max_size_bytes Optional override for max file size.
     * @return true|WP_Error True if valid, WP_Error otherwise.
     */
    public static function validate_vector_upload_file(array $file_data, ?array $allowed_mime_types = null, ?int $max_size_bytes = null): bool|WP_Error
    {
        if ($file_data['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', __('Error during file upload: Code ', 'gpt3-ai-content-generator') . $file_data['error']);
        }

        if ($allowed_mime_types === null) {
            $allowed_mime_types = self::get_vector_upload_allowed_mime_types();
        }

        // Check MIME type (more reliable than extension for security)
        $file_mime_type = '';
        if (function_exists('mime_content_type') && is_readable($file_data['tmp_name'])) {
            $file_mime_type = mime_content_type($file_data['tmp_name']);
        } elseif (isset($file_data['type'])) {
            $file_mime_type = $file_data['type']; // Fallback to browser-sent type
        }

        if (empty($file_mime_type) || !in_array(strtolower($file_mime_type), array_map('strtolower', $allowed_mime_types), true)) {
            return new WP_Error('invalid_file_type_vector', sprintf(
                __('Invalid file type: %1$s. Allowed types: %2$s', 'gpt3-ai-content-generator'),
                esc_html($file_mime_type ?: 'Unknown'),
                esc_html(implode(', ', $allowed_mime_types))
            ));
        }

        // Check file size
        if ($max_size_bytes === null) {
            $upload_limits = self::get_effective_upload_limit_summary();
            $max_size_bytes = $upload_limits['limit_bytes'];
        }

        if ($file_data['size'] > $max_size_bytes) {
            return new WP_Error('file_too_large_vector', sprintf(
                __('File is too large (%1$s). Maximum allowed size is %2$s.', 'gpt3-ai-content-generator'),
                size_format($file_data['size']),
                size_format($max_size_bytes)
            ));
        }

        return true;
    }
}
