<?php
// File: classes/core/stream/formatter/fn-apply-runtime-mitigations.php

namespace WPAICG\Core\Stream\Formatter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Applies capability-based mitigations that make SSE flushing more reliable.
 *
 * @return void
 */
function apply_sse_runtime_mitigations_logic(): void {
    $capabilities = get_sse_runtime_capabilities_logic();

    ignore_user_abort(true);

    if ($capabilities['session_active']) {
        session_write_close();
    }

    if ($capabilities['apache_setenv_available']) {
        @apache_setenv('no-gzip', '1');
        @apache_setenv('dont-vary', '1');
    }

    if ($capabilities['implicit_flush_available']) {
        @ob_implicit_flush(true);
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}
