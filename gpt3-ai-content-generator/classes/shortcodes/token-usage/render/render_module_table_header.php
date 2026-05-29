<?php
 namespace WPAICG\Shortcodes\TokenUsage\Render; if (!defined('ABSPATH')) { exit; } function render_module_table_header_logic(string $first_column_label): string { ob_start(); ?>
    <thead>
        <tr>
            <th><?php echo esc_html($first_column_label); ?></th>
            <th><?php esc_html_e('Used', 'gpt3-ai-content-generator'); ?></th>
            <th><?php esc_html_e('Quota', 'gpt3-ai-content-generator'); ?></th>
            <th><?php esc_html_e('Remaining', 'gpt3-ai-content-generator'); ?></th>
            <th><?php esc_html_e('Progress', 'gpt3-ai-content-generator'); ?></th>
        </tr>
    </thead>
    <?php
 return ob_get_clean(); } 