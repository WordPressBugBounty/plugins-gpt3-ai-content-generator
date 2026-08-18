<?php

namespace WPAICG\Core\Providers\Google\Files;

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_google_files_api_files = [
    'class-google-files-url-builder.php',
    'class-google-files-client.php',
    'class-google-files-context-token.php',
];

foreach ($aipkit_google_files_api_files as $aipkit_google_files_api_file) {
    require_once __DIR__ . '/' . $aipkit_google_files_api_file;
}

unset($aipkit_google_files_api_file, $aipkit_google_files_api_files);
