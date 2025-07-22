<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/template-manager/get-templates-for-user.php
// Status: MODIFIED

namespace WPAICG\ContentWriter\TemplateManagerMethods;

use WPAICG\AIPKIT_AI_Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* Logic for retrieving all templates for the current user.
*
* @param \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance The instance of the template manager.
* @param string $type The type of template to retrieve.
* @return array An array of template objects.
*/
function get_templates_for_user_logic(\WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance, string $type = 'content_writer'): array
{
    $wpdb = $managerInstance->get_wpdb();
    $table_name = $managerInstance->get_table_name();

    $user_id = get_current_user_id();
    if (!$user_id) {
        return [];
    }
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: Direct query to a custom table. Caches will be invalidated.
    $user_templates_raw = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE user_id = %d AND is_default = 0 AND template_type = %s ORDER BY template_name ASC",
            $user_id,
            $type
        ),
        ARRAY_A
    );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: Direct query to a custom table. Caches will be invalidated.
    $default_template_raw = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE user_id = 0 AND is_default = 1 AND template_type = %s LIMIT 1", $type
        ),
        ARRAY_A
    );

    $templates = [];
    $process_raw_template = function ($raw_template) {
        if (!$raw_template) {
            return null;
        }
        $config = json_decode($raw_template['config'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $config = [];
        } else {
            // Handle Google Sheets credentials if they exist
            if (isset($config['gsheets_credentials'])) {
                $creds = $config['gsheets_credentials'];
                // If it's a string, it might be double-encoded JSON from a previous bug. Try to decode it.
                if (is_string($creds)) {
                    $decoded_creds = json_decode($creds, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_creds)) {
                        // It was a JSON string, replace it with the decoded array.
                        $config['gsheets_credentials'] = $decoded_creds;
                    }
                }
            }
        }
        $raw_template['config'] = $config;


        if (isset($raw_template['post_categories'])) {
            $cat_ids_from_db = json_decode($raw_template['post_categories'], true);
            $raw_template['config']['post_categories'] = is_array($cat_ids_from_db) ? array_map('absint', $cat_ids_from_db) : [];
        } elseif (isset($raw_template['config']['post_categories'])) {
            if (is_string($raw_template['config']['post_categories'])) {
                $cat_input = array_map('trim', explode(',', $raw_template['config']['post_categories']));
                $cat_ids = array_filter(array_map('absint', $cat_input), function ($id) {
                    return $id > 0;
                });
                $raw_template['config']['post_categories'] = array_values(array_unique($cat_ids));
            } elseif (!is_array($raw_template['config']['post_categories'])) {
                $raw_template['config']['post_categories'] = [];
            } else {
                $raw_template['config']['post_categories'] = array_values(array_unique(array_map('absint', $raw_template['config']['post_categories'])));
            }
        } else {
            $raw_template['config']['post_categories'] = [];
        }
        unset($raw_template['config']['post_tags']);

        $db_config_keys = ['post_type', 'post_author', 'post_status'];
        foreach ($db_config_keys as $key) {
            if (!isset($raw_template['config'][$key]) && isset($raw_template[$key])) {
                $raw_template['config'][$key] = $raw_template[$key];
            }
        }
        if (isset($raw_template['post_schedule']) && $raw_template['post_schedule'] !== null && $raw_template['post_schedule'] !== '0000-00-00 00:00:00') {
            $ts = strtotime($raw_template['post_schedule']);
            $raw_template['config']['post_schedule_date'] = wp_date('Y-m-d', $ts);
            $raw_template['config']['post_schedule_time'] = wp_date('H:i', $ts);
        } else {
            $raw_template['config']['post_schedule_date'] = '';
            $raw_template['config']['post_schedule_time'] = '';
        }

        if (!isset($raw_template['config']['content_max_tokens']) && class_exists(AIPKIT_AI_Settings::class)) {
            $ai_parameters = AIPKIT_AI_Settings::get_ai_parameters();
            $raw_template['config']['content_max_tokens'] = (string)($ai_parameters['max_completion_tokens'] ?? 1500);
        }

        return $raw_template;
    };

    $processed_default = $process_raw_template($default_template_raw);
    if ($processed_default) {
        $templates[] = $processed_default;
    }

    if (is_array($user_templates_raw)) {
        foreach ($user_templates_raw as $template_raw) {
            $processed_user_template = $process_raw_template($template_raw);
            if ($processed_user_template) {
                $templates[] = $processed_user_template;
            }
        }
    }
    return $templates;
}