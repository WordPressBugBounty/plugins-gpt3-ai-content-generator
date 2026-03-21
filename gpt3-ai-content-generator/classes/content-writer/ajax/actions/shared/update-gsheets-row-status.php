<?php

namespace WPAICG\ContentWriter\Ajax\Actions\Shared;

use WPAICG\Lib\ContentWriter\AIPKit_Google_Sheets_Parser;
use WPAICG\Lib\Utils\AIPKit_Google_Credentials_Handler;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Updates the Google Sheets status column for Content Writer gsheets mode.
 *
 * @param array  $settings The current action payload or sanitized settings.
 * @param string $status_prefix Status prefix such as "Queued on" or "Processed on".
 * @return bool|WP_Error True on success or no-op, WP_Error on failure.
 */
function maybe_update_gsheets_row_status_logic(array $settings, string $status_prefix): bool|WP_Error
{
    $generation_mode = isset($settings['cw_generation_mode'])
        ? sanitize_key((string) $settings['cw_generation_mode'])
        : '';

    if ($generation_mode !== 'gsheets') {
        return true;
    }

    $sheet_id = isset($settings['gsheets_sheet_id'])
        ? sanitize_text_field((string) $settings['gsheets_sheet_id'])
        : '';
    $row_index = isset($settings['gsheets_row_index'])
        ? absint($settings['gsheets_row_index'])
        : 0;

    if ($sheet_id === '' || $row_index <= 0) {
        return new WP_Error(
            'missing_gsheets_status_context',
            __('Google Sheets status update is missing the sheet ID or row index.', 'gpt3-ai-content-generator')
        );
    }

    if (!class_exists(AIPKit_Google_Credentials_Handler::class)) {
        return new WP_Error(
            'gsheets_credentials_handler_missing',
            __('Google Sheets credentials handler is unavailable.', 'gpt3-ai-content-generator')
        );
    }

    $credentials = AIPKit_Google_Credentials_Handler::process_credentials($settings['gsheets_credentials'] ?? null);
    if (!is_array($credentials) || empty($credentials['private_key']) || empty($credentials['client_email'])) {
        return new WP_Error(
            'invalid_gsheets_status_credentials',
            __('Google Sheets status update is missing valid service account credentials.', 'gpt3-ai-content-generator')
        );
    }

    if (!class_exists(AIPKit_Google_Sheets_Parser::class)) {
        return new WP_Error(
            'gsheets_parser_missing',
            __('Google Sheets parser component is missing.', 'gpt3-ai-content-generator')
        );
    }

    try {
        $sheets_parser = new AIPKit_Google_Sheets_Parser($credentials);
        $status_text = trim($status_prefix) . ' ' . current_time('mysql');

        return $sheets_parser->update_row_status($sheet_id, $row_index, $status_text);
    } catch (\Exception $e) {
        return new WP_Error(
            'gsheets_status_update_exception',
            sprintf(
                /* translators: %s: Exception message. */
                __('Failed to update Google Sheets status: %s', 'gpt3-ai-content-generator'),
                $e->getMessage()
            )
        );
    }
}
