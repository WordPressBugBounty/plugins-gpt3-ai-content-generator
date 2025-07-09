<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/global-form/global-form-wrapper.php
// Status: NEW FILE
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="aipkit_ai_training_section_wrapper aipkit_global_add_data_form_wrapper">
    <?php include __DIR__ . '/form-controls-row.php'; ?>
    <?php include __DIR__ . '/inline-create-forms/openai.php'; ?>
    <?php include __DIR__ . '/inline-create-forms/pinecone.php'; ?>
    <?php include __DIR__ . '/inline-create-forms/qdrant.php'; ?>
</div>