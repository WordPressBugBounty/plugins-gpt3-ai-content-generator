<?php

namespace WPAICG\AutoGPT\Cron\EventProcessor\Processor\ContentIndexing;

use WPAICG\Vector\PostProcessor\Google\GooglePostProcessor;

if (!defined('ABSPATH')) {
    exit;
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
