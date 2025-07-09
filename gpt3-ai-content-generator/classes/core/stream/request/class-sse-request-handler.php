<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/stream/request/class-sse-request-handler.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Request;

use WPAICG\Core\Stream\Cache\AIPKit_SSE_Message_Cache;
use WPAICG\Chat\Storage\LogStorage;
use WPAICG\Core\TokenManager\AIPKit_Token_Manager; // Corrected use statement
use WPAICG\Core\AIPKit_AI_Caller;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
// --- MODIFIED: Use new PostProcessor namespaces ---
use WPAICG\Vector\PostProcessor\Pinecone\PineconePostProcessor;
use WPAICG\Vector\PostProcessor\Qdrant\QdrantPostProcessor;
// --- END MODIFICATION ---
use WPAICG\Core\Stream\Vector\SSEVectorContextHelper;
use WPAICG\AIForms\Storage\AIPKit_AI_Form_Storage;
use WPAICG\Core\Stream\Contexts\Chat\SSEChatStreamContextHandler;
use WPAICG\Core\Stream\Contexts\ContentWriter\SSEContentWriterStreamContextHandler;
use WPAICG\Core\Stream\Contexts\AIForms\SSEAIFormsStreamContextHandler;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Load method logic file
require_once __DIR__ . '/fn-process-initial-request.php';

/**
 * Handles initial validation, data retrieval, and payload formatting for SSE requests.
 * Routes requests to context-specific handlers.
 */
class SSERequestHandler
{
    private $log_storage;
    private $sse_message_cache;
    private $token_manager;
    private $ai_caller;
    private $vector_store_manager;
    // --- MODIFIED: Type hint for new PostProcessors ---
    private $pinecone_post_processor;
    private $qdrant_post_processor;
    // --- END MODIFICATION ---
    private $sse_vector_context_helper;
    private $ai_form_storage;
    // Context Handlers
    private $chat_context_handler;
    private $content_writer_context_handler;
    private $ai_forms_context_handler;


