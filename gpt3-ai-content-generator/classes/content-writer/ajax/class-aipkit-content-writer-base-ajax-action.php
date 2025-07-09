<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/class-aipkit-content-writer-base-ajax-action.php

namespace WPAICG\ContentWriter\Ajax;

use WPAICG\Dashboard\Ajax\BaseDashboardAjaxHandler;
use WPAICG\Chat\Storage\LogStorage;
use WPAICG\Core\AIPKit_AI_Caller;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* Base class for Content Writer AJAX actions.
* Initializes common dependencies like LogStorage and AICaller.
*/
abstract class AIPKit_Content_Writer_Base_Ajax_Action extends BaseDashboardAjaxHandler
{
    public $log_storage;
    public $ai_caller;

    public function __construct()
    {
        // Ensure LogStorage is available
        if (class_exists(\WPAICG\Chat\Storage\LogStorage::class)) {
            $this->log_storage = new LogStorage();
        } else {
            error_log('AIPKit Content Writer Base AJAX Error: LogStorage class not found.');
            // Optionally, throw an exception or handle this fatal dependency issue
        }

        // Ensure AICaller is available
        if (class_exists(\WPAICG\Core\AIPKit_AI_Caller::class)) {
            $this->ai_caller = new AIPKit_AI_Caller();
        } else {
            error_log('AIPKit Content Writer Base AJAX Error: AIPKit_AI_Caller class not found.');
            // Optionally, throw an exception or handle this fatal dependency issue
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
}
