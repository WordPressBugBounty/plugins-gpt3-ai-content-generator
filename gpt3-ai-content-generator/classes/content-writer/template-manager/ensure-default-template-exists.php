<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/template-manager/ensure-default-template-exists.php
// Status: MODIFIED
// I have updated this file to create a personal, editable "Default Template" for each user instead of a single, global, un-editable one.

namespace WPAICG\ContentWriter\TemplateManagerMethods;

if (!defined('ABSPATH')) {
    exit;
}

/**
* Logic for ensuring a user-specific default template exists.
*
* @param \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance The instance of the template manager.
*/
function ensure_default_template_exists_logic(\WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager $managerInstance)
{
    $wpdb = $managerInstance->get_wpdb();
    $table_name = $managerInstance->get_table_name();

    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        return; // Do not create default templates for logged-out users/processes
    }
    ensure_starter_templates_exist_logic($managerInstance);
    set_cw_short_starter_as_default($managerInstance);
}
