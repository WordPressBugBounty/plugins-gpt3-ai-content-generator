<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/shortcodes/token-usage/render/render_purchase_details.php
// Status: MODIFIED

namespace WPAICG\Shortcodes\TokenUsage\Render;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Render the purchase details section with expandable purchase history.
 *
 * @param array $purchase_history Array of purchase data from get_user_purchase_history_logic
 * @param int $current_balance Current credit balance
 * @param string $shop_page_url Optional shop URL for buying more credits.
 * @param string $buy_credits_label Optional CTA label.
 * @param bool $show_purchase_history Whether to show purchase history UI.
 * @return string HTML output for purchase details section
 */
function render_purchase_details_logic(
    array $purchase_history,
    int $current_balance,
    string $shop_page_url = '',
    string $buy_credits_label = '',
    bool $show_purchase_history = true
): string
{
    $buy_credits_label = trim($buy_credits_label) !== ''
        ? $buy_credits_label
        : __('Buy credits', 'gpt3-ai-content-generator');
    ob_start();
    ?>

    <section class="aipkit_customer_shell aipkit_customer_shell--balance" id="aipkit_customer_dashboard_credits">
        <div class="aipkit_customer_shell_header">
            <div class="aipkit_customer_shell_intro">
                <h3 class="aipkit_customer_shell_title"><?php esc_html_e('Credits', 'gpt3-ai-content-generator'); ?></h3>
                <p class="aipkit_customer_shell_hint"><?php esc_html_e('Available balance and recent purchases.', 'gpt3-ai-content-generator'); ?></p>
            </div>
        </div>
        <div class="aipkit_customer_shell_body aipkit_customer_shell_body--balance">
            <div class="aipkit_token_balance_wrapper">
                <div class="aipkit_token_balance_info">
                    <span class="aipkit_token_balance_label"><?php esc_html_e('Available credits', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_token_balance_value"><?php echo esc_html(number_format_i18n($current_balance)); ?></span>
                </div>

                <div class="aipkit_purchase_actions">
                    <?php if (!empty($shop_page_url)): ?>
                        <a
                            class="aipkit_btn aipkit_btn-primary"
                            href="<?php echo esc_url($shop_page_url); ?>"
                        >
                            <?php echo esc_html($buy_credits_label); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($show_purchase_history && !empty($purchase_history)): ?>
                    <button type="button"
                            class="aipkit_toggle_purchase_history"
                            aria-expanded="false"
                            aria-controls="aipkit_purchase_history_details">
                        <span class="aipkit_toggle_text"><?php esc_html_e('Recent purchases', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_toggle_arrow">▼</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($show_purchase_history && empty($purchase_history)): ?>
                <p class="aipkit_token_balance_empty"><?php esc_html_e('No credit purchases yet.', 'gpt3-ai-content-generator'); ?></p>
            <?php endif; ?>

            <?php if ($show_purchase_history && !empty($purchase_history)): ?>
                <div id="aipkit_purchase_history_details" class="aipkit_purchase_history_details" data-aipkit-customer-purchases="1" style="display: none;">
                    <h4 class="aipkit_purchase_history_title"><?php esc_html_e('Recent credit purchases', 'gpt3-ai-content-generator'); ?></h4>

                    <div class="aipkit_purchase_history_list">
                        <?php foreach ($purchase_history as $purchase): ?>
                            <div class="aipkit_purchase_item">
                                <div class="aipkit_purchase_header">
                                    <div class="aipkit_purchase_date">
                                        <strong><?php echo esc_html(wp_date(get_option('date_format'), $purchase['date']->getTimestamp())); ?></strong>
                                    </div>
                                    <div class="aipkit_purchase_summary_info">
                                        <span class="aipkit_purchase_tokens">+<?php echo esc_html(number_format_i18n($purchase['tokens_granted'])); ?> <?php esc_html_e('credits', 'gpt3-ai-content-generator'); ?></span>
                                        <span class="aipkit_purchase_amount"><?php echo wp_kses_post(wc_price($purchase['total_amount'])); ?></span>
                                    </div>
                                </div>

                                <div class="aipkit_purchase_details">
                                    <div class="aipkit_purchase_order_info">
                                        <span class="aipkit_purchase_order_id">
                                            <?php esc_html_e('Order #', 'gpt3-ai-content-generator'); ?><?php echo esc_html($purchase['order_id']); ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($purchase['products'])): ?>
                                        <div class="aipkit_purchase_products">
                                            <?php foreach ($purchase['products'] as $product): ?>
                                                <div class="aipkit_purchase_product">
                                                    <span class="aipkit_product_name"><?php echo esc_html($product['name']); ?></span>
                                                    <span class="aipkit_product_details">
                                                        <?php if ($product['quantity'] > 1): ?>
                                                        <?php echo esc_html($product['quantity']); ?>x
                                                        <?php endif; ?>
                                                        <?php echo esc_html(number_format_i18n($product['tokens_per_item'])); ?> <?php esc_html_e('credits', 'gpt3-ai-content-generator'); ?>
                                                        = <?php echo esc_html(number_format_i18n($product['total_tokens'])); ?> <?php esc_html_e('credits', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="aipkit_purchase_history_footer">
                        <p class="aipkit_purchase_note">
                            <?php esc_html_e('Showing your most recent credit purchases. Orders must be completed to appear here.', 'gpt3-ai-content-generator'); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php
    return ob_get_clean();
}
