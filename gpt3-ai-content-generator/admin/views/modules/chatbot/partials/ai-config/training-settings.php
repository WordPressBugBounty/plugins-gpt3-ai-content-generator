<?php
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.
$default_training_post_types = ['page', 'post'];
?>
<section id="aipkit_sources_training_card" class="aipkit_builder_card aipkit_builder_card--training">
    <div class="aipkit_training_decision" data-aipkit-training-decision>
        <div class="aipkit_training_decision_header">
            <div class="aipkit_training_header_copy">
                <p class="aipkit_training_decision_title">
                    <?php esc_html_e('Knowledge', 'gpt3-ai-content-generator'); ?>
                </p>
                <p class="aipkit_training_decision_subtitle">
                    <?php esc_html_e('Choose what this chatbot should know.', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>
        </div>
        <div class="aipkit_training_decision_row">
            <div class="aipkit_training_source_list" role="group" aria-label="<?php esc_attr_e('Knowledge sources', 'gpt3-ai-content-generator'); ?>">
                <div class="aipkit_training_choice_card is-active" data-aipkit-training-choice-card="website">
                    <button
                        type="button"
                        class="aipkit_training_choice aipkit_training_source_row is-active"
                        data-aipkit-training-choice="website"
                        data-aipkit-website-options-toggle
                        aria-pressed="true"
                        aria-expanded="false"
                        aria-haspopup="menu"
                        aria-controls="aipkit_training_website_options"
                    >
                        <span class="aipkit_training_choice_copy">
                            <span class="aipkit_training_choice_title_row">
                                <span class="aipkit_training_choice_title"><?php esc_html_e('My website', 'gpt3-ai-content-generator'); ?></span>
                            </span>
                            <span class="aipkit_training_source_summary" data-aipkit-training-website-summary>
                                <?php esc_html_e('Pages and posts', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </span>
                        <span class="aipkit_training_source_plus aipkit_training_source_plus--website" aria-hidden="true">+</span>
                    </button>
                    <div
                        id="aipkit_training_website_options"
                        class="aipkit_training_website_options"
                        data-aipkit-website-options
                        hidden
                    >
                        <div id="aipkit_wp_content_bulk_panel" class="aipkit_training_site_field aipkit_training_site_field--menu">
                            <div
                                class="aipkit_training_site_dropdown"
                                data-aipkit-training-types="bulk"
                                data-placeholder="<?php echo esc_attr__('Select types', 'gpt3-ai-content-generator'); ?>"
                                data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                            >
                                <div
                                    id="aipkit_training_types_menu_bulk"
                                    class="aipkit_training_site_dropdown_panel"
                                    role="menu"
                                >
                                    <div id="aipkit_vs_wp_types_checkboxes" class="aipkit_training_site_checks aipkit_training_site_checks--dropdown">
                                        <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                            <label class="aipkit_training_site_check" data-ptype="<?php echo esc_attr($post_type_slug); ?>">
                                                <input type="checkbox" class="aipkit_wp_type_cb" value="<?php echo esc_attr($post_type_slug); ?>" <?php checked(in_array($post_type_slug, $default_training_post_types, true)); ?> />
                                                <span class="aipkit_training_site_check_label"><?php echo esc_html($post_type_obj->label); ?></span>
                                                <span class="aipkit_count_badge" data-count="-1"></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <select id="aipkit_vs_wp_content_post_types" class="aipkit_training_site_hidden_select" multiple size="3">
                                <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                    <option value="<?php echo esc_attr($post_type_slug); ?>" <?php selected(in_array($post_type_slug, $default_training_post_types, true)); ?>>
                                        <?php echo esc_html($post_type_obj->label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="aipkit_training_choice_card" data-aipkit-training-choice-card="other">
                    <button
                        type="button"
                        class="aipkit_training_choice aipkit_training_source_row aipkit_builder_sheet_trigger"
                        data-aipkit-training-choice="other"
                        data-sheet-title="<?php echo esc_attr__('Other sources', 'gpt3-ai-content-generator'); ?>"
                        data-sheet-description="<?php echo esc_attr__('Add extra content your chatbot should know.', 'gpt3-ai-content-generator'); ?>"
                        data-sheet-content="other-sources"
                        aria-pressed="false"
                        aria-haspopup="dialog"
                        aria-controls="aipkit_builder_sheet"
                    >
                        <span class="aipkit_training_choice_copy">
                            <span class="aipkit_training_choice_title"><?php esc_html_e('Other sources', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_training_source_summary"><?php esc_html_e('Text, Q&A, or files', 'gpt3-ai-content-generator'); ?></span>
                        </span>
                        <span class="aipkit_training_source_plus" aria-hidden="true">+</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="aipkit_training_website_panel is-active" data-aipkit-training-website-panel>
        <div class="aipkit_training_website">
            <div class="aipkit_training_decision_actions">
                <div class="aipkit_training_action_meta">
                    <div class="aipkit_training_state_wrap" data-aipkit-training-state-wrap>
                        <button
                            type="button"
                            class="aipkit_training_state is-pending"
                            data-aipkit-training-state
                            aria-live="polite"
                            aria-busy="true"
                            aria-haspopup="dialog"
                            aria-expanded="false"
                            aria-controls="aipkit_training_state_menu"
                            aria-label="<?php esc_attr_e('Knowledge status', 'gpt3-ai-content-generator'); ?>"
                            disabled
                        >
                            <span class="aipkit_training_state_dot" aria-hidden="true"></span>
                            <span class="aipkit_training_state_value" data-aipkit-training-state-value>
                                <?php esc_html_e('No knowledge yet', 'gpt3-ai-content-generator'); ?>
                            </span>
                            <span class="aipkit_training_state_help" aria-hidden="true">?</span>
                        </button>
                        <div
                            id="aipkit_training_state_menu"
                            class="aipkit_training_state_menu"
                            data-aipkit-training-state-menu
                            role="dialog"
                            aria-label="<?php esc_attr_e('Knowledge details', 'gpt3-ai-content-generator'); ?>"
                            hidden
                        >
                            <div class="aipkit_training_state_menu_title" data-aipkit-training-report-status>
                                <?php esc_html_e('Adding knowledge', 'gpt3-ai-content-generator'); ?>
                            </div>
                            <div class="aipkit_training_state_report">
                                <span><?php esc_html_e('Knowledge entries', 'gpt3-ai-content-generator'); ?></span>
                                <strong data-aipkit-training-report-trained>0</strong>
                                <span><?php esc_html_e('Queued', 'gpt3-ai-content-generator'); ?></span>
                                <strong data-aipkit-training-report-queued>0</strong>
                                <span><?php esc_html_e('Processing', 'gpt3-ai-content-generator'); ?></span>
                                <strong data-aipkit-training-report-processing>0</strong>
                                <span><?php esc_html_e('Failed', 'gpt3-ai-content-generator'); ?></span>
                                <strong data-aipkit-training-report-failed>0</strong>
                            </div>
                            <button type="button" class="aipkit_training_state_sources" data-aipkit-view-training-sources>
                                <?php esc_html_e('View knowledge', 'gpt3-ai-content-generator'); ?>
                            </button>
                            <button type="button" class="aipkit_training_state_stop" data-aipkit-stop-training>
                                <?php esc_html_e('Stop training', 'gpt3-ai-content-generator'); ?>
                            </button>
                        </div>
                    </div>
                    <span class="aipkit_training_status" id="aipkit_training_status" data-aipkit-training-status aria-live="polite"></span>
                </div>
                <button
                    type="button"
                    class="aipkit_training_action_btn aipkit_training_decision_start"
                    data-training-action="add"
                    data-aipkit-training-start
                    data-aipkit-training-main-action
                >
                    <span class="aipkit_training_action_spinner" aria-hidden="true"></span>
                    <span class="aipkit_training_action_text"><?php esc_html_e('Add knowledge', 'gpt3-ai-content-generator'); ?></span>
                </button>
            </div>
            <select id="aipkit_vs_wp_content_status" class="aipkit_training_site_hidden_select" aria-hidden="true" tabindex="-1">
                <option value="publish" selected><?php esc_html_e('Published', 'gpt3-ai-content-generator'); ?></option>
            </select>

            <div id="aipkit_vs_wp_content_messages_area" class="aipkit_form-help aipkit_training_site_status" aria-live="polite"></div>
            <select id="aipkit_vs_global_target_select" class="aipkit_training_site_target_select" aria-hidden="true" tabindex="-1">
                <option value=""></option>
            </select>
        </div>
    </div>

    <div class="aipkit_training_footer">
        <div class="aipkit_builder_action_row aipkit_training_action_row">
            <div class="aipkit_builder_action_group aipkit_training_sources_row">
                <button
                    type="button"
                    class="aipkit_training_sources_btn aipkit_builder_sheet_trigger"
                    data-base-label="<?php echo esc_attr__('Knowledge entries', 'gpt3-ai-content-generator'); ?>"
                    data-sheet-title="<?php echo esc_attr__('Knowledge entries', 'gpt3-ai-content-generator'); ?>"
                    data-sheet-description="<?php echo esc_attr__('Review entries in the selected knowledge store.', 'gpt3-ai-content-generator'); ?>"
                    data-sheet-content="sources"
                    hidden
                >
                    <span class="aipkit_training_sources_label">
                        <?php echo esc_html__('Knowledge entries', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <span class="aipkit_training_sources_count" aria-hidden="true">0</span>
                </button>
            </div>
        </div>
    </div>
</section>
