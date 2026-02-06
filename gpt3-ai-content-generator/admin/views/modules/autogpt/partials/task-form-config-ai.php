<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/autogpt/partials/task-form-config-ai.php
// Status: MODIFIED

/**
 * Partial: Automated Task Form - AI Configuration
 * Contains fields for selecting AI Provider, Model, and parameters.
 * Used for Content Writing tasks.
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent: $cw_providers_for_select, $cw_default_temperature, $cw_default_max_tokens
?>
<div id="aipkit_task_config_ai" class="aipkit_task_config_section">
    <div class="aipkit_autogpt_setting_cards">
        <?php
        $aipkit_ai_card_id = 'aipkit_autogpt_cw_ai_settings_popover';
        $aipkit_ai_popover_body_path = __DIR__ . '/content-writing/model-settings.php';
        $aipkit_ai_provider_selector = '#aipkit_task_cw_ai_provider';
        $aipkit_ai_model_selector = '#aipkit_task_cw_ai_model';
        include __DIR__ . '/shared/ai-settings-card.php';

        $aipkit_image_card_id = 'aipkit_autogpt_cw_image_settings_popover';
        $aipkit_image_popover_body_path = __DIR__ . '/content-writing/image-settings.php';
        include __DIR__ . '/shared/image-settings-card.php';

        $aipkit_kb_card_id = 'aipkit_autogpt_cw_vector_settings_popover';
        $aipkit_kb_popover_body_path = __DIR__ . '/content-writing/knowledge-base-settings.php';
        $aipkit_kb_provider_selector = '#aipkit_task_cw_vector_store_provider';
        $aipkit_kb_openai_store_selector = '#aipkit_task_cw_openai_vector_store_ids';
        $aipkit_kb_pinecone_selector = '#aipkit_task_cw_pinecone_index_name';
        $aipkit_kb_qdrant_selector = '#aipkit_task_cw_qdrant_collection_name';
        $aipkit_kb_toggle_selector = '#aipkit_task_cw_enable_vector_store';
        include __DIR__ . '/shared/knowledge-base-card.php';

        $aipkit_prompts_card_id = 'aipkit_autogpt_cw_prompts_popover';
        $aipkit_prompts_popover_body_path = __DIR__ . '/content-writing/prompts-settings.php';
        include __DIR__ . '/shared/prompts-settings-card.php';
        ?>
    </div>
</div>
