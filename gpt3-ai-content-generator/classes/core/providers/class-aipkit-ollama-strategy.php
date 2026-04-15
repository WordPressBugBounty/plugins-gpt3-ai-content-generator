<?php

namespace WPAICG\Core\Providers;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

// Backward-compatibility shim: delegate to new bootstrap which loads lib-based strategy.
// No class definitions here to avoid duplicate declarations.
$bootstrap = __DIR__ . '/ollama/bootstrap-provider-strategy.php';
if (file_exists($bootstrap)) {
    require_once $bootstrap;
}
