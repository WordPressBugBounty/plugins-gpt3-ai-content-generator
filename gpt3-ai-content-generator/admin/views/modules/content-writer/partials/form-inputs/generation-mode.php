<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/content-writer/partials/form-inputs/generation-mode.php
// Status: MODIFIED
/**
 * Partial: Content Writer - Generation Mode & Topic Input Panel
 * Source selector is now in the left rail (partials/source-selector.php).
 * This file contains only the mode-specific input panels.
 */

if (!defined('ABSPATH')) {
    exit;
}
// Variables from loader-vars.php: $is_pro
?>
<div class="aipkit_cw_mode_container">
    <!-- Hidden content_title input used by JS for generation -->
    <input type="hidden" id="aipkit_content_writer_title" name="content_title" value="" class="aipkit_autosave_trigger">
    
    <div class="aipkit_cw_mode_panel">
        <div class="aipkit_cw_tab_content_container">
            <!-- Manual Entry Pane (was Bulk Editor) -->
            <div class="aipkit_cw_tab_content aipkit_active" data-pane="task">
                <div class="aipkit_cw_bulk_source_panel" data-aipkit-bulk-source-panel="task">
                    <div class="aipkit_cw_bulk_editor" data-aipkit-bulk-editor>
                        <!-- Simplified bulk rows - All fields in single row, details toggle visibility -->
                        <div class="aipkit_cw_bulk_rows" data-aipkit-bulk-rows>
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <div class="aipkit_cw_bulk_row" data-aipkit-bulk-row>
                                    <span class="aipkit_cw_bulk_row_number" aria-hidden="true"><?php echo ($i + 1); ?></span>
                                    <input type="text" class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_input--topic aipkit_autosave_trigger" data-bulk-field="topic" placeholder="<?php esc_attr_e('Enter topic...', 'gpt3-ai-content-generator'); ?>" aria-label="<?php esc_attr_e('Topic', 'gpt3-ai-content-generator'); ?>">
                                    <input type="text" class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_input--keywords aipkit_autosave_trigger" data-bulk-field="keywords" placeholder="<?php esc_attr_e('Keywords (optional)', 'gpt3-ai-content-generator'); ?>" aria-label="<?php esc_attr_e('Keywords', 'gpt3-ai-content-generator'); ?>">
                                    <select class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_detail aipkit_autosave_trigger" data-bulk-field="category" aria-label="<?php esc_attr_e('Category', 'gpt3-ai-content-generator'); ?>">
                                        <option value=""><?php esc_html_e('Category', 'gpt3-ai-content-generator'); ?></option>
                                        <?php foreach ($wp_categories as $category): ?>
                                            <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_detail aipkit_autosave_trigger" data-bulk-field="author" aria-label="<?php esc_attr_e('Author', 'gpt3-ai-content-generator'); ?>">
                                        <option value=""><?php esc_html_e('Author', 'gpt3-ai-content-generator'); ?></option>
                                        <?php foreach ($users_for_author as $user): ?>
                                            <option value="<?php echo esc_attr($user->user_login); ?>" data-user-id="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_detail aipkit_autosave_trigger" data-bulk-field="type" aria-label="<?php esc_attr_e('Post Type', 'gpt3-ai-content-generator'); ?>">
                                        <option value=""><?php esc_html_e('Post', 'gpt3-ai-content-generator'); ?></option>
                                        <?php foreach ($available_post_types as $pt_slug => $pt_obj): ?>
                                            <option value="<?php echo esc_attr($pt_slug); ?>"><?php echo esc_html($pt_obj->label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="datetime-local" class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_detail aipkit_autosave_trigger" data-bulk-field="schedule" aria-label="<?php esc_attr_e('Schedule', 'gpt3-ai-content-generator'); ?>">
                                    <button type="button" class="aipkit_cw_bulk_remove_row" aria-label="<?php esc_attr_e('Remove row', 'gpt3-ai-content-generator'); ?>">
                                        <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                                    </button>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <div class="aipkit_cw_bulk_footer">
                            <button type="button" class="aipkit_cw_bulk_add_row" aria-label="<?php esc_attr_e('Add row', 'gpt3-ai-content-generator'); ?>">
                                <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                                <span class="aipkit_cw_bulk_add_text"><?php esc_html_e('Add More', 'gpt3-ai-content-generator'); ?></span>
                            </button>
                            <div class="aipkit_cw_bulk_footer_actions">
                                <button
                                    type="button"
                                    class="aipkit_cw_bulk_toggle_details"
                                    aria-expanded="false"
                                    title="<?php esc_attr_e('Show category, author, type, schedule', 'gpt3-ai-content-generator'); ?>"
                                >
                                    <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                                    <span class="aipkit_cw_toggle_label"><?php esc_html_e('Details', 'gpt3-ai-content-generator'); ?></span>
                                </button>
                                <button
                                    type="button"
                                    class="aipkit_cw_bulk_toggle_raw"
                                    aria-expanded="false"
                                    aria-controls="aipkit_cw_bulk_raw"
                                    data-label-raw="<?php echo esc_attr__('Paste', 'gpt3-ai-content-generator'); ?>"
                                    data-label-grid="<?php echo esc_attr__('Editor', 'gpt3-ai-content-generator'); ?>"
                                    title="<?php esc_attr_e('Paste multiple topics at once', 'gpt3-ai-content-generator'); ?>"
                                >
                                    <span class="dashicons dashicons-edit-large" aria-hidden="true"></span>
                                    <span class="aipkit_cw_toggle_label"><?php esc_html_e('Paste', 'gpt3-ai-content-generator'); ?></span>
                                </button>
                            </div>
                        </div>
                        <div id="aipkit_cw_bulk_raw" class="aipkit_cw_bulk_raw" hidden>
                            <textarea id="aipkit_cw_bulk_topics" name="content_title_bulk" class="aipkit_form-input aipkit_autosave_trigger aipkit_cw_bulk_textarea" rows="6" placeholder="<?php esc_attr_e("Enter one topic per line\ne.g., How to bake a cake | frosting, flour", 'gpt3-ai-content-generator'); ?>"></textarea>
                            <p class="aipkit_form-help aipkit_cw_bulk_raw_help">
                                <?php esc_html_e('Optional columns: Keywords | Category ID | Author | Post Type | Schedule', 'gpt3-ai-content-generator'); ?>
                            </p>
                        </div>
                        <template id="aipkit_cw_bulk_row_template">
                            <div class="aipkit_cw_bulk_row" data-aipkit-bulk-row>
                                <span class="aipkit_cw_bulk_row_number" aria-hidden="true"></span>
                                <input type="text" class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_input--topic aipkit_autosave_trigger" data-bulk-field="topic" placeholder="<?php esc_attr_e('Enter topic...', 'gpt3-ai-content-generator'); ?>" aria-label="<?php esc_attr_e('Topic', 'gpt3-ai-content-generator'); ?>">
                                <input type="text" class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_input--keywords aipkit_autosave_trigger" data-bulk-field="keywords" placeholder="<?php esc_attr_e('Keywords (optional)', 'gpt3-ai-content-generator'); ?>" aria-label="<?php esc_attr_e('Keywords', 'gpt3-ai-content-generator'); ?>">
                                <select class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_detail aipkit_autosave_trigger" data-bulk-field="category" aria-label="<?php esc_attr_e('Category', 'gpt3-ai-content-generator'); ?>">
                                    <option value=""><?php esc_html_e('Category', 'gpt3-ai-content-generator'); ?></option>
                                    <?php foreach ($wp_categories as $category): ?>
                                        <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_detail aipkit_autosave_trigger" data-bulk-field="author" aria-label="<?php esc_attr_e('Author', 'gpt3-ai-content-generator'); ?>">
                                    <option value=""><?php esc_html_e('Author', 'gpt3-ai-content-generator'); ?></option>
                                    <?php foreach ($users_for_author as $user): ?>
                                        <option value="<?php echo esc_attr($user->user_login); ?>" data-user-id="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_detail aipkit_autosave_trigger" data-bulk-field="type" aria-label="<?php esc_attr_e('Post Type', 'gpt3-ai-content-generator'); ?>">
                                    <option value=""><?php esc_html_e('Post', 'gpt3-ai-content-generator'); ?></option>
                                    <?php foreach ($available_post_types as $pt_slug => $pt_obj): ?>
                                        <option value="<?php echo esc_attr($pt_slug); ?>"><?php echo esc_html($pt_obj->label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="datetime-local" class="aipkit_form-input aipkit_cw_bulk_input aipkit_cw_bulk_detail aipkit_autosave_trigger" data-bulk-field="schedule" aria-label="<?php esc_attr_e('Schedule', 'gpt3-ai-content-generator'); ?>">
                                <button type="button" class="aipkit_cw_bulk_remove_row" aria-label="<?php esc_attr_e('Remove row', 'gpt3-ai-content-generator'); ?>">
                                    <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <!-- Import CSV Pane - Redesigned with Aesthetics, Chunking, and Choice Overload principles -->
            <div class="aipkit_cw_tab_content" data-pane="csv">
                <div class="aipkit_csv_import_container">
                    
                    <!-- Chunk 1: Upload Zone (Primary Action) -->
                    <div class="aipkit_csv_upload_zone" data-csv-upload-zone>
                        <label for="aipkit_cw_csv_file_input" class="aipkit_csv_dropzone">
                            <span class="aipkit_csv_dropzone_icon" aria-hidden="true">
                                <span class="dashicons dashicons-upload"></span>
                            </span>
                            <span class="aipkit_csv_dropzone_text">
                                <span class="aipkit_csv_dropzone_primary"><?php esc_html_e('Drop your CSV file here', 'gpt3-ai-content-generator'); ?></span>
                                <span class="aipkit_csv_dropzone_secondary"><?php esc_html_e('or click to browse', 'gpt3-ai-content-generator'); ?></span>
                            </span>
                            <input 
                                type="file" 
                                id="aipkit_cw_csv_file_input" 
                                name="csv_file_input" 
                                class="aipkit_csv_file_input_hidden" 
                                accept=".csv, text/csv"
                            >
                        </label>
                    </div>

                    <!-- Chunk 2: Status Feedback (Progressive Disclosure) -->
                    <div class="aipkit_csv_status_container" id="aipkit_cw_csv_status_container" data-csv-status hidden>
                        <div class="aipkit_csv_status_card" data-csv-status-card>
                            <div class="aipkit_csv_status_icon" data-csv-status-icon>
                                <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                            </div>
                            <div class="aipkit_csv_status_content">
                                <span class="aipkit_csv_file_name" data-csv-file-name></span>
                                <span id="aipkit_cw_csv_analysis_results" class="aipkit_csv_analysis_results" data-csv-message></span>
                            </div>
                            <button type="button" class="aipkit_csv_clear_btn" data-csv-clear aria-label="<?php esc_attr_e('Remove file', 'gpt3-ai-content-generator'); ?>">
                                <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden data holder for form submission -->
                    <textarea name="content_title_csv" id="aipkit_cw_csv_data_holder" class="aipkit_csv_data_holder" style="display: none;" readonly></textarea>

                    <!-- Chunk 3: CSV format guide -->
                    <div class="aipkit_csv_help_content">
                        <div class="aipkit_csv_columns_row">
                            <div class="aipkit_csv_column_chip aipkit_csv_column_chip--required">
                                <span class="aipkit_csv_column_label"><?php esc_html_e('Topic', 'gpt3-ai-content-generator'); ?></span>
                                <span class="aipkit_csv_column_tag"><?php esc_html_e('required', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                            <span class="aipkit_csv_column_divider">→</span>
                            <div class="aipkit_csv_column_chip">
                                <span class="aipkit_csv_column_label"><?php esc_html_e('Keywords', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                            <span class="aipkit_csv_column_divider">→</span>
                            <div class="aipkit_csv_column_chip">
                                <span class="aipkit_csv_column_label"><?php esc_html_e('Category', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                            <span class="aipkit_csv_column_divider">→</span>
                            <div class="aipkit_csv_column_chip">
                                <span class="aipkit_csv_column_label"><?php esc_html_e('Author', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                            <span class="aipkit_csv_column_divider">→</span>
                            <div class="aipkit_csv_column_chip">
                                <span class="aipkit_csv_column_label"><?php esc_html_e('Post Type', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                            <span class="aipkit_csv_column_divider">→</span>
                            <div class="aipkit_csv_column_chip">
                                <span class="aipkit_csv_column_label"><?php esc_html_e('Schedule', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                            <a 
                                href="https://docs.google.com/spreadsheets/d/1WOnO_UKkbRCoyjRxQnDDTy0i-RsnrY_MDKD3Ks09JJk/edit?usp=sharing" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="aipkit_csv_sample_link"
                            >
                                <span class="dashicons dashicons-download" aria-hidden="true"></span>
                                <?php esc_html_e('Download sample CSV', 'gpt3-ai-content-generator'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
            <!-- RSS Feed Pane -->
            <div class="aipkit_cw_tab_content" data-pane="rss">
                <?php include __DIR__ . '/mode-rss.php'; ?>
            </div>
            <!-- Website URL Pane -->
            <div class="aipkit_cw_tab_content" data-pane="url">
                <?php include __DIR__ . '/mode-url.php'; ?>
            </div>
            <!-- Google Sheets Pane -->
            <div class="aipkit_cw_tab_content" data-pane="gsheets">
                <?php include __DIR__ . '/mode-gsheets.php'; ?>
            </div>
            <!-- Update Existing Content Pane -->
            <div class="aipkit_cw_tab_content" data-pane="existing">
                <div class="aipkit_cw_existing_panel" data-aipkit-existing-panel>
                    <div class="aipkit_cw_existing_controls">
                        <div class="aipkit_cw_existing_filters" data-aipkit-existing-filter="type">
                            <label class="aipkit_form-label" for="aipkit_cw_existing_post_type"><?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_cw_existing_post_type" class="aipkit_form-input">
                                <option value=""><?php esc_html_e('All types', 'gpt3-ai-content-generator'); ?></option>
                                <?php foreach ($available_post_types as $pt_slug => $pt_obj): ?>
                                    <option value="<?php echo esc_attr($pt_slug); ?>"><?php echo esc_html($pt_obj->label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="aipkit_cw_existing_filters" data-aipkit-existing-filter="status">
                            <label class="aipkit_form-label" for="aipkit_cw_existing_post_status"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_cw_existing_post_status" class="aipkit_form-input">
                                <option value=""><?php esc_html_e('Any status', 'gpt3-ai-content-generator'); ?></option>
                                <option value="publish"><?php esc_html_e('Published', 'gpt3-ai-content-generator'); ?></option>
                                <option value="draft"><?php esc_html_e('Draft', 'gpt3-ai-content-generator'); ?></option>
                                <option value="pending"><?php esc_html_e('Pending', 'gpt3-ai-content-generator'); ?></option>
                                <option value="future"><?php esc_html_e('Scheduled', 'gpt3-ai-content-generator'); ?></option>
                                <option value="private"><?php esc_html_e('Private', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                        <div class="aipkit_cw_existing_filters" data-aipkit-existing-filter="media">
                            <label class="aipkit_form-label" for="aipkit_cw_existing_media_filter"><?php esc_html_e('Media', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_cw_existing_media_filter" class="aipkit_form-input">
                                <option value=""><?php esc_html_e('All media items', 'gpt3-ai-content-generator'); ?></option>
                                <option value="image"><?php esc_html_e('Images', 'gpt3-ai-content-generator'); ?></option>
                                <option value="detached"><?php esc_html_e('Unattached', 'gpt3-ai-content-generator'); ?></option>
                                <option value="mine"><?php esc_html_e('Mine', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                        <div class="aipkit_cw_existing_search">
                            <label class="aipkit_form-label" for="aipkit_cw_existing_search"><?php esc_html_e('Search', 'gpt3-ai-content-generator'); ?></label>
                            <input
                                type="search"
                                id="aipkit_cw_existing_search"
                                class="aipkit_form-input"
                                placeholder="<?php esc_attr_e('Search by title...', 'gpt3-ai-content-generator'); ?>"
                            >
                        </div>
                    </div>

                    <div class="aipkit_cw_existing_list">
                        <div class="aipkit_cw_existing_table_wrap">
                            <table class="aipkit_cw_existing_table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="aipkit_cw_existing_col_check">
                                            <label class="screen-reader-text" for="aipkit_cw_existing_select_all"><?php esc_html_e('Select all', 'gpt3-ai-content-generator'); ?></label>
                                            <input type="checkbox" id="aipkit_cw_existing_select_all">
                                        </th>
                                        <th scope="col" class="aipkit_cw_existing_col_title"><?php esc_html_e('Title', 'gpt3-ai-content-generator'); ?></th>
                                        <th scope="col" class="aipkit_cw_existing_col_type"><?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?></th>
                                        <th scope="col" class="aipkit_cw_existing_col_status"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></th>
                                        <th scope="col" class="aipkit_cw_existing_col_alt"><?php esc_html_e('Alt', 'gpt3-ai-content-generator'); ?></th>
                                        <th scope="col" class="aipkit_cw_existing_col_caption"><?php esc_html_e('Caption', 'gpt3-ai-content-generator'); ?></th>
                                        <th scope="col" class="aipkit_cw_existing_col_description"><?php esc_html_e('Description', 'gpt3-ai-content-generator'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="aipkit_cw_existing_posts_body">
                                    <tr class="aipkit_cw_existing_empty">
                                        <td colspan="7"><?php esc_html_e('Select filters to load posts.', 'gpt3-ai-content-generator'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="aipkit_cw_existing_pagination" id="aipkit_cw_existing_pagination">
                            <button type="button" class="aipkit_btn aipkit_btn-secondary" id="aipkit_cw_existing_page_prev" disabled>
                                <?php esc_html_e('Previous', 'gpt3-ai-content-generator'); ?>
                            </button>
                            <span class="aipkit_cw_existing_page_status" id="aipkit_cw_existing_page_status"><?php esc_html_e('Page 1 of 1', 'gpt3-ai-content-generator'); ?></span>
                            <button type="button" class="aipkit_btn aipkit_btn-secondary" id="aipkit_cw_existing_page_next" disabled>
                                <?php esc_html_e('Next', 'gpt3-ai-content-generator'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="aipkit_cw_existing_footer">
                        <span class="aipkit_cw_existing_selected" id="aipkit_cw_existing_selected_count"><?php esc_html_e('0 selected', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
