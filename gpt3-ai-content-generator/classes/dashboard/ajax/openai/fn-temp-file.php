<?php

// File: classes/dashboard/ajax/openai/fn-temp-file.php
// Status: NEW FILE

namespace WPAICG\Dashboard\Ajax\OpenAI;

use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Creates a temporary file from a string of content.
 *
 * @param string $content_string The content to write to the file.
 * @param string $filename_prefix Prefix for the temporary filename.
 * @return string|WP_Error The path to the temporary file or WP_Error on failure.
 */
function _aipkit_openai_vs_files_create_temp_file_from_string(string $content_string, string $filename_prefix = 'aipkit-content'): string|\WP_Error
{
    $temp_file_path = wp_tempnam($filename_prefix, get_temp_dir());
    if ($temp_file_path === false) {
        return new WP_Error('temp_file_creation_failed', __('Could not create temporary file for content.', 'gpt3-ai-content-generator'));
    }
    $final_temp_file_path = dirname($temp_file_path) . '/' . basename($temp_file_path, '.tmp') . '.txt';
    if (rename($temp_file_path, $final_temp_file_path)) {
        $temp_file_path = $final_temp_file_path;
    }
    
    $bytes_written = file_put_contents($temp_file_path, $content_string);
    if ($bytes_written === false) {
        @unlink($temp_file_path);
        return new WP_Error('temp_file_write_failed', __('Could not write content to temporary file.', 'gpt3-ai-content-generator'));
    }
    return $temp_file_path;
}
