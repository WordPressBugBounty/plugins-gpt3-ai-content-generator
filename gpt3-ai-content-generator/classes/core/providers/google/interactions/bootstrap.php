<?php

namespace WPAICG\Core\Providers\Google\Interactions;

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_google_interactions_files = [
    'class-google-interactions-url-builder.php',
    'class-google-interactions-request-builder.php',
    'class-google-interactions-text-adapter.php',
    'class-google-interactions-image-adapter.php',
    'class-google-interactions-tts-adapter.php',
    'class-google-interactions-stt-adapter.php',
    'class-google-interactions-error-parser.php',
    'class-google-interactions-response-parser.php',
    'class-google-interactions-stream-parser.php',
    'class-google-model-capability-classifier.php',
    'class-google-interactions-client.php',
];

foreach ($aipkit_google_interactions_files as $aipkit_google_interactions_file) {
    require_once __DIR__ . '/' . $aipkit_google_interactions_file;
}

unset($aipkit_google_interactions_file, $aipkit_google_interactions_files);
