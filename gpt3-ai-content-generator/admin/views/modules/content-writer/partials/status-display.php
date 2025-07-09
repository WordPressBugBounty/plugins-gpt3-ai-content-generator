<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/content-writer/partials/status-display.php
// Status: MODIFIED
// I have made the status container visible by default to improve the UI when no content has been generated yet.
/**
 * Partial: Content Writer Status Display
 * Contains all status indicators, messages, and progress bars for the generation process.
 * Placed in the right-hand column, above the template manager.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- The sub-container has been removed. The content is now a direct child of the column. -->
<div id="aipkit_cw_status_display_container">
    <div id="aipkit_content_writer_form_status" class="aipkit_form-help"></div>
    <?php include __DIR__ . '/generation-status-indicators.php'; ?>
    <div class="aipkit_content_writer_progress_bar_container" style="display: none;">
        <div class="aipkit_content_writer_progress_bar"></div>
    </div>
</div>