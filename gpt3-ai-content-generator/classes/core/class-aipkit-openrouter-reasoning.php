<?php

namespace WPAICG\Core;

use function WPAICG\Core\Providers\OpenRouter\Methods\normalize_reasoning_effort_logic;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Central OpenRouter reasoning-effort normalization for feature consumers.
 */
class AIPKit_OpenRouter_Reasoning
{
    /**
     * Maps the saved five-level UI value to the selected model's API effort.
     * Returns an empty string when the model has no effort control.
     *
     * @param mixed $effort Raw saved effort.
     */
    public static function normalize_effort_for_model(string $model, $effort): string
    {
        return normalize_reasoning_effort_logic($model, $effort);
    }
}
