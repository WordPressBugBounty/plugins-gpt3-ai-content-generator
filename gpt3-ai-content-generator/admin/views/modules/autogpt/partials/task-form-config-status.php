<?php
/**
 * Partial: Automated Task Form - Status Configuration
 * Shared status toggle for all task categories.
 *
 * @since NEXT_VERSION
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_task_config_status" class="aipkit_task_config_section">
    <div class="aipkit_autogpt_schedule_shell">
        <div class="aipkit_autogpt_setting_cards">
            <?php
            $aipkit_status_label = __('Schedule', 'gpt3-ai-content-generator');
            include __DIR__ . '/shared/schedule-card.php';
            ?>
        </div>
    </div>
</div>
