<?php
// File: classes/core/token-manager/helpers/GetGuestUsageLogic.php

namespace WPAICG\Core\TokenManager\Helpers;

use WPAICG\Core\TokenManager\AIPKit_Token_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic to get current token usage and last reset time for a guest.
 *
 * @param AIPKit_Token_Manager $managerInstance The instance of AIPKit_Token_Manager.
 * @param string $session_id The guest's session ID.
 * @param int $guest_context_table_id The context ID for the guest table (bot_id or module identifier).
 * @return array ['current_usage' => int, 'last_reset_time' => int]
 */
function GetGuestUsageLogic(AIPKit_Token_Manager $managerInstance, string $session_id, int $guest_context_table_id): array {
    global $wpdb;
    $guest_table_name = $managerInstance->get_guest_table_name();
    $current_usage = 0;
    $last_reset_time = 0;

    $guest_row = $wpdb->get_row($wpdb->prepare(
        "SELECT tokens_used, last_reset_timestamp FROM {$guest_table_name} WHERE session_id = %s AND bot_id = %d",
        $session_id, $guest_context_table_id
    ), ARRAY_A);

    if ($guest_row) {
        $current_usage = (int) $guest_row['tokens_used'];
        $last_reset_time = (int) $guest_row['last_reset_timestamp'];
    }
    return ['current_usage' => $current_usage, 'last_reset_time' => $last_reset_time];
}