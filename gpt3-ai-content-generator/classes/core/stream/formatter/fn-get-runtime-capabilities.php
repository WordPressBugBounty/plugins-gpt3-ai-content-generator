<?php
// File: classes/core/stream/formatter/fn-get-runtime-capabilities.php

namespace WPAICG\Core\Stream\Formatter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Detects runtime capabilities that influence SSE buffering and shutdown behavior.
 *
 * @return array<string, bool|int>
 */
function get_sse_runtime_capabilities_logic(): array {
    return [
        'session_active'           => session_status() === PHP_SESSION_ACTIVE,
        'apache_setenv_available'  => function_exists('apache_setenv'),
        'implicit_flush_available' => function_exists('ob_implicit_flush'),
        'fastcgi_finish_available' => function_exists('fastcgi_finish_request'),
        'litespeed_finish_available' => function_exists('litespeed_finish_request'),
        'output_buffer_level'      => ob_get_level(),
    ];
}
