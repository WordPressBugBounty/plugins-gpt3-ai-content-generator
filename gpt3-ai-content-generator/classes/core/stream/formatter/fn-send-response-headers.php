<?php
// File: classes/core/stream/formatter/fn-send-response-headers.php

namespace WPAICG\Core\Stream\Formatter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Emits the shared SSE response headers.
 *
 * @return void
 */
function send_sse_response_headers_logic(): void {
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, no-transform');
    header('Pragma: no-cache');
    header('Content-Type: text/event-stream; charset=utf-8');
    header('X-Accel-Buffering: no');
    header('Connection: keep-alive');
    // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- This is an SSE endpoint which requires a long-running script.
    set_time_limit(0);
}
