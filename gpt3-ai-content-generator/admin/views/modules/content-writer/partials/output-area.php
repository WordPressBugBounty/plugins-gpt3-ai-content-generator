<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/content-writer/partials/output-area.php
// Status: MODIFIED
// Redesigned with Choice Overload reduction and Chunking principles:
// - Progressive disclosure of meta fields
// - Visual chunking with clear boundaries
// - Prioritized action hierarchy
// - Streamlined, focused interface

/**
 * Partial: Content Writer Output Area
 * Contains the title display, main action buttons, and the content output display.
 * The primary Generate/Create button has been moved to the new Action Bar in index.php.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_cw_single_output_wrapper" class="aipkit_cw_output_wrapper" style="display: none;">
    
    <!-- Primary Content Chunk: Article Preview -->
    <div class="aipkit_cw_output_chunk aipkit_cw_output_chunk--primary">
        <div class="aipkit_cw_chunk_header">
            <div class="aipkit_cw_chunk_title_row">
                <span class="aipkit_cw_chunk_icon dashicons dashicons-media-document" aria-hidden="true"></span>
                <span class="aipkit_cw_chunk_label"><?php esc_html_e('Article Preview', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_cw_chunk_tools">
                <span id="aipkit_cw_article_counter" class="aipkit_cw_chunk_counter" aria-live="polite" aria-atomic="true" hidden></span>
                <!-- Inline actions for quick access (Copy/Clear) - shown after generation -->
                <div class="aipkit_cw_chunk_actions aipkit_content_writer_output_actions" style="display: none;">
                    <button type="button" id="aipkit_content_writer_copy_btn" class="aipkit_cw_icon_btn" disabled title="<?php esc_attr_e('Copy to clipboard', 'gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
                        <span class="screen-reader-text"><?php esc_html_e('Copy', 'gpt3-ai-content-generator'); ?></span>
                    </button>
                    <button
                        type="button"
                        id="aipkit_cw_expand_preview_btn"
                        class="aipkit_cw_icon_btn"
                        disabled
                        title="<?php esc_attr_e('Expand preview', 'gpt3-ai-content-generator'); ?>"
                        aria-pressed="false"
                        data-label-expand="<?php echo esc_attr__('Expand preview', 'gpt3-ai-content-generator'); ?>"
                        data-label-restore="<?php echo esc_attr__('Restore preview', 'gpt3-ai-content-generator'); ?>"
                    >
                        <span class="dashicons dashicons-fullscreen-alt" aria-hidden="true"></span>
                        <span class="screen-reader-text aipkit_cw_expand_label"><?php esc_html_e('Expand preview', 'gpt3-ai-content-generator'); ?></span>
                    </button>
                    <button type="button" id="aipkit_content_writer_clear_btn" class="aipkit_cw_icon_btn aipkit_cw_icon_btn--danger" disabled title="<?php esc_attr_e('Clear output', 'gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                        <span class="screen-reader-text"><?php esc_html_e('Clear', 'gpt3-ai-content-generator'); ?></span>
                    </button>
                </div>
            </div>
        </div>
        
        <div id="aipkit_content_writer_output_display" class="aipkit_cw_output_canvas">
            <!-- Title Display Area -->
            <h2 id="aipkit_cw_generated_title_display" class="aipkit_cw_output_title" style="display: none;"></h2>

            <!-- Image Preview (Featured + Inline Thumbnails) -->
            <div id="aipkit_cw_image_preview" class="aipkit_cw_image_preview" hidden>
                <div id="aipkit_cw_featured_image" class="aipkit_cw_featured_image" hidden>
                    <div class="aipkit_cw_image_frame">
                        <img id="aipkit_cw_featured_image_img" alt="<?php esc_attr_e('Featured image preview', 'gpt3-ai-content-generator'); ?>" loading="lazy" decoding="async">
                    </div>
                    <span class="aipkit_cw_image_caption"><?php esc_html_e('Featured image', 'gpt3-ai-content-generator'); ?></span>
                </div>
                <button
                    type="button"
                    id="aipkit_cw_inline_images_toggle"
                    class="aipkit_cw_inline_images_toggle"
                    aria-expanded="false"
                    aria-controls="aipkit_cw_inline_images_grid"
                    hidden
                >
                    <span class="dashicons dashicons-format-gallery" aria-hidden="true"></span>
                    <span class="aipkit_cw_inline_images_label"><?php esc_html_e('Images', 'gpt3-ai-content-generator'); ?></span>
                    <span id="aipkit_cw_inline_images_count" class="aipkit_cw_inline_images_count">0</span>
                </button>
                <div id="aipkit_cw_inline_images_grid" class="aipkit_cw_inline_images_grid" hidden></div>
            </div>
            
            <!-- Content Area where the article body will be streamed -->
            <div id="aipkit_cw_generated_content_area" class="aipkit_cw_output_body">
            </div>
        </div>
    </div>

    <!-- Secondary Content Chunk: SEO & Metadata -->
    <div id="aipkit_cw_meta_chunk" class="aipkit_cw_output_chunk aipkit_cw_output_chunk--secondary" style="display: none;">
        <div class="aipkit_cw_chunk_header aipkit_cw_chunk_header--static">
            <div class="aipkit_cw_chunk_title_row">
                <span class="aipkit_cw_chunk_icon dashicons dashicons-tag" aria-hidden="true"></span>
                <span class="aipkit_cw_chunk_label"><?php esc_html_e('SEO & Metadata', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>
        
        <div id="aipkit_cw_meta_content" class="aipkit_cw_chunk_body">
            <!-- Excerpt Field -->
            <div id="aipkit_cw_excerpt_output_wrapper" class="aipkit_cw_meta_field" style="display: none;">
                <label class="aipkit_cw_meta_label" for="aipkit_cw_generated_excerpt">
                    <span class="dashicons dashicons-editor-quote aipkit_cw_meta_icon" aria-hidden="true"></span>
                    <?php esc_html_e('Excerpt', 'gpt3-ai-content-generator'); ?>
                </label>
                <textarea id="aipkit_cw_generated_excerpt" name="generated_excerpt" class="aipkit_cw_meta_input aipkit_autosave_trigger aipkit_cw_generated_output_field" rows="2" placeholder="<?php esc_attr_e('Short summary for previews...', 'gpt3-ai-content-generator'); ?>"></textarea>
            </div>

            <!-- Tags Field -->
            <div id="aipkit_cw_tags_output_wrapper" class="aipkit_cw_meta_field" style="display: none;">
                <label class="aipkit_cw_meta_label" for="aipkit_cw_generated_tags">
                    <span class="dashicons dashicons-tag aipkit_cw_meta_icon" aria-hidden="true"></span>
                    <?php esc_html_e('Tags', 'gpt3-ai-content-generator'); ?>
                </label>
                <textarea id="aipkit_cw_generated_tags" name="generated_tags" class="aipkit_cw_meta_input aipkit_autosave_trigger aipkit_cw_generated_output_field" rows="1" placeholder="<?php esc_attr_e('Comma-separated tags...', 'gpt3-ai-content-generator'); ?>"></textarea>
            </div>

            <!-- Focus Keyword Field -->
            <div id="aipkit_cw_focus_keyword_output_wrapper" class="aipkit_cw_meta_field" style="display: none;">
                <label class="aipkit_cw_meta_label" for="aipkit_cw_generated_focus_keyword">
                    <span class="dashicons dashicons-flag aipkit_cw_meta_icon" aria-hidden="true"></span>
                    <?php esc_html_e('Focus Keyword', 'gpt3-ai-content-generator'); ?>
                </label>
                <input type="text" id="aipkit_cw_generated_focus_keyword" name="focus_keyword" class="aipkit_cw_meta_input aipkit_autosave_trigger aipkit_cw_generated_output_field" placeholder="<?php esc_attr_e('Primary SEO keyword...', 'gpt3-ai-content-generator'); ?>">
            </div>

            <!-- Meta Description Field -->
            <div id="aipkit_cw_meta_desc_output_wrapper" class="aipkit_cw_meta_field" style="display: none;">
                <label class="aipkit_cw_meta_label" for="aipkit_cw_generated_meta_desc">
                    <span class="dashicons dashicons-search aipkit_cw_meta_icon" aria-hidden="true"></span>
                    <?php esc_html_e('Meta Description', 'gpt3-ai-content-generator'); ?>
                </label>
                <textarea id="aipkit_cw_generated_meta_desc" name="meta_description" class="aipkit_cw_meta_input aipkit_autosave_trigger aipkit_cw_generated_output_field" rows="2" placeholder="<?php esc_attr_e('SEO description for search results...', 'gpt3-ai-content-generator'); ?>"></textarea>
                <span class="aipkit_cw_meta_char_count" aria-live="polite"></span>
            </div>
        </div>
    </div>

    <!-- Primary Action: Save as Post (elevated, single clear action) -->
    <div class="aipkit_cw_output_primary_action" style="display: none;">
        <button type="button" id="aipkit_cw_save_as_post_btn" class="aipkit_btn aipkit_btn-primary aipkit_cw_save_btn" disabled>
            <span class="dashicons dashicons-saved" aria-hidden="true"></span>
            <span class="aipkit_btn-text"><?php esc_html_e('Save as Post', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_spinner" style="display:none;"></span>
        </button>
        <div id="aipkit_cw_save_post_status" class="aipkit_cw_save_status" aria-live="polite"></div>
    </div>
    
</div>
