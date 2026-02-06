<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/settings/partials/settings-advanced-integrations.php
// Status: MODIFIED

/**
 * Partial: Integrations Settings
 * This file acts as a router, including different integration partials.
 */
if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="aipkit_settings-tab-content-inner-padding">
    <div class="aipkit_accordion-group">

        <?php include __DIR__ . '/integrations/rest-api.php'; ?>

    </div>
</div>
