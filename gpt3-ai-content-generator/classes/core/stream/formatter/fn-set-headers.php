<?php
// File: classes/core/stream/formatter/fn-set-headers.php

namespace WPAICG\Core\Stream\Formatter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Sets the required HTTP headers for an SSE response.
 *
 * @param \WPAICG\Core\Stream\Formatter\SSEResponseFormatter $formatterInstance The instance of the SSEResponseFormatter.
 * @return void
 */
function set_sse_headers_logic(\WPAICG\Core\Stream\Formatter\SSEResponseFormatter $formatterInstance): void {
    if ($formatterInstance->get_headers_sent_status() || headers_sent()) {
        return;
    }

    apply_sse_runtime_mitigations_logic();
    send_sse_response_headers_logic();
    $formatterInstance->set_headers_sent_status(true);
    send_sse_preamble_logic($formatterInstance);
}
