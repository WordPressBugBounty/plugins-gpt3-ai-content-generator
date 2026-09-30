<?php
namespace WPAICG\Cloud;

use WPAICG\AIPKit_Role_Manager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tells site managers when Cloud credits are low or gone and how to resolve it.
 * Reads the stored balance; displaying or dismissing notices never calls Cloud.
 */
final class CreditNotice
{
    private const DISMISSED = 'aipkit_cloud_notice_dismissed';
    private const ACTION = 'aipkit_cloud_notice_dismiss';

    public static function register(): void
    {
        add_action('admin_notices', [self::class, 'render']);
        add_action('admin_init', [self::class, 'dismiss']);
    }

    /** @return array{key: string, type: string, message: string, days: int}|null */
    public static function alert(): ?array
    {
        if (!Connection::generation_ready()) { return null; }
        $state = Connection::credit_state();
        $date = Connection::refresh_date();
        if (($state['problem'] ?? '') === 'site_limit') {
            return ['key' => 'site_limit', 'type' => 'error', 'days' => 1,
                'message' => __('This site has reached its AI Puffer Cloud spending limit. Cloud requests cannot complete until the limit resets or you raise it in your Cloud account.', 'gpt3-ai-content-generator')];
        }
        if (!empty($state['credits']['restricted']) || ($state['problem'] ?? '') === 'credit_deficit') {
            return ['key' => 'credit_deficit', 'type' => 'error', 'days' => 1,
                'message' => __('A refunded or disputed Cloud credit purchase left this account with a credit deficit. Cloud requests are restricted until you add credits or contact AI Puffer support.', 'gpt3-ai-content-generator')];
        }
        if (Connection::credits_exhausted()) {
            return ['key' => 'empty', 'type' => 'error', 'days' => 1, 'message' => $date !== ''
                /* translators: %s: date the free monthly credits refresh. */
                ? sprintf(__('Your AI Puffer Cloud credits have run out. Cloud requests need credits to continue. Free credits refresh on %s.', 'gpt3-ai-content-generator'), $date)
                : __('Your AI Puffer Cloud credits have run out. Cloud requests need credits to continue.', 'gpt3-ai-content-generator')];
        }
        if (Connection::credits_low()) {
            $available_units = (int) ($state['credits']['available'] ?? 0);
            return ['key' => 'low', 'type' => 'warning', 'days' => 7, 'message' => sprintf(
                /* translators: %s: number of credits left. */
                _n('Only %s AI Puffer Cloud credit left.', 'Only %s AI Puffer Cloud credits left.', $available_units === 1000 ? 1 : 2, 'gpt3-ai-content-generator'),
                Connection::format_credits($available_units)
            ) . ($date !== ''
                /* translators: %s: date the free monthly credits refresh. */
                ? ' ' . sprintf(__('Free credits refresh on %s.', 'gpt3-ai-content-generator'), $date) : '')];
        }
        return null;
    }

    private static function plugin_screen(): bool
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        return $screen && (strpos((string) $screen->id, 'wpaicg') !== false || strpos((string) $screen->id, 'aipkit') !== false);
    }

    public static function render(bool $on_plugin_screen = false): void
    {
        if (!AIPKit_Role_Manager::user_can_manage_settings()) { return; }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && strpos((string) $screen->id, 'aipkit-setup') !== false) { return; }
        $alert = self::alert();
        // Running low is only worth mentioning where AI Puffer is being used; running out is shown everywhere.
        if (!$alert || ($alert['type'] !== 'error' && !$on_plugin_screen && !self::plugin_screen())) { return; }
        $dismissed = get_user_meta(get_current_user_id(), self::DISMISSED, true);
        if (is_array($dismissed) && (int) ($dismissed[$alert['key']] ?? 0) > time()) { return; }
        $dismiss_url = $on_plugin_screen
            ? add_query_arg([self::ACTION => $alert['key']], Connection::account_url())
            : add_query_arg([self::ACTION => $alert['key']]);
        $dismiss = wp_nonce_url($dismiss_url, self::ACTION);
        printf(
            '<div class="notice notice-%1$s aipkit_cloud_credit_notice"><p><strong>%2$s</strong> %3$s</p><p><a class="button button-primary" href="%4$s">%5$s</a> <a class="button-link" href="%6$s">%7$s</a></p></div>',
            esc_attr($alert['type']),
            esc_html__('AI Puffer Cloud:', 'gpt3-ai-content-generator'),
            esc_html($alert['message']),
            esc_url(Connection::account_url()),
            esc_html__('View credits', 'gpt3-ai-content-generator'),
            esc_url($dismiss),
            esc_html($alert['days'] > 1 ? __('Remind me next week', 'gpt3-ai-content-generator') : __('Remind me tomorrow', 'gpt3-ai-content-generator'))
        );
    }

    public static function dismiss(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified just below.
        $key = isset($_GET[self::ACTION]) ? sanitize_key(wp_unslash($_GET[self::ACTION])) : '';
        if ($key === '' || !AIPKit_Role_Manager::user_can_manage_settings() || !check_admin_referer(self::ACTION)) { return; }
        $days = in_array($key, ['empty', 'site_limit', 'credit_deficit'], true) ? 1 : 7;
        $dismissed = get_user_meta(get_current_user_id(), self::DISMISSED, true);
        $dismissed = is_array($dismissed) ? $dismissed : [];
        $dismissed[$key] = time() + $days * DAY_IN_SECONDS;
        update_user_meta(get_current_user_id(), self::DISMISSED, $dismissed);
        wp_safe_redirect(remove_query_arg([self::ACTION, '_wpnonce']));
        exit;
    }
}
