<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/qdrant/upsert-vectors.php

namespace WPAICG\Vector\Providers\Qdrant\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Qdrant_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the upsert_vectors method of AIPKit_Vector_Qdrant_Strategy.
 *
 * @param AIPKit_Vector_Qdrant_Strategy $strategyInstance The instance of the strategy class.
 * @param string $index_name The name of the index (collection).
 * @param array $vectors_data Data containing vectors to upsert.
 * @return array|WP_Error Result of the upsert operation or WP_Error.
 */
function upsert_vectors_logic(AIPKit_Vector_Qdrant_Strategy $strategyInstance, string $index_name, array $vectors_data): array|WP_Error {
    $path = '/collections/' . urlencode($index_name) . '/points';
    $body = [];
    $query_params = [];

    if (isset($vectors_data['points']) && is_array($vectors_data['points'])) {
        $body['points'] = $vectors_data['points'];
    } else {
        $body['points'] = $vectors_data;
    }
    foreach($body['points'] as &$point) {
        if (isset($point['values']) && !isset($point['vector'])) {
            $point['vector'] = $point['values'];
            unset($point['values']);
        }
        if (isset($point['metadata']) && !isset($point['payload'])) {
            $point['payload'] = $point['metadata'];
            unset($point['metadata']);
        }
    }
    unset($point);

    if (isset($vectors_data['wait'])) {
        $query_params['wait'] = ($vectors_data['wait'] === true || $vectors_data['wait'] === 'true') ? 'true' : 'false';
    }

    $response = _request_logic($strategyInstance, 'PUT', $path, $body, $query_params);
    return $response;
}