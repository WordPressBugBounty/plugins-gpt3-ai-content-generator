<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/index.php
// Status: MODIFIED

/**
 * AIPKit AI Training Module
 *
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="aipkit_container">
    <div class="aipkit_container-header">
        <div class="aipkit_container-title"><?php esc_html_e('Train', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_container-actions">
            <?php // Placeholder for potential future actions ?>
        </div>
    </div>
    <?php
    // The content is now directly included without tabs.
    // The included partial provides the `aipkit_container-body` div.
    include __DIR__ . '/partials/vector-store.php';
    ?>
</div>