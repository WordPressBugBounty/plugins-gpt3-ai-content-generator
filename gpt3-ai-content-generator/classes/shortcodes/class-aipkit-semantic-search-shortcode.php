<?php
 namespace WPAICG\Shortcodes; if (!defined('ABSPATH')) { exit; } class AIPKit_Semantic_Search_Shortcode { public function render_shortcode($atts = []) { ob_start(); ?>
        <div class="aipkit_semantic_search_wrapper">
            <form class="aipkit_semantic_search_form" onsubmit="return false;">
                <input type="search" class="aipkit_semantic_search_input" placeholder="<?php esc_attr_e('Search...', 'gpt3-ai-content-generator'); ?>" required>
                <button type="submit" class="aipkit_semantic_search_button">
                    <span class="aipkit_btn-text"><?php esc_html_e('Search', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_spinner" style="display:none;"></span>
                </button>
            </form>
            <div class="aipkit_semantic_search_results">
            </div>
        </div>
        <?php
 return ob_get_clean(); } } 