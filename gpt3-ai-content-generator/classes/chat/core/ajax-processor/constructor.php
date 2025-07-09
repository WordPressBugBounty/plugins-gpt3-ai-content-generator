<?php
// File: classes/chat/core/ajax-processor/constructor.php

namespace WPAICG\Chat\Core\AjaxProcessor;

use WPAICG\Chat\Storage\BotStorage;
use WPAICG\Chat\Core\AIService;
use WPAICG\Chat\Storage\LogStorage;
// --- MODIFIED: Use new Token Manager namespace ---
use WPAICG\Core\TokenManager\AIPKit_Token_Manager;
// --- END MODIFICATION ---
use WPAICG\Core\AIPKit_Content_Moderator; // For Content Moderator

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the AjaxProcessor constructor.
 * Initializes dependencies.
 *
 * @param \WPAICG\Chat\Core\AjaxProcessor $processorInstance The instance of the AjaxProcessor class.
 * @return void
 */
function constructor(\WPAICG\Chat\Core\AjaxProcessor $processorInstance): void {
    if (!class_exists(\WPAICG\Chat\Storage\BotStorage::class)) {
        error_log('AIPKit AjaxProcessor Constructor Logic Error: BotStorage facade class not found.');
        $processorInstance->set_bot_storage(null);
    } else {
        $processorInstance->set_bot_storage(new BotStorage());
    }

    if (!class_exists(\WPAICG\Chat\Core\AIService::class)) {
        error_log('AIPKit AjaxProcessor Constructor Logic Error: AIService class not found.');
        $processorInstance->set_ai_service(null);
    } else {
        $processorInstance->set_ai_service(new AIService());
    }

    if (!class_exists(\WPAICG\Chat\Storage\LogStorage::class)) {
        error_log('AIPKit AjaxProcessor Constructor Logic Error: LogStorage class not found.');
        $processorInstance->set_log_storage(null);
    } else {
        $processorInstance->set_log_storage(new LogStorage());
    }

    // --- MODIFIED: Check and instantiate new Token Manager ---
    if (!class_exists(\WPAICG\Core\TokenManager\AIPKit_Token_Manager::class)) {
        error_log('AIPKit AjaxProcessor Constructor Logic Error: New AIPKit_Token_Manager class not found.');
        $processorInstance->set_token_manager(null);
    } else {
        $processorInstance->set_token_manager(new \WPAICG\Core\TokenManager\AIPKit_Token_Manager());
    }
    // --- END MODIFICATION ---

    // Ensure Content Moderator is loaded (it's used by ajax_frontend_chat_message)
    if (!class_exists(\WPAICG\Core\AIPKit_Content_Moderator::class)) {
        $moderator_path = WPAICG_PLUGIN_DIR . 'classes/core/class-aipkit-content-moderator.php';
         if (file_exists($moderator_path)) { require_once $moderator_path; }
         else { error_log('AIPKit AjaxProcessor Constructor Logic Error: AIPKit_Content_Moderator class file not found.'); }
    }
}