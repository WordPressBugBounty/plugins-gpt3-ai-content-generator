<?php

namespace WPAICG\AutoGPT\Ajax;

use WP_Error;
use WPAICG\AutoGPT\Cron\AIPKit_Automation_Server_Cron;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * Manages the opt-in external server-cron secret and status.
 */
class AIPKit_Manage_Automation_Server_Cron_Action extends AIPKit_Automated_Task_Base_Ajax_Action
{
    public function handle_request()
    {
        $permission_check = $this->check_module_access_permissions('autogpt', self::NONCE_ACTION);
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        if (!class_exists(AIPKit_Automation_Server_Cron::class)) {
            $this->send_wp_error(
                new WP_Error(
                    'aipkit_server_cron_unavailable',
                    __('Server cron is unavailable.', 'gpt3-ai-content-generator')
                ),
                503
            );
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked above.
        $operation = isset($_POST['operation']) ? sanitize_key(wp_unslash($_POST['operation'])) : 'status';

        if ($operation === 'enable') {
            $result = AIPKit_Automation_Server_Cron::enable();
        } elseif ($operation === 'rotate') {
            $result = AIPKit_Automation_Server_Cron::rotate_secret();
        } elseif ($operation === 'disable') {
            $result = AIPKit_Automation_Server_Cron::disable();
        } elseif ($operation === 'status') {
            $result = AIPKit_Automation_Server_Cron::get_status();
        } else {
            $result = new WP_Error(
                'aipkit_server_cron_invalid_operation',
                __('Invalid server cron operation.', 'gpt3-ai-content-generator')
            );
        }

        if (is_wp_error($result)) {
            $this->send_wp_error($result, 400);
            return;
        }

        wp_send_json_success($result);
    }
}
