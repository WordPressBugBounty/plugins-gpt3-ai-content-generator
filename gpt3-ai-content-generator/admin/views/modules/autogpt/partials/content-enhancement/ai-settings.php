<?php
/**
 * Partial: Content Enhancement Automated Task - AI Settings (Redesigned)
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_ai_provider_id = 'aipkit_task_ce_ai_provider';
$aipkit_ai_provider_name = 'ce_ai_provider';
$aipkit_ai_model_id = 'aipkit_task_ce_ai_model';
$aipkit_ai_model_name = 'ce_ai_model';
$aipkit_ai_temperature_id = 'aipkit_task_ce_ai_temperature';
$aipkit_ai_temperature_name = 'ce_ai_temperature';
$aipkit_ai_temperature_default = isset($cw_default_temperature) ? $cw_default_temperature : '1';
$aipkit_ai_reasoning_id = 'aipkit_task_ce_reasoning_effort';
$aipkit_ai_reasoning_name = 'ce_reasoning_effort';
$aipkit_ai_reasoning_wrapper_class = 'aipkit_task_ce_reasoning_effort_field';
$aipkit_ai_length_mode = 'max_tokens';
$aipkit_ai_length_input_id = 'aipkit_task_ce_content_max_tokens';
$aipkit_ai_length_input_name = 'ce_content_max_tokens';
$aipkit_ai_length_default = 'medium';
$aipkit_ai_length_short_tokens = 2000;
$aipkit_ai_length_medium_tokens = 4000;
$aipkit_ai_length_long_tokens = 6000;
$aipkit_ai_providers_for_select = isset($cw_providers_for_select) ? $cw_providers_for_select : [];
$aipkit_ai_provider_notice_target = 'aipkit_provider_notice_autogpt';

include __DIR__ . '/../shared/ai-settings.php';
