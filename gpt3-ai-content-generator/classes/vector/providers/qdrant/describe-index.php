<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/qdrant/describe-index.php

namespace WPAICG\Vector\Providers\Qdrant\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Qdrant_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the describe_index method of AIPKit_Vector_Qdrant_Strategy.
 * Describes a Qdrant collection.
 *
 * @param AIPKit_Vector_Qdrant_Strategy $strategyInstance The instance of the strategy class.
 * @param string $index_name The name of the index (collection).
 * @return array|WP_Error Collection details or WP_Error.
 */
function describe_index_logic(AIPKit_Vector_Qdrant_Strategy $strategyInstance, string $index_name): array|WP_Error {
    $path = '/collections/' . urlencode($index_name);
    return _request_logic($strategyInstance, 'GET', $path);
}