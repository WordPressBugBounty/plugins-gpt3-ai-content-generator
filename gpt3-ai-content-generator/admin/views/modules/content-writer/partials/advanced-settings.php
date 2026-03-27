<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<details class="aipkit_cw_advanced_group" id="aipkit_cw_advanced_group">
    <summary class="aipkit_cw_advanced_toggle">
        <span class="aipkit_cw_panel_label">
            <?php esc_html_e('Advanced', 'gpt3-ai-content-generator'); ?>
        </span>
    </summary>
    <div class="aipkit_cw_advanced_body">
        <section class="aipkit_cw_advanced_section aipkit_cw_inline_section--vector">
            <?php include __DIR__ . '/form-inputs/vector-settings.php'; ?>
        </section>
    </div>
</details>
