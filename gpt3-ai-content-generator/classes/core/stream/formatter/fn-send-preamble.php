<?php
// File: classes/core/stream/formatter/fn-send-preamble.php

namespace WPAICG\Core\Stream\Formatter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Sends a one-time SSE comment preamble to encourage small-chunk flushing.
 *
 * @param \WPAICG\Core\Stream\Formatter\SSEResponseFormatter $formatterInstance The instance of the SSEResponseFormatter.
 * @return void
 */
function send_sse_preamble_logic(\WPAICG\Core\Stream\Formatter\SSEResponseFormatter $formatterInstance): void {
    if ($formatterInstance->get_preamble_sent_status()) {
        return;
    }

    // Prime intermediaries with an SSE comment so subsequent small chunks flush promptly.
    send_raw_logic(':' . str_repeat(' ', 4096) . "\n\n");
    $formatterInstance->set_preamble_sent_status(true);
}
