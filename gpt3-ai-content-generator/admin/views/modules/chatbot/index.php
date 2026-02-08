<?php
/**
 * AIPKit Chatbot Module - Admin View
 *
 * Layout-only rebuild based on the provided reference UI.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use WPAICG\Chat\Storage\BotStorage;
use WPAICG\Chat\Storage\DefaultBotSetup;
use WPAICG\Chat\Storage\BotSettingsManager;
use WPAICG\Chat\Storage\AIPKit_Bot_Settings_Getter;
use WPAICG\Chat\Utils\AIPKit_SVG_Icons;
use WPAICG\aipkit_dashboard; // Required for addon status checks
use WPAICG\AIPKit_Providers;
use WPAICG\Vector\AIPKit_Vector_Store_Registry;

// Instantiate the storage classes
$bot_storage = new BotStorage();
$default_setup = new DefaultBotSetup();

// Fetch bot posts only to keep the initial module load lightweight.
$all_chatbots = $bot_storage->get_chatbots(false);

// These variables are defined by the AJAX loader and sanitized there.
$force_active_bot_id = isset($force_active_bot_id) ? intval($force_active_bot_id) : 0;
$force_active_tab = isset($force_active_tab) ? sanitize_key($force_active_tab) : '';

// Get the ID of the default bot
$default_bot_id = $default_setup->get_default_bot_id();

// Separate the default bot and sort the others alphabetically
$default_bot_post = null;
$other_bots_posts = [];
if (!empty($all_chatbots)) {
    foreach ($all_chatbots as $bot_post) {
        if ($bot_post->ID === $default_bot_id) {
            $default_bot_post = $bot_post;
        } else {
            $other_bots_posts[] = $bot_post;
        }
    }
    usort($other_bots_posts, function ($a, $b) {
        return strcmp($a->post_title, $b->post_title);
    });
}

// Combine all bots into one list for the dropdown
$all_bots_ordered_entries = [];
if ($default_bot_post) {
    $all_bots_ordered_entries[] = ['post' => $default_bot_post];
}
foreach ($other_bots_posts as $bot_post) {
    $all_bots_ordered_entries[] = ['post' => $bot_post];
}

// Determine the initial active bot
$initial_active_bot_id = 0;
if ($force_active_tab === 'create') {
    $initial_active_bot_id = 0;
} elseif ($force_active_bot_id > 0) {
    $initial_active_bot_id = $force_active_bot_id;
} elseif ($default_bot_post) {
    $initial_active_bot_id = $default_bot_post->ID;
} elseif (!empty($other_bots_posts)) {
    $initial_active_bot_id = $other_bots_posts[0]->ID;
}

// Find the active bot post
$active_bot_post = null;
if ($initial_active_bot_id) {
    foreach ($all_bots_ordered_entries as $bot_entry) {
        if ($bot_entry['post']->ID === $initial_active_bot_id) {
            $active_bot_post = $bot_entry['post'];
            break;
        }
    }
}

$active_bot_settings = [];
if ($active_bot_post && class_exists(AIPKit_Bot_Settings_Getter::class)) {
    $settings = AIPKit_Bot_Settings_Getter::get($active_bot_post->ID);
    if (!is_wp_error($settings)) {
        $active_bot_settings = $settings;
    }
}
$active_bot_instructions = $active_bot_settings['instructions'] ?? '';
$saved_theme = $active_bot_settings['theme'] ?? 'dark';
$aipkit_hide_custom_theme = false;
$available_themes = [
    'light'   => __('Light', 'gpt3-ai-content-generator'),
    'dark'    => __('Dark', 'gpt3-ai-content-generator'),
    'chatgpt' => __('ChatGPT', 'gpt3-ai-content-generator'),
];
if (!$aipkit_hide_custom_theme || $saved_theme === 'custom') {
    $available_themes['custom'] = __('Custom', 'gpt3-ai-content-generator');
}
$custom_theme_presets = class_exists(BotSettingsManager::class)
    ? BotSettingsManager::get_custom_theme_presets()
    : [];
$popup_enabled = $active_bot_settings['popup_enabled'] ?? '0';
$popup_enabled = in_array($popup_enabled, ['0', '1'], true) ? $popup_enabled : '0';
$site_wide_enabled = $active_bot_settings['site_wide_enabled'] ?? '0';
$site_wide_enabled = in_array($site_wide_enabled, ['0', '1'], true) ? $site_wide_enabled : '0';
$deploy_mode = ($popup_enabled === '1') ? 'popup' : 'inline';
$deploy_popup_scope = ($site_wide_enabled === '1') ? 'sitewide' : 'page';
$deploy_scope = 'site';
$is_pro_plan = class_exists('\\WPAICG\\aipkit_dashboard') && aipkit_dashboard::is_pro_plan();
$consent_feature_available = $is_pro_plan && class_exists('\\WPAICG\\Lib\\Addons\\AIPKit_Consent_Compliance');
$openai_moderation_available = $is_pro_plan && class_exists('\\WPAICG\\Lib\\Addons\\AIPKit_OpenAI_Moderation');
$embed_anywhere_active = $is_pro_plan;
$triggers_available = $is_pro_plan;
$embed_allowed_domains = $active_bot_settings['embed_allowed_domains'] ?? '';
$pricing_url = admin_url('admin.php?page=wpaicg-pricing');
$post_types_args = ['public' => true];
$all_selectable_post_types = get_post_types($post_types_args, 'objects');
$all_selectable_post_types = array_filter($all_selectable_post_types, function ($post_type_obj) {
    return $post_type_obj->name !== 'attachment';
});
$popup_position = $active_bot_settings['popup_position'] ?? 'bottom-right';
$popup_position = in_array($popup_position, ['bottom-right', 'bottom-left', 'top-right', 'top-left'], true)
    ? $popup_position
    : 'bottom-right';
$popup_delay = isset($active_bot_settings['popup_delay'])
    ? absint($active_bot_settings['popup_delay'])
    : BotSettingsManager::DEFAULT_POPUP_DELAY;
$popup_icon_type = $active_bot_settings['popup_icon_type'] ?? BotSettingsManager::DEFAULT_POPUP_ICON_TYPE;
$popup_icon_type = in_array($popup_icon_type, ['default', 'custom'], true)
    ? $popup_icon_type
    : BotSettingsManager::DEFAULT_POPUP_ICON_TYPE;
$popup_icon_style = $active_bot_settings['popup_icon_style'] ?? BotSettingsManager::DEFAULT_POPUP_ICON_STYLE;
$popup_icon_style = in_array($popup_icon_style, ['circle', 'square', 'none'], true)
    ? $popup_icon_style
    : BotSettingsManager::DEFAULT_POPUP_ICON_STYLE;
$popup_icon_value = $active_bot_settings['popup_icon_value'] ?? BotSettingsManager::DEFAULT_POPUP_ICON_VALUE;
$popup_icon_size = $active_bot_settings['popup_icon_size'] ?? BotSettingsManager::DEFAULT_POPUP_ICON_SIZE;
$allowed_icon_sizes = ['small', 'medium', 'large', 'xlarge'];
$popup_icon_size = in_array($popup_icon_size, $allowed_icon_sizes, true)
    ? $popup_icon_size
    : BotSettingsManager::DEFAULT_POPUP_ICON_SIZE;
$allowed_default_icons = ['chat-bubble', 'spark', 'openai', 'plus', 'question-mark'];
if ($popup_icon_type === 'default' && !in_array($popup_icon_value, $allowed_default_icons, true)) {
    $popup_icon_value = BotSettingsManager::DEFAULT_POPUP_ICON_VALUE;
}
$saved_header_avatar_url = $active_bot_settings['header_avatar_url'] ?? '';
$saved_header_avatar_type = $active_bot_settings['header_avatar_type'] ?? BotSettingsManager::DEFAULT_HEADER_AVATAR_TYPE;
if (!in_array($saved_header_avatar_type, ['default', 'custom'], true)) {
    $saved_header_avatar_type = $saved_header_avatar_url !== '' ? 'custom' : BotSettingsManager::DEFAULT_HEADER_AVATAR_TYPE;
}
$saved_header_avatar_value = $active_bot_settings['header_avatar_value'] ?? BotSettingsManager::DEFAULT_HEADER_AVATAR_VALUE;
if ($saved_header_avatar_type === 'custom') {
    if ($saved_header_avatar_url === '' && !empty($saved_header_avatar_value)) {
        $saved_header_avatar_url = $saved_header_avatar_value;
    }
} else {
    if (!in_array($saved_header_avatar_value, $allowed_default_icons, true)) {
        $saved_header_avatar_value = BotSettingsManager::DEFAULT_HEADER_AVATAR_VALUE;
    }
    $saved_header_avatar_url = '';
}
$saved_header_online_text = $active_bot_settings['header_online_text'] ?? __('Online', 'gpt3-ai-content-generator');
$popup_label_enabled = $active_bot_settings['popup_label_enabled'] ?? BotSettingsManager::DEFAULT_POPUP_LABEL_ENABLED;
$popup_label_enabled = in_array($popup_label_enabled, ['0', '1'], true)
    ? $popup_label_enabled
    : BotSettingsManager::DEFAULT_POPUP_LABEL_ENABLED;
$popup_label_text = $active_bot_settings['popup_label_text'] ?? '';
$popup_label_mode = $active_bot_settings['popup_label_mode'] ?? BotSettingsManager::DEFAULT_POPUP_LABEL_MODE;
$popup_label_mode = in_array($popup_label_mode, ['on_delay', 'until_open', 'until_dismissed', 'always'], true)
    ? $popup_label_mode
    : BotSettingsManager::DEFAULT_POPUP_LABEL_MODE;
$popup_label_delay_seconds = isset($active_bot_settings['popup_label_delay_seconds'])
    ? absint($active_bot_settings['popup_label_delay_seconds'])
    : BotSettingsManager::DEFAULT_POPUP_LABEL_DELAY_SECONDS;
$popup_label_auto_hide_seconds = isset($active_bot_settings['popup_label_auto_hide_seconds'])
    ? absint($active_bot_settings['popup_label_auto_hide_seconds'])
    : BotSettingsManager::DEFAULT_POPUP_LABEL_AUTO_HIDE_SECONDS;
$popup_label_dismissible = $active_bot_settings['popup_label_dismissible'] ?? BotSettingsManager::DEFAULT_POPUP_LABEL_DISMISSIBLE;
$popup_label_dismissible = in_array($popup_label_dismissible, ['0', '1'], true)
    ? $popup_label_dismissible
    : BotSettingsManager::DEFAULT_POPUP_LABEL_DISMISSIBLE;
$popup_label_frequency = $active_bot_settings['popup_label_frequency'] ?? BotSettingsManager::DEFAULT_POPUP_LABEL_FREQUENCY;
$popup_label_frequency = in_array($popup_label_frequency, ['once_per_visitor', 'once_per_session', 'always'], true)
    ? $popup_label_frequency
    : BotSettingsManager::DEFAULT_POPUP_LABEL_FREQUENCY;
$popup_label_show_on_mobile = $active_bot_settings['popup_label_show_on_mobile'] ?? BotSettingsManager::DEFAULT_POPUP_LABEL_SHOW_ON_MOBILE;
$popup_label_show_on_mobile = in_array($popup_label_show_on_mobile, ['0', '1'], true)
    ? $popup_label_show_on_mobile
    : BotSettingsManager::DEFAULT_POPUP_LABEL_SHOW_ON_MOBILE;
$popup_label_show_on_desktop = $active_bot_settings['popup_label_show_on_desktop'] ?? BotSettingsManager::DEFAULT_POPUP_LABEL_SHOW_ON_DESKTOP;
$popup_label_show_on_desktop = in_array($popup_label_show_on_desktop, ['0', '1'], true)
    ? $popup_label_show_on_desktop
    : BotSettingsManager::DEFAULT_POPUP_LABEL_SHOW_ON_DESKTOP;
$popup_label_version = $active_bot_settings['popup_label_version'] ?? '';
$popup_label_size = $active_bot_settings['popup_label_size'] ?? BotSettingsManager::DEFAULT_POPUP_LABEL_SIZE;
$popup_label_size = in_array($popup_label_size, $allowed_icon_sizes, true)
    ? $popup_label_size
    : BotSettingsManager::DEFAULT_POPUP_LABEL_SIZE;
$default_popup_icons = [];
if (class_exists(AIPKit_SVG_Icons::class)) {
    $default_popup_icons = [
        'chat-bubble' => AIPKit_SVG_Icons::get_chat_bubble_svg(),
        'spark' => AIPKit_SVG_Icons::get_spark_svg(),
        'openai' => AIPKit_SVG_Icons::get_openai_svg(),
        'plus' => AIPKit_SVG_Icons::get_plus_svg(),
        'question-mark' => AIPKit_SVG_Icons::get_question_mark_svg(),
    ];
}
$popup_icons = $default_popup_icons;

// Web & Grounding settings values (used in model settings sheet).
$current_provider_for_this_bot = $active_bot_settings['provider'] ?? 'OpenAI';
$openai_web_search_enabled_val = $active_bot_settings['openai_web_search_enabled']
    ?? BotSettingsManager::DEFAULT_OPENAI_WEB_SEARCH_ENABLED;
$openai_web_search_context_size_val = $active_bot_settings['openai_web_search_context_size']
    ?? BotSettingsManager::DEFAULT_OPENAI_WEB_SEARCH_CONTEXT_SIZE;
$openai_web_search_loc_type_val = $active_bot_settings['openai_web_search_loc_type']
    ?? BotSettingsManager::DEFAULT_OPENAI_WEB_SEARCH_LOC_TYPE;
$openai_web_search_loc_country_val = $active_bot_settings['openai_web_search_loc_country'] ?? '';
$openai_web_search_loc_city_val = $active_bot_settings['openai_web_search_loc_city'] ?? '';
$openai_web_search_loc_region_val = $active_bot_settings['openai_web_search_loc_region'] ?? '';
$openai_web_search_loc_timezone_val = $active_bot_settings['openai_web_search_loc_timezone'] ?? '';
$claude_web_search_enabled_val = $active_bot_settings['claude_web_search_enabled']
    ?? BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_ENABLED;
$claude_web_search_max_uses_val = isset($active_bot_settings['claude_web_search_max_uses'])
    ? absint($active_bot_settings['claude_web_search_max_uses'])
    : BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_MAX_USES;
$claude_web_search_max_uses_val = max(1, min($claude_web_search_max_uses_val, 20));
$claude_web_search_loc_type_val = $active_bot_settings['claude_web_search_loc_type']
    ?? BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_LOC_TYPE;
$claude_web_search_loc_country_val = $active_bot_settings['claude_web_search_loc_country'] ?? '';
$claude_web_search_loc_city_val = $active_bot_settings['claude_web_search_loc_city'] ?? '';
$claude_web_search_loc_region_val = $active_bot_settings['claude_web_search_loc_region'] ?? '';
$claude_web_search_loc_timezone_val = $active_bot_settings['claude_web_search_loc_timezone'] ?? '';
$claude_web_search_allowed_domains_val = $active_bot_settings['claude_web_search_allowed_domains'] ?? '';
$claude_web_search_blocked_domains_val = $active_bot_settings['claude_web_search_blocked_domains'] ?? '';
$claude_web_search_cache_ttl_val = $active_bot_settings['claude_web_search_cache_ttl']
    ?? BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_CACHE_TTL;
$openrouter_web_search_enabled_val = $active_bot_settings['openrouter_web_search_enabled']
    ?? BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_ENABLED;
$openrouter_web_search_engine_val = $active_bot_settings['openrouter_web_search_engine']
    ?? BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_ENGINE;
if (!in_array($openrouter_web_search_engine_val, ['auto', 'native', 'exa'], true)) {
    $openrouter_web_search_engine_val = BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_ENGINE;
}
$openrouter_web_search_max_results_val = isset($active_bot_settings['openrouter_web_search_max_results'])
    ? absint($active_bot_settings['openrouter_web_search_max_results'])
    : BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_MAX_RESULTS;
$openrouter_web_search_max_results_val = max(1, min($openrouter_web_search_max_results_val, 10));
$openrouter_web_search_search_prompt_val = $active_bot_settings['openrouter_web_search_search_prompt']
    ?? BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_SEARCH_PROMPT;
$web_toggle_default_on_val = $active_bot_settings['web_toggle_default_on']
    ?? BotSettingsManager::DEFAULT_WEB_TOGGLE_DEFAULT_ON;
$google_search_grounding_enabled_val = $active_bot_settings['google_search_grounding_enabled']
    ?? BotSettingsManager::DEFAULT_GOOGLE_SEARCH_GROUNDING_ENABLED;
$google_grounding_mode_val = $active_bot_settings['google_grounding_mode']
    ?? BotSettingsManager::DEFAULT_GOOGLE_GROUNDING_MODE;
$google_grounding_dynamic_threshold_val = isset($active_bot_settings['google_grounding_dynamic_threshold'])
    ? floatval($active_bot_settings['google_grounding_dynamic_threshold'])
    : BotSettingsManager::DEFAULT_GOOGLE_GROUNDING_DYNAMIC_THRESHOLD;
$google_grounding_dynamic_threshold_val = max(0.0, min($google_grounding_dynamic_threshold_val, 1.0));

// Conversations settings values (used in model settings sheet).
$saved_stream_enabled = $active_bot_settings['stream_enabled']
    ?? BotSettingsManager::DEFAULT_STREAM_ENABLED;
$saved_stream_enabled = in_array($saved_stream_enabled, ['0', '1'], true)
    ? $saved_stream_enabled
    : BotSettingsManager::DEFAULT_STREAM_ENABLED;
$openai_conversation_state_enabled_val = $active_bot_settings['openai_conversation_state_enabled']
    ?? BotSettingsManager::DEFAULT_OPENAI_CONVERSATION_STATE_ENABLED;
$openai_conversation_state_enabled_val = in_array($openai_conversation_state_enabled_val, ['0', '1'], true)
    ? $openai_conversation_state_enabled_val
    : BotSettingsManager::DEFAULT_OPENAI_CONVERSATION_STATE_ENABLED;
$saved_max_messages = isset($active_bot_settings['max_messages'])
    ? absint($active_bot_settings['max_messages'])
    : BotSettingsManager::DEFAULT_MAX_MESSAGES;
$saved_max_messages = max(1, min($saved_max_messages, 1024));
$enable_image_upload = $active_bot_settings['enable_image_upload']
    ?? BotSettingsManager::DEFAULT_ENABLE_IMAGE_UPLOAD;
$enable_image_upload = in_array($enable_image_upload, ['0', '1'], true)
    ? $enable_image_upload
    : BotSettingsManager::DEFAULT_ENABLE_IMAGE_UPLOAD;
$enable_vector_store = $active_bot_settings['enable_vector_store']
    ?? BotSettingsManager::DEFAULT_ENABLE_VECTOR_STORE;
$enable_vector_store = in_array($enable_vector_store, ['0', '1'], true)
    ? $enable_vector_store
    : BotSettingsManager::DEFAULT_ENABLE_VECTOR_STORE;
$enable_file_upload = $active_bot_settings['enable_file_upload']
    ?? BotSettingsManager::DEFAULT_ENABLE_FILE_UPLOAD;
$enable_file_upload = in_array($enable_file_upload, ['0', '1'], true)
    ? $enable_file_upload
    : BotSettingsManager::DEFAULT_ENABLE_FILE_UPLOAD;
$content_aware_enabled = $active_bot_settings['content_aware_enabled']
    ?? BotSettingsManager::DEFAULT_CONTENT_AWARE_ENABLED;
$content_aware_enabled = in_array($content_aware_enabled, ['0', '1'], true)
    ? $content_aware_enabled
    : BotSettingsManager::DEFAULT_CONTENT_AWARE_ENABLED;
$vector_store_provider = $active_bot_settings['vector_store_provider']
    ?? BotSettingsManager::DEFAULT_VECTOR_STORE_PROVIDER;
$allowed_vector_store_providers = ['openai', 'pinecone', 'qdrant', 'claude_files'];
if (!in_array($vector_store_provider, $allowed_vector_store_providers, true)) {
    $vector_store_provider = BotSettingsManager::DEFAULT_VECTOR_STORE_PROVIDER;
}
$openai_vector_store_ids_saved = [];
if (isset($active_bot_settings['openai_vector_store_ids'])) {
    if (is_array($active_bot_settings['openai_vector_store_ids'])) {
        $openai_vector_store_ids_saved = $active_bot_settings['openai_vector_store_ids'];
    } elseif (is_string($active_bot_settings['openai_vector_store_ids'])) {
        $decoded_ids = json_decode($active_bot_settings['openai_vector_store_ids'], true);
        if (is_array($decoded_ids)) {
            $openai_vector_store_ids_saved = $decoded_ids;
        }
    }
}
$pinecone_index_name = $active_bot_settings['pinecone_index_name'] ?? BotSettingsManager::DEFAULT_PINECONE_INDEX_NAME;
$vector_embedding_provider = $active_bot_settings['vector_embedding_provider'] ?? BotSettingsManager::DEFAULT_VECTOR_EMBEDDING_PROVIDER;
$allowed_embedding_providers = ['openai', 'google', 'azure', 'openrouter'];
if (!in_array($vector_embedding_provider, $allowed_embedding_providers, true)) {
    $vector_embedding_provider = BotSettingsManager::DEFAULT_VECTOR_EMBEDDING_PROVIDER;
}
$vector_embedding_model = $active_bot_settings['vector_embedding_model'] ?? BotSettingsManager::DEFAULT_VECTOR_EMBEDDING_MODEL;
$qdrant_collection_names = [];
if (!empty($active_bot_settings['qdrant_collection_names']) && is_array($active_bot_settings['qdrant_collection_names'])) {
    $qdrant_collection_names = $active_bot_settings['qdrant_collection_names'];
} elseif (!empty($active_bot_settings['qdrant_collection_name'])) {
    $qdrant_collection_names = [$active_bot_settings['qdrant_collection_name']];
}
$vector_store_top_k = isset($active_bot_settings['vector_store_top_k'])
    ? absint($active_bot_settings['vector_store_top_k'])
    : BotSettingsManager::DEFAULT_VECTOR_STORE_TOP_K;
$vector_store_top_k = max(1, min($vector_store_top_k, 20));
$vector_store_confidence_threshold = $active_bot_settings['vector_store_confidence_threshold']
    ?? BotSettingsManager::DEFAULT_VECTOR_STORE_CONFIDENCE_THRESHOLD;
$vector_store_confidence_threshold = max(0, min(absint($vector_store_confidence_threshold), 100));
$openai_vector_stores = [];
$pinecone_indexes = [];
$qdrant_collections = [];
$openai_embedding_models = [];
$google_embedding_models = [];
$azure_embedding_models = [];
$openrouter_embedding_models = [];
$openai_provider_data = [];
$pinecone_provider_data = [];
$qdrant_provider_data = [];
$google_provider_data = [];
$azure_provider_data = [];
$claude_provider_data = [];
$elevenlabs_provider_data = [];
if (class_exists(AIPKit_Vector_Store_Registry::class)) {
    $openai_vector_stores = AIPKit_Vector_Store_Registry::get_registered_stores_by_provider('OpenAI');
}
if (class_exists(AIPKit_Providers::class)) {
    $pinecone_indexes = AIPKit_Providers::get_pinecone_indexes();
    $qdrant_collections = AIPKit_Providers::get_qdrant_collections();
    $openai_embedding_models = AIPKit_Providers::get_openai_embedding_models();
    $google_embedding_models = AIPKit_Providers::get_google_embedding_models();
    $azure_embedding_models = AIPKit_Providers::get_azure_embedding_models();
    $openrouter_embedding_models = AIPKit_Providers::get_openrouter_embedding_models();
    $openai_provider_data = AIPKit_Providers::get_provider_data('OpenAI');
    $pinecone_provider_data = AIPKit_Providers::get_provider_data('Pinecone');
    $qdrant_provider_data = AIPKit_Providers::get_provider_data('Qdrant');
    $google_provider_data = AIPKit_Providers::get_provider_data('Google');
    $azure_provider_data = AIPKit_Providers::get_provider_data('Azure');
    $claude_provider_data = AIPKit_Providers::get_provider_data('Claude');
    $elevenlabs_provider_data = AIPKit_Providers::get_provider_data('ElevenLabs');
    $replicate_provider_data = AIPKit_Providers::get_provider_data('Replicate');
}
$openai_api_key = $openai_provider_data['api_key'] ?? '';
$pinecone_api_key = $pinecone_provider_data['api_key'] ?? '';
$qdrant_url = $qdrant_provider_data['url'] ?? '';
$qdrant_api_key = $qdrant_provider_data['api_key'] ?? '';
$google_api_key = $google_provider_data['api_key'] ?? '';
$azure_api_key = $azure_provider_data['api_key'] ?? '';
$claude_api_key = $claude_provider_data['api_key'] ?? '';
$elevenlabs_api_key = $elevenlabs_provider_data['api_key'] ?? '';
$replicate_api_key = $replicate_provider_data['api_key'] ?? '';
$image_triggers = $active_bot_settings['image_triggers']
    ?? BotSettingsManager::DEFAULT_IMAGE_TRIGGERS;
$chat_image_model_id = $active_bot_settings['chat_image_model_id']
    ?? BotSettingsManager::DEFAULT_CHAT_IMAGE_MODEL_ID;
$replicate_model_list = AIPKit_Providers::get_replicate_models();
$openrouter_image_model_list = AIPKit_Providers::get_openrouter_image_models();
$available_image_models = [
    'OpenAI' => [
        ['id' => 'gpt-image-1.5', 'name' => 'GPT Image 1.5'],
        ['id' => 'gpt-image-1', 'name' => 'GPT Image 1'],
        ['id' => 'gpt-image-1-mini', 'name' => 'GPT Image 1 mini'],
        ['id' => 'dall-e-3', 'name' => 'DALL-E 3'],
        ['id' => 'dall-e-2', 'name' => 'DALL-E 2'],
    ],
    'Azure' => AIPKit_Providers::get_azure_image_models(),
    'Google' => AIPKit_Providers::get_google_image_models(),
];
if (isset($openrouter_image_model_list) && is_array($openrouter_image_model_list) && !empty($openrouter_image_model_list)) {
    $available_image_models['OpenRouter'] = $openrouter_image_model_list;
}
if (isset($replicate_model_list) && is_array($replicate_model_list) && !empty($replicate_model_list)) {
    $available_image_models['Replicate'] = $replicate_model_list;
}
$reasoning_effort_val = $active_bot_settings['reasoning_effort']
    ?? BotSettingsManager::DEFAULT_REASONING_EFFORT;
$reasoning_effort_val = \WPAICG\Core\AIPKit_OpenAI_Reasoning::sanitize_effort($reasoning_effort_val);
$allowed_reasoning_effort = ['none', 'low', 'medium', 'high', 'xhigh'];
if (!in_array($reasoning_effort_val, $allowed_reasoning_effort, true)) {
    $reasoning_effort_val = BotSettingsManager::DEFAULT_REASONING_EFFORT;
}

// Audio settings values (used in audio flyout).
$enable_voice_input = $active_bot_settings['enable_voice_input']
    ?? BotSettingsManager::DEFAULT_ENABLE_VOICE_INPUT;
$enable_voice_input = in_array($enable_voice_input, ['0', '1'], true)
    ? $enable_voice_input
    : BotSettingsManager::DEFAULT_ENABLE_VOICE_INPUT;
$stt_provider = $active_bot_settings['stt_provider']
    ?? BotSettingsManager::DEFAULT_STT_PROVIDER;
$allowed_stt_providers = ['OpenAI', 'Azure'];
if (!in_array($stt_provider, $allowed_stt_providers, true)) {
    $stt_provider = BotSettingsManager::DEFAULT_STT_PROVIDER;
}
$stt_openai_model_id = $active_bot_settings['stt_openai_model_id']
    ?? BotSettingsManager::DEFAULT_STT_OPENAI_MODEL_ID;
$openai_stt_models = AIPKit_Providers::get_openai_stt_models();

$tts_enabled = $active_bot_settings['tts_enabled']
    ?? BotSettingsManager::DEFAULT_TTS_ENABLED;
$tts_enabled = in_array($tts_enabled, ['0', '1'], true)
    ? $tts_enabled
    : BotSettingsManager::DEFAULT_TTS_ENABLED;
$tts_provider = $active_bot_settings['tts_provider']
    ?? BotSettingsManager::DEFAULT_TTS_PROVIDER;
$tts_providers = ['Google', 'OpenAI', 'ElevenLabs'];
if (!in_array($tts_provider, $tts_providers, true)) {
    $tts_provider = BotSettingsManager::DEFAULT_TTS_PROVIDER;
}
$tts_google_voice_id = $active_bot_settings['tts_google_voice_id'] ?? '';
$tts_openai_voice_id = $active_bot_settings['tts_openai_voice_id'] ?? 'alloy';
$tts_openai_model_id = $active_bot_settings['tts_openai_model_id']
    ?? BotSettingsManager::DEFAULT_TTS_OPENAI_MODEL_ID;
$tts_elevenlabs_voice_id = $active_bot_settings['tts_elevenlabs_voice_id'] ?? '';
$tts_elevenlabs_model_id = $active_bot_settings['tts_elevenlabs_model_id']
    ?? BotSettingsManager::DEFAULT_TTS_ELEVENLABS_MODEL_ID;
$tts_auto_play = $active_bot_settings['tts_auto_play']
    ?? BotSettingsManager::DEFAULT_TTS_AUTO_PLAY;
$tts_auto_play = in_array($tts_auto_play, ['0', '1'], true)
    ? $tts_auto_play
    : BotSettingsManager::DEFAULT_TTS_AUTO_PLAY;

$google_tts_voices = class_exists('\\WPAICG\\Core\\Providers\\Google\\GoogleSettingsHandler')
    ? \WPAICG\Core\Providers\Google\GoogleSettingsHandler::get_synced_google_tts_voices()
    : [];
$elevenlabs_tts_voices = AIPKit_Providers::get_elevenlabs_voices();
$elevenlabs_tts_models = AIPKit_Providers::get_elevenlabs_models();
$openai_tts_models = AIPKit_Providers::get_openai_tts_models();
$openai_tts_voices = [
    ['id' => 'alloy', 'name' => 'Alloy'],
    ['id' => 'echo', 'name' => 'Echo'],
    ['id' => 'fable', 'name' => 'Fable'],
    ['id' => 'onyx', 'name' => 'Onyx'],
    ['id' => 'nova', 'name' => 'Nova'],
    ['id' => 'shimmer', 'name' => 'Shimmer'],
];

$enable_realtime_voice = $active_bot_settings['enable_realtime_voice']
    ?? BotSettingsManager::DEFAULT_ENABLE_REALTIME_VOICE;
$enable_realtime_voice = in_array($enable_realtime_voice, ['0', '1'], true)
    ? $enable_realtime_voice
    : BotSettingsManager::DEFAULT_ENABLE_REALTIME_VOICE;
$direct_voice_mode = $active_bot_settings['direct_voice_mode']
    ?? BotSettingsManager::DEFAULT_DIRECT_VOICE_MODE;
$direct_voice_mode = in_array($direct_voice_mode, ['0', '1'], true)
    ? $direct_voice_mode
    : BotSettingsManager::DEFAULT_DIRECT_VOICE_MODE;
$realtime_model = $active_bot_settings['realtime_model']
    ?? BotSettingsManager::DEFAULT_REALTIME_MODEL;
$realtime_voice = $active_bot_settings['realtime_voice']
    ?? BotSettingsManager::DEFAULT_REALTIME_VOICE;
$turn_detection = $active_bot_settings['turn_detection']
    ?? BotSettingsManager::DEFAULT_TURN_DETECTION;
$speed = isset($active_bot_settings['speed'])
    ? floatval($active_bot_settings['speed'])
    : BotSettingsManager::DEFAULT_SPEED;
$speed = max(0.25, min($speed, 1.5));
$input_audio_format = $active_bot_settings['input_audio_format']
    ?? BotSettingsManager::DEFAULT_INPUT_AUDIO_FORMAT;
$output_audio_format = $active_bot_settings['output_audio_format']
    ?? BotSettingsManager::DEFAULT_OUTPUT_AUDIO_FORMAT;
$input_audio_noise_reduction = $active_bot_settings['input_audio_noise_reduction']
    ?? BotSettingsManager::DEFAULT_INPUT_AUDIO_NOISE_REDUCTION;
$input_audio_noise_reduction = in_array($input_audio_noise_reduction, ['0', '1'], true)
    ? $input_audio_noise_reduction
    : BotSettingsManager::DEFAULT_INPUT_AUDIO_NOISE_REDUCTION;

$realtime_models = ['gpt-4o-realtime-preview', 'gpt-4o-mini-realtime'];
$realtime_voices = ['alloy', 'ash', 'ballad', 'coral', 'echo', 'fable', 'onyx', 'nova', 'shimmer', 'verse'];
$direct_voice_mode_disabled = !($popup_enabled === '1' && $enable_realtime_voice === '1');
$direct_voice_mode_tooltip = $direct_voice_mode_disabled
    ? __('Requires "Popup Enabled" (in Interface) and "Enable Realtime Voice Agent" to be active.', 'gpt3-ai-content-generator')
    : '';

// Provider/model data for AI selection.
$providers = ['OpenAI', 'Google', 'Claude', 'OpenRouter', 'Azure', 'Ollama', 'DeepSeek'];
$is_pro = class_exists('\\WPAICG\\aipkit_dashboard') && aipkit_dashboard::is_pro_plan();
$rt_disabled_by_plan = !$is_pro_plan;
$rt_controls_disabled = $rt_disabled_by_plan;
$rt_force_visible = $rt_controls_disabled;

$can_enable_file_upload = false;
$file_upload_disabled_reason = '';
$file_upload_tooltip_default = __('Allow users upload files and chat with them.', 'gpt3-ai-content-generator');
$file_upload_tooltip_upgrade = __('File upload is a paid feature. Please upgrade.', 'gpt3-ai-content-generator');
if (class_exists(aipkit_dashboard::class)) {
    if (!$is_pro_plan) {
        $file_upload_disabled_reason = $file_upload_tooltip_upgrade;
    } else {
        $can_enable_file_upload = true;
    }
} else {
    $file_upload_disabled_reason = __('Cannot determine Pro status.', 'gpt3-ai-content-generator');
}
$file_upload_toggle_value = ($can_enable_file_upload && $enable_file_upload === '1') ? '1' : '0';
$file_upload_tooltip = $can_enable_file_upload
    ? $file_upload_tooltip_default
    : $file_upload_disabled_reason;

$grouped_openai_models = get_option('aipkit_openai_model_list', []);
$openrouter_model_list = get_option('aipkit_openrouter_model_list', []);
$google_model_list = get_option('aipkit_google_model_list', []);
$azure_deployment_list = AIPKit_Providers::get_azure_deployments();
$claude_model_list = AIPKit_Providers::get_claude_models();
$deepseek_model_list = AIPKit_Providers::get_deepseek_models();
$ollama_model_list = AIPKit_Providers::get_ollama_models();

$saved_provider = $active_bot_settings['provider'] ?? 'OpenAI';
$saved_model = $active_bot_settings['model'] ?? '';
if (!in_array($saved_provider, $providers, true)) {
    $provider_map = [
        'openai' => 'OpenAI',
        'openrouter' => 'OpenRouter',
        'google' => 'Google',
        'azure' => 'Azure',
        'claude' => 'Claude',
        'deepseek' => 'DeepSeek',
        'ollama' => 'Ollama',
    ];
    $normalized_provider = $provider_map[strtolower((string) $saved_provider)] ?? '';
    $saved_provider = $normalized_provider ?: ($providers[0] ?? 'OpenAI');
}

// Preview placeholder content
$preview_placeholder_key = $active_bot_post ? 'previewLoading' : 'previewPlaceholderSelect';
$preview_placeholder_text = $active_bot_post
    ? __('Loading preview...', 'gpt3-ai-content-generator')
    : __('Select a bot to see the preview.', 'gpt3-ai-content-generator');

$is_default_active = ($active_bot_post && $default_bot_id && $active_bot_post->ID === $default_bot_id);
$rename_disabled_title = $is_default_active
    ? __('Default bot cannot be renamed.', 'gpt3-ai-content-generator')
    : __('Rename chatbot', 'gpt3-ai-content-generator');
// Chatolia notice
$chatolia_notice_dismissed = get_option('aipkit_chatolia_notice_dismissed', '0') === '1';
if (!$chatolia_notice_dismissed) {
    include __DIR__ . '/partials/chatolia-notice.php';
}

$aipkit_notice_id = 'aipkit_provider_notice_chatbot';
include WPAICG_PLUGIN_DIR . 'admin/views/shared/provider-key-notice.php';

?>

<div
    class="aipkit_chatbot_module_container aipkit_chatbot_builder"
    data-aipkit-chatbot-layout="next"
    data-active-bot-id="<?php echo esc_attr($initial_active_bot_id); ?>"
    data-default-bot-id="<?php echo esc_attr($default_bot_id); ?>"
    data-openai-api-key-set="<?php echo esc_attr(!empty($openai_api_key) ? 'true' : 'false'); ?>"
    data-pinecone-api-key-set="<?php echo esc_attr(!empty($pinecone_api_key) ? 'true' : 'false'); ?>"
    data-qdrant-api-key-set="<?php echo esc_attr(!empty($qdrant_api_key) ? 'true' : 'false'); ?>"
    data-qdrant-url-set="<?php echo esc_attr(!empty($qdrant_url) ? 'true' : 'false'); ?>"
    data-google-api-key-set="<?php echo esc_attr(!empty($google_api_key) ? 'true' : 'false'); ?>"
    data-azure-api-key-set="<?php echo esc_attr(!empty($azure_api_key) ? 'true' : 'false'); ?>"
    data-claude-api-key-set="<?php echo esc_attr(!empty($claude_api_key) ? 'true' : 'false'); ?>"
    data-model-settings-title="<?php esc_attr_e('Settings', 'gpt3-ai-content-generator'); ?>"
    data-model-settings-description="<?php esc_attr_e('Configure model settings and behavior for this chatbot.', 'gpt3-ai-content-generator'); ?>"
>
    <div class="aipkit_chatbot_builder_layout">
        <div class="aipkit_chatbot_builder_left">
            <div id="aipkit_chatbot_main_tab_content_container">
                <div class="aipkit_tab-content aipkit_active">
                    <div class="aipkit_chatbot-settings-area aipkit_builder_settings_area">
                        <form
                            class="aipkit_chatbot_settings_form"
                            data-bot-id="<?php echo esc_attr($initial_active_bot_id); ?>"
                            onsubmit="return false;"
                        >
                            <?php include WPAICG_PLUGIN_DIR . 'admin/views/shared/vector-store-nonce-fields.php'; ?>
                            <section class="aipkit_builder_card aipkit_builder_card-primary aipkit_builder_card--status">
                            <div class="aipkit_builder_field">
                                <div class="aipkit_builder_ai_model">
                                    <?php
                                    $bot_id = $initial_active_bot_id;
                                    $bot_settings = $active_bot_settings;
                                    $is_next_layout = true;
                                    include __DIR__ . '/partials/ai-config/provider-model.php';
                                    ?>
                                </div>
                            </div>

                            <div class="aipkit_builder_field">
                                <label for="aipkit_bot_<?php echo esc_attr($initial_active_bot_id); ?>_instructions" class="aipkit_builder_label">
                                    <?php esc_html_e('Instructions', 'gpt3-ai-content-generator'); ?>
                                </label>
                                <div class="aipkit_builder_textarea_wrap">
                                    <textarea
                                        id="aipkit_bot_<?php echo esc_attr($initial_active_bot_id); ?>_instructions"
                                        name="instructions"
                                        class="aipkit_builder_textarea aipkit_form-input"
                                        rows="5"
                                        placeholder="<?php esc_attr_e('e.g., You are a helpful AI Assistant. Please be friendly.', 'gpt3-ai-content-generator'); ?>"
                                    ><?php echo esc_textarea($active_bot_instructions); ?></textarea>
                                    <button
                                        type="button"
                                        class="aipkit_builder_icon_btn aipkit_builder_textarea_expand aipkit_builder_instructions_expand"
                                        aria-label="<?php esc_attr_e('Expand instructions editor', 'gpt3-ai-content-generator'); ?>"
                                    >
                                        <span class="dashicons dashicons-editor-expand"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="aipkit_builder_field aipkit_builder_field--themes">
                                <div class="aipkit_builder_theme_row" role="group" aria-label="<?php esc_attr_e('Themes', 'gpt3-ai-content-generator'); ?>">
                                    <button
                                        type="button"
                                        class="aipkit_builder_icon_btn aipkit_theme_scroll_btn aipkit_theme_scroll_btn--prev"
                                        aria-label="<?php esc_attr_e('Scroll themes left', 'gpt3-ai-content-generator'); ?>"
                                    >
                                        <span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
                                    </button>
                                    <div class="aipkit_builder_theme_row_content">
                                        <div class="aipkit_theme_choice_group" role="radiogroup" aria-label="<?php esc_attr_e('Theme', 'gpt3-ai-content-generator'); ?>">
                                            <?php foreach ($available_themes as $theme_key => $theme_name) : ?>
                                                <?php
                                                $theme_card_class = 'aipkit_theme_choice_card';
                                                if ($theme_key === 'custom') {
                                                    $theme_card_class .= ' aipkit_theme_choice_card--custom';
                                                }
                                                ?>
                                                <label class="<?php echo esc_attr($theme_card_class); ?>">
                                                    <input
                                                        type="radio"
                                                        name="theme"
                                                        value="<?php echo esc_attr($theme_key); ?>"
                                                        <?php checked($saved_theme, $theme_key); ?>
                                                        <?php echo ($aipkit_hide_custom_theme && $theme_key === 'custom') ? 'disabled' : ''; ?>
                                                    >
                                                    <span class="aipkit_theme_choice_content">
                                                        <span class="aipkit_theme_choice_preview aipkit_theme_choice_preview--<?php echo esc_attr($theme_key); ?>" aria-hidden="true"></span>
                                                        <span class="aipkit_theme_choice_label">
                                                            <?php echo esc_html($theme_name); ?>
                                                        </span>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if (!empty($custom_theme_presets)) : ?>
                                            <div class="aipkit_custom_theme_presets" data-bot-id="<?php echo esc_attr($initial_active_bot_id); ?>" role="group" aria-label="<?php esc_attr_e('Theme presets', 'gpt3-ai-content-generator'); ?>">
                                                <?php foreach ($custom_theme_presets as $preset) : ?>
                                                    <button
                                                        type="button"
                                                        class="aipkit_custom_theme_preset aipkit_custom_theme_preset--<?php echo esc_attr($preset['key']); ?>"
                                                        data-primary="<?php echo esc_attr($preset['primary']); ?>"
                                                        data-secondary="<?php echo esc_attr($preset['secondary']); ?>"
                                                        data-bot-id="<?php echo esc_attr($initial_active_bot_id); ?>"
                                                        aria-pressed="false"
                                                    >
                                                        <span class="aipkit_custom_theme_preset_swatch" aria-hidden="true"></span>
                                                        <span class="aipkit_custom_theme_preset_label">
                                                            <?php echo esc_html($preset['label']); ?>
                                                        </span>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button
                                        type="button"
                                        class="aipkit_builder_icon_btn aipkit_theme_scroll_btn aipkit_theme_scroll_btn--next"
                                        aria-label="<?php esc_attr_e('Scroll themes right', 'gpt3-ai-content-generator'); ?>"
                                    >
                                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="aipkit_builder_action_row aipkit_builder_primary_actions" aria-label="<?php esc_attr_e('Chatbot configuration areas', 'gpt3-ai-content-generator'); ?>">
                                <div class="aipkit_builder_action_group">
                                    <button
                                        type="button"
                                        class="aipkit_btn aipkit_btn-primary aipkit_builder_action_btn aipkit_cb_ai_settings_toggle"
                                    >
                                        <span class="dashicons dashicons-admin-generic"></span>
                                        <span><?php esc_html_e('Configure', 'gpt3-ai-content-generator'); ?></span>
                                    </button>
                                    <button
                                        type="button"
                                        class="aipkit_btn aipkit_btn-secondary aipkit_builder_action_btn aipkit_deploy_settings_trigger"
                                        aria-controls="aipkit_deploy_settings_popover"
                                        aria-expanded="false"
                                    >
                                        <span class="dashicons dashicons-cloud-upload"></span>
                                        <span><?php esc_html_e('Deploy', 'gpt3-ai-content-generator'); ?></span>
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section class="aipkit_builder_card aipkit_builder_card--training">
                            <div class="aipkit_builder_card_header">
                                <h3 class="aipkit_builder_card_title"><?php esc_html_e('Training', 'gpt3-ai-content-generator'); ?></h3>
                                <div class="aipkit_builder_meta aipkit_training_meta">
                                    <span class="aipkit_training_status" id="aipkit_training_status" aria-live="polite"></span>
                                </div>
                            </div>

                            <div class="aipkit_builder_card_body">
                                <div class="aipkit_builder_tabs aipkit_builder_tabs--training" role="tablist" aria-label="<?php esc_attr_e('Training sources', 'gpt3-ai-content-generator'); ?>" data-aipkit-tabs="training">
                                    <button type="button" class="aipkit_builder_tab is-active" role="tab" aria-selected="true" data-aipkit-tab="qa">
                                        <?php esc_html_e('Q&A', 'gpt3-ai-content-generator'); ?>
                                    </button>
                                    <button type="button" class="aipkit_builder_tab" role="tab" aria-selected="false" data-aipkit-tab="text">
                                        <?php esc_html_e('Text', 'gpt3-ai-content-generator'); ?>
                                    </button>
                                    <button type="button" class="aipkit_builder_tab" role="tab" aria-selected="false" data-aipkit-tab="files">
                                        <?php esc_html_e('Files', 'gpt3-ai-content-generator'); ?>
                                    </button>
                                    <button type="button" class="aipkit_builder_tab" role="tab" aria-selected="false" data-aipkit-tab="website">
                                        <?php esc_html_e('Website', 'gpt3-ai-content-generator'); ?>
                                    </button>
                                </div>

                                <div class="aipkit_builder_tab_panels aipkit_builder_tab_panels--training">
                                    <div class="aipkit_builder_tab_panel is-active" data-aipkit-panel="qa">
                                        <div class="aipkit_builder_training_qa">
                                            <div class="aipkit_training_field">
                                                <textarea
                                                    id="aipkit_training_qa_question"
                                                    class="aipkit_builder_textarea aipkit_training_textarea"
                                                    rows="1"
                                                    placeholder="<?php esc_attr_e('Question: What is your refund policy?', 'gpt3-ai-content-generator'); ?>"
                                                ></textarea>
                                            </div>
                                            <div class="aipkit_training_field">
                                                <textarea
                                                    id="aipkit_training_qa_answer"
                                                    class="aipkit_builder_textarea aipkit_training_textarea"
                                                    rows="1"
                                                    placeholder="<?php esc_attr_e('Answer: We offer refunds within 30 days of purchase.', 'gpt3-ai-content-generator'); ?>"
                                                ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="aipkit_builder_tab_panel" data-aipkit-panel="text" hidden>
                                        <div class="aipkit_training_field">
                                            <textarea
                                                id="aipkit_training_text_input"
                                                name="training_text"
                                                class="aipkit_builder_textarea aipkit_training_textarea aipkit_training_text_input"
                                                rows="4"
                                                placeholder="<?php esc_attr_e('Add training text...', 'gpt3-ai-content-generator'); ?>"
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div class="aipkit_builder_tab_panel" data-aipkit-panel="files" hidden>
                                        <div class="aipkit_training_field">
                                            <div class="aipkit_builder_dropzone aipkit_training_dropzone">
                                                <div class="aipkit_builder_dropzone_inner">
                                                    <?php if ( $is_pro_plan ) : ?>
                                                        <input
                                                            id="aipkit_training_files_input"
                                                            class="aipkit_training_files_input"
                                                            type="file"
                                                            multiple
                                                            accept=".pdf,.docx,.txt,.md,.csv,.json"
                                                            hidden
                                                        >
                                                        <button
                                                            type="button"
                                                            class="aipkit_btn aipkit_btn-secondary aipkit_builder_action_btn aipkit_training_files_button"
                                                        >
                                                            <?php esc_html_e('Choose files', 'gpt3-ai-content-generator'); ?>
                                                        </button>
                                                    <?php else : ?>
                                                        <a
                                                            class="aipkit_btn aipkit_btn-primary aipkit_builder_action_btn aipkit_training_files_button"
                                                            href="<?php echo esc_url($pricing_url); ?>"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                        >
                                                            <?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                    <p class="aipkit_builder_help_text">
                                                        <?php esc_html_e('Supported: pdf, docx, txt, md, csv, json', 'gpt3-ai-content-generator'); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="aipkit_training_file_list"
                                            id="aipkit_training_file_list"
                                            data-upgrade-url="<?php echo esc_url($pricing_url); ?>"
                                        ></div>
                                    </div>
                                    <div class="aipkit_builder_tab_panel" data-aipkit-panel="website" hidden>
                                        <div class="aipkit_training_website">
                                            <div class="aipkit_training_site_row">
                                                <span class="aipkit_training_site_label"><?php esc_html_e('Mode', 'gpt3-ai-content-generator'); ?></span>
                                                <div class="aipkit_training_site_toggle">
                                                    <label class="aipkit_training_site_option">
                                                        <input type="radio" name="aipkit_wp_content_mode" id="aipkit_wp_content_mode_bulk" value="bulk" checked>
                                                        <?php esc_html_e('All', 'gpt3-ai-content-generator'); ?>
                                                    </label>
                                                    <label class="aipkit_training_site_option">
                                                        <input type="radio" name="aipkit_wp_content_mode" id="aipkit_wp_content_mode_specific" value="specific">
                                                        <?php esc_html_e('Specific', 'gpt3-ai-content-generator'); ?>
                                                    </label>
                                                </div>
                                                <span class="aipkit_training_site_divider" aria-hidden="true">|</span>
                                                <div class="aipkit_training_site_group aipkit_training_site_group--status">
                                                    <span class="aipkit_training_site_label"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></span>
                                                    <div id="aipkit_wp_content_status_wrap_bulk" class="aipkit_training_site_status_wrap">
                                                        <select id="aipkit_vs_wp_content_status" class="aipkit_form-input aipkit_training_site_select">
                                                            <option value="publish"><?php esc_html_e('Published', 'gpt3-ai-content-generator'); ?></option>
                                                            <option value="draft"><?php esc_html_e('Draft', 'gpt3-ai-content-generator'); ?></option>
                                                            <option value="any"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                                        </select>
                                                    </div>
                                                    <div id="aipkit_wp_content_status_wrap_specific" class="aipkit_training_site_status_wrap" hidden>
                                                        <select id="aipkit_vs_wp_content_status_specific" class="aipkit_form-input aipkit_training_site_select">
                                                            <option value="publish"><?php esc_html_e('Published', 'gpt3-ai-content-generator'); ?></option>
                                                            <option value="draft"><?php esc_html_e('Draft', 'gpt3-ai-content-generator'); ?></option>
                                                            <option value="any"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <span class="aipkit_training_site_divider" aria-hidden="true">|</span>
                                                <div id="aipkit_wp_content_bulk_panel" class="aipkit_training_site_group">
                                                    <span class="aipkit_training_site_label"><?php esc_html_e('Types', 'gpt3-ai-content-generator'); ?></span>
                                                    <div
                                                        class="aipkit_training_site_dropdown"
                                                        data-aipkit-training-types="bulk"
                                                        data-placeholder="<?php echo esc_attr__('Select types', 'gpt3-ai-content-generator'); ?>"
                                                        data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                                                    >
                                                        <button
                                                            type="button"
                                                            class="aipkit_training_site_dropdown_btn"
                                                            aria-expanded="false"
                                                            aria-controls="aipkit_training_types_menu_bulk"
                                                        >
                                                            <span class="aipkit_training_site_dropdown_label">
                                                                <?php esc_html_e('Select types', 'gpt3-ai-content-generator'); ?>
                                                            </span>
                                                        </button>
                                                        <div
                                                            id="aipkit_training_types_menu_bulk"
                                                            class="aipkit_training_site_dropdown_panel"
                                                            role="menu"
                                                            hidden
                                                        >
                                                            <div id="aipkit_vs_wp_types_checkboxes" class="aipkit_training_site_checks aipkit_training_site_checks--dropdown">
                                                                <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                                                    <label class="aipkit_training_site_check" data-ptype="<?php echo esc_attr($post_type_slug); ?>">
                                                                        <input type="checkbox" class="aipkit_wp_type_cb" value="<?php echo esc_attr($post_type_slug); ?>" <?php checked(in_array($post_type_slug, ['post', 'page'], true)); ?> />
                                                                        <span class="aipkit_training_site_check_label"><?php echo esc_html($post_type_obj->label); ?></span>
                                                                        <span class="aipkit_count_badge" data-count="-1"></span>
                                                                    </label>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <select id="aipkit_vs_wp_content_post_types" class="aipkit_training_site_hidden_select" multiple size="3">
                                                        <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                                            <option value="<?php echo esc_attr($post_type_slug); ?>" <?php selected(in_array($post_type_slug, ['post', 'page'], true)); ?>>
                                                                <?php echo esc_html($post_type_obj->label); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div id="aipkit_wp_content_specific_types_panel" class="aipkit_training_site_group" hidden>
                                                    <span class="aipkit_training_site_label"><?php esc_html_e('Types', 'gpt3-ai-content-generator'); ?></span>
                                                    <div
                                                        class="aipkit_training_site_dropdown"
                                                        data-aipkit-training-types="specific"
                                                        data-placeholder="<?php echo esc_attr__('Select types', 'gpt3-ai-content-generator'); ?>"
                                                        data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                                                    >
                                                        <button
                                                            type="button"
                                                            class="aipkit_training_site_dropdown_btn"
                                                            aria-expanded="false"
                                                            aria-controls="aipkit_training_types_menu_specific"
                                                        >
                                                            <span class="aipkit_training_site_dropdown_label">
                                                                <?php esc_html_e('Select types', 'gpt3-ai-content-generator'); ?>
                                                            </span>
                                                        </button>
                                                        <div
                                                            id="aipkit_training_types_menu_specific"
                                                            class="aipkit_training_site_dropdown_panel"
                                                            role="menu"
                                                            hidden
                                                        >
                                                            <div id="aipkit_vs_wp_types_checkboxes_specific" class="aipkit_training_site_checks aipkit_training_site_checks--dropdown">
                                                                <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                                                    <label class="aipkit_training_site_check" data-ptype="<?php echo esc_attr($post_type_slug); ?>">
                                                                        <input type="checkbox" class="aipkit_wp_type_cb_specific" value="<?php echo esc_attr($post_type_slug); ?>" <?php checked(in_array($post_type_slug, ['post', 'page'], true)); ?> />
                                                                        <span class="aipkit_training_site_check_label"><?php echo esc_html($post_type_obj->label); ?></span>
                                                                        <span class="aipkit_count_badge" data-count="-1"></span>
                                                                    </label>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <select id="aipkit_vs_wp_content_post_types_specific" class="aipkit_training_site_hidden_select" multiple size="3">
                                                        <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                                            <option value="<?php echo esc_attr($post_type_slug); ?>" <?php selected(in_array($post_type_slug, ['post', 'page'], true)); ?>>
                                                                <?php echo esc_html($post_type_obj->label); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <input type="hidden" id="aipkit_vs_wp_content_mode" value="bulk" />
                                            </div>

                                            <div id="aipkit_wp_content_bulk_hint" class="aipkit_training_site_hint">
                                                <p class="aipkit_builder_help_text">
                                                    <?php esc_html_e('Select the content you want to add to your bot, then hit Train to start.', 'gpt3-ai-content-generator'); ?>
                                                </p>
                                            </div>

                                            <div id="aipkit_background_indexing_confirm" class="aipkit_inline_confirm" hidden>
                                                <div class="aipkit_inline_confirm_content">
                                                    <p id="aipkit_background_indexing_message" class="aipkit_builder_help_text"></p>
                                                    <div class="aipkit_inline_confirm_actions">
                                                        <button type="button" id="aipkit_background_indexing_yes" class="aipkit_btn aipkit_btn-primary aipkit_btn-sm">
                                                            <?php esc_html_e('Yes, run in background', 'gpt3-ai-content-generator'); ?>
                                                        </button>
                                                        <button type="button" id="aipkit_background_indexing_no" class="aipkit_btn aipkit_btn-secondary aipkit_btn-sm">
                                                            <?php esc_html_e('No, run now', 'gpt3-ai-content-generator'); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="aipkit_wp_content_specific_panel" class="aipkit_training_site_panel" hidden>
                                                <div id="aipkit_vs_wp_content_list_area" class="aipkit_training_site_list">
                                                    <p class="aipkit_builder_help_text">
                                                        <?php esc_html_e('Select criteria to load content.', 'gpt3-ai-content-generator'); ?>
                                                    </p>
                                                </div>
                                                <div id="aipkit_vs_wp_content_pagination" class="aipkit_training_site_pagination"></div>
                                            </div>

                                            <div id="aipkit_vs_wp_content_messages_area" class="aipkit_form-help aipkit_training_site_status" aria-live="polite"></div>
                                            <select id="aipkit_vs_global_target_select" class="aipkit_training_site_target_select" aria-hidden="true" tabindex="-1">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="aipkit_training_footer">
                                    <div class="aipkit_builder_action_row aipkit_training_action_row">
                                        <div class="aipkit_builder_action_group aipkit_training_primary_actions">
                                            <button
                                                type="button"
                                                class="aipkit_btn aipkit_btn-primary aipkit_builder_action_btn aipkit_training_action_btn"
                                                data-training-action="add"
                                            >
                                                <?php esc_html_e('Train', 'gpt3-ai-content-generator'); ?>
                                            </button>
                                            <button
                                                type="button"
                                                class="aipkit_btn aipkit_btn-secondary aipkit_builder_action_btn aipkit_training_stop_btn"
                                                hidden
                                            >
                                                <?php esc_html_e('Stop', 'gpt3-ai-content-generator'); ?>
                                            </button>
                                        </div>
                                        <div class="aipkit_builder_action_group aipkit_training_sources_row">
                                            <button
                                                type="button"
                                                class="aipkit_training_sources_btn aipkit_builder_sheet_trigger"
                                                data-base-label="<?php echo esc_attr__('Sources', 'gpt3-ai-content-generator'); ?>"
                                                data-sheet-title="<?php echo esc_attr__('Sources', 'gpt3-ai-content-generator'); ?>"
                                                data-sheet-description="<?php echo esc_attr__('Browse and manage training sources for this chatbot.', 'gpt3-ai-content-generator'); ?>"
                                                data-sheet-content="sources"
                                            >
                                                <span class="aipkit_training_sources_label">
                                                    <?php echo esc_html__('Sources', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                                <span class="aipkit_training_sources_count" aria-hidden="true">0</span>
                                                <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </section>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="aipkit_chatbot-preview-column aipkit_chatbot_builder_right">
            <div class="aipkit_builder_preview_frame">
                <div id="aipkit_admin_chat_preview_container">
                    <p class="aipkit_preview_placeholder" data-key="<?php echo esc_attr($preview_placeholder_key); ?>">
                        <?php echo esc_html($preview_placeholder_text); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php if ($active_bot_post) : ?>
        <div
            class="aipkit_popover_hint_flyout"
            id="aipkit_popup_hint_flyout"
            aria-hidden="true"
            role="dialog"
        >
            <div class="aipkit_popover_flyout_header">
                <span class="aipkit_popover_flyout_title">
                    <?php esc_html_e('Launcher hint', 'gpt3-ai-content-generator'); ?>
                </span>
                <button
                    type="button"
                    class="aipkit_popover_flyout_close aipkit_popup_hint_flyout_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit_popover_flyout_body aipkit_popover_hint_body">
                <div class="aipkit_popover_option_row aipkit_popup_hint_extra" <?php echo ($popup_label_enabled === '1') ? '' : 'hidden'; ?>>
                    <div class="aipkit_popover_option_main">
                        <span
                            class="aipkit_popover_option_label"
                            tabindex="0"
                            data-tooltip="<?php echo esc_attr__('Users can manually hide the hint.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Dismissible', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <label class="aipkit_switch">
                            <input
                                type="checkbox"
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_dismissible_deploy"
                                name="popup_label_dismissible"
                                class="aipkit_toggle_switch"
                                value="1"
                                <?php checked($popup_label_dismissible, '1'); ?>
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
                <div class="aipkit_popover_option_row aipkit_popup_hint_extra" <?php echo ($popup_label_enabled === '1') ? '' : 'hidden'; ?>>
                    <div class="aipkit_popover_option_main">
                        <span
                            class="aipkit_popover_option_label"
                            tabindex="0"
                            data-tooltip="<?php echo esc_attr__('Display the hint on desktop screens.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Show on Desktop', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <label class="aipkit_switch">
                            <input
                                type="checkbox"
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_show_on_desktop_deploy"
                                name="popup_label_show_on_desktop"
                                class="aipkit_toggle_switch"
                                value="1"
                                <?php checked($popup_label_show_on_desktop, '1'); ?>
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
                <div class="aipkit_popover_option_row aipkit_popup_hint_extra" <?php echo ($popup_label_enabled === '1') ? '' : 'hidden'; ?>>
                    <div class="aipkit_popover_option_main">
                        <span
                            class="aipkit_popover_option_label"
                            tabindex="0"
                            data-tooltip="<?php echo esc_attr__('Display the hint on mobile screens.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Show on Mobile', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <label class="aipkit_switch">
                            <input
                                type="checkbox"
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_show_on_mobile_deploy"
                                name="popup_label_show_on_mobile"
                                class="aipkit_toggle_switch"
                                value="1"
                                <?php checked($popup_label_show_on_mobile, '1'); ?>
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
                <div class="aipkit_popup_hint_conditional_row" <?php echo ($popup_label_enabled === '1') ? '' : 'hidden'; ?>>
                    <div class="aipkit_popover_option_row">
                        <div class="aipkit_popover_option_main">
                            <span
                                class="aipkit_popover_option_label"
                                tabindex="0"
                                data-tooltip="<?php echo esc_attr__('Plain text only; keep it short (1–60 chars).', 'gpt3-ai-content-generator'); ?>"
                            >
                                <?php esc_html_e('Hint Text', 'gpt3-ai-content-generator'); ?>
                            </span>
                            <input
                                type="text"
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_text_deploy"
                                name="popup_label_text"
                                class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                                value="<?php echo esc_attr($popup_label_text); ?>"
                                maxlength="60"
                                placeholder="<?php esc_attr_e('Need help? Ask me!', 'gpt3-ai-content-generator'); ?>"
                            >
                        </div>
                    </div>
                    <div class="aipkit_popover_option_row">
                        <div class="aipkit_popover_option_main">
                            <span
                                class="aipkit_popover_option_label"
                                tabindex="0"
                                data-tooltip="<?php echo esc_attr__('Choose when the hint appears and persists.', 'gpt3-ai-content-generator'); ?>"
                            >
                                <?php esc_html_e('Show Mode', 'gpt3-ai-content-generator'); ?>
                            </span>
                            <select
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_mode_deploy"
                                name="popup_label_mode"
                                class="aipkit_popover_option_select"
                            >
                                <option value="on_delay" <?php selected($popup_label_mode, 'on_delay'); ?>><?php esc_html_e('On delay', 'gpt3-ai-content-generator'); ?></option>
                                <option value="always" <?php selected($popup_label_mode, 'always'); ?>><?php esc_html_e('Always (immediate)', 'gpt3-ai-content-generator'); ?></option>
                                <option value="until_open" <?php selected($popup_label_mode, 'until_open'); ?>><?php esc_html_e('Until chat is opened', 'gpt3-ai-content-generator'); ?></option>
                                <option value="until_dismissed" <?php selected($popup_label_mode, 'until_dismissed'); ?>><?php esc_html_e('Until hint is dismissed', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="aipkit_popover_option_row">
                        <div class="aipkit_popover_option_main">
                            <span
                                class="aipkit_popover_option_label"
                                tabindex="0"
                                data-tooltip="<?php echo esc_attr__('Controls hint font size and padding.', 'gpt3-ai-content-generator'); ?>"
                            >
                                <?php esc_html_e('Hint Size', 'gpt3-ai-content-generator'); ?>
                            </span>
                            <select
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_size_deploy"
                                name="popup_label_size"
                                class="aipkit_popover_option_select"
                            >
                                <option value="small" <?php selected($popup_label_size, 'small'); ?>><?php esc_html_e('Small', 'gpt3-ai-content-generator'); ?></option>
                                <option value="medium" <?php selected($popup_label_size, 'medium'); ?>><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                                <option value="large" <?php selected($popup_label_size, 'large'); ?>><?php esc_html_e('Large', 'gpt3-ai-content-generator'); ?></option>
                                <option value="xlarge" <?php selected($popup_label_size, 'xlarge'); ?>><?php esc_html_e('Extra Large', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="aipkit_popover_option_row">
                        <div class="aipkit_popover_option_main">
                            <span
                                class="aipkit_popover_option_label"
                                tabindex="0"
                                data-tooltip="<?php echo esc_attr__('Time to wait before showing. 0 = immediate.', 'gpt3-ai-content-generator'); ?>"
                            >
                                <?php esc_html_e('Delay (sec)', 'gpt3-ai-content-generator'); ?>
                            </span>
                            <input
                                type="number"
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_delay_seconds_deploy"
                                name="popup_label_delay_seconds"
                                class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--compact"
                                min="0"
                                step="1"
                                value="<?php echo esc_attr($popup_label_delay_seconds); ?>"
                            >
                        </div>
                    </div>
                    <div class="aipkit_popover_option_row">
                        <div class="aipkit_popover_option_main">
                            <span
                                class="aipkit_popover_option_label"
                                tabindex="0"
                                data-tooltip="<?php echo esc_attr__('0 disables auto-hide.', 'gpt3-ai-content-generator'); ?>"
                            >
                                <?php esc_html_e('Auto-hide (sec)', 'gpt3-ai-content-generator'); ?>
                            </span>
                            <input
                                type="number"
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_auto_hide_seconds_deploy"
                                name="popup_label_auto_hide_seconds"
                                class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--compact"
                                min="0"
                                step="1"
                                value="<?php echo esc_attr($popup_label_auto_hide_seconds); ?>"
                            >
                        </div>
                    </div>
                    <div class="aipkit_popover_option_row">
                        <div class="aipkit_popover_option_main">
                            <span
                                class="aipkit_popover_option_label"
                                tabindex="0"
                                data-tooltip="<?php echo esc_attr__('Controls persistence after seen/dismissed.', 'gpt3-ai-content-generator'); ?>"
                            >
                                <?php esc_html_e('Frequency', 'gpt3-ai-content-generator'); ?>
                            </span>
                            <select
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_frequency_deploy"
                                name="popup_label_frequency"
                                class="aipkit_popover_option_select"
                            >
                                <option value="once_per_visitor" <?php selected($popup_label_frequency, 'once_per_visitor'); ?>><?php esc_html_e('Once per visitor', 'gpt3-ai-content-generator'); ?></option>
                                <option value="once_per_session" <?php selected($popup_label_frequency, 'once_per_session'); ?>><?php esc_html_e('Once per session', 'gpt3-ai-content-generator'); ?></option>
                                <option value="always" <?php selected($popup_label_frequency, 'always'); ?>><?php esc_html_e('Always', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="aipkit_popover_option_row">
                        <div class="aipkit_popover_option_main">
                            <span
                                class="aipkit_popover_option_label"
                                tabindex="0"
                                data-tooltip="<?php echo esc_attr__('Change to re-show the hint for everyone.', 'gpt3-ai-content-generator'); ?>"
                            >
                                <?php esc_html_e('Version', 'gpt3-ai-content-generator'); ?>
                            </span>
                            <input
                                type="text"
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_version_deploy"
                                name="popup_label_version"
                                class="aipkit_popover_option_input aipkit_popover_option_input--framed"
                                value="<?php echo esc_attr($popup_label_version); ?>"
                                placeholder="v1"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="aipkit_popover_flyout_footer">
                <span class="aipkit_popover_flyout_footer_text">
                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/chat#popup-mode'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($active_bot_post) : ?>
        <div
            class="aipkit_popover_starters_flyout"
            id="aipkit_starters_flyout"
            aria-hidden="true"
            role="dialog"
        >
        <div class="aipkit_popover_flyout_header">
            <span class="aipkit_popover_flyout_title">
                <?php esc_html_e('Suggested prompts', 'gpt3-ai-content-generator'); ?>
            </span>
            <button
                type="button"
                class="aipkit_popover_flyout_close aipkit_starters_flyout_close"
                aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit_popover_flyout_body aipkit_popover_starters_body">
            <?php
            $bot_id = $initial_active_bot_id;
            $bot_settings = $active_bot_settings;
            $conversation_starters = $bot_settings['conversation_starters'] ?? [];
            $conversation_starters_text = implode("\n", $conversation_starters);
            ?>
            <div class="aipkit_popover_options_list">
                <div class="aipkit_popover_option_row">
                    <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                        <label
                            class="aipkit_popover_option_label"
                            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_conversation_starters"
                            data-tooltip="<?php echo esc_attr__('Enter one prompt per line. Users can click them to start a conversation. Defaults are used if empty.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Starter prompts (max 6)', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <textarea
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_conversation_starters"
                            name="conversation_starters"
                            class="aipkit_popover_option_textarea"
                            rows="4"
                        ><?php echo esc_textarea($conversation_starters_text); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="aipkit_popover_flyout_footer">
            <span class="aipkit_popover_flyout_footer_text">
                <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
            </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/Appearance#conversation-starters'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
            </a>
        </div>
        </div>
    <?php endif; ?>
    <div
        class="aipkit-modal-overlay aipkit_builder_new_bot_modal"
        id="aipkit_builder_new_bot_modal"
        aria-hidden="true"
    >
        <div
            class="aipkit-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="aipkit_builder_new_bot_title"
        >
            <div class="aipkit-modal-header">
                <h3 class="aipkit-modal-title" id="aipkit_builder_new_bot_title">
                    <?php esc_html_e('Create New Bot', 'gpt3-ai-content-generator'); ?>
                </h3>
                <button
                    type="button"
                    class="aipkit-modal-close-btn aipkit_builder_new_bot_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    &times;
                </button>
            </div>
            <div class="aipkit-modal-body" id="chatbot-create-new-content">
                <div class="aipkit_builder_field">
                    <label for="aipkit_new_bot_name" class="aipkit_builder_label">
                        <?php esc_html_e('Bot name', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_new_bot_name"
                        class="aipkit_builder_input"
                        placeholder="<?php esc_attr_e('e.g., Support Assistant', 'gpt3-ai-content-generator'); ?>"
                    >
                </div>
                <div class="aipkit_builder_action_row">
                    <button
                        type="button"
                        id="aipkit_create_new_bot_btn"
                        class="aipkit_btn aipkit_btn-primary aipkit_builder_action_btn"
                    >
                        <span class="aipkit_btn-text"><?php esc_html_e('Create Bot', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_spinner"></span>
                    </button>
                </div>
                <div id="aipkit_create_bot_message" class="aipkit_form-help"></div>
            </div>
        </div>
    </div>

    <div
        class="aipkit-modal-overlay aipkit_builder_rename_bot_modal"
        id="aipkit_builder_rename_bot_modal"
        aria-hidden="true"
    >
        <div
            class="aipkit-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="aipkit_builder_rename_bot_title"
        >
            <div class="aipkit-modal-header">
                <h3 class="aipkit-modal-title" id="aipkit_builder_rename_bot_title">
                    <?php esc_html_e('Rename Bot', 'gpt3-ai-content-generator'); ?>
                </h3>
                <button
                    type="button"
                    class="aipkit-modal-close-btn aipkit_builder_rename_bot_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    &times;
                </button>
            </div>
            <div class="aipkit-modal-body">
                <div class="aipkit_builder_field">
                    <label for="aipkit_rename_bot_name" class="aipkit_builder_label">
                        <?php esc_html_e('Bot name', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_rename_bot_name"
                        class="aipkit_builder_input"
                        placeholder="<?php esc_attr_e('Enter a new name', 'gpt3-ai-content-generator'); ?>"
                    >
                </div>
                <div class="aipkit_builder_action_row">
                    <button
                        type="button"
                        id="aipkit_rename_bot_btn"
                        class="aipkit_btn aipkit_btn-primary aipkit_builder_action_btn"
                    >
                        <span class="aipkit_btn-text"><?php esc_html_e('Save Name', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_spinner"></span>
                    </button>
                </div>
                <div id="aipkit_rename_bot_message" class="aipkit_form-help"></div>
            </div>
        </div>
    </div>

    <div
        class="aipkit-modal-overlay aipkit_builder_instructions_modal"
        id="aipkit_builder_instructions_modal"
        aria-hidden="true"
    >
        <div
            class="aipkit-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="aipkit_builder_instructions_title"
            aria-describedby="aipkit_builder_instructions_description"
        >
            <div class="aipkit-modal-header">
                <div>
                    <h3 class="aipkit-modal-title" id="aipkit_builder_instructions_title">
                        <?php esc_html_e('Agent Instructions', 'gpt3-ai-content-generator'); ?>
                    </h3>
                    <p class="aipkit_builder_modal_subtitle" id="aipkit_builder_instructions_description">
                        <?php esc_html_e('Define how your agent should behave. Changes are saved automatically when you close this dialog.', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>
                <button
                    type="button"
                    class="aipkit-modal-close-btn aipkit_builder_instructions_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit-modal-body">
                <div class="aipkit_builder_field">
                    <textarea
                        id="aipkit_bot_<?php echo esc_attr($initial_active_bot_id); ?>_instructions_modal"
                        class="aipkit_builder_textarea aipkit_builder_textarea_large aipkit_builder_instructions_modal_textarea"
                        rows="14"
                        aria-label="<?php esc_attr_e('Agent instructions', 'gpt3-ai-content-generator'); ?>"
                    ></textarea>
                </div>
                <div class="aipkit_builder_modal_meta">
                    <span class="aipkit_builder_char_count aipkit_builder_instructions_count">
                        <?php esc_html_e('0 characters', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <span class="aipkit_builder_key_hint">
                        <?php esc_html_e('Press ESC to close', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div
        class="aipkit_builder_web_settings_modal aipkit_popover_web_flyout"
        id="aipkit_builder_web_settings_modal"
        aria-hidden="true"
    >
        <div
            class="aipkit-modal-content"
            role="dialog"
            aria-modal="false"
            aria-labelledby="aipkit_builder_web_settings_title"
        >
        <div class="aipkit_popover_flyout_header">
            <span class="aipkit_popover_flyout_title" id="aipkit_builder_web_settings_title">
                <?php esc_html_e('Web search', 'gpt3-ai-content-generator'); ?>
            </span>
            <button
                type="button"
                class="aipkit_popover_flyout_close aipkit_builder_web_settings_close"
                aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit_popover_flyout_body aipkit_popover_web_body">
            <?php if ($active_bot_post) : ?>
                <?php
                $bot_id = $initial_active_bot_id;
                $bot_settings = $active_bot_settings;
                ?>
                <?php include __DIR__ . '/partials/ai-config/web-settings-flyout.php'; ?>
            <?php else : ?>
                <p class="aipkit_builder_help_text">
                    <?php esc_html_e('Select a bot to configure web search options.', 'gpt3-ai-content-generator'); ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="aipkit_popover_flyout_footer">
            <span class="aipkit_popover_flyout_footer_text">
                <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
            </span>
            <a
                class="aipkit_popover_flyout_footer_link"
                href="<?php echo esc_url('https://docs.aipower.org/docs/ai-configuration#web-search'); ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
            </a>
        </div>
        </div>
    </div>

    <div
        class="aipkit_builder_sheet_overlay"
        id="aipkit_builder_sheet"
        aria-hidden="true"
    >
        <div
            class="aipkit_builder_sheet_panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="aipkit_builder_sheet_title"
            aria-describedby="aipkit_builder_sheet_description"
        >
            <div class="aipkit_builder_sheet_header">
                <div>
                    <div class="aipkit_builder_sheet_title_row">
                        <h3 class="aipkit_builder_sheet_title" id="aipkit_builder_sheet_title">
                            <?php esc_html_e('Sheet', 'gpt3-ai-content-generator'); ?>
                        </h3>
                        <span class="aipkit_popover_status_inline aipkit_triggers_status" aria-live="polite"></span>
                    </div>
                    <p class="aipkit_builder_sheet_description" id="aipkit_builder_sheet_description">
                        <?php esc_html_e('Settings will appear here.', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>
                <button
                    type="button"
                    class="aipkit_builder_sheet_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit_builder_sheet_body">
                <div class="aipkit_builder_sheet_section" data-sheet="placeholder">
                    <p class="aipkit_builder_help_text">
                        <?php esc_html_e('This panel will contain the selected settings section.', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>
                <div class="aipkit_builder_sheet_section" data-sheet="triggers" hidden>
                    <?php if ($active_bot_post) : ?>
                        <?php
                        $bot_id = $initial_active_bot_id;
                        $triggers_json = $active_bot_settings['triggers_json'] ?? '[]';
                        ?>
                        <?php if ($triggers_available) : ?>
                            <?php
                            $trigger_builder_view_path = defined('WPAICG_LIB_DIR')
                                ? WPAICG_LIB_DIR . 'views/chatbot/partials/triggers/trigger-builder-main.php'
                                : '';
                            if (!empty($trigger_builder_view_path) && file_exists($trigger_builder_view_path)) {
                                include $trigger_builder_view_path;
                            } else {
                                echo '<p class="aipkit_builder_help_text">' . esc_html__('Rules builder UI is not available.', 'gpt3-ai-content-generator') . '</p>';
                            }
                            ?>
                            <textarea
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_triggers_json"
                                name="triggers_json"
                                class="aipkit_trigger_hidden_textarea"
                                aria-hidden="true"
                                tabindex="-1"
                            ><?php echo esc_textarea($triggers_json); ?></textarea>
                            <p class="aipkit_builder_help_text">
                                <?php esc_html_e('Use the UI above to configure rules.', 'gpt3-ai-content-generator'); ?>
                                <a href="<?php echo esc_url('https://docs.aipower.org/docs/triggers'); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e('Learn More', 'gpt3-ai-content-generator'); ?>
                                </a>
                            </p>
                        <?php else : ?>
                            <div class="aipkit_popover_options_list">
                                <div class="aipkit_popover_option_row aipkit_popover_option_row--notice">
                                    <div class="aipkit_popover_option_main">
                                        <span class="aipkit_popover_option_notice">
                                            <?php esc_html_e('Rules are available on Pro plans.', 'gpt3-ai-content-generator'); ?>
                                        </span>
                                        <a
                                            class="aipkit_popover_upgrade_link"
                                            href="<?php echo esc_url($pricing_url); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <?php esc_html_e('Upgrade', 'gpt3-ai-content-generator'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <p class="aipkit_builder_help_text">
                            <?php esc_html_e('Select a bot to configure rules.', 'gpt3-ai-content-generator'); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="aipkit_builder_sheet_section" data-sheet="sources" hidden>
                    <?php if ($active_bot_post) : ?>
                        <div class="aipkit_sources_meta">
                            <span class="aipkit_sources_meta_label"><?php esc_html_e('Provider:', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_sources_meta_value" id="aipkit_sources_provider_label">—</span>
                            <span class="aipkit_sources_meta_label"><?php esc_html_e('Store:', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_sources_meta_value" id="aipkit_sources_store_label">—</span>
                        </div>
                        <div class="aipkit_sources_toolbar">
                            <div class="aipkit_sources_toolbar_group">
                                <input
                                    type="search"
                                    class="aipkit_popover_option_input aipkit_sources_search_input"
                                    placeholder="<?php esc_attr_e('Search sources', 'gpt3-ai-content-generator'); ?>"
                                >
                            </div>
                            <div class="aipkit_sources_toolbar_group aipkit_sources_toolbar_group--right">
                                <select class="aipkit_popover_select aipkit_sources_filter_select">
                                    <option value=""><?php esc_html_e('All statuses', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="indexed"><?php esc_html_e('Trained', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="processing"><?php esc_html_e('Processing', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="failed"><?php esc_html_e('Failed', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                                <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_sources_refresh_btn">
                                    <?php esc_html_e('Refresh', 'gpt3-ai-content-generator'); ?>
                                </button>
                            </div>
                        </div>
                        <p id="aipkit_sources_status" class="aipkit_form-help"></p>
                        <div class="aipkit_data-table aipkit_sources_table">
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Time', 'gpt3-ai-content-generator'); ?></th>
                                        <th><?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?></th>
                                        <th><?php esc_html_e('Source', 'gpt3-ai-content-generator'); ?></th>
                                        <th><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></th>
                                        <th class="aipkit_actions_cell_header"><?php esc_html_e('Actions', 'gpt3-ai-content-generator'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="aipkit_sources_table_body">
                                    <tr>
                                        <td colspan="5" class="aipkit_text-center">
                                            <?php esc_html_e('Select a knowledge base to view sources.', 'gpt3-ai-content-generator'); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="aipkit_sources_pagination" class="aipkit_logs_pagination_container"></div>
                    <?php else : ?>
                        <p class="aipkit_builder_help_text">
                            <?php esc_html_e('Select a bot to manage sources.', 'gpt3-ai-content-generator'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div
        class="aipkit-modal-overlay aipkit_builder_sources_editor_modal"
        id="aipkit_sources_editor_modal"
        aria-hidden="true"
    >
        <div
            class="aipkit-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="aipkit_sources_editor_title"
            aria-describedby="aipkit_sources_editor_description"
        >
            <div class="aipkit-modal-header">
                <div>
                    <h3 class="aipkit-modal-title" id="aipkit_sources_editor_title">
                        <?php esc_html_e('Edit source', 'gpt3-ai-content-generator'); ?>
                    </h3>
                    <p class="aipkit_builder_modal_subtitle" id="aipkit_sources_editor_description">
                        <?php esc_html_e('Update the source text and save to retrain this entry.', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>
                <button
                    type="button"
                    class="aipkit-modal-close-btn aipkit_sources_editor_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit-modal-body">
                <div class="aipkit_builder_field">
                    <textarea
                        class="aipkit_builder_textarea aipkit_builder_textarea_large aipkit_sources_editor_textarea"
                        rows="10"
                        aria-label="<?php esc_attr_e('Source text', 'gpt3-ai-content-generator'); ?>"
                    ></textarea>
                </div>
                <div class="aipkit_builder_action_row aipkit_sources_editor_actions">
                    <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_sources_editor_cancel">
                        <?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?>
                    </button>
                    <button type="button" class="aipkit_btn aipkit_btn-primary aipkit_sources_editor_save">
                        <?php esc_html_e('Save & retrain', 'gpt3-ai-content-generator'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        class="aipkit-modal-overlay aipkit_builder_sources_delete_modal"
        id="aipkit_sources_delete_modal"
        aria-hidden="true"
    >
        <div
            class="aipkit-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="aipkit_sources_delete_title"
            aria-describedby="aipkit_sources_delete_description"
        >
            <div class="aipkit-modal-header">
                <div>
                    <h3 class="aipkit-modal-title" id="aipkit_sources_delete_title">
                        <?php esc_html_e('Delete source', 'gpt3-ai-content-generator'); ?>
                    </h3>
                    <p class="aipkit_builder_modal_subtitle" id="aipkit_sources_delete_description">
                        <?php esc_html_e('This cannot be undone. The source will be removed from your knowledge base.', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>
                <button
                    type="button"
                    class="aipkit-modal-close-btn aipkit_sources_delete_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit-modal-body">
                <div class="aipkit_builder_action_row">
                    <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_sources_delete_cancel">
                        <?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?>
                    </button>
                    <button type="button" class="aipkit_btn aipkit_btn-danger aipkit_sources_delete_confirm">
                        <?php esc_html_e('Delete', 'gpt3-ai-content-generator'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        class="aipkit_builder_audio_settings_modal aipkit_popover_audio_flyout"
        id="aipkit_builder_audio_settings_modal"
        aria-hidden="true"
    >
        <div
            class="aipkit-modal-content"
            role="dialog"
            aria-modal="false"
            aria-labelledby="aipkit_builder_audio_settings_title"
        >
            <div class="aipkit_popover_flyout_header">
                <div class="aipkit_popover_flyout_title_wrap">
                    <span class="aipkit_popover_flyout_title" id="aipkit_builder_audio_settings_title">
                        <?php esc_html_e('Audio', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <span class="aipkit_popover_status_inline aipkit_tts_sync_status" aria-live="polite"></span>
                </div>
                <button
                    type="button"
                    class="aipkit_popover_flyout_close aipkit_builder_audio_settings_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit_popover_flyout_body aipkit_popover_audio_body">
                <?php if ($active_bot_post) : ?>
                    <?php
                    $bot_id = $initial_active_bot_id;
                    $bot_settings = $active_bot_settings;
                    ?>
                    <?php include __DIR__ . '/partials/ai-config/audio-settings-flyout.php'; ?>
                <?php else : ?>
                    <p class="aipkit_builder_help_text">
                        <?php esc_html_e('Select a bot to view audio settings.', 'gpt3-ai-content-generator'); ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="aipkit_popover_flyout_footer">
                <span class="aipkit_popover_flyout_footer_text">
                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/voice-features'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                </a>
            </div>
        </div>
    </div>

    <div
        class="aipkit_model_settings_popover"
        id="aipkit_model_settings_popover"
        aria-hidden="true"
        data-title-root="<?php esc_attr_e('Settings', 'gpt3-ai-content-generator'); ?>"
        data-title-appearance="<?php esc_attr_e('Interface', 'gpt3-ai-content-generator'); ?>"
        data-title-behavior="<?php esc_attr_e('Behavior', 'gpt3-ai-content-generator'); ?>"
        data-title-context="<?php esc_attr_e('Context', 'gpt3-ai-content-generator'); ?>"
        data-title-tools="<?php esc_attr_e('Tools', 'gpt3-ai-content-generator'); ?>"
        data-title-safety="<?php esc_attr_e('Safety', 'gpt3-ai-content-generator'); ?>"
        data-title-limits="<?php esc_attr_e('Limits', 'gpt3-ai-content-generator'); ?>"
    >
        <div
            class="aipkit_model_settings_popover_panel aipkit_model_settings_popover_panel--allow-overflow"
            role="dialog"
            aria-modal="false"
            aria-labelledby="aipkit_model_settings_popover_title"
        >
            <div class="aipkit_model_settings_popover_header">
                <div class="aipkit_model_settings_popover_header_start">
                    <button
                        type="button"
                        class="aipkit_model_settings_popover_back"
                        aria-label="<?php esc_attr_e('Back', 'gpt3-ai-content-generator'); ?>"
                        hidden
                    >
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <span class="aipkit_model_settings_popover_title" id="aipkit_model_settings_popover_title">
                        <?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <span class="aipkit_popover_status_inline aipkit_model_sync_status" aria-live="polite"></span>
                </div>
                <div class="aipkit_model_settings_popover_header_end">
                    <button
                        type="button"
                        class="aipkit_model_settings_popover_close"
                        aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                    >
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            </div>
            <div class="aipkit_model_settings_popover_body">
                <?php if ($active_bot_post) : ?>
                    <?php
                    $bot_id = $initial_active_bot_id;
                    $bot_settings = $active_bot_settings;
                    ?>
                    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="root">
                        <!-- Options List -->
                        <div class="aipkit_popover_options_list aipkit_popover_options_list--settings-root">
                            <div class="aipkit_popover_option_group">
                                <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                    <button
                                        type="button"
                                        class="aipkit_popover_option_nav aipkit_style_settings_trigger"
                                        data-aipkit-panel-target="appearance"
                                    >
                                        <span class="aipkit_popover_option_label">
                                            <span class="aipkit_popover_option_icon dashicons dashicons-admin-appearance" aria-hidden="true"></span>
                                            <span class="aipkit_popover_option_label_content">
                                                <span class="aipkit_popover_option_label_text">
                                                    <?php esc_html_e('Interface', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                                <span class="aipkit_popover_option_hint">
                                                    <?php esc_html_e('UI essentials', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </span>
                                    </button>
                                </div>
                                <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                    <button
                                        type="button"
                                        class="aipkit_popover_option_nav aipkit_style_settings_trigger"
                                        data-aipkit-panel-target="behavior"
                                    >
                                        <span class="aipkit_popover_option_label">
                                            <span class="aipkit_popover_option_icon dashicons dashicons-admin-settings" aria-hidden="true"></span>
                                            <span class="aipkit_popover_option_label_content">
                                                <span class="aipkit_popover_option_label_text">
                                                    <?php esc_html_e('Behavior', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                                <span class="aipkit_popover_option_hint">
                                                    <?php esc_html_e('Response settings', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </span>
                                    </button>
                                </div>
                                <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                    <button
                                        type="button"
                                        class="aipkit_popover_option_nav aipkit_style_settings_trigger"
                                        data-aipkit-panel-target="context"
                                    >
                                        <span class="aipkit_popover_option_label">
                                            <span class="aipkit_popover_option_icon dashicons dashicons-database" aria-hidden="true"></span>
                                            <span class="aipkit_popover_option_label_content">
                                                <span class="aipkit_popover_option_label_text">
                                                    <?php esc_html_e('Context', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                                <span class="aipkit_popover_option_hint">
                                                    <?php esc_html_e('Knowledge and data sources', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <div class="aipkit_popover_option_group">
                                <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                    <button
                                        type="button"
                                        class="aipkit_popover_option_nav aipkit_style_settings_trigger"
                                        data-aipkit-panel-target="tools"
                                    >
                                        <span class="aipkit_popover_option_label">
                                            <span class="aipkit_popover_option_icon dashicons dashicons-admin-tools" aria-hidden="true"></span>
                                            <span class="aipkit_popover_option_label_content">
                                                <span class="aipkit_popover_option_label_text">
                                                    <?php esc_html_e('Tools', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                                <span class="aipkit_popover_option_hint">
                                                    <?php esc_html_e('Uploads, web, image, audio', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </span>
                                    </button>
                                </div>
                                <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                    <button
                                        type="button"
                                        class="aipkit_popover_option_nav aipkit_builder_sheet_trigger"
                                        data-sheet-title="<?php esc_attr_e('Rules', 'gpt3-ai-content-generator'); ?>"
                                        data-sheet-description="<?php esc_attr_e('Configure rules and automation behaviors.', 'gpt3-ai-content-generator'); ?>"
                                        data-sheet-content="triggers"
                                    >
                                        <span class="aipkit_popover_option_label">
                                            <span class="aipkit_popover_option_icon dashicons dashicons-controls-repeat" aria-hidden="true"></span>
                                            <span class="aipkit_popover_option_label_content">
                                                <span class="aipkit_popover_option_label_text">
                                                    <?php esc_html_e('Rules', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                                <span class="aipkit_popover_option_hint">
                                                    <?php esc_html_e('Triggers & Automation', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </span>
                                    </button>
                                </div>
                                <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                    <button
                                        type="button"
                                        class="aipkit_popover_option_nav aipkit_style_settings_trigger"
                                        data-aipkit-panel-target="safety"
                                    >
                                        <span class="aipkit_popover_option_label">
                                            <span class="aipkit_popover_option_icon dashicons dashicons-shield" aria-hidden="true"></span>
                                            <span class="aipkit_popover_option_label_content">
                                                <span class="aipkit_popover_option_label_text">
                                                    <?php esc_html_e('Safety', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                                <span class="aipkit_popover_option_hint">
                                                    <?php esc_html_e('Moderation and privacy', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </span>
                                    </button>
                                </div>
                                <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                    <button
                                        type="button"
                                        class="aipkit_popover_option_nav aipkit_style_settings_trigger"
                                        data-aipkit-panel-target="limits"
                                    >
                                        <span class="aipkit_popover_option_label">
                                            <span class="aipkit_popover_option_icon dashicons dashicons-chart-bar" aria-hidden="true"></span>
                                            <span class="aipkit_popover_option_label_content">
                                                <span class="aipkit_popover_option_label_text">
                                                    <?php esc_html_e('Limits', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                                <span class="aipkit_popover_option_hint">
                                                    <?php esc_html_e('Tokens and usage', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="appearance" hidden>
                        <?php include __DIR__ . '/partials/ai-config/appearance-settings.php'; ?>
                    </div>
                    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="behavior" hidden>
                        <?php include __DIR__ . '/partials/ai-config/behavior-settings.php'; ?>
                    </div>
                    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="context" hidden>
                        <?php include __DIR__ . '/partials/ai-config/context-settings.php'; ?>
                    </div>
                    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="tools" hidden>
                        <?php include __DIR__ . '/partials/ai-config/tools-settings.php'; ?>
                    </div>
                    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="safety" hidden>
                        <?php include __DIR__ . '/partials/ai-config/safety-settings.php'; ?>
                    </div>
                    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="limits" hidden>
                        <?php include __DIR__ . '/partials/ai-config/limits-settings.php'; ?>
                    </div>

                <?php else : ?>
                    <p class="aipkit_builder_help_text">
                        <?php esc_html_e('Select a bot to view model settings.', 'gpt3-ai-content-generator'); ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php if ($active_bot_post) : ?>
                <div class="aipkit_model_settings_popover_footer">
                    <button
                        type="button"
                        class="aipkit_popover_footer_btn aipkit_popover_footer_btn--danger"
                        data-action="delete"
                        <?php echo $is_default_active ? 'disabled aria-disabled="true"' : ''; ?>
                        title="<?php echo esc_attr($is_default_active ? __('Default bot cannot be deleted.', 'gpt3-ai-content-generator') : __('Delete chatbot', 'gpt3-ai-content-generator')); ?>"
                    >
                        <?php esc_html_e('Delete', 'gpt3-ai-content-generator'); ?>
                    </button>
                    <button
                        type="button"
                        class="aipkit_popover_footer_btn aipkit_popover_footer_btn--secondary"
                        data-action="reset"
                        title="<?php esc_attr_e('Reset chatbot settings', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('Reset', 'gpt3-ai-content-generator'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($active_bot_post && !$aipkit_hide_custom_theme) : ?>
        <?php
        $bot_id = $initial_active_bot_id;
        $bot_settings = $active_bot_settings;
        include __DIR__ . '/partials/appearance/custom-theme-flyout.php';
        ?>
    <?php endif; ?>
    <div
        class="aipkit_model_settings_popover aipkit_deploy_settings_popover"
        id="aipkit_deploy_settings_popover"
        aria-hidden="true"
    >
        <div
            class="aipkit_model_settings_popover_panel aipkit_deploy_settings_popover_panel"
            role="dialog"
            aria-modal="false"
            aria-labelledby="aipkit_deploy_settings_popover_title"
        >
            <div class="aipkit_model_settings_popover_header">
                <span class="aipkit_model_settings_popover_title" id="aipkit_deploy_settings_popover_title">
                    <?php esc_html_e('Deploy', 'gpt3-ai-content-generator'); ?>
                </span>
                <button
                    type="button"
                    class="aipkit_model_settings_popover_close aipkit_deploy_settings_popover_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit_model_settings_popover_body">
                <?php if ($active_bot_post) : ?>
                    <?php
                    $bot_id = $initial_active_bot_id;
                    $shortcode_text = sprintf('[aipkit_chatbot id=%d]', $bot_id);
                    $embed_script_url = WPAICG_PLUGIN_URL . 'dist/js/embed-bootstrap.bundle.js';
                    $embed_target_div = 'aipkit-chatbot-container-' . $bot_id;
                    $embed_code = sprintf(
                        '(function() { var d = document; var c = d.createElement("div"); c.id = "%s"; var s = d.createElement("script"); s.src = "%s"; s.setAttribute("data-bot-id", "%d"); s.setAttribute("data-wp-site", "%s"); s.async = true; var t = d.currentScript || d.getElementsByTagName("script")[0]; t.parentNode.insertBefore(c, t); t.parentNode.insertBefore(s, t); })();',
                        esc_js($embed_target_div),
                        esc_js($embed_script_url),
                        esc_js($bot_id),
                        esc_js(home_url())
                    );
                    $embed_code = '<script type="text/javascript">' . $embed_code . '</script>';
                    ?>
                    <div class="aipkit_deploy_wizard">
                        <div class="aipkit_popover_options_list aipkit_deploy_scope_options">
                            <div class="aipkit_popover_option_row">
                                <div class="aipkit_popover_option_main">
                                    <span
                                        class="aipkit_popover_option_label"
                                        tabindex="0"
                                        data-tooltip="<?php echo esc_attr__('Choose where this chatbot will be available.', 'gpt3-ai-content-generator'); ?>"
                                    >
                                        <?php esc_html_e('Location', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                    <select name="aipkit_deploy_scope" class="aipkit_popover_option_select">
                                        <option value="site" <?php selected($deploy_scope, 'site'); ?>><?php esc_html_e('This site', 'gpt3-ai-content-generator'); ?></option>
                                        <option value="external" <?php selected($deploy_scope, 'external'); ?>><?php esc_html_e('External embed', 'gpt3-ai-content-generator'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="aipkit_popover_option_row aipkit_deploy_row_mode">
                                <div class="aipkit_popover_option_main">
                                    <span
                                        class="aipkit_popover_option_label"
                                        tabindex="0"
                                        data-tooltip="<?php echo esc_attr__('Choose how the chatbot appears on your site.', 'gpt3-ai-content-generator'); ?>"
                                    >
                                        <?php esc_html_e('Mode', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                    <select name="aipkit_deploy_mode" class="aipkit_popover_option_select">
                                        <option value="inline" <?php selected($deploy_mode, 'inline'); ?>><?php esc_html_e('Inline (shortcode)', 'gpt3-ai-content-generator'); ?></option>
                                        <option value="popup" <?php selected($deploy_mode, 'popup'); ?>><?php esc_html_e('Floating widget', 'gpt3-ai-content-generator'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="aipkit_popover_option_row aipkit_deploy_row_popup_scope">
                                <div class="aipkit_popover_option_main">
                                    <span
                                        class="aipkit_popover_option_label"
                                        tabindex="0"
                                        data-tooltip="<?php echo esc_attr__('Show the widget on all pages or limit to specific pages.', 'gpt3-ai-content-generator'); ?>"
                                    >
                                        <?php esc_html_e('Show on', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                    <select name="aipkit_deploy_popup_scope" class="aipkit_popover_option_select">
                                        <option value="page" <?php selected($deploy_popup_scope, 'page'); ?>><?php esc_html_e('Limit to pages', 'gpt3-ai-content-generator'); ?></option>
                                        <option value="sitewide" <?php selected($deploy_popup_scope, 'sitewide'); ?>><?php esc_html_e('All pages', 'gpt3-ai-content-generator'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="aipkit_deploy_panel" data-deploy-panel="inline" hidden>
                            <div class="aipkit_popover_option_row aipkit_deploy_shortcode_row">
                                <div class="aipkit_popover_option_main">
                                    <span class="aipkit_popover_option_label">
                                        <?php esc_html_e('Shortcode', 'gpt3-ai-content-generator'); ?>
                                    </span>
                                    <div class="aipkit_popover_option_actions">
                                        <button
                                            type="button"
                                            class="aipkit_shortcode_pill aipkit_builder_shortcode_pill"
                                            data-shortcode="<?php echo esc_attr($shortcode_text); ?>"
                                            title="<?php esc_attr_e('Click to copy shortcode', 'gpt3-ai-content-generator'); ?>"
                                        >
                                            <span class="aipkit_shortcode_text"><?php echo esc_html($shortcode_text); ?></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="aipkit_deploy_panel" data-deploy-panel="popup" hidden>
                            <div class="aipkit_deploy_popup_settings" id="aipkit_builder_popup_settings_panel">
                                <div class="aipkit_popover_options_list aipkit_deploy_popup_options">
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('Select the screen corner for the widget.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Widget position', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <select
                                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_position_deploy"
                                                name="popup_position"
                                                class="aipkit_popover_option_select"
                                            >
                                                <option value="bottom-right" <?php selected($popup_position, 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'gpt3-ai-content-generator'); ?></option>
                                                <option value="bottom-left" <?php selected($popup_position, 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'gpt3-ai-content-generator'); ?></option>
                                                <option value="top-right" <?php selected($popup_position, 'top-right'); ?>><?php esc_html_e('Top Right', 'gpt3-ai-content-generator'); ?></option>
                                                <option value="top-left" <?php selected($popup_position, 'top-left'); ?>><?php esc_html_e('Top Left', 'gpt3-ai-content-generator'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('0 disables auto-open.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Auto-open after (seconds)', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <input
                                                type="number"
                                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_delay_deploy"
                                                name="popup_delay"
                                                class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--compact"
                                                value="<?php echo esc_attr($popup_delay); ?>"
                                                min="0"
                                                step="1"
                                            />
                                        </div>
                                    </div>

                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('Adjust the launcher style, size, and source.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Launcher icon', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <div class="aipkit_popover_inline_controls">
                                                <span class="aipkit_popover_inline_select">
                                                    <select
                                                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_style_deploy"
                                                        name="popup_icon_style"
                                                        class="aipkit_popover_option_select aipkit_popover_option_select--fit"
                                                    >
                                                        <option value="circle" <?php selected($popup_icon_style, 'circle'); ?>><?php esc_html_e('Circle', 'gpt3-ai-content-generator'); ?></option>
                                                        <option value="square" <?php selected($popup_icon_style, 'square'); ?>><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                                                        <option value="none" <?php selected($popup_icon_style, 'none'); ?>><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                                                    </select>
                                                </span>
                                                <span class="aipkit_popover_inline_select">
                                                    <select
                                                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_size_deploy"
                                                        name="popup_icon_size"
                                                        class="aipkit_popover_option_select aipkit_popover_option_select--fit"
                                                    >
                                                        <option value="small" <?php selected($popup_icon_size, 'small'); ?>><?php esc_html_e('Small', 'gpt3-ai-content-generator'); ?></option>
                                                        <option value="medium" <?php selected($popup_icon_size, 'medium'); ?>><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                                                        <option value="large" <?php selected($popup_icon_size, 'large'); ?>><?php esc_html_e('Large', 'gpt3-ai-content-generator'); ?></option>
                                                        <option value="xlarge" <?php selected($popup_icon_size, 'xlarge'); ?>><?php esc_html_e('X-Large', 'gpt3-ai-content-generator'); ?></option>
                                                    </select>
                                                </span>
                                                <span class="aipkit_popover_inline_select">
                                                    <select
                                                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_type_deploy"
                                                        name="popup_icon_type"
                                                        class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_popup_icon_type_select"
                                                    >
                                                        <option value="default" <?php selected($popup_icon_type, 'default'); ?>><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                                        <option value="custom" <?php selected($popup_icon_type, 'custom'); ?>><?php esc_html_e('Custom', 'gpt3-ai-content-generator'); ?></option>
                                                    </select>
                                                </span>
                                            </div>
                                            <div hidden>
                                                <input type="radio" name="popup_icon_style" value="circle" <?php checked($popup_icon_style, 'circle'); ?> />
                                                <input type="radio" name="popup_icon_style" value="square" <?php checked($popup_icon_style, 'square'); ?> />
                                                <input type="radio" name="popup_icon_style" value="none" <?php checked($popup_icon_style, 'none'); ?> />
                                                <input type="radio" name="popup_icon_type" value="default" <?php checked($popup_icon_type, 'default'); ?> />
                                                <input type="radio" name="popup_icon_type" value="custom" <?php checked($popup_icon_type, 'custom'); ?> />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row aipkit_popup_icon_default_selector_container" <?php echo ($popup_icon_type === 'default') ? '' : 'hidden'; ?>>
                                        <div class="aipkit_popover_option_main">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('Select a default icon for the popup.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Choose icon', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <div class="aipkit_popover_option_actions">
                                                <div class="aipkit_popup_icon_default_selector">
                                                    <?php foreach ($popup_icons as $icon_key => $svg_html) : ?>
                                                        <?php
                                                        $radio_id = 'aipkit_bot_' . absint($bot_id) . '_popup_icon_deploy_' . sanitize_key($icon_key);
                                                        $icon_checked = $popup_icon_value === $icon_key;
                                                        ?>
                                                        <label class="aipkit_option_card" for="<?php echo esc_attr($radio_id); ?>" title="<?php echo esc_attr(ucfirst(str_replace('-', ' ', $icon_key))); ?>">
                                                            <input
                                                                type="radio"
                                                                id="<?php echo esc_attr($radio_id); ?>"
                                                                name="popup_icon_default"
                                                                value="<?php echo esc_attr($icon_key); ?>"
                                                                <?php checked($icon_checked); ?>
                                                            />
                                                            <?php echo $svg_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row aipkit_popup_icon_custom_input_container" <?php echo ($popup_icon_type === 'custom') ? '' : 'hidden'; ?>>
                                        <div class="aipkit_popover_option_main">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('Ideal ~32x32. PNG or SVG.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Launcher icon URL', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <input
                                                type="url"
                                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_custom_url_deploy"
                                                name="popup_icon_custom_url"
                                                class="aipkit_popover_option_input aipkit_popover_option_input--framed"
                                                value="<?php echo ($popup_icon_type === 'custom') ? esc_url($popup_icon_value) : ''; ?>"
                                                placeholder="<?php esc_attr_e('Enter full URL (e.g., https://.../icon.png)', 'gpt3-ai-content-generator'); ?>"
                                            />
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('Choose a default icon or use a custom avatar.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Header avatar', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <div class="aipkit_popover_inline_controls">
                                                <span class="aipkit_popover_inline_select">
                                                    <select
                                                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_header_avatar_type_deploy"
                                                        name="header_avatar_type"
                                                        class="aipkit_popover_option_select"
                                                    >
                                                        <option value="default" <?php selected($saved_header_avatar_type, 'default'); ?>><?php esc_html_e('Icon', 'gpt3-ai-content-generator'); ?></option>
                                                        <option value="custom" <?php selected($saved_header_avatar_type, 'custom'); ?>><?php esc_html_e('Custom', 'gpt3-ai-content-generator'); ?></option>
                                                    </select>
                                                </span>
                                            </div>
                                            <div hidden>
                                                <input type="radio" name="header_avatar_type" value="default" <?php checked($saved_header_avatar_type, 'default'); ?> />
                                                <input type="radio" name="header_avatar_type" value="custom" <?php checked($saved_header_avatar_type, 'custom'); ?> />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row aipkit_header_avatar_default_selector_container" <?php echo ($saved_header_avatar_type === 'default') ? '' : 'hidden'; ?>>
                                        <div class="aipkit_popover_option_main">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('Select a default avatar icon.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Choose avatar', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <div class="aipkit_popover_option_actions">
                                                <div class="aipkit_popup_icon_default_selector">
                                                    <?php foreach ($popup_icons as $icon_key => $svg_html) : ?>
                                                        <?php
                                                        $radio_id = 'aipkit_bot_' . absint($bot_id) . '_header_avatar_icon_deploy_' . sanitize_key($icon_key);
                                                        $icon_checked = $saved_header_avatar_value === $icon_key;
                                                        ?>
                                                        <label class="aipkit_option_card" for="<?php echo esc_attr($radio_id); ?>" title="<?php echo esc_attr(ucfirst(str_replace('-', ' ', $icon_key))); ?>">
                                                            <input
                                                                type="radio"
                                                                id="<?php echo esc_attr($radio_id); ?>"
                                                                name="header_avatar_default"
                                                                value="<?php echo esc_attr($icon_key); ?>"
                                                                <?php checked($icon_checked); ?>
                                                            />
                                                            <?php echo $svg_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row aipkit_header_avatar_custom_input_container" <?php echo ($saved_header_avatar_type === 'custom') ? '' : 'hidden'; ?>>
                                        <div class="aipkit_popover_option_main">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('Ideal ~32x32. PNG or SVG.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Avatar URL', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <input
                                                type="url"
                                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_header_avatar_url_deploy"
                                                name="header_avatar_url"
                                                class="aipkit_popover_option_input aipkit_popover_option_input--framed"
                                                value="<?php echo ($saved_header_avatar_type === 'custom') ? esc_url($saved_header_avatar_url) : ''; ?>"
                                                placeholder="<?php esc_attr_e('Enter full URL (e.g., https://.../avatar.png)', 'gpt3-ai-content-generator'); ?>"
                                            />
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main">
                                            <label
                                                class="aipkit_popover_option_label"
                                                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_header_online_text_deploy"
                                            >
                                                <?php esc_html_e('Status text', 'gpt3-ai-content-generator'); ?>
                                            </label>
                                            <input
                                                type="text"
                                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_header_online_text_deploy"
                                                name="header_online_text"
                                                class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                                                value="<?php echo esc_attr($saved_header_online_text); ?>"
                                                placeholder="<?php esc_attr_e('Online', 'gpt3-ai-content-generator'); ?>"
                                            />
                                        </div>
                                    </div>

                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('Displays a short hint above the launcher.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Launcher hint', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <div class="aipkit_popover_option_actions">
                                                <label class="aipkit_switch">
                                                    <input
                                                        type="checkbox"
                                                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_enabled_deploy"
                                                        name="popup_label_enabled"
                                                        class="aipkit_toggle_switch aipkit_popup_hint_toggle_switch"
                                                        value="1"
                                                        <?php checked($popup_label_enabled, '1'); ?>
                                                    >
                                                    <span class="aipkit_switch_slider"></span>
                                                </label>
                                                <button
                                                    type="button"
                                                    class="aipkit_popover_option_btn aipkit_popup_hint_config_btn"
                                                    aria-expanded="false"
                                                    aria-controls="aipkit_popup_hint_flyout"
                                                    style="<?php echo ($popup_label_enabled === '1') ? '' : 'display:none;'; ?>"
                                                >
                                                    <?php esc_html_e('Configure', 'gpt3-ai-content-generator'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="aipkit_deploy_popup_shortcode" data-deploy-popup-shortcode hidden>
                                <div class="aipkit_popover_option_row aipkit_deploy_shortcode_row">
                                    <div class="aipkit_popover_option_main">
                                        <span class="aipkit_popover_option_label">
                                            <?php esc_html_e('Shortcode', 'gpt3-ai-content-generator'); ?>
                                        </span>
                                        <div class="aipkit_popover_option_actions">
                                            <button
                                                type="button"
                                                class="aipkit_shortcode_pill aipkit_builder_shortcode_pill"
                                                data-shortcode="<?php echo esc_attr($shortcode_text); ?>"
                                                title="<?php esc_attr_e('Click to copy shortcode', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <span class="aipkit_shortcode_text"><?php echo esc_html($shortcode_text); ?></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="aipkit_deploy_panel" data-deploy-panel="external" hidden>
                            <?php if ($embed_anywhere_active) : ?>
                                <div class="aipkit_popover_options_list aipkit_deploy_external_options">
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                                            <div class="aipkit_popover_option_header">
                                                <span
                                                    class="aipkit_popover_option_label"
                                                    tabindex="0"
                                                    data-tooltip="<?php echo esc_attr__('Copy the script snippet to embed this chatbot on any site.', 'gpt3-ai-content-generator'); ?>"
                                                >
                                                    <?php esc_html_e('Embed code', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                                <div class="aipkit_popover_option_actions">
                                                    <button
                                                        type="button"
                                                        class="aipkit_btn aipkit_btn-secondary aipkit_btn-small aipkit_copy_embed_code_btn"
                                                        data-target="aipkit_embed_code_<?php echo esc_attr($bot_id); ?>"
                                                    >
                                                        <span class="dashicons dashicons-admin-page"></span>
                                                        <?php esc_html_e('Copy Code', 'gpt3-ai-content-generator'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <textarea
                                                id="aipkit_embed_code_<?php echo esc_attr($bot_id); ?>"
                                                class="aipkit_builder_textarea aipkit_deploy_code_input"
                                                rows="5"
                                                readonly
                                            ><?php echo esc_textarea($embed_code); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                                            <span
                                                class="aipkit_popover_option_label"
                                                tabindex="0"
                                                data-tooltip="<?php echo esc_attr__('Limit where the embed code can be used.', 'gpt3-ai-content-generator'); ?>"
                                            >
                                                <?php esc_html_e('Allowed domains', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <textarea
                                                id="aipkit_embed_allowed_domains_<?php echo esc_attr($bot_id); ?>"
                                                name="embed_allowed_domains"
                                                class="aipkit_builder_textarea aipkit_deploy_domains_input"
                                                rows="3"
                                                placeholder="<?php esc_attr_e('e.g., https://example.com', 'gpt3-ai-content-generator'); ?>"
                                            ><?php echo esc_textarea($embed_allowed_domains); ?></textarea>
                                            <p class="aipkit_builder_help_text">
                                                <?php esc_html_e('Enter full domains (including https://). Leave empty to allow all domains.', 'gpt3-ai-content-generator'); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="aipkit_popover_options_list aipkit_deploy_external_options">
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                                            <div class="aipkit_deploy_locked_panel">
                                                <div class="aipkit_deploy_panel_header">
                                                    <div class="aipkit_deploy_panel_title"><?php esc_html_e('Embed anywhere', 'gpt3-ai-content-generator'); ?></div>
                                                    <p class="aipkit_builder_help_text">
                                                        <?php esc_html_e('Embed your chatbot on non-WordPress sites with a simple script tag. Great for landing pages, storefronts, and custom apps.', 'gpt3-ai-content-generator'); ?>
                                                    </p>
                                                </div>
                                                <div class="aipkit_deploy_locked_cta">
                                                    <a class="aipkit_btn aipkit_btn-primary" href="<?php echo esc_url($pricing_url); ?>">
                                                        <?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <p class="aipkit_builder_help_text">
                        <?php esc_html_e('Select a bot to configure deployment options.', 'gpt3-ai-content-generator'); ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php if ($active_bot_post) : ?>
                <div class="aipkit_popover_flyout_footer">
                    <span class="aipkit_popover_flyout_footer_text">
                        <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <a
                        class="aipkit_popover_flyout_footer_link"
                        href="<?php echo esc_url('https://docs.aipower.org/docs/chat#embed-anywhere-external-sites'); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($active_bot_post) : ?>
        <div
            class="aipkit_popover_image_flyout"
            id="aipkit_image_settings_flyout"
            aria-hidden="true"
            role="dialog"
        >
            <div class="aipkit_popover_flyout_header">
                <span class="aipkit_popover_flyout_title">
                    <?php esc_html_e('Image settings', 'gpt3-ai-content-generator'); ?>
                </span>
                <button
                    type="button"
                    class="aipkit_popover_flyout_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit_popover_flyout_body aipkit_popover_image_body">
                <div class="aipkit_popover_option_row aipkit_image_analysis_popover_row" style="<?php echo (($current_provider_for_this_bot === 'OpenAI' || $current_provider_for_this_bot === 'Claude' || $current_provider_for_this_bot === 'OpenRouter')) ? '' : 'display:none;'; ?>">
                    <div class="aipkit_popover_option_main">
                        <span
                            class="aipkit_popover_option_label"
                            tabindex="0"
                            data-tooltip="<?php echo esc_attr__('Allow image uploads for analysis (OpenAI, Claude, and OpenRouter only).', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Image upload', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <label class="aipkit_switch">
                            <input
                                type="checkbox"
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_image_upload_popover"
                                name="enable_image_upload"
                                class="aipkit_image_analysis_checkbox"
                                value="1"
                                <?php checked($enable_image_upload, '1'); ?>
                            />
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
                <div class="aipkit_popover_option_row">
                    <div class="aipkit_popover_option_main">
                        <span
                            class="aipkit_popover_option_label"
                            tabindex="0"
                            data-tooltip="<?php echo esc_attr__('Select the image model for this bot.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Image generation', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <div class="aipkit_popover_option_actions aipkit_popover_option_actions--image">
                            <select
                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_chat_image_model_id_popover"
                                name="chat_image_model_id"
                                class="aipkit_popover_option_select aipkit_popover_option_select--compact"
                            >
                                <?php
                                $found_current_model = false;
                                foreach ($available_image_models as $provider_group => $models) {
                                    echo '<optgroup label="' . esc_attr($provider_group) . '">';
                                    foreach ($models as $model) {
                                        if ((string) $chat_image_model_id === (string) $model['id']) {
                                            $found_current_model = true;
                                        }
                                        echo '<option value="' . esc_attr($model['id']) . '"' . selected($chat_image_model_id, $model['id'], false) . '>' . esc_html($model['name']) . '</option>';
                                    }
                                    echo '</optgroup>';
                                }
                                if (!$found_current_model && !empty($chat_image_model_id)) {
                                    echo '<option value="' . esc_attr($chat_image_model_id) . '" selected="selected">' . esc_html($chat_image_model_id) . ' (Manual/Current)</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="aipkit_popover_option_row">
                    <div class="aipkit_popover_option_main">
                        <label
                            class="aipkit_popover_option_label"
                            for="aipkit_chat_image_replicate_api_key"
                        >
                            <?php esc_html_e('Replicate API key', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                            <input
                                type="password"
                                id="aipkit_chat_image_replicate_api_key"
                                name="replicate_api_key"
                                class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                                value="<?php echo esc_attr($replicate_api_key); ?>"
                                placeholder="<?php esc_attr_e('Enter your Replicate API key', 'gpt3-ai-content-generator'); ?>"
                                autocomplete="new-password"
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-form-type="other"
                            />
                            <span class="aipkit_api-key-toggle">
                                <span class="dashicons dashicons-visibility"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="aipkit_popover_option_row">
                    <div class="aipkit_popover_option_main">
                        <label
                            class="aipkit_popover_option_label"
                            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_image_triggers_popover"
                            data-tooltip="<?php echo esc_attr__('Comma-separated commands (e.g., /image, /generate) to trigger.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Triggers', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <input
                            type="text"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_image_triggers_popover"
                            name="image_triggers"
                            class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                            placeholder="/image, /generate"
                            value="<?php echo esc_attr($image_triggers); ?>"
                        />
                    </div>
                </div>
            </div>
            <div class="aipkit_popover_flyout_footer">
                <span class="aipkit_popover_flyout_footer_text">
                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                </span>
                <a
                    class="aipkit_popover_flyout_footer_link"
                    href="<?php echo esc_url('https://docs.aipower.org/docs/image-features'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="aipkit_available_bots_json" class="aipkit_hidden" data-bots="<?php
    $bot_list_for_filter = [];
    if (!empty($all_bots_ordered_entries)) {
        foreach ($all_bots_ordered_entries as $bot_entry_filter) {
            $bot_list_for_filter[] = ['id' => $bot_entry_filter['post']->ID, 'title' => $bot_entry_filter['post']->post_title];
        }
    }
    echo esc_attr(wp_json_encode($bot_list_for_filter));
?>"></div>

<div id="aipkit_google_tts_voices_json_main" class="aipkit_hidden" data-voices="<?php
    $google_voices_main = class_exists('\WPAICG\Core\Providers\Google\GoogleSettingsHandler') ? \WPAICG\Core\Providers\Google\GoogleSettingsHandler::get_synced_google_tts_voices() : [];
    echo esc_attr(wp_json_encode($google_voices_main ?: []));
?>"></div>
<?php
$elevenlabs_voices_cached = AIPKit_Providers::get_elevenlabs_voices();
$elevenlabs_models_cached = AIPKit_Providers::get_elevenlabs_models();
?>
<?php foreach ($all_bots_ordered_entries as $bot_entry_for_json) : ?>
    <?php $bot_id_for_json = $bot_entry_for_json['post']->ID; ?>
    <div
        id="aipkit_elevenlabs_voices_json_<?php echo esc_attr($bot_id_for_json); ?>"
        class="aipkit_hidden"
        data-voices="<?php echo esc_attr(wp_json_encode($elevenlabs_voices_cached ?: [])); ?>"
    ></div>
    <div
        id="aipkit_elevenlabs_models_json_<?php echo esc_attr($bot_id_for_json); ?>"
        class="aipkit_hidden"
        data-models="<?php echo esc_attr(wp_json_encode($elevenlabs_models_cached ?: [])); ?>"
    ></div>
<?php endforeach; ?>
