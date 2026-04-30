<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/upload-file-for-vector-store.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function upload_file_for_vector_store_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $file_path, string $original_filename, string $purpose = 'user_data'): array|WP_Error
{
    return new WP_Error('chroma_file_upload_not_applicable', __('Direct file upload is not supported by the Chroma vector strategy. Files must be parsed, chunked, embedded, and upserted as records.', 'gpt3-ai-content-generator'));
}
