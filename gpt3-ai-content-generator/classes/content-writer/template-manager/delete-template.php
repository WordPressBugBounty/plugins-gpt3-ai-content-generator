<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/template-manager/delete-template.php

namespace WPAICG\ContentWriter\TemplateManagerMethods;

use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* Logic for deleting a template.
*
* @param \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance The instance of the template manager.
* @param int $template_id The ID of the template to delete.
* @return bool|WP_Error True on success, or a WP_Error on failure.
*/
function delete_template_logic(\WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance, int $template_id): bool|WP_Error
{
    $wpdb = $managerInstance->get_wpdb();
    $table_name = $managerInstance->get_table_name();

    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('not_logged_in', __('User must be logged in to delete templates.', 'gpt3-ai-content-generator'));
    }

    $template = $managerInstance->get_template($template_id, $user_id);
    if (is_wp_error($template)) {
        return $template;
    }
    if ($template['is_default']) {
        return new WP_Error('cannot_delete_default', __('The default template cannot be deleted.', 'gpt3-ai-content-generator'));
    }
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reason: Direct query to a custom table. Caches will be invalidated.
    $result = $wpdb->delete(
        $table_name,
        ['id' => $template_id, 'user_id' => $user_id, 'is_default' => 0],
        ['%d', '%d', '%d']
    );

    if ($result === false) {
        return new WP_Error('db_delete_error', __('Failed to delete template.', 'gpt3-ai-content-generator'));
    }
    return (bool)$result;
}
