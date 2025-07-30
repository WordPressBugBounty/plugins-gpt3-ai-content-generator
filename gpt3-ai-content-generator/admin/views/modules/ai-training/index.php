<?php
/**
 * AIPKit AI Training Module (Knowledge Base) - Main View
 * REVISED: The main view now loads a single content area which handles switching
 * between the new knowledge base card view and the detail view.
 */

if (!defined('ABSPATH')) {
    exit;
}

// All necessary variables will be defined within the included partials.
?>
<div class="aipkit_container aipkit_ai_training_container" id="aipkit_ai_training_module_container">
    <!-- The header and tab structure remains the same -->
    <div class="aipkit_container-header">
        <div class="aipkit_container-title"><?php esc_html_e('AI Training', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_container-actions">
            <button id="aipkit_toggle_add_content_form_btn" class="aipkit_btn aipkit_btn-primary">
                <?php esc_html_e('Add Content', 'gpt3-ai-content-generator'); ?>
            </button>
            <button id="aipkit_resync_all_providers_btn" class="aipkit_btn aipkit_btn-secondary" title="<?php esc_attr_e('Sync and fetch all indexes from OpenAI, Pinecone, and Qdrant providers', 'gpt3-ai-content-generator'); ?>">
                <span class="aipkit_spinner" style="display:none;"></span>
                <?php esc_html_e('Sync', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>
    <div class="aipkit_tabs aipkit_main_tabs">
        <div class="aipkit_tab aipkit_active" data-tab="knowledge-base-tab"><?php esc_html_e('Knowledge Base', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_tab" data-tab="indexing-settings-tab"><?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?></div>
    </div>
    <div class="aipkit_tab_content_container">
        <!-- The "Knowledge Base" tab now loads the main vector-store partial which controls the new UI -->
        <div class="aipkit_tab-content aipkit_active" id="knowledge-base-tab-content">
            <?php include __DIR__ . '/partials/vector-store.php'; ?>
        </div>
        <div class="aipkit_tab-content" id="indexing-settings-tab-content">
            <?php include __DIR__ . '/partials/settings.php'; ?>
        </div>
    </div>
</div>