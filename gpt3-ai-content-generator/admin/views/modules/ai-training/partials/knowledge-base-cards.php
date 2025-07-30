<?php
/**
 * Partial: AI Training - Knowledge Base Cards View
 * Displays all available knowledge bases (vector stores) in a card grid.
 * Stats like document count and last update are loaded asynchronously via JavaScript.
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Vector\AIPKit_Vector_Store_Registry;

// --- NEW: Get general settings for filtering user uploads ---
$training_general_settings = get_option('aipkit_training_general_settings', ['hide_user_uploads' => true]);
$hide_user_uploads = $training_general_settings['hide_user_uploads'] ?? true;
// --- END NEW ---

$all_stores = [];
if (class_exists(AIPKit_Vector_Store_Registry::class)) {
    // OpenAI
    $openai_stores = AIPKit_Vector_Store_Registry::get_registered_stores_by_provider('OpenAI');
    if (is_array($openai_stores)) {
        foreach ($openai_stores as $store) {
            $store_name = $store['name'] ?? $store['id'];
            $is_user_upload = strpos($store_name, 'chat_file_') === 0;
            // --- NEW: Conditionally skip user uploads ---
            if ($hide_user_uploads && $is_user_upload) {
                continue;
            }
            // --- END NEW ---
            $all_stores[] = [
                'name' => $store_name, 
                'id' => $store['id'], 
                'provider' => 'OpenAI',
                'is_user_upload' => $is_user_upload,
                'expires_at' => $store['expires_at'] ?? null
            ];
        }
    }
    // Pinecone
    $pinecone_indexes = AIPKit_Vector_Store_Registry::get_registered_stores_by_provider('Pinecone');
    if (is_array($pinecone_indexes)) {
        foreach ($pinecone_indexes as $index) {
            $all_stores[] = [
                'name' => $index['name'] ?? $index['id'], 
                'id' => $index['name'] ?? $index['id'], 
                'provider' => 'Pinecone',
                'is_user_upload' => false
            ];
        }
    }
    // Qdrant
    $qdrant_collections = AIPKit_Vector_Store_Registry::get_registered_stores_by_provider('Qdrant');
    if (is_array($qdrant_collections)) {
        foreach ($qdrant_collections as $collection) {
            $all_stores[] = [
                'name' => $collection['name'] ?? $collection['id'], 
                'id' => $collection['name'] ?? $collection['id'], 
                'provider' => 'Qdrant',
                'is_user_upload' => false
            ];
        }
    }
}
// Sort by name, case-insensitively
usort($all_stores, function ($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

?>

<?php if (empty($all_stores)): ?>
    <?php include __DIR__ . '/vector-store/_empty-state.php'; ?>
<?php else: ?>
    <div class="aipkit_kb_card_grid" role="grid" aria-label="<?php esc_attr_e('Knowledge Base Cards', 'gpt3-ai-content-generator'); ?>">
        <?php foreach ($all_stores as $store):
            $provider_lower = strtolower($store['provider']);
        ?>
            <article class="aipkit_kb_card"
                     data-provider="<?php echo esc_attr($store['provider']); ?>"
                     data-id="<?php echo esc_attr($store['id']); ?>"
                     data-name="<?php echo esc_attr($store['name']); ?>"
                     tabindex="0"
                     role="button"
                     aria-label="<?php echo esc_attr(sprintf(__('View details for %s knowledge base', 'gpt3-ai-content-generator'), $store['name'])); ?>">

                <div class="aipkit_kb_card_body">
                    <header class="aipkit_kb_card_body_header">
                        <h3 class="aipkit_kb_card_title"><?php echo esc_html($store['name']); ?></h3>
                        <div class="aipkit_kb_card_header_actions">
                            <span class="aipkit_kb_card_provider aipkit_provider_tag_<?php echo esc_attr($provider_lower); ?>"><?php echo esc_html($store['provider']); ?></span>
                            <?php if (!empty($store['is_user_upload'])): ?>
                                <span class="aipkit_kb_card_user_upload_badge" title="<?php esc_attr_e('User uploaded knowledge base from chat interface', 'gpt3-ai-content-generator'); ?>">
                                    <span class="dashicons dashicons-admin-users"></span>
                                    <?php esc_html_e('User', 'gpt3-ai-content-generator'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </header>

                    <section class="aipkit_kb_card_stats" aria-label="<?php esc_attr_e('Knowledge base statistics', 'gpt3-ai-content-generator'); ?>">
                        <div class="aipkit_kb_card_stat">
                            <span class="aipkit_kb_card_stat_label"><?php esc_html_e('Documents', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_kb_card_stat_value" data-stat="doc-count" aria-live="polite">
                                <span class="aipkit_spinner" aria-label="<?php esc_attr_e('Loading document count...', 'gpt3-ai-content-generator'); ?>"></span>
                            </span>
                        </div>
                        <div class="aipkit_kb_card_stat">
                            <span class="aipkit_kb_card_stat_label">
                                <?php 
                                // Show "Expires" for user uploads, "Updated" for regular stores
                                echo !empty($store['is_user_upload']) && !empty($store['expires_at']) 
                                    ? esc_html__('Expires', 'gpt3-ai-content-generator')
                                    : esc_html__('Updated', 'gpt3-ai-content-generator'); 
                                ?>
                            </span>
                            <span class="aipkit_kb_card_stat_value" data-stat="<?php echo !empty($store['is_user_upload']) && !empty($store['expires_at']) ? 'expires' : 'last-updated'; ?>" aria-live="polite">
                                <?php if (!empty($store['is_user_upload']) && !empty($store['expires_at'])): ?>
                                    <?php 
                                    $expires_timestamp = is_numeric($store['expires_at']) ? $store['expires_at'] : strtotime($store['expires_at']);
                                    echo esc_html(date_i18n(get_option('date_format'), $expires_timestamp));
                                    ?>
                                <?php else: ?>
                                    <span class="aipkit_spinner" aria-label="<?php esc_attr_e('Loading last updated...', 'gpt3-ai-content-generator'); ?>"></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </section>
                </div>
                
                <footer class="aipkit_kb_card_footer">
                    <span><?php esc_html_e('Manage', 'gpt3-ai-content-generator'); ?></span>
                    <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                </footer>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>