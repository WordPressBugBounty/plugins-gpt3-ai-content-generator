<?php
// Redesigned with Choice Overload reduction and Chunking principles:
// - Progressive disclosure of meta fields
// - Visual chunking with clear boundaries
// - Prioritized action hierarchy
// - Streamlined, focused interface

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_cw_single_output_wrapper" class="aipkit_cw_output_wrapper" style="display: none;">
    <div class="aipkit_cw_output_workspace">
        <div class="aipkit_cw_output_main">
            <!-- Primary Content Chunk: Article Preview -->
            <div class="aipkit_cw_output_chunk aipkit_cw_output_chunk--primary">
                <div class="aipkit_cw_chunk_header">
                    <div class="aipkit_cw_chunk_title_row">
                        <span class="aipkit_cw_chunk_icon dashicons dashicons-media-document" aria-hidden="true"></span>
                        <span class="aipkit_cw_chunk_label"><?php esc_html_e('Article Preview', 'gpt3-ai-content-generator'); ?></span>
                        <span id="aipkit_cw_article_counter" class="aipkit_cw_chunk_counter" aria-live="polite" aria-atomic="true" hidden></span>
                    </div>
                    <div class="aipkit_cw_chunk_header_actions">
                        <div id="aipkit_cw_single_run_actions" class="aipkit_cw_single_run_actions"></div>
                    </div>
                </div>

                <div id="aipkit_content_writer_output_display" class="aipkit_cw_output_canvas">
                    <!-- Title Display Area -->
                    <h2 id="aipkit_cw_generated_title_display" class="aipkit_cw_output_title" style="display: none;"></h2>

                    <!-- Inline Image Preview -->
                    <div id="aipkit_cw_image_preview" class="aipkit_cw_image_preview" hidden>
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
        </div>
        <aside class="aipkit_cw_output_sidebar">
            <div id="aipkit_cw_output_media_panel" class="aipkit_cw_output_sidebar_card aipkit_cw_output_sidebar_card--media" hidden>
                <div class="aipkit_cw_output_sidebar_title"><?php esc_html_e('Featured Image', 'gpt3-ai-content-generator'); ?></div>
                <div id="aipkit_cw_featured_image" class="aipkit_cw_featured_image" hidden>
                    <div class="aipkit_cw_image_frame">
                        <img id="aipkit_cw_featured_image_img" alt="<?php esc_attr_e('Featured image preview', 'gpt3-ai-content-generator'); ?>" loading="lazy" decoding="async">
                    </div>
                </div>
            </div>

            <div id="aipkit_cw_meta_chunk" class="aipkit_cw_meta_cards" style="display: none;">
                <!-- Excerpt Field -->
                <div id="aipkit_cw_excerpt_output_wrapper" class="aipkit_cw_meta_field aipkit_cw_output_sidebar_card" style="display: none;">
                    <label class="aipkit_cw_meta_label" for="aipkit_cw_generated_excerpt">
                        <span class="dashicons dashicons-editor-quote aipkit_cw_meta_icon" aria-hidden="true"></span>
                        <?php esc_html_e('Excerpt', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <textarea id="aipkit_cw_generated_excerpt" name="generated_excerpt" class="aipkit_cw_meta_input aipkit_autosave_trigger aipkit_cw_generated_output_field" rows="2" placeholder="<?php esc_attr_e('Short summary for previews...', 'gpt3-ai-content-generator'); ?>"></textarea>
                </div>

                <!-- Tags Field -->
                <div id="aipkit_cw_tags_output_wrapper" class="aipkit_cw_meta_field aipkit_cw_output_sidebar_card" style="display: none;">
                    <label class="aipkit_cw_meta_label" for="aipkit_cw_tags_chip_input">
                        <span class="dashicons dashicons-tag aipkit_cw_meta_icon" aria-hidden="true"></span>
                        <?php esc_html_e('Tags', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div class="aipkit_cw_tags_editor" data-aipkit-cw-tags-editor>
                        <div id="aipkit_cw_tags_chip_list" class="aipkit_cw_tags_chip_list" aria-live="polite"></div>
                        <input
                            type="text"
                            id="aipkit_cw_tags_chip_input"
                            class="aipkit_cw_tags_chip_input"
                            placeholder="<?php esc_attr_e('Add a tag...', 'gpt3-ai-content-generator'); ?>"
                            autocomplete="off"
                        >
                    </div>
                    <textarea id="aipkit_cw_generated_tags" name="generated_tags" class="aipkit_cw_meta_input aipkit_autosave_trigger aipkit_cw_generated_output_field aipkit_cw_tags_source_input" rows="1" placeholder="<?php esc_attr_e('Comma-separated tags...', 'gpt3-ai-content-generator'); ?>" aria-hidden="true" tabindex="-1"></textarea>
                </div>

                <!-- Focus Keyword Field -->
                <div id="aipkit_cw_focus_keyword_output_wrapper" class="aipkit_cw_meta_field aipkit_cw_output_sidebar_card" style="display: none;">
                    <label class="aipkit_cw_meta_label" for="aipkit_cw_generated_focus_keyword">
                        <span class="dashicons dashicons-flag aipkit_cw_meta_icon" aria-hidden="true"></span>
                        <?php esc_html_e('Focus Keyword', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="text" id="aipkit_cw_generated_focus_keyword" name="focus_keyword" class="aipkit_cw_meta_input aipkit_autosave_trigger aipkit_cw_generated_output_field" placeholder="<?php esc_attr_e('Primary SEO keyword...', 'gpt3-ai-content-generator'); ?>">
                </div>

                <!-- Meta Description Field -->
                <div id="aipkit_cw_meta_desc_output_wrapper" class="aipkit_cw_meta_field aipkit_cw_output_sidebar_card" style="display: none;">
                    <label class="aipkit_cw_meta_label" for="aipkit_cw_generated_meta_desc">
                        <span class="dashicons dashicons-search aipkit_cw_meta_icon" aria-hidden="true"></span>
                        <?php esc_html_e('Meta Description', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <textarea id="aipkit_cw_generated_meta_desc" name="meta_description" class="aipkit_cw_meta_input aipkit_autosave_trigger aipkit_cw_generated_output_field" rows="2" placeholder="<?php esc_attr_e('SEO description for search results...', 'gpt3-ai-content-generator'); ?>"></textarea>
                    <span class="aipkit_cw_meta_char_count" aria-live="polite"></span>
                </div>
            </div>

            <div class="aipkit_content_writer_output_actions aipkit_cw_output_sidebar_card aipkit_cw_output_sidebar_card--actions" style="display: none;">
                <div class="aipkit_cw_output_sidebar_title"><?php esc_html_e('Actions', 'gpt3-ai-content-generator'); ?></div>
                <div class="aipkit_cw_output_actions_row">
                    <button type="button" id="aipkit_cw_save_as_post_btn" data-aipkit-cw-save-post-btn class="button button-primary aipkit_btn aipkit_btn-primary aipkit_cw_output_action_btn" disabled>
                        <span class="aipkit_btn-text"><?php esc_html_e('Save', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_spinner" style="display:none;"></span>
                    </button>
                    <button type="button" id="aipkit_content_writer_copy_btn" class="button aipkit_btn aipkit_btn-secondary aipkit_cw_output_action_btn" disabled>
                        <span class="aipkit_btn-text"><?php esc_html_e('Copy', 'gpt3-ai-content-generator'); ?></span>
                    </button>
                    <button type="button" id="aipkit_content_writer_clear_btn" class="button aipkit_btn aipkit_btn-danger aipkit_cw_output_action_btn" disabled>
                        <span class="aipkit_btn-text"><?php esc_html_e('Clear', 'gpt3-ai-content-generator'); ?></span>
                    </button>
                </div>
                <div id="aipkit_cw_save_post_status" class="aipkit_cw_save_status" aria-live="polite"></div>
            </div>
        </aside>
    </div>
</div>
