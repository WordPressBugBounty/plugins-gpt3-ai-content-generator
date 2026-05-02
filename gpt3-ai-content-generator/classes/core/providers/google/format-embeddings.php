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
    $texts_to_embed = [];
    if (is_array($input)) {
        foreach ($input as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $text = trim((string) $item);
            if ($text !== '') {
                $texts_to_embed[] = $text;
            }
        }
    } elseif (is_scalar($input)) {
        $text = trim((string) $input);
        if ($text !== '') {
            $texts_to_embed[] = $text;
        }
    }

    // Keep one empty part to preserve previous behavior for edge-case empty input.
    if (empty($texts_to_embed)) {
        $texts_to_embed[] = '';
    }

    $parts = [];
    foreach ($texts_to_embed as $text) {
        $parts[] = ['text' => $text];
    }

    // Google Embeddings expects the model name in the form "models/<model-id>" in the request body
    $model_for_body = isset($options['model']) ? (string) $options['model'] : '';
    if ($model_for_body !== '' && strpos($model_for_body, 'models/') !== 0) {
        $model_for_body = 'models/' . $model_for_body;
    }

    $request_options = [];

    if (isset($options['taskType']) && is_string($options['taskType'])) {
        $request_options['taskType'] = $options['taskType'];
    }
    if (isset($options['title']) && is_string($options['title'])) {
        $request_options['title'] = $options['title'];
    }
    if (isset($options['outputDimensionality']) && is_int($options['outputDimensionality'])) {
        $request_options['outputDimensionality'] = $options['outputDimensionality'];
    }

    if (count($texts_to_embed) > 1) {
        $requests = [];
        foreach ($texts_to_embed as $text) {
            $requests[] = array_merge([
                'model' => $model_for_body,
                'content' => [
                    'parts' => [
                        ['text' => $text],
                    ],
                ],
            ], $request_options);
        }

        return ['requests' => $requests];
    }

    return array_merge([
        'model' => $model_for_body,
        'content' => [
            'parts' => $parts
        ]
    ], $request_options);
}
