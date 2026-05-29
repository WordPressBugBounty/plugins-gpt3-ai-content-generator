<?php
 namespace WPAICG\Core\Providers\OpenAI\Methods; if (!defined('ABSPATH')) { exit; } function format_moderation_logic_for_payload_formatter(string $text): array { return ['input' => $text]; }