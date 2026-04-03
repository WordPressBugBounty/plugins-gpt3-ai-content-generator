<?php
// File: classes/core/stream/formatter/fn-finish-request.php

namespace WPAICG\Core\Stream\Formatter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Finalizes the current SSE request using the best available runtime capability.
 *
 * @return void
 */
function finish_sse_request_logic(): void {
    $capabilities = get_sse_runtime_capabilities_logic();

    if ($capabilities['fastcgi_finish_available']) {
        fastcgi_finish_request();
        return;
    }

    if ($capabilities['litespeed_finish_available']) {
        litespeed_finish_request();
    }
}
