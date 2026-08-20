<?php

namespace WPAICG\Core\Providers\OpenAI;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalizes the OpenAI-compatible chat API mode.
 *
 * Missing and unknown values deliberately fall back to Responses API so
 * existing installations keep their current request contract.
 */
final class OpenAIApiMode
{
    public const RESPONSES = 'responses';
    public const CHAT_COMPLETIONS = 'chat_completions';

    public static function normalize($value): string
    {
        return is_string($value) && $value === self::CHAT_COMPLETIONS
            ? self::CHAT_COMPLETIONS
            : self::RESPONSES;
    }

    public static function is_chat_completions($value): bool
    {
        return self::normalize($value) === self::CHAT_COMPLETIONS;
    }
}