    public function __construct(LogStorage $log_storage_passed = null)
    {
        // Dependencies should be loaded by AIPKit_Dependency_Loader.
        // Constructors now assume classes are available.

        $this->log_storage = $log_storage_passed;
        if (!$this->log_storage && class_exists(\WPAICG\Chat\Storage\LogStorage::class)) {
            $this->log_storage = new \WPAICG\Chat\Storage\LogStorage();
        } elseif (!$this->log_storage) {
            error_log('SSERequestHandler Error: LogStorage class not found and not passed.');
        }

        $this->sse_message_cache = class_exists(\WPAICG\Core\Stream\Cache\AIPKit_SSE_Message_Cache::class)
            ? new \WPAICG\Core\Stream\Cache\AIPKit_SSE_Message_Cache()
            : null;
        if (!$this->sse_message_cache) {
            error_log('SSERequestHandler Error: AIPKit_SSE_Message_Cache class not found.');
        }

        $this->token_manager = class_exists(\WPAICG\Core\TokenManager\AIPKit_Token_Manager::class)
            ? new \WPAICG\Core\TokenManager\AIPKit_Token_Manager()
            : null;
        if (!$this->token_manager) {
            error_log('SSERequestHandler Error: AIPKit_Token_Manager class not found.');
        }

        $this->ai_caller = class_exists(\WPAICG\Core\AIPKit_AI_Caller::class)
            ? new \WPAICG\Core\AIPKit_AI_Caller()
            : null;
        if (!$this->ai_caller) {
            error_log('SSERequestHandler Error: AIPKit_AI_Caller class not found.');
        }

        $this->vector_store_manager = class_exists(\WPAICG\Vector\AIPKit_Vector_Store_Manager::class)
            ? new \WPAICG\Vector\AIPKit_Vector_Store_Manager()
            : null;
        if (!$this->vector_store_manager) {
            error_log('SSERequestHandler Error: AIPKit_Vector_Store_Manager class not found.');
        }

        // --- MODIFIED: Instantiate new PostProcessors ---
        $this->pinecone_post_processor = class_exists(\WPAICG\Vector\PostProcessor\Pinecone\PineconePostProcessor::class)
            ? new \WPAICG\Vector\PostProcessor\Pinecone\PineconePostProcessor()
            : null;
        if (!$this->pinecone_post_processor) {
            error_log('SSERequestHandler Error: PineconePostProcessor class not found.');
        }

        $this->qdrant_post_processor = class_exists(\WPAICG\Vector\PostProcessor\Qdrant\QdrantPostProcessor::class)
            ? new \WPAICG\Vector\PostProcessor\Qdrant\QdrantPostProcessor()
            : null;
        if (!$this->qdrant_post_processor) {
            error_log('SSERequestHandler Error: QdrantPostProcessor class not found.');
        }
        // --- END MODIFICATION ---

        $this->sse_vector_context_helper = (class_exists(\WPAICG\Core\Stream\Vector\SSEVectorContextHelper::class) && $this->ai_caller && $this->vector_store_manager)
            ? new \WPAICG\Core\Stream\Vector\SSEVectorContextHelper($this->ai_caller, $this->vector_store_manager, $this->pinecone_post_processor, $this->qdrant_post_processor)
            : null;
        if (!$this->sse_vector_context_helper) {
            error_log('SSERequestHandler Error: SSEVectorContextHelper or its dependencies missing.');
        }

        $this->ai_form_storage = class_exists(\WPAICG\AIForms\Storage\AIPKit_AI_Form_Storage::class)
            ? new \WPAICG\AIForms\Storage\AIPKit_AI_Form_Storage()
            : null;
        if (!$this->ai_form_storage) {
            error_log('SSERequestHandler Error: AIPKit_AI_Form_Storage class not found.');
        }

        // Instantiate Context Handlers
        $bot_storage_for_chat_handler = class_exists(\WPAICG\Chat\Storage\BotStorage::class) ? new \WPAICG\Chat\Storage\BotStorage() : null;
        if ($this->log_storage && $this->token_manager && $bot_storage_for_chat_handler && class_exists(\WPAICG\Core\Stream\Contexts\Chat\SSEChatStreamContextHandler::class)) {
            $this->chat_context_handler = new \WPAICG\Core\Stream\Contexts\Chat\SSEChatStreamContextHandler($bot_storage_for_chat_handler, $this->log_storage, $this->token_manager, $this->sse_vector_context_helper);
        } else {
            error_log('SSERequestHandler Error: Dependencies missing for SSEChatStreamContextHandler. LogStorage: ' . ($this->log_storage ? 'OK' : 'FAIL') . ', TokenManager: ' . ($this->token_manager ? 'OK' : 'FAIL') . ', BotStorage: ' . ($bot_storage_for_chat_handler ? 'OK' : 'FAIL'));
            $this->chat_context_handler = null;
        }

        if ($this->log_storage && class_exists(\WPAICG\Core\Stream\Contexts\ContentWriter\SSEContentWriterStreamContextHandler::class)) {
            $this->content_writer_context_handler = new \WPAICG\Core\Stream\Contexts\ContentWriter\SSEContentWriterStreamContextHandler($this->log_storage);
        } else {
            error_log('SSERequestHandler Error: LogStorage missing or SSEContentWriterStreamContextHandler class not found.');
            $this->content_writer_context_handler = null;
        }

        $this->ai_forms_context_handler = (
            $this->log_storage && $this->ai_form_storage && $this->token_manager &&
            $this->ai_caller && $this->vector_store_manager &&
            class_exists(\WPAICG\Core\Stream\Contexts\AIForms\SSEAIFormsStreamContextHandler::class)
        ) ? new \WPAICG\Core\Stream\Contexts\AIForms\SSEAIFormsStreamContextHandler(
            $this->log_storage,
            $this->ai_form_storage,
            $this->token_manager,
            $this->ai_caller,
            $this->vector_store_manager
        )
          : null;

        if (!$this->ai_forms_context_handler) {
            error_log('SSERequestHandler Error: Dependencies missing for SSEAIFormsStreamContextHandler.');
        }
    }

    // Getters for externalized logic
    public function get_sse_message_cache(): ?AIPKit_SSE_Message_Cache
    {
        return $this->sse_message_cache;
    }
    public function get_chat_context_handler(): ?SSEChatStreamContextHandler
    {
        return $this->chat_context_handler;
    }
    public function get_content_writer_context_handler(): ?SSEContentWriterStreamContextHandler
    {
        return $this->content_writer_context_handler;
    }
    public function get_ai_forms_context_handler(): ?SSEAIFormsStreamContextHandler
    {
        return $this->ai_forms_context_handler;
    }


    public function process_initial_request(array $get_params): array|WP_Error
    {
        return process_initial_request_logic($this, $get_params);
    }
}
