<?php
// File: classes/core/token-manager/helpers/UpsertGuestUsageLogic.php

namespace WPAICG\Core\TokenManager\Helpers;

use WPAICG\Core\TokenManager\AIPKit_Token_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic to update or insert guest token usage data.
 *
 * @param AIPKit_Token_Manager $managerInstance The instance of AIPKit_Token_Manager.
 * @param string $session_id The guest's session ID.
 * @param int $guest_context_table_id The context ID for the guest table.
 * @param int $new_usage The new total token usage.
 * @param int $last_reset_timestamp The timestamp of the last reset.
 */
function UpsertGuestUsageLogic(
    AIPKit_Token_Manager $managerInstance,
    string $session_id,
    int $guest_context_table_id,
    int $new_usage,
    int $last_reset_timestamp
): void {
    global $wpdb;
    $guest_table_name = $managerInstance->get_guest_table_name();

    $upsert_result = $wpdb->replace(
        $guest_table_name,
        [
            'session_id' => $session_id,
            'bot_id' => $guest_context_table_id,
            'tokens_used' => $new_usage,
            'last_reset_timestamp' => $last_reset_timestamp,
            'last_updated_at' => current_time('mysql', 1)
        ],
        ['%s', '%d', '%d', '%d', '%s']
    );

    if ($upsert_result === false) {
        error_log("AIPKit Token Manager Helper: Failed to update guest usage for Guest {$session_id}, Context ID {$guest_context_table_id}. Error: " . $wpdb->last_error);
    }
}