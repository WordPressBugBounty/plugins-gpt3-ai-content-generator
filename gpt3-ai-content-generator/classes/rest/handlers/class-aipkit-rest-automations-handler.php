<?php

namespace WPAICG\REST\Handlers;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPAICG\AutoGPT\Cron\AIPKit_Automation_Runner;
use WPAICG\AutoGPT\Cron\AIPKit_Automation_Server_Cron;
use WPAICG\Includes\DependencyLoaders\Automated_Task_Cron_Helpers_Loader;
use WPAICG\Includes\DependencyLoaders\Automated_Task_Dependencies_Loader;
use WPAICG\Includes\DependencyLoaders\Content_Writer_Dependencies_Loader;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * Authenticated REST handler that directly wakes AI Puffer automations.
 */
class AIPKit_REST_Automations_Handler
{
    /**
     * Checks the dedicated server-cron secret without using the public API key.
     *
     * @return true|WP_Error
     */
    public function check_permissions(WP_REST_Request $request)
    {
        self::ensure_automation_dependencies();
        if (!class_exists(AIPKit_Automation_Server_Cron::class)) {
            return new WP_Error(
                'aipkit_server_cron_unavailable',
                __('AI Puffer server cron is unavailable.', 'gpt3-ai-content-generator'),
                ['status' => 503]
            );
        }

        return AIPKit_Automation_Server_Cron::check_request($request);
    }

    /**
     * Runs due tasks and one existing queue-worker batch directly.
     */
    public function handle_request(WP_REST_Request $request)
    {
        unset($request);
        self::ensure_automation_dependencies();
        if (!class_exists(AIPKit_Automation_Runner::class)) {
            return new WP_Error(
                'aipkit_automation_runner_unavailable',
                __('The AI Puffer automation runner is unavailable.', 'gpt3-ai-content-generator'),
                ['status' => 503]
            );
        }

        $result = AIPKit_Automation_Runner::run_server_cron();
        AIPKit_Automation_Server_Cron::record_run($result);

        $response = new WP_REST_Response($result, 200);
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        return $response;
    }

    /**
     * Loads automation-only dependencies for a frontend REST request.
     */
    private static function ensure_automation_dependencies(): void
    {
        Content_Writer_Dependencies_Loader::load();
        Automated_Task_Dependencies_Loader::load();
        Automated_Task_Cron_Helpers_Loader::load();
    }
}
