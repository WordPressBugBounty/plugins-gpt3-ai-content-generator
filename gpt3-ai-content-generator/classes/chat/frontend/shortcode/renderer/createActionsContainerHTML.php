<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/shortcode/renderer/createActionsContainerHTML.php

namespace WPAICG\Chat\Frontend\Shortcode\RendererMethods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for creating the HTML for message action buttons.
 *
 * @param array $config
 * @return string HTML for the actions container.
 */
function createActionsContainerHTML_logic(array $config): string {
    $actionsHTML = '';
    $texts = $config['text'] ?? [];
    if ($config['ttsEnabled'] ?? false) {
        $playTitle = $texts['playActionLabel'] ?? 'Play audio';
        $actionsHTML .= sprintf(
             '<button type="button" class="aipkit_action_btn aipkit_play_btn" title="%1$s" aria-label="%1$s">' .
             '<span class="dashicons dashicons-controls-play"></span>' .
             '</button>',
             esc_attr($playTitle)
         );
    }
    if ($config['enableCopyButton'] ?? false) {
        $copyTitle = $texts['copyActionLabel'] ?? 'Copy response';
        $actionsHTML .= sprintf(
            '<button type="button" class="aipkit_action_btn aipkit_copy_btn" title="%1$s" aria-label="%1$s">' .
            '<span class="dashicons dashicons-admin-page"></span>' .
            '</button>',
            esc_attr($copyTitle)
        );
    }
    if ($config['enableFeedback'] ?? false) {
        $likeTitle = $texts['feedbackLikeLabel'] ?? 'Like response';
        $dislikeTitle = $texts['feedbackDislikeLabel'] ?? 'Dislike response';
        $actionsHTML .= sprintf(
            '<button type="button" class="aipkit_action_btn aipkit_feedback_btn aipkit_thumb_up_btn" title="%1$s" aria-label="%1$s" data-feedback="up">' .
            '<span class="dashicons dashicons-thumbs-up"></span>' .
            '</button>',
            esc_attr($likeTitle)
        );
         $actionsHTML .= sprintf(
            '<button type="button" class="aipkit_action_btn aipkit_feedback_btn aipkit_thumb_down_btn" title="%1$s" aria-label="%1$s" data-feedback="down">' .
            '<span class="dashicons dashicons-thumbs-down"></span>' .
            '</button>',
            esc_attr($dislikeTitle)
        );
    }

    if ($actionsHTML) {
        return '<div class="aipkit_message_actions">' . $actionsHTML . '</div>';
    }
    return '';
}