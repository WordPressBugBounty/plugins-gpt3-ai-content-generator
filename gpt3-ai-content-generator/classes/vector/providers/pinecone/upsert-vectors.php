<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/pinecone/upsert-vectors.php
// Status: NEW

namespace WPAICG\Vector\Providers\Pinecone\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Pinecone_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the upsert_vectors method of AIPKit_Vector_Pinecone_Strategy.
 *
 * @param AIPKit_Vector_Pinecone_Strategy $strategyInstance The instance of the strategy class.
 * @param string $index_name The name of the index.
 * @param array $vectors_data Data containing vectors and optional namespace.
 * @return array|WP_Error Result of the upsert operation or WP_Error.
 */
function upsert_vectors_logic(AIPKit_Vector_Pinecone_Strategy $strategyInstance, string $index_name, array $vectors_data): array|WP_Error {
    $index_description = describe_index_logic($strategyInstance, $index_name); // Use externalized describe_index_logic
    if (is_wp_error($index_description)) return $index_description;
    $host = $index_description['host'] ?? null;
    if (empty($host)) return new WP_Error('missing_host_pinecone_upsert', __('Index host not found for upsert operation.', 'gpt3-ai-content-generator'));

    $path = '/vectors/upsert';
    $body = ['vectors' => $vectors_data['vectors'] ?? $vectors_data];

    if (isset($vectors_data['namespace'])) {
        $body['namespace'] = $vectors_data['namespace'];
    }

    $response = _request_logic($strategyInstance, 'POST', $path, $body, 'https://' . $host);
    return $response;
}