<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/shortcodes/token-usage/render/render_dashboard.php
// Status: MODIFIED

namespace WPAICG\Shortcodes\TokenUsage\Render;

// --- NEW: Require the new helper function file ---
require_once __DIR__ . '/render_module_table_header.php';

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for rendering the HTML for the token usage dashboard.
 *
 * @param \WPAICG\Shortcodes\AIPKit_Token_Usage_Shortcode $facade The facade instance.
 * @param array $usage_data Structured usage data grouped by module.
 * @param bool $show_chatbot
 * @param bool $show_aiforms
 * @param bool $show_imagegenerator
 * @param string $dashboard_title
 * @param string $dashboard_intro
 * @param bool $show_buy_credits
 * @param string $buy_credits_label
 * @param string $buy_credits_url
 * @param bool $show_purchase_history
 * @return string HTML output.
 */
function render_dashboard_logic(
    \WPAICG\Shortcodes\AIPKit_Token_Usage_Shortcode $facade,
    array $usage_data,
    bool $show_chatbot = true,
    bool $show_aiforms = true,
    bool $show_imagegenerator = true,
    string $dashboard_title = '',
    string $dashboard_intro = '',
    bool $show_buy_credits = true,
    string $buy_credits_label = '',
    string $buy_credits_url = '',
    bool $show_purchase_history = true
): string {
    ob_start();
    $shop_page_url = trim($buy_credits_url);
    if ($shop_page_url === '') {
        $shop_page_url = get_option('aipkit_token_shop_page_url', '');
    }
    if ($shop_page_url === '' && function_exists('wc_get_page_id')) {
        $shop_page_url = get_permalink(wc_get_page_id('shop'));
    }
    $dashboard_title = trim($dashboard_title) !== ''
        ? $dashboard_title
        : __('Credits & Usage', 'gpt3-ai-content-generator');
    $dashboard_intro = trim($dashboard_intro) !== ''
        ? $dashboard_intro
        : __('View your credits, purchases, and quotas.', 'gpt3-ai-content-generator');
    $buy_credits_label = trim($buy_credits_label) !== ''
        ? $buy_credits_label
        : __('Buy credits', 'gpt3-ai-content-generator');
    ?>
    <div class="aipkit_token_usage_dashboard">
        <div class="aipkit_token_usage_header">
            <div class="aipkit_token_usage_header_copy">
                <h2 class="aipkit_token_usage_title"><?php echo esc_html($dashboard_title); ?></h2>
                <p class="aipkit_token_usage_intro"><?php echo esc_html($dashboard_intro); ?></p>
            </div>
        </div>
        <div class="aipkit_token_usage_content">

            <?php
            $purchase_history = \WPAICG\Shortcodes\TokenUsage\Data\get_user_purchase_history_logic(get_current_user_id(), 10);
            echo wp_kses_post(\WPAICG\Shortcodes\TokenUsage\Render\render_purchase_details_logic(
                $purchase_history,
                $usage_data['token_balance'],
                $show_buy_credits ? (string) $shop_page_url : '',
                $buy_credits_label,
                $show_purchase_history
            ));
            ?>

            <?php
            $has_periodic_usage = ($show_chatbot && !empty($usage_data['chat'])) ||
                                  ($show_imagegenerator && !empty($usage_data['image_generator'])) ||
                                  ($show_aiforms && !empty($usage_data['ai_forms'])) ||
                                  has_action('aipkit_after_token_usage_dashboard');
            ?>
            <section class="aipkit_customer_shell aipkit_customer_shell--usage">
                <div class="aipkit_customer_shell_header">
                    <div class="aipkit_customer_shell_intro">
                        <h3 class="aipkit_customer_shell_title"><?php esc_html_e('Quota Usage', 'gpt3-ai-content-generator'); ?></h3>
                        <p class="aipkit_customer_shell_hint"><?php esc_html_e('Track usage across your enabled modules.', 'gpt3-ai-content-generator'); ?></p>
                    </div>
                </div>
                <div class="aipkit_customer_shell_body aipkit_customer_shell_body--usage">
                    <?php if ($has_periodic_usage) : ?>
                        <div class="aipkit_customer_usage_stack">
                            <?php if ($show_chatbot && !empty($usage_data['chat'])) : ?>
                                <div class="aipkit_customer_usage_group">
                                    <h4 class="aipkit_customer_usage_group_title"><?php esc_html_e('Chatbot', 'gpt3-ai-content-generator'); ?></h4>
                                    <div class="aipkit_usage_table_shell">
                                        <table class="aipkit_usage_table">
                                            <?php echo wp_kses_post(render_module_table_header_logic(__('Chatbot', 'gpt3-ai-content-generator'))); ?>
                                            <tbody>
                                                <?php
                                                foreach ($usage_data['chat'] as $bot_usage) {
                                                    echo wp_kses_post(\WPAICG\Shortcodes\TokenUsage\Render\render_usage_row_logic($facade, $bot_usage, __('Chatbot', 'gpt3-ai-content-generator')));
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($show_imagegenerator && !empty($usage_data['image_generator'])) : ?>
                                <div class="aipkit_customer_usage_group">
                                    <h4 class="aipkit_customer_usage_group_title"><?php esc_html_e('Image Generator', 'gpt3-ai-content-generator'); ?></h4>
                                    <div class="aipkit_usage_table_shell">
                                        <table class="aipkit_usage_table">
                                            <?php echo wp_kses_post(render_module_table_header_logic(__('Module', 'gpt3-ai-content-generator'))); ?>
                                            <tbody>
                                                <?php
                                                foreach ($usage_data['image_generator'] as $item) {
                                                    echo wp_kses_post(\WPAICG\Shortcodes\TokenUsage\Render\render_usage_row_logic($facade, $item, __('Module', 'gpt3-ai-content-generator')));
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($show_aiforms && !empty($usage_data['ai_forms'])) : ?>
                                <div class="aipkit_customer_usage_group">
                                    <h4 class="aipkit_customer_usage_group_title"><?php esc_html_e('AI Forms', 'gpt3-ai-content-generator'); ?></h4>
                                    <div class="aipkit_usage_table_shell">
                                        <table class="aipkit_usage_table">
                                            <?php echo wp_kses_post(render_module_table_header_logic(__('Module', 'gpt3-ai-content-generator'))); ?>
                                            <tbody>
                                                <?php
                                                foreach ($usage_data['ai_forms'] as $item) {
                                                    echo wp_kses_post(\WPAICG\Shortcodes\TokenUsage\Render\render_usage_row_logic($facade, $item, __('Module', 'gpt3-ai-content-generator')));
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php do_action('aipkit_after_token_usage_dashboard', $usage_data); ?>
                        </div>
                    <?php else : ?>
                        <div class="aipkit_usage_empty_state">
                            <p class="aipkit_customer_shell_hint"><?php esc_html_e('No quota usage has been recorded yet.', 'gpt3-ai-content-generator'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
// --- END: render_dashboard_logic() ---
