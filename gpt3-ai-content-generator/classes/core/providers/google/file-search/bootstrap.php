<?php

namespace WPAICG\Core\Providers\Google\FileSearch;

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_google_file_search_files = [
    'class-google-file-search-url-builder.php',
    'class-google-file-search-request-builder.php',
    'class-google-file-search-error-parser.php',
    'class-google-file-search-client.php',
];

foreach ($aipkit_google_file_search_files as $aipkit_google_file_search_file) {
    require_once __DIR__ . '/' . $aipkit_google_file_search_file;
}

unset($aipkit_google_file_search_file, $aipkit_google_file_search_files);
