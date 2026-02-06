<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/autogpt/partials/task-automation-ui.php
// Status: MODIFIED
/**
 * Main UI for Task Automation.
 * Includes the form for creating/editing tasks and the list of existing tasks/queue.
 * Variable definitions are now in admin/views/modules/autogpt/index.php
 * REVISED: Wizard steps are now rendered by JavaScript for dynamic behavior.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="aipkit_container-body">
    <!-- Add New Task / Edit Task Form (Initially Hidden) -->
    <div id="aipkit_automated_task_form_wrapper">
        <div class="aipkit_task_form_container">
            <form id="aipkit_automated_task_form" onsubmit="return false;">
                <input type="hidden" name="task_id" id="aipkit_automated_task_id" value="">
                <input type="hidden" name="task_name" id="aipkit_automated_task_name" value="">

                <!-- Container for all possible content steps -->
                <div class="aipkit_wizard_content_container">
                    <div class="aipkit_autogpt_form_layout">
                        <div class="aipkit_autogpt_form_sidebar aipkit_autogpt_form_left">
                            <?php include __DIR__ . '/shared/category-selector.php'; ?>
                        </div>
                        <div class="aipkit_autogpt_form_main">
                            <!-- Step Content: Setup -->
                            <div class="aipkit_wizard_content_step" data-content-id="task_form_setup">
                                <?php include __DIR__ . '/task-form-setup.php'; ?>
                            </div>
                            <!-- Step Content: SEO -->
                            <div class="aipkit_wizard_content_step" data-content-id="task_config_seo">
                                <?php include __DIR__ . '/task-form-config-seo.php'; ?>
                            </div>
                            <!-- Step Content: Comment Reply Settings (new) -->
                            <div class="aipkit_wizard_content_step" data-content-id="task_config_comment_reply">
                                <?php include __DIR__ . '/task-form-config-comment-reply.php'; ?>
                            </div>
                        </div>
                        <div class="aipkit_autogpt_form_sidebar aipkit_autogpt_form_right">
                            <!-- Step Content: Status -->
                            <div class="aipkit_wizard_content_step aipkit_autogpt_sidebar_step" data-content-id="task_config_status">
                                <?php include __DIR__ . '/task-form-config-status.php'; ?>
                            </div>
                            <!-- Step Content: AI & Prompts -->
                            <div class="aipkit_wizard_content_step aipkit_autogpt_sidebar_step" data-content-id="task_config_ai">
                                <?php include __DIR__ . '/task-form-config-ai.php'; ?>
                            </div>
                            <!-- Step Content: Content Writing (Finish) -->
                            <div class="aipkit_wizard_content_step aipkit_autogpt_sidebar_step" data-content-id="task_config_content_writing">
                                <?php include __DIR__ . '/task-form-config-content-writing.php'; ?>
                            </div>
                            <!-- Step Content: Content Indexing (Finish) -->
                            <!-- Step Content: Comment Reply AI Settings (new) -->
                            <div class="aipkit_wizard_content_step aipkit_autogpt_sidebar_step" data-content-id="task_config_comment_reply_ai">
                                <?php include __DIR__ . '/task-form-config-comment-reply-ai.php'; ?>
                            </div>
                            <!-- Step Content: Content Enhancement - AI & Prompts (new) -->
                            <div class="aipkit_wizard_content_step aipkit_autogpt_sidebar_step" data-content-id="task_config_enhancement_ai_and_prompts">
                                 <?php include __DIR__ . '/content-enhancement/ai-and-prompts.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>


            </form>
        </div>
    </div>

    <!-- List of Existing Tasks -->
    <?php include __DIR__ . '/task-list.php'; ?>

    <!-- Indexing Queue Viewer -->
    <?php include __DIR__ . '/task-queue.php'; ?>

</div>
