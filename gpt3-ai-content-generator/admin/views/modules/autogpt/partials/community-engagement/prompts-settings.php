<?php
/**
 * Partial: Community Engagement Automated Task - Prompt Settings
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
$default_reply_prompt = "Write a helpful and friendly reply to this comment on my blog post titled '{post_title}'.\n\nComment: {comment_content}";

$aipkit_prompt_items = [
    [
        'key' => 'reply',
        'label' => __('Reply Prompt', 'gpt3-ai-content-generator'),
        'description' => __('Write the comment response', 'gpt3-ai-content-generator'),
        'flyout_id' => 'aipkit_task_cc_reply_prompt_flyout',
        'flyout_title' => __('Reply Prompt', 'gpt3-ai-content-generator'),
        'textarea' => [
            'id' => 'aipkit_task_cc_custom_content_prompt',
            'name' => 'cc_custom_content_prompt',
            'value' => $default_reply_prompt,
            'placeholder' => __('Enter your reply prompt...', 'gpt3-ai-content-generator'),
        ],
        'placeholders' => [
            '{comment_content}',
            '{comment_author}',
            '{post_title}',
        ],
    ],
];

$aipkit_prompts_render_list = false;

include __DIR__ . '/../shared/prompts-popover-body.php';

// Prevent this flag from leaking into subsequent includes.
unset($aipkit_prompts_render_list);
?>
