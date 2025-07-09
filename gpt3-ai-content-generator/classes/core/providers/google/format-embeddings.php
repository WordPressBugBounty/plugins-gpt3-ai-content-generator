<?php
// File: classes/core/providers/google/format-embeddings.php
// Status: NEW FILE

namespace WPAICG\Core\Providers\Google\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the format_embeddings static method of GooglePayloadFormatter.
 *
 * @param string|array $input The input text or array of texts.
 * @param array  $options Embedding options including 'model', 'taskType', 'outputDimensionality'.
 * @return array The formatted request body data.
 */
function format_embeddings_logic_for_payload_formatter($input, array $options): array {
    $text_to_embed = is_array($input) ? ($input[0] ?? '') : $input;

    $payload = [
        'model' => $options['model'], 
        'content' => [
            'parts' => [['text' => (string)$text_to_embed]]
        ]
    ];

    if (isset($options['taskType']) && is_string($options['taskType'])) {
        $payload['taskType'] = $options['taskType'];
    }
    if (isset($options['outputDimensionality']) && is_int($options['outputDimensionality'])) {
        $payload['outputDimensionality'] = $options['outputDimensionality'];
    }

    return $payload;
}