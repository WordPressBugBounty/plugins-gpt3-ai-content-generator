<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/autogpt/partials/shared/category-selector.php
// Status: NEW FILE

/**
 * Partial: AutoGPT - Category Selector
 * Renders category cards for task creation.
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_autogpt_category_cards = [
    'content_creation' => [
        'label' => __('Create New Content', 'gpt3-ai-content-generator'),
        'description' => __('Generate new posts', 'gpt3-ai-content-generator'),
        'icon' => 'dashicons-edit',
    ],
    'content_enhancement' => [
        'label' => __('Update Existing Content', 'gpt3-ai-content-generator'),
        'description' => __('Rewrite titles and copy', 'gpt3-ai-content-generator'),
        'icon' => 'dashicons-update',
    ],
    'knowledge_base' => [
        'label' => __('Content Indexing', 'gpt3-ai-content-generator'),
        'description' => __('Vectorize your site for AI', 'gpt3-ai-content-generator'),
        'icon' => 'dashicons-database',
    ],
    'community_engagement' => [
        'label' => __('Engagement', 'gpt3-ai-content-generator'),
        'description' => __('Auto-reply to comments', 'gpt3-ai-content-generator'),
        'icon' => 'dashicons-admin-comments',
    ],
];
?>
<div class="aipkit_autogpt_category_selector" data-aipkit-autogpt-category-selector>
    <div class="aipkit_cw_mode_cards" role="list" aria-label="<?php esc_attr_e('Task categories', 'gpt3-ai-content-generator'); ?>">
        <?php foreach ($aipkit_autogpt_category_cards as $slug => $card) : ?>
            <button type="button" class="aipkit_cw_mode_card" data-category="<?php echo esc_attr($slug); ?>" aria-pressed="false">
                <span class="aipkit_cw_mode_icon" aria-hidden="true"><span class="dashicons <?php echo esc_attr($card['icon']); ?>"></span></span>
                <span class="aipkit_cw_mode_text">
                    <span class="aipkit_cw_mode_title"><?php echo esc_html($card['label']); ?></span>
                    <span class="aipkit_cw_mode_desc"><?php echo esc_html($card['description']); ?></span>
                </span>
            </button>
        <?php endforeach; ?>
    </div>
</div>
