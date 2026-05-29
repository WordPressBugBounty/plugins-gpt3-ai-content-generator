<?php
 if (!defined('ABSPATH')) { exit; } ?>
<div id="aipkit_task_config_comment_reply_main" class="aipkit_task_config_section">
    <?php
 $comment_reply_settings_partial = __DIR__ . '/community-engagement/comment-reply-settings.php'; if (file_exists($comment_reply_settings_partial)) { include $comment_reply_settings_partial; } else { echo '<p>Error: Comment Reply Settings UI partial is missing.</p>'; } ?>
</div>