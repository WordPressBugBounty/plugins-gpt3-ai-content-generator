<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store.php
// Status: MODIFIED

/**
 * Partial: AI Training - Vector Store Tab Content (Revised for Global Form & WordPress Content Source)
 * This file is now the main orchestrator for the AI Training UI, including sub-partials.
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\AIPKit_Providers;
use WPAICG\Vector\AIPKit_Vector_Store_Registry; // For fetching initial stores

// These variables are used by the included partials.
$openai_data = AIPKit_Providers::get_provider_data('OpenAI');
$openai_api_key_is_set = !empty($openai_data['api_key']);

$pinecone_data = AIPKit_Providers::get_provider_data('Pinecone');
$pinecone_api_key_is_set = !empty($pinecone_data['api_key']);

$qdrant_data = AIPKit_Providers::get_provider_data('Qdrant');
$qdrant_url_is_set = !empty($qdrant_data['url']);
$qdrant_api_key_is_set = !empty($qdrant_data['api_key']);

$initial_active_provider = 'openai';

$initial_openai_stores = [];
if (class_exists(AIPKit_Vector_Store_Registry::class)) {
    $initial_openai_stores = AIPKit_Vector_Store_Registry::get_registered_stores_by_provider('OpenAI');
}

$openai_embedding_models_list = AIPKit_Providers::get_openai_embedding_models();
$google_embedding_models_list = AIPKit_Providers::get_google_embedding_models();

$post_types_args = ['public' => true, '_builtin' => false];
$custom_post_types = get_post_types($post_types_args, 'objects');
$default_post_types = ['post' => get_post_type_object('post'), 'page' => get_post_type_object('page')];
$all_selectable_post_types = array_merge($default_post_types, $custom_post_types);
$all_selectable_post_types = array_filter($all_selectable_post_types, function ($pt_obj) {
    return $pt_obj->name !== 'attachment';
});

?>
<div class="aipkit_container-body" id="aipkit_vector_store_management_area">
    <?php include __DIR__ . '/vector-store/nonce-fields.php'; ?>
    <?php include __DIR__ . '/vector-store/data-attributes.php'; ?>
    <?php include __DIR__ . '/vector-store/layout/layout.php'; ?>
</div>