<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/template-manager/update-template.php

namespace WPAICG\ContentWriter\TemplateManagerMethods;

use WP_Error;

// Load dependencies
require_once __DIR__ . '/sanitize-config.php';
require_once __DIR__ . '/calculate-schedule-datetime.php';

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* Logic for updating an existing template.
*
* @param \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance The instance of the template manager.
* @param int $template_id The ID of the template to update.
* @param string $template_name The new name for the template.
* @param array $config The new configuration for the template.
* @return bool|WP_Error True on success, or a WP_Error on failure.
*/
function update_template_logic(\WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance, int $template_id, string $template_name, array $config): bool|WP_Error
{
    $wpdb = $managerInstance->get_wpdb();
    $table_name = $managerInstance->get_table_name();

    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('not_logged_in', __('User must be logged in to update templates.', 'gpt3-ai-content-generator'));
    }
    if (empty($template_name)) {
        return new WP_Error('empty_template_name', __('Template name cannot be empty.', 'gpt3-ai-content-generator'));
    }

    $template = $managerInstance->get_template($template_id, $user_id);
    if (is_wp_error($template)) {
        return $template;
    }
    if ($template['is_default']) {
        return new WP_Error('cannot_update_default', __('The default template cannot be modified.', 'gpt3-ai-content-generator'));
    }
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reason: Direct query to a custom table. Caches will be invalidated.
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table_name} WHERE user_id = %d AND template_name = %s AND id != %d",
        $user_id,
        $template_name,
        $template_id
    ));
    if ($existing) {
        return new WP_Error('duplicate_template_name_update', __('Another template with this name already exists.', 'gpt3-ai-content-generator'));
    }

    $sanitized_config = sanitize_config_logic($managerInstance, $config);
    $post_schedule_datetime = calculate_schedule_datetime_logic($sanitized_config['post_schedule_date'] ?? '', $sanitized_config['post_schedule_time'] ?? '');
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reason: Direct update to a custom table. Caches will be invalidated.
    $result = $wpdb->update(
        $table_name,
        [
            'template_name' => sanitize_text_field($template_name),
            'config' => wp_json_encode($sanitized_config),
            'updated_at' => current_time('mysql', 1),
            'post_type' => $sanitized_config['post_type'] ?? 'post',
            'post_author' => $sanitized_config['post_author'] ?? $user_id,
            'post_status' => $sanitized_config['post_status'] ?? 'draft',
            'post_schedule' => $post_schedule_datetime,
            'post_categories' => wp_json_encode($sanitized_config['post_categories'] ?? []),
            ],
        ['id' => $template_id, 'user_id' => $user_id, 'is_default' => 0],
        ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s'],
        ['%d', '%d', '%d']
    );

    if ($result === false) {
        return new WP_Error('db_update_error', __('Failed to update template.', 'gpt3-ai-content-generator'));
    }
    return true;
}
