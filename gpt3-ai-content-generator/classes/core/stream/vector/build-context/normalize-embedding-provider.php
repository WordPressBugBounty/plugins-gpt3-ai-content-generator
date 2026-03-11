<?php

// File: classes/core/stream/vector/build-context/normalize-embedding-provider.php
// Status: NEW FILE

namespace WPAICG\Core\Stream\Vector\BuildContext;

use WPAICG\AIPKit_Providers;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Normalizes the embedding provider key to a standard name.
 *
 * @param string $embedding_provider_key The key from settings (e.g., 'openai', 'google').
 * @return string The normalized provider name (e.g., 'OpenAI', 'Google').
 */
function normalize_embedding_provider_logic(string $embedding_provider_key): string
{
    $provider_lookup = sanitize_key((string) strtolower($embedding_provider_key));

    return AIPKit_Providers::normalize_embedding_provider_name(
        $provider_lookup,
        'stream_vector_build_context'
    );
}
