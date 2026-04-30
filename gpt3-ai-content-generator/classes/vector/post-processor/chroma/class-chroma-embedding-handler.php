<?php

namespace WPAICG\Vector\PostProcessor\Chroma;

use WPAICG\Core\AIPKit_AI_Caller;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles embedding generation for Chroma post processing.
 */
class ChromaEmbeddingHandler
{
    private $ai_caller;

    public function __construct()
    {
        if (!class_exists(\WPAICG\Core\AIPKit_AI_Caller::class)) {
            $ai_caller_path = WPAICG_PLUGIN_DIR . 'classes/core/class-aipkit_ai_caller.php';
            if (file_exists($ai_caller_path)) {
                require_once $ai_caller_path;
            }
        }
        if (class_exists(\WPAICG\Core\AIPKit_AI_Caller::class)) {
            $this->ai_caller = new AIPKit_AI_Caller();
        }
    }

    public function generate_embedding(string $content_string, string $embedding_provider, string $embedding_model): array|WP_Error
    {
        if (!$this->ai_caller) {
            return new WP_Error('ai_caller_missing_chroma_embed', 'AI Caller component is not available for Chroma embeddings.');
        }

        $embedding_result = $this->ai_caller->generate_embeddings($embedding_provider, $content_string, ['model' => $embedding_model]);
        if (is_wp_error($embedding_result) || empty($embedding_result['embeddings'][0])) {
            return is_wp_error($embedding_result) ? $embedding_result : new WP_Error('embedding_failed_chroma_embed', 'No embeddings returned for Chroma.');
        }

        return $embedding_result;
    }
}
