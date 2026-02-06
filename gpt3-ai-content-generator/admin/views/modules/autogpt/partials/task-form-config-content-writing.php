<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/autogpt/partials/task-form-config-content-writing.php
// Status: MODIFIED

/**
 * Partial: Automated Task Form - Content Writing Configuration
 * Contains fields specific to the 'content_writing' task type.
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\aipkit_dashboard; // For pro checks

// Variables available from parent:
// $cw_available_post_types, $cw_current_user_id, $cw_users_for_author,
// $cw_post_statuses, $cw_wp_categories, $is_pro

?>
<div id="aipkit_task_config_cw_main" class="aipkit_task_config_section">
    <div class="aipkit_autogpt_setting_cards">
        <?php
        $aipkit_post_settings_card_id = 'aipkit_autogpt_cw_post_settings_popover';
        $aipkit_post_settings_popover_body_path = __DIR__ . '/content-writing/post-settings.php';
        include __DIR__ . '/shared/post-settings-card.php';
        ?>
    </div>
</div> <!-- End Content Writing Specific Fields -->
