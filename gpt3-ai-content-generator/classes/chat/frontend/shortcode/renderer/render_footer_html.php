<?php
 namespace WPAICG\Chat\Frontend\Shortcode\RendererMethods; if (!defined('ABSPATH')) { exit; } function render_footer_html_logic(string $footer_text) { if (!empty($footer_text)) { ?>
        <div class="aipkit_chat_footer"><?php echo wp_kses_post($footer_text); ?></div>
        <?php
 } }