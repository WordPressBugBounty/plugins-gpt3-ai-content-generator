<?php
 namespace WPAICG\Chat\Frontend\Shortcode\RendererMethods; if (!defined('ABSPATH')) { exit; } function render_sidebar_html_logic(array $frontend_config) { $new_chat_svg = '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>'; ?>
    <div class="aipkit_chat_sidebar" aria-hidden="true">
         <div class="aipkit_sidebar_header">
            <h4 class="aipkit_sidebar_title"><?php echo esc_html($frontend_config['text']['conversations']); ?></h4>
            <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small aipkit_sidebar_new_chat_btn" aria-label="<?php echo esc_attr($frontend_config['text']['newChat']); ?>">
                 <?php echo $new_chat_svg; ?> <?php echo esc_html($frontend_config['text']['newChat']); ?>
            </button>
         </div>
         <div class="aipkit_sidebar_content" aria-live="polite">
         </div>
         <div class="aipkit_sidebar_footer">
         </div>
    </div>
    <?php
} 