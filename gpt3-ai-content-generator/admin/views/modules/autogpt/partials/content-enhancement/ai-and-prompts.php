<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/lib/views/modules/autogpt/partials/content-enhancement/ai-and-prompts.php
// Status: MODIFIED

/**
 * Partial: Automated Task Form - Content Enhancement AI & Prompt Settings
 * This is the content pane for the "AI Settings & Prompts" step in the wizard.
 *
 * @since NEXT_VERSION
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent: $cw_providers_for_select, $cw_default_temperature, $cw_default_max_tokens

?>
<div id="aipkit_task_config_enhancement_ai_and_prompts_main" class="aipkit_task_config_section">
    <div class="aipkit_autogpt_setting_cards">
        <?php
        $aipkit_ai_card_id = 'aipkit_autogpt_ce_ai_settings_popover';
        $aipkit_ai_popover_body_path = __DIR__ . '/ai-settings.php';
        $aipkit_ai_provider_selector = '#aipkit_task_ce_ai_provider';
        $aipkit_ai_model_selector = '#aipkit_task_ce_ai_model';
        include __DIR__ . '/../shared/ai-settings-card.php';

        $content_enhancement_kb_partial = WPAICG_LIB_DIR . 'views/modules/autogpt/partials/content-enhancement/knowledge-base-settings.php';
        if (isset($is_pro) && $is_pro && file_exists($content_enhancement_kb_partial)) {
            $aipkit_kb_card_id = 'aipkit_autogpt_ce_vector_settings_popover';
            $aipkit_kb_popover_body_path = $content_enhancement_kb_partial;
            $aipkit_kb_provider_selector = '#aipkit_task_ce_vector_store_provider';
            $aipkit_kb_openai_store_selector = '#aipkit_task_ce_openai_vector_store_ids';
            $aipkit_kb_pinecone_selector = '#aipkit_task_ce_pinecone_index_name';
            $aipkit_kb_qdrant_selector = '#aipkit_task_ce_qdrant_collection_name';
            $aipkit_kb_toggle_selector = '#aipkit_task_ce_enable_vector_store';
            include __DIR__ . '/../shared/knowledge-base-card.php';
        }

        $aipkit_prompts_card_id = 'aipkit_autogpt_ce_prompts_popover';
        $aipkit_prompts_popover_body_path = __DIR__ . '/prompts-settings.php';
        include __DIR__ . '/../shared/prompts-settings-card.php';
        ?>
    </div>
</div>
