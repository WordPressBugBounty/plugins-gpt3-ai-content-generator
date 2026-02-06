<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/class-aipkit-content-writer-base-ajax-action.php

namespace WPAICG\ContentWriter\Ajax;

use WPAICG\Dashboard\Ajax\BaseDashboardAjaxHandler;
use WPAICG\Chat\Storage\LogStorage;
use WPAICG\Core\AIPKit_AI_Caller;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* Base class for Content Writer AJAX actions.
* Initializes common dependencies like LogStorage, AICaller, and VectorStoreManager.
*/
abstract class AIPKit_Content_Writer_Base_Ajax_Action extends BaseDashboardAjaxHandler
{
    public $log_storage;
    public $ai_caller;
    public $vector_store_manager;
    protected $disabled_functions = [];

    public function __construct()
    {
        $this->disabled_functions = $this->get_disabled_functions();

        // Ensure LogStorage is available
        if (class_exists(\WPAICG\Chat\Storage\LogStorage::class)) {
            $this->log_storage = new LogStorage();
        }

        // Ensure AICaller is available
        if (class_exists(\WPAICG\Core\AIPKit_AI_Caller::class)) {
            $this->ai_caller = new AIPKit_AI_Caller();
        }

        // Ensure VectorStoreManager is available
        if (class_exists(\WPAICG\Vector\AIPKit_Vector_Store_Manager::class)) {
            $this->vector_store_manager = new AIPKit_Vector_Store_Manager();
        }
    }

    /**
    * Public getter for the ai_caller dependency.
    * @return AIPKit_AI_Caller|null
    */
    public function get_ai_caller(): ?AIPKit_AI_Caller
    {
        return $this->ai_caller;
    }

    /**
    * Public getter for the vector_store_manager dependency.
    * @return AIPKit_Vector_Store_Manager|null
    */
    public function get_vector_store_manager(): ?AIPKit_Vector_Store_Manager
    {
        return $this->vector_store_manager;
    }

    protected function maybe_extend_execution_limits(int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        $max_execution_time = function_exists('ini_get') ? (int) ini_get('max_execution_time') : 0;
        if ($max_execution_time > 0 && $max_execution_time < $seconds) {
            if ($this->can_use_function('set_time_limit')) {
                set_time_limit($seconds);
            }
            if ($this->can_use_function('ini_set')) {
                ini_set('max_execution_time', (string) $seconds);
            }
        }

        $socket_timeout = function_exists('ini_get') ? (int) ini_get('default_socket_timeout') : 0;
        if ($socket_timeout > 0 && $socket_timeout < $seconds && $this->can_use_function('ini_set')) {
            ini_set('default_socket_timeout', (string) $seconds);
        }
    }

    private function can_use_function(string $function_name): bool
    {
        return function_exists($function_name) && !in_array($function_name, $this->disabled_functions, true);
    }

    private function get_disabled_functions(): array
    {
        if (!function_exists('ini_get')) {
            return [];
        }

        $disabled_functions = (string) ini_get('disable_functions');
        if ($disabled_functions === '') {
            return [];
        }

        return array_map('trim', explode(',', $disabled_functions));
    }
}
