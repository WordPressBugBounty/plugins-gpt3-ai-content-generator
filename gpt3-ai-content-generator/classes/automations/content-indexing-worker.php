<?php

namespace WPAICG\AutoGPT\Cron\EventProcessor\Processor\ContentIndexing;

use WPAICG\Vector\PostProcessor\OpenAI\OpenAIPostProcessor;
use WPAICG\Vector\PostProcessor\Google\GooglePostProcessor;
use WPAICG\Vector\PostProcessor\Pinecone\PineconePostProcessor;
use WPAICG\Vector\PostProcessor\Qdrant\QdrantPostProcessor;
use WPAICG\Vector\PostProcessor\Chroma\ChromaPostProcessor;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Processes a single OpenAI content indexing queue item.
 *
 * @param array $item The queue item from the database.
 * @param array $item_config The decoded item_config from the queue item.
 * @return array ['status' => 'success'|'error', 'message' => '...']
 */
function process_openai_indexing_logic(array $item, array $item_config): array
{
    if (!class_exists(OpenAIPostProcessor::class)) {
        return ['status' => 'error', 'message' => "OpenAI Vector Post Processor class not found."];
    }
    $processor = new OpenAIPostProcessor();
    $post_id_to_index = absint($item['target_identifier']);
    $target_store_id = $item_config['target_store_id'] ?? null;

    if (empty($target_store_id)) {
        return ['status' => 'error', 'message' => "Target Store ID is missing for OpenAI indexing task."];
    }

    return $processor->index_single_post_to_store($post_id_to_index, $target_store_id, null);
}

/**
 * Submits a WordPress item to Google File Search.
 *
 * The processor records a durable processing row and schedules reconciliation;
 * queue success therefore means accepted for asynchronous indexing.
 *
 * @param array<string, mixed> $item
 * @param array<string, mixed> $item_config
 * @return array{status:string,message:string,job_id?:int,operation_name?:string}
 */
function process_google_indexing_logic(array $item, array $item_config): array
{
    if (!class_exists(GooglePostProcessor::class)) {
        return ['status' => 'error', 'message' => 'Google File Search post processor is unavailable.'];
    }

    $post_id = absint($item['target_identifier'] ?? 0);
    $store_name = sanitize_text_field((string) ($item_config['target_store_id'] ?? ''));
    if ($post_id < 1 || strpos($store_name, 'fileSearchStores/') !== 0) {
        return ['status' => 'error', 'message' => 'Google File Search task configuration is incomplete.'];
    }

    return (new GooglePostProcessor())->index_single_post_to_store($post_id, $store_name);
}

/**
 * Processes a single Pinecone content indexing queue item.
 *
 * @param array $item The queue item from the database.
 * @param array $item_config The decoded item_config from the queue item.
 * @return array ['status' => 'success'|'error', 'message' => '...']
 */
function process_pinecone_indexing_logic(array $item, array $item_config): array
{
    if (!class_exists(PineconePostProcessor::class)) {
        return ['status' => 'error', 'message' => "Pinecone Vector Post Processor class not found."];
    }
    $processor = new PineconePostProcessor();
    $post_id_to_index = absint($item['target_identifier']);
    $target_store_id = $item_config['target_store_id'] ?? null;
    $embedding_provider = $item_config['embedding_provider'] ?? null;
    $embedding_model = $item_config['embedding_model'] ?? null;

    if (empty($target_store_id) || empty($embedding_provider) || empty($embedding_model)) {
        return ['status' => 'error', 'message' => "Missing configuration for Pinecone indexing task (index, embedding provider, or model)."];
    }

    return $processor->index_single_post_to_index($post_id_to_index, $target_store_id, $embedding_provider, $embedding_model);
}

/**
 * Processes a single Qdrant content indexing queue item.
 *
 * @param array $item The queue item from the database.
 * @param array $item_config The decoded item_config from the queue item.
 * @return array ['status' => 'success'|'error', 'message' => '...']
 */
function process_qdrant_indexing_logic(array $item, array $item_config): array
{
    if (!class_exists(QdrantPostProcessor::class)) {
        return ['status' => 'error', 'message' => "Qdrant Vector Post Processor class not found."];
    }
    $processor = new QdrantPostProcessor();
    $post_id_to_index = absint($item['target_identifier']);
    $target_store_id = $item_config['target_store_id'] ?? null;
    $embedding_provider = $item_config['embedding_provider'] ?? null;
    $embedding_model = $item_config['embedding_model'] ?? null;

    if (empty($target_store_id) || empty($embedding_provider) || empty($embedding_model)) {
        return ['status' => 'error', 'message' => "Missing configuration for Qdrant indexing task (collection, embedding provider, or model)."];
    }

    return $processor->index_single_post_to_collection($post_id_to_index, $target_store_id, $embedding_provider, $embedding_model);
}

/**
 * Processes a single Chroma content indexing queue item.
 *
 * @param array $item The queue item from the database.
 * @param array $item_config The decoded item_config from the queue item.
 * @return array ['status' => 'success'|'error', 'message' => '...']
 */
function process_chroma_indexing_logic(array $item, array $item_config): array
{
    if (!class_exists(ChromaPostProcessor::class)) {
        $processor_path = WPAICG_PLUGIN_DIR . 'classes/knowledge-base/indexing-chroma.php';
        if (file_exists($processor_path)) {
            require_once $processor_path;
        }
    }

    if (!class_exists(ChromaPostProcessor::class)) {
        return ['status' => 'error', 'message' => 'Chroma Vector Post Processor class not found.'];
    }

    $processor = new ChromaPostProcessor();
    $post_id_to_index = absint($item['target_identifier']);
    $target_store_id = $item_config['target_store_id'] ?? null;
    $embedding_provider = $item_config['embedding_provider'] ?? null;
    $embedding_model = $item_config['embedding_model'] ?? null;

    if (empty($target_store_id) || empty($embedding_provider) || empty($embedding_model)) {
        return ['status' => 'error', 'message' => 'Missing configuration for Chroma indexing task (collection, embedding provider, or model).'];
    }

    return $processor->index_single_post_to_collection($post_id_to_index, $target_store_id, $embedding_provider, $embedding_model);
}
