<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/rest/class-aipkit-rest-controller.php
// Status: MODIFIED
// I have updated this controller to instantiate and register the new vector store handler and its REST endpoint.

namespace WPAICG\REST;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
// Import the new handler classes
use WPAICG\REST\Handlers\AIPKit_REST_Text_Handler;
use WPAICG\REST\Handlers\AIPKit_REST_Image_Handler;
use WPAICG\REST\Handlers\AIPKit_REST_Embeddings_Handler;
use WPAICG\REST\Handlers\AIPKit_REST_Chat_Handler;
use WPAICG\REST\Handlers\AIPKit_REST_Vector_Store_Handler;
use WPAICG\REST\Handlers\AIPKit_REST_Base_Handler; // For permission callback

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * REST API Controller for AIPKit Public Interactions.
 * Registers routes and delegates handling to specific handler classes.
 */
class AIPKit_REST_Controller extends WP_REST_Controller
{
    protected $namespace = 'aipkit/v1';
    protected $rest_base_generate = 'generate';
    protected $rest_base_images = 'images/generate';
    protected $rest_base_embeddings = 'embeddings';
    protected $rest_base_chat = 'chat';
    protected $rest_base_vectors = 'vector-stores';

    private $text_handler;
    private $image_handler;
    private $embeddings_handler;
    private $chat_handler;
    private $vector_store_handler;
    private $base_handler; // For permission check

    public function __construct()
    {
        $this->namespace = 'aipkit/v1';
        $this->rest_base_generate = 'generate';
        $this->rest_base_images = 'images/generate';
        $this->rest_base_embeddings = 'embeddings';
        $this->rest_base_chat = 'chat';
        $this->rest_base_vectors = 'vector-stores';

        // Instantiate handlers
        if (class_exists(AIPKit_REST_Text_Handler::class)) {
            $this->text_handler = new AIPKit_REST_Text_Handler();
        } else {
            error_log('AIPKit REST Controller Error: AIPKit_REST_Text_Handler class not found.');
        }

        if (class_exists(AIPKit_REST_Image_Handler::class)) {
            $this->image_handler = new AIPKit_REST_Image_Handler();
        } else {
            error_log('AIPKit REST Controller Error: AIPKit_REST_Image_Handler class not found.');
        }

        if (class_exists(AIPKit_REST_Embeddings_Handler::class)) {
            $this->embeddings_handler = new AIPKit_REST_Embeddings_Handler();
        } else {
            error_log('AIPKit REST Controller Error: AIPKit_REST_Embeddings_Handler class not found.');
        }
        
        if (class_exists(AIPKit_REST_Chat_Handler::class)) {
            $this->chat_handler = new AIPKit_REST_Chat_Handler();
        } else {
            error_log('AIPKit REST Controller Error: AIPKit_REST_Chat_Handler class not found.');
        }
        
        if (class_exists(AIPKit_REST_Vector_Store_Handler::class)) {
            $this->vector_store_handler = new AIPKit_REST_Vector_Store_Handler();
        } else {
            error_log('AIPKit REST Controller Error: AIPKit_REST_Vector_Store_Handler class not found.');
        }

        if ($this->text_handler) {
            $this->base_handler = $this->text_handler;
        }

    }

    /**
     * Register text, image, embeddings, and chat generation routes.
     */
    public function register_routes()
    {
        if (!$this->text_handler || !$this->image_handler || !$this->embeddings_handler || !$this->chat_handler || !$this->vector_store_handler || !$this->base_handler) {
            error_log('AIPKit REST Controller: One or more handlers failed to instantiate. Routes not registered.');
            return;
        }

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base_generate,
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this->text_handler, 'handle_request'),
                    'permission_callback' => array($this->base_handler, 'check_permissions'),
                    'args'                => $this->text_handler->get_endpoint_args(),
                ),
                'schema' => array($this->text_handler, 'get_item_schema'),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base_images,
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this->image_handler, 'handle_request'),
                    'permission_callback' => array($this->base_handler, 'check_permissions'),
                    'args'                => $this->image_handler->get_endpoint_args(),
                ),
                'schema' => array($this->image_handler, 'get_item_schema'),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base_embeddings,
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this->embeddings_handler, 'handle_request'),
                    'permission_callback' => array($this->base_handler, 'check_permissions'),
                    'args'                => $this->embeddings_handler->get_endpoint_args(),
                ),
                'schema' => array($this->embeddings_handler, 'get_item_schema'),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base_chat . '/(?P<bot_id>\d+)/message',
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this->chat_handler, 'handle_request'),
                    'permission_callback' => array($this->base_handler, 'check_permissions'),
                    'args'                => $this->chat_handler->get_endpoint_args(),
                ),
                'schema' => array($this->chat_handler, 'get_item_schema'),
            )
        );
        
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base_vectors . '/upsert',
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this->vector_store_handler, 'handle_request'),
                    'permission_callback' => array($this->base_handler, 'check_permissions'),
                    'args'                => $this->vector_store_handler->get_endpoint_args(),
                ),
                'schema' => array($this->vector_store_handler, 'get_item_schema'),
            )
        );
    }

}