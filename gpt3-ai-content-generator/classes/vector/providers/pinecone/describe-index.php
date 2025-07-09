<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/pinecone/describe-index.php
// Status: NEW

namespace WPAICG\Vector\Providers\Pinecone\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Pinecone_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the describe_index method of AIPKit_Vector_Pinecone_Strategy.
 *
 * @param AIPKit_Vector_Pinecone_Strategy $strategyInstance The instance of the strategy class.
 * @param string $index_name The name of the index to describe.
 * @return array|WP_Error Index details or WP_Error.
 */
function describe_index_logic(AIPKit_Vector_Pinecone_Strategy $strategyInstance, string $index_name): array|WP_Error {
    $path = '/indexes/' . urlencode($index_name);
    return _request_logic($strategyInstance, 'GET', $path);
}