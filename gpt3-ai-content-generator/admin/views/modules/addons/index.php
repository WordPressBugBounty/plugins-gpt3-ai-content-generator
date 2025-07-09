<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/addons/index.php
// Status: MODIFIED
// I have added 'semantic_search' to the list of available addons.

/**
 * AIPKit Addons Module - Admin View
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
use WPAICG\aipkit_dashboard;

$addon_status = aipkit_dashboard::get_addon_status();
$is_pro = aipkit_dashboard::is_pro_plan();
$upgrade_url = admin_url('admin.php?page=wpaicg-pricing');

$addons = [
    [
        'key' => 'pdf_download', 'title' => __('PDF Download', 'gpt3-ai-content-generator'),
        'description' => __('Allow users to download chat transcripts and AI Form results as PDF files.', 'gpt3-ai-content-generator'),
        'pro' => true, 'category' => 'chat'
    ],
    [
        'key' => 'conversation_starters', 'title' => __('Conversation Starters', 'gpt3-ai-content-generator'),
        'description' => __('Display predefined prompts to help users start conversations with chatbots.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'chat'
    ],
    [
        'key' => 'token_management', 'title' => __('Token Management', 'gpt3-ai-content-generator'),
        'description' => __('Set token usage limits per user/role for chatbots and other modules.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'core'
    ],
    [
        'key' => 'ip_anonymization', 'title' => __('IP Anonymization', 'gpt3-ai-content-generator'),
        'description' => __('Anonymize user IP addresses in chat logs for privacy compliance.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'privacy'
    ],
    [
        'key' => 'consent_compliance', 'title' => __('Consent Compliance', 'gpt3-ai-content-generator'),
        'description' => __('Display a consent box before users interact with chatbots.', 'gpt3-ai-content-generator'),
        'pro' => true, 'category' => 'privacy'
    ],
    [
        'key' => 'openai_moderation', 'title' => __('OpenAI Moderation', 'gpt3-ai-content-generator'),
        'description' => __('Filter harmful content in chat messages using OpenAI\'s Moderation API.', 'gpt3-ai-content-generator'),
        'pro' => true, 'category' => 'security'
    ],
    [
        'key' => 'ai_post_enhancer', 'title' => __('Content Assistant', 'gpt3-ai-content-generator'),
        'description' => __('Generate or improve WooCommerce product titles, short descriptions, meta tags, and excerpts. Also available inside the Classic and Block Editor toolbars. Supports all content types.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'content'
    ],
    [
        'key' => 'deepseek', 'title' => __('DeepSeek Integration', 'gpt3-ai-content-generator'),
        'description' => __('Enable DeepSeek models for text generation in various modules.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'core'
    ],
    [
        'key' => 'voice_playback', 'title' => __('Voice Playback (TTS)', 'gpt3-ai-content-generator'),
        'description' => __('Enable Text-to-Speech for chatbot responses using Google, OpenAI, or ElevenLabs voices.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'chat'
    ],
    [
        'key' => 'vector_databases', 'title' => __('Vector Database Integrations', 'gpt3-ai-content-generator'),
        'description' => __('Connect to Pinecone and Qdrant vector databases for advanced AI Training and retrieval.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'training'
    ],
    [
        'key' => 'file_upload', 'title' => __('File Upload (OpenAI)', 'gpt3-ai-content-generator'),
        'description' => __('Allow users to upload files (PDF, TXT) for context in OpenAI chatbots and manage files in AI Training.', 'gpt3-ai-content-generator'),
        'pro' => true, 'category' => 'chat'
    ],
    [
        'key' => 'triggers', 'title' => __('Chatbot Triggers', 'gpt3-ai-content-generator'),
        'description' => __('Automate chatbot interactions with event-based triggers, conditions, and actions.', 'gpt3-ai-content-generator'),
        'pro' => true, 'category' => 'chat'
    ],
    [
        'key' => 'stock_images', 'title' => __('Stock Images', 'gpt3-ai-content-generator'),
        'description' => __('Search and use images from stock providers like Pexels directly within the Content Writer.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'content'
    ],
    [
        'key' => 'replicate', 'title' => __('Replicate Integration', 'gpt3-ai-content-generator'),
        'description' => __('Enable image generation using models hosted on Replicate.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'core'
    ],
    [
        'key' => 'semantic_search', 'title' => __('Semantic Search', 'gpt3-ai-content-generator'),
        'description' => __('Enable a frontend shortcode for users to perform semantic search on your custom knowledge base.', 'gpt3-ai-content-generator'),
        'pro' => false, 'category' => 'content'
    ],
];

// Sort addons alphabetically by title
usort($addons, function ($a, $b) {
    return strcmp($a['title'], $b['title']);
});

// Define categories and their order
$categories = [
    'core'    => __('Core', 'gpt3-ai-content-generator'),
    'chat'    => __('Chat', 'gpt3-ai-content-generator'),
    'content' => __('Content', 'gpt3-ai-content-generator'),
    'training' => __('AI Training & Data', 'gpt3-ai-content-generator'),
    'security' => __('Security & Moderation', 'gpt3-ai-content-generator'),
    'privacy' => __('Privacy & Compliance', 'gpt3-ai-content-generator'),
];


$category_filters = array_merge(['all' => __('All', 'gpt3-ai-content-generator')], $categories);
?>
<div class="aipkit_container aipkit_addons_container" id="aipkit_addons_container"> <?php // Add ID for JS targeting?>
    <div class="aipkit_container-header">
        <div class="aipkit_container-title"><?php esc_html_e('Add-ons', 'gpt3-ai-content-generator'); ?></div>
    </div>
    <div class="aipkit_container-body">
        <!-- Filter Links -->
        <div class="aipkit_addons_filters">
            <div class="aipkit_filter_group" data-filter-group="category">
                <span class="aipkit_filter_label"><?php esc_html_e('Filter by Category:', 'gpt3-ai-content-generator'); ?></span>
                <?php
                $first_filter = true;
foreach ($category_filters as $slug => $name) :
    if (!$first_filter) {
        echo '<span class="aipkit_filter_separator">|</span>';
    }
    $active_class = ($slug === 'all') ? 'aipkit_active' : ''; // 'All' is active by default
    ?>
                    <a
                        href="#"
                        class="aipkit_filter_link <?php echo esc_attr($active_class); ?>"
                        data-filter-value="<?php echo esc_attr($slug); ?>"
                        role="button"
                        tabindex="0"
                        aria-pressed="<?php echo ($slug === 'all') ? 'true' : 'false'; ?>"
                    >
                        <?php echo esc_html($name); ?>
                    </a>
                <?php
        $first_filter = false;
endforeach; ?>
            </div>
        </div>

        <div class="aipkit_stats-grid" id="aipkit_addons_grid">
            <?php foreach ($addons as $addon) :
                $key = $addon['key'];
                $isActive = isset($addon_status[$key]) ? $addon_status[$key] : false;
                $isProFeature = $addon['pro'];
                $canActivate = (!$isProFeature || ($isProFeature && $is_pro));
                $categories_str = esc_attr($addon['category']); // Single category for now
                ?>
            <div
                class="aipkit_stat-card aipkit_addon_card"
                data-status="<?php echo $isActive ? 'active' : 'inactive'; ?>"
                data-categories="<?php echo esc_attr($categories_str); ?>"
            >
                <div class="aipkit_stat-title-wrapper">
                    <h3 class="aipkit_stat-title"><?php echo esc_html($addon['title']); ?></h3>
                    <?php if ($isProFeature) : ?>
                        <span class="aipkit_status-tag" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;"><?php esc_html_e('Pro', 'gpt3-ai-content-generator'); ?></span>
                    <?php endif; ?>
                </div>
                <p class="aipkit_stat-description"><?php echo esc_html($addon['description']); ?></p>
                <?php if (!empty($addon['category'])): ?>
                     <p class="aipkit_addon_categories">
                        <strong><?php esc_html_e('Category:', 'gpt3-ai-content-generator'); ?></strong>
                         <?php echo esc_html($categories[$addon['category']] ?? ucfirst($addon['category'])); ?>
                    </p>
                <?php endif; ?>

                <div style="margin-top: auto;"> <?php // Push button to bottom?>
                    <?php if ($canActivate) : ?>
                        <button
                            type="button"
                            class="aipkit_btn <?php echo $isActive ? 'aipkit_btn-secondary' : 'aipkit_btn-primary'; ?> aipkit_addon_toggle_btn"
                            data-addon-key="<?php echo esc_attr($key); ?>"
                            data-active="<?php echo $isActive ? '1' : '0'; ?>"
                        >
                            <span class="aipkit_btn-text"><?php echo $isActive ? esc_html__('Deactivate', 'gpt3-ai-content-generator') : esc_html__('Activate', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_spinner" style="display:none;"></span>
                        </button>
                    <?php else : // Pro feature but not Pro plan?>
                        <a href="<?php echo esc_url($upgrade_url); ?>" class="aipkit_btn aipkit_btn-primary" target="_blank" rel="noopener noreferrer">
                            <?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>
                        </a>
                    <?php endif; ?>
                     <div class="aipkit_addon_status_msg"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div id="aipkit_no_addons_message" style="display:none; text-align:center; padding:20px; color: var(--aipkit_text-secondary);">
            <?php esc_html_e('No add-ons match the current filter.', 'gpt3-ai-content-generator'); ?>
        </div>
    </div><!-- /.aipkit_container-body -->
</div><!-- /.aipkit_container -->