<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/layout/right-panel.php
// Status: NEW FILE
if (!defined('ABSPATH')) {
    exit;
}
// Variables from parent: $initial_active_provider
?>
<div class="aipkit_ai_training_right_column">
    <div id="aipkit_vector_provider_management_blocks_container">
        <div class="aipkit_vector_provider_management_block" id="aipkit_vector_openai_management_ui" data-provider="openai" style="display: <?php echo $initial_active_provider === 'openai' ? 'block' : 'none'; ?>;">
            <?php include dirname(__DIR__) . '/provider-ui/vector-store-openai.php'; ?>
        </div>
        <div class="aipkit_vector_provider_management_block" id="aipkit_vector_pinecone_management_ui" data-provider="pinecone" style="display: <?php echo $initial_active_provider === 'pinecone' ? 'block' : 'none'; ?>;">
            <?php include dirname(__DIR__) . '/provider-ui/vector-store-pinecone.php'; ?>
        </div>
        <div class="aipkit_vector_provider_management_block" id="aipkit_vector_qdrant_management_ui" data-provider="qdrant" style="display: <?php echo $initial_active_provider === 'qdrant' ? 'block' : 'none'; ?>;">
            <?php include dirname(__DIR__) . '/provider-ui/vector-store-qdrant.php'; ?>
        </div>
    </div>
</div>