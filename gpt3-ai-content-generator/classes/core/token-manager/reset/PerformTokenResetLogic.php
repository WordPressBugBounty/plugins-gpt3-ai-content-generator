<?php
// File: classes/core/token-manager/reset/PerformTokenResetLogic.php

namespace WPAICG\Core\TokenManager\Reset;

use WPAICG\Core\TokenManager\AIPKit_Token_Manager;
use WPAICG\Core\TokenManager\Constants\MetaKeysConstants;
use WPAICG\Core\TokenManager\Constants\GuestTableConstants;
use WPAICG\Images\AIPKit_Image_Settings_Ajax_Handler; // For Image Generator settings

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for performing the token reset for chatbots AND the image generator module.
 * This function is called by the perform_token_reset method in AIPKit_Token_Manager.
 *
 * @param AIPKit_Token_Manager $managerInstance The instance of AIPKit_Token_Manager.
 */
function PerformTokenResetLogic(AIPKit_Token_Manager $managerInstance): void {
    error_log('AIPKit Token Manager (Reset Logic): Running scheduled token reset...');
    global $wpdb;
    $current_time = time();
    $current_day_of_week = date('N', $current_time);
    $current_day_of_month = date('j', $current_time);

    // --- 1. Chatbot Token Reset ---
    $bot_storage = $managerInstance->get_bot_storage();
    if ($bot_storage) {
        $all_chatbots = $bot_storage->get_chatbots(); // Assumes get_chatbots() returns array of WP_Post
        $users_reset_chat = 0;
        $guests_reset_chat = 0;

        if (!empty($all_chatbots)) {
            foreach ($all_chatbots as $bot_post) {
                $bot_id = $bot_post->ID;
                $settings = $bot_storage->get_chatbot_settings($bot_id);
                $reset_period = $settings['token_reset_period'] ?? 'never';

                if ($reset_period === 'never') continue;

                $reset_needed = IsResetDueLogic($current_time, $reset_period, (int)get_user_meta(0, MetaKeysConstants::CHAT_RESET_META_KEY_PREFIX . $bot_id, true)); // Check generic last reset for logic, though actual meta is per-user
                // More accurately, the cron should just trigger the reset, and individual check_and_reset_tokens handles if it's due *per user*
                // For a global cron, we reset all.

                $reset_needed_for_cron = false;
                if ($reset_period === 'daily') $reset_needed_for_cron = true;
                elseif ($reset_period === 'weekly' && $current_day_of_week == get_option('start_of_week', 1)) $reset_needed_for_cron = true;
                elseif ($reset_period === 'monthly' && $current_day_of_month == 1) $reset_needed_for_cron = true;


                if ($reset_needed_for_cron) {
                    $meta_key_usage = MetaKeysConstants::CHAT_USAGE_META_KEY_PREFIX . $bot_id;
                    $meta_key_reset = MetaKeysConstants::CHAT_RESET_META_KEY_PREFIX . $bot_id;

                    $deleted_user_usage_meta = $wpdb->delete($wpdb->usermeta, ['meta_key' => $meta_key_usage], ['%s']);
                    $deleted_user_reset_meta = $wpdb->delete($wpdb->usermeta, ['meta_key' => $meta_key_reset], ['%s']);

                    if($deleted_user_usage_meta !== false) $users_reset_chat += $deleted_user_usage_meta; // Count affected rows (approx users)

                    $guest_table_name = $managerInstance->get_guest_table_name();
                    $deleted_guests = $wpdb->delete($guest_table_name, ['bot_id' => $bot_id], ['%d']);
                    if ($deleted_guests !== false) $guests_reset_chat += $deleted_guests;
                    else error_log("AIPKit Token Manager (Reset Logic): Error deleting guest usage for Chat Bot ID {$bot_id}. Error: " . $wpdb->last_error);
                    error_log("AIPKit Token Manager (Reset Logic): Reset usage counters for chatbot ID {$bot_id} (Period: {$reset_period}).");
                }
            }
        }
        error_log("AIPKit Token Manager (Reset Logic): Chatbot reset complete. User meta rows deleted: {$users_reset_chat}, Guest rows deleted: {$guests_reset_chat}.");
    } else {
        error_log('AIPKit Token Manager (Reset Logic): BotStorage not initialized, cannot perform reset for chatbots.');
    }

    // --- 2. Image Generator Token Reset ---
    if (class_exists(AIPKit_Image_Settings_Ajax_Handler::class)) {
        $img_settings_all = AIPKit_Image_Settings_Ajax_Handler::get_settings();
        $img_token_settings = $img_settings_all['token_management'] ?? [];
        $img_reset_period = $img_token_settings['token_reset_period'] ?? 'never';
        $users_reset_img = 0;
        $guests_reset_img = 0;

        if ($img_reset_period !== 'never') {
            $img_reset_needed_for_cron = false;
            if ($img_reset_period === 'daily') $img_reset_needed_for_cron = true;
            elseif ($img_reset_period === 'weekly' && $current_day_of_week == get_option('start_of_week', 1)) $img_reset_needed_for_cron = true;
            elseif ($img_reset_period === 'monthly' && $current_day_of_month == 1) $img_reset_needed_for_cron = true;

            if ($img_reset_needed_for_cron) {
                $deleted_user_img_usage_meta = $wpdb->delete($wpdb->usermeta, ['meta_key' => MetaKeysConstants::IMG_USAGE_META_KEY], ['%s']);
                $deleted_user_img_reset_meta = $wpdb->delete($wpdb->usermeta, ['meta_key' => MetaKeysConstants::IMG_RESET_META_KEY], ['%s']);
                if($deleted_user_img_usage_meta !== false) $users_reset_img += $deleted_user_img_usage_meta;

                $guest_table_name = $managerInstance->get_guest_table_name();
                $deleted_img_guests = $wpdb->delete($guest_table_name, ['bot_id' => GuestTableConstants::IMG_GEN_GUEST_CONTEXT_ID], ['%d']);
                if ($deleted_img_guests !== false) $guests_reset_img += $deleted_img_guests;
                else error_log("AIPKit Token Manager (Reset Logic): Error deleting guest usage for Image Generator. Error: " . $wpdb->last_error);
                error_log("AIPKit Token Manager (Reset Logic): Reset usage counters for Image Generator module (Period: {$img_reset_period}).");
            }
        }
        error_log("AIPKit Token Manager (Reset Logic): Image Generator reset complete. User meta rows cleared: {$users_reset_img}, Guest rows deleted for image gen: {$guests_reset_img}.");
    } else {
        error_log('AIPKit Token Manager (Reset Logic): Image Settings Handler class not found, cannot perform reset for Image Generator.');
    }
    error_log('AIPKit Token Manager (Reset Logic): Scheduled token reset process finished.');
}