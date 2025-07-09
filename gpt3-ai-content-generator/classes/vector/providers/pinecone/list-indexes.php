<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/pinecone/list-indexes.php
// Status: NEW

namespace WPAICG\Vector\Providers\Pinecone\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Pinecone_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the list_indexes method of AIPKit_Vector_Pinecone_Strategy.
 *
 * @param AIPKit_Vector_Pinecone_Strategy $strategyInstance The instance of the strategy class.
 * @param int|null $limit Max items.
 * @param string|null $order Sort order.
 * @param string|null $after Cursor for next page.
 * @param string|null $before Cursor for previous page.
 * @return array|WP_Error List of indexes or WP_Error.
 */
function list_indexes_logic(AIPKit_Vector_Pinecone_Strategy $strategyInstance, ?int $limit = null, ?string $order = null, ?string $after = null, ?string $before = null): array|WP_Error {
    $path = '/indexes';
    // Pinecone list indexes does not directly support pagination parameters in the same way as OpenAI.
    // It returns a list of index names or full index descriptions based on API version.
    // This logic assumes the newer API (2024-06-20) that might return more details.
    $list_response = _request_logic($strategyInstance, 'GET', $path);
    if (is_wp_error($list_response)) return $list_response;

    $indexes_data = $list_response['indexes'] ?? []; // Newer API structure
    if (empty($indexes_data) && is_array($list_response)) { // Fallback for older list_collections format (array of strings)
        if (isset($list_response['collections'])) $indexes_data = $list_response['collections'];
        else $indexes_data = $list_response; // Assume it's directly an array of index names
    }

    $formatted_indexes = [];
    if (is_array($indexes_data)) {
        foreach ($indexes_data as $index_obj_from_list) {
            $index_name = is_string($index_obj_from_list) ? $index_obj_from_list : ($index_obj_from_list['name'] ?? null);
            if (!$index_name) continue;

            // Fetch full details for each index
            $index_detail = describe_index_logic($strategyInstance, $index_name);
            if (is_wp_error($index_detail)) {
                error_log("AIPKit Pinecone Strategy: Error describing index '{$index_name}' during list_indexes: " . $index_detail->get_error_message());
                $formatted_indexes[] = ['id' => $index_name, 'name' => $index_name, 'status' => 'Error fetching details'];
                continue;
            }

            $index_host = $index_detail['host'] ?? null;
            $total_vector_count = 'N/A';
            if ($index_host) {
                $stats_response = _request_logic($strategyInstance, 'POST', '/describe_index_stats', [], 'https://' . $index_host);
                if (!is_wp_error($stats_response) && isset($stats_response['totalVectorCount'])) {
                    $total_vector_count = (int) $stats_response['totalVectorCount'];
                } else {
                    error_log("AIPKit Pinecone Strategy: Error fetching stats for index '{$index_name}': " . (is_wp_error($stats_response) ? $stats_response->get_error_message() : 'Stats not found in response.'));
                    $total_vector_count = 'Error';
                }
            } else {
                error_log("AIPKit Pinecone Strategy: Host not found for index '{$index_name}' when trying to fetch stats.");
                $total_vector_count = 'No Host';
            }

            $formatted_indexes[] = [
                'id'   => $index_name,
                'name' => $index_name,
                'dimension' => $index_detail['dimension'] ?? ($index_obj_from_list['dimension'] ?? null),
                'metric'    => $index_detail['metric'] ?? ($index_obj_from_list['metric'] ?? null),
                'host'      => $index_host,
                'status'    => $index_detail['status'] ?? ($index_obj_from_list['status'] ?? null),
                'spec'      => $index_detail['spec'] ?? ($index_obj_from_list['spec'] ?? null),
                'total_vector_count' => $total_vector_count,
            ];
        }
    }
    return $formatted_indexes;
}