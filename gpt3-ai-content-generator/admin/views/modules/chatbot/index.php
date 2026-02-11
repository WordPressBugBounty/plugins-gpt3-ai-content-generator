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

// If a forced/stored bot ID no longer exists, gracefully fall back.
if (!$active_bot_post) {
    if ($default_bot_post instanceof \WP_Post) {
        $active_bot_post = $default_bot_post;
        $initial_active_bot_id = (int) $default_bot_post->ID;
    } elseif (!empty($other_bots_posts) && $other_bots_posts[0] instanceof \WP_Post) {
        $active_bot_post = $other_bots_posts[0];
        $initial_active_bot_id = (int) $other_bots_posts[0]->ID;
    } else {
        $initial_active_bot_id = 0;
    }
}

// Always initialize a bot ID variable for downstream partials/flyouts.
$bot_id = (int) $initial_active_bot_id;

$active_bot_settings = [];
if ($active_bot_post && class_exists(AIPKit_Bot_Settings_Getter::class)) {
    $settings = AIPKit_Bot_Settings_Getter::get($active_bot_post->ID);
    if (!is_wp_error($settings)) {
        $active_bot_settings = $settings;
    }
}
$active_bot_instructions = $active_bot_settings['instructions'] ?? '';
$saved_theme = $active_bot_settings['theme'] ?? 'dark';
$saved_greeting = $active_bot_settings['greeting'] ?? '';
$saved_subgreeting = $active_bot_settings['subgreeting'] ?? '';
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
$selected_theme_preset_key = '';
$selected_theme_preset_label = '';
if ($saved_theme === 'custom' && !empty($custom_theme_presets)) {
    $preset_label_map = [];
    $preset_color_map = [];
    foreach ($custom_theme_presets as $preset) {
        if (!is_array($preset)) {
            continue;
        }
        $preset_key = isset($preset['key']) ? sanitize_key((string) $preset['key']) : '';
        if ($preset_key === '') {
            continue;
        }
        $preset_label_map[$preset_key] = isset($preset['label']) ? (string) $preset['label'] : '';
        $preset_color_map[$preset_key] = [
            'primary' => isset($preset['primary']) ? strtolower(trim((string) $preset['primary'])) : '',
            'secondary' => isset($preset['secondary']) ? strtolower(trim((string) $preset['secondary'])) : '',
        ];
    }

    $stored_theme_preset_key = isset($active_bot_settings['theme_preset_key'])
        ? sanitize_key((string) $active_bot_settings['theme_preset_key'])
        : '';
    if ($stored_theme_preset_key !== '' && isset($preset_label_map[$stored_theme_preset_key])) {
        $selected_theme_preset_key = $stored_theme_preset_key;
        $selected_theme_preset_label = $preset_label_map[$stored_theme_preset_key];
    } else {
        // Backward compatibility for bots saved before explicit preset keys.
        $saved_custom_theme_settings = isset($active_bot_settings['custom_theme_settings']) && is_array($active_bot_settings['custom_theme_settings'])
            ? $active_bot_settings['custom_theme_settings']
            : [];
        $saved_custom_primary = isset($saved_custom_theme_settings['primary_color'])
            ? strtolower(trim((string) $saved_custom_theme_settings['primary_color']))
            : '';
        $saved_custom_secondary = isset($saved_custom_theme_settings['secondary_color'])
            ? strtolower(trim((string) $saved_custom_theme_settings['secondary_color']))
            : '';

        if ($saved_custom_primary !== '' && $saved_custom_secondary !== '') {
            foreach ($preset_color_map as $preset_key => $preset_colors) {
                if (
                    $preset_colors['primary'] !== '' &&
                    $preset_colors['secondary'] !== '' &&
                    $saved_custom_primary === $preset_colors['primary'] &&
                    $saved_custom_secondary === $preset_colors['secondary']
                ) {
                    $selected_theme_preset_key = $preset_key;
                    $selected_theme_preset_label = $preset_label_map[$preset_key] ?? '';
                    break;
                }
            }
        }
    }
}
$popup_enabled = $active_bot_settings['popup_enabled'] ?? '0';
$popup_enabled = in_array($popup_enabled, ['0', '1'], true) ? $popup_enabled : '0';
$site_wide_enabled = $active_bot_settings['site_wide_enabled'] ?? '0';
$site_wide_enabled = in_array($site_wide_enabled, ['0', '1'], true) ? $site_wide_enabled : '0';
$deploy_mode = ($popup_enabled === '1') ? 'popup' : 'inline';
$deploy_popup_scope = ($site_wide_enabled === '1') ? 'sitewide' : 'page';
$shortcode_text = $active_bot_post
    ? sprintf('[aipkit_chatbot id=%d]', absint($initial_active_bot_id))
    : '';
$is_pro_plan = class_exists('\\WPAICG\\aipkit_dashboard') && aipkit_dashboard::is_pro_plan();
$embed_anywhere_active = $is_pro_plan;
$embed_allowed_domains = $active_bot_settings['embed_allowed_domains'] ?? '';
$embed_script_url = WPAICG_PLUGIN_URL . 'dist/js/embed-bootstrap.bundle.js';
$embed_target_div = 'aipkit-chatbot-container-' . absint($initial_active_bot_id);
$embed_code = sprintf(
    '(function(){var d=document;var c=d.createElement("div");c.id="%1$s";var s=d.createElement("script");s.src="%2$s";s.setAttribute("data-bot-id","%3$d");s.setAttribute("data-wp-site","%4$s");s.async=true;var t=d.currentScript||d.getElementsByTagName("script")[0];t.parentNode.insertBefore(c,t);t.parentNode.insertBefore(s,t);}());',
    esc_js($embed_target_div),
    esc_js($embed_script_url),
    absint($initial_active_bot_id),
    esc_js(home_url())
);
$embed_code = '<script type="text/javascript">' . $embed_code . '</script>';
$embed_docs_url = 'https://docs.aipower.org/docs/chat#embed-anywhere-external-sites';
$consent_feature_available = $is_pro_plan && class_exists('\\WPAICG\\Lib\\Addons\\AIPKit_Consent_Compliance');
$openai_moderation_available = $is_pro_plan && class_exists('\\WPAICG\\Lib\\Addons\\AIPKit_OpenAI_Moderation');
$triggers_available = $is_pro_plan;
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
    ? __('Requires "Popup Enabled" (in Appearance) and "Enable Realtime Voice Agent" to be active.', 'gpt3-ai-content-generator')
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
                            <?php if ($active_bot_post) : ?>
                            <section class="aipkit_builder_card aipkit_builder_card--shortcode">
                                <div class="aipkit_builder_shortcode_row">
                                    <div class="aipkit_builder_shortcode_meta">
                                        <button
                                            type="button"
                                            class="aipkit_shortcode_pill aipkit_builder_shortcode_pill"
                                            data-shortcode="<?php echo esc_attr($shortcode_text); ?>"
                                            title="<?php esc_attr_e('Click to copy shortcode', 'gpt3-ai-content-generator'); ?>"
                                        >
                                            <span class="aipkit_shortcode_text"><?php echo esc_html($shortcode_text); ?></span>
                                        </button>
                                        <div class="aipkit_builder_card_status aipkit_model_status_slot">
                                            <span class="aipkit_model_sync_status" aria-live="polite"></span>
                                            <span
                                                id="aipkit_chatbot_global_save_status_container"
                                                class="aipkit_save_status_container aipkit_builder_save_status"
                                                aria-live="polite"
                                            ></span>
                                        </div>
                                    </div>
                                    <div class="aipkit_builder_mode_inline">
                                        <label
                                            for="aipkit_builder_top_mode_select"
                                            class="aipkit_builder_mode_label"
                                        >
                                            <?php esc_html_e('Mode:', 'gpt3-ai-content-generator'); ?>
                                        </label>
                                        <select
                                            id="aipkit_builder_top_mode_select"
                                            class="aipkit_form-input aipkit_builder_mode_select"
                                            data-aipkit-top-mode-select
                                        >
                                            <option value="inline" <?php selected($deploy_mode, 'inline'); ?>>
                                                <?php esc_html_e('On-page', 'gpt3-ai-content-generator'); ?>
                                            </option>
                                            <option value="popup" <?php selected($deploy_mode, 'popup'); ?>>
                                                <?php esc_html_e('Popup', 'gpt3-ai-content-generator'); ?>
                                            </option>
                                            <option value="external">
                                                <?php esc_html_e('Embed Anywhere', 'gpt3-ai-content-generator'); ?>
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </section>
                            <section class="aipkit_builder_card aipkit_builder_card--bot-tabs">
                                <div class="aipkit_builder_bot_tabs_row">
                                    <div class="aipkit_builder_bot_tabs_shell">
                                        <div class="aipkit_builder_bot_tabs" data-aipkit-bot-tabs role="tablist" aria-label="<?php esc_attr_e('Chatbots', 'gpt3-ai-content-generator'); ?>">
                                            <?php foreach ($all_bots_ordered_entries as $bot_entry_for_tabs) : ?>
                                                <?php
                                                $bot_post_for_tabs = $bot_entry_for_tabs['post'];
                                                $is_active_tab = ((int) $initial_active_bot_id === (int) $bot_post_for_tabs->ID);
                                                ?>
                                                <button
                                                    type="button"
                                                    class="aipkit_builder_bot_tab<?php echo $is_active_tab ? ' is-active' : ''; ?>"
                                                    data-bot-id="<?php echo esc_attr($bot_post_for_tabs->ID); ?>"
                                                    role="tab"
                                                    aria-selected="<?php echo $is_active_tab ? 'true' : 'false'; ?>"
                                                    tabindex="<?php echo $is_active_tab ? '0' : '-1'; ?>"
                                                >
                                                    <span class="aipkit_builder_bot_tab_label">
                                                        <?php echo esc_html($bot_post_for_tabs->post_title); ?>
                                                    </span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                        <button
                                            type="button"
                                            class="aipkit_btn aipkit_btn-secondary aipkit_icon_btn aipkit_builder_new_bot_btn aipkit_builder_new_bot_btn--label"
                                            aria-label="<?php esc_attr_e('Create new chatbot', 'gpt3-ai-content-generator'); ?>"
                                            title="<?php esc_attr_e('Create new chatbot', 'gpt3-ai-content-generator'); ?>"
                                        >
                                            <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                                        </button>
                                        <div class="aipkit_builder_bot_overflow">
                                            <button
                                                type="button"
                                                class="aipkit_btn aipkit_btn-secondary aipkit_icon_btn aipkit_builder_bot_overflow_trigger"
                                                aria-label="<?php esc_attr_e('Show chatbot list', 'gpt3-ai-content-generator'); ?>"
                                                title="<?php esc_attr_e('Show chatbot list', 'gpt3-ai-content-generator'); ?>"
                                                aria-haspopup="menu"
                                                aria-expanded="false"
                                                hidden
                                            >
                                                <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                            </button>
                                            <div class="aipkit_builder_bot_overflow_menu" role="menu" hidden></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="aipkit_builder_bot_select_shell" hidden>
                                    <label for="aipkit_chatbot_builder_bot_select" class="screen-reader-text">
                                        <?php esc_html_e('Chatbot', 'gpt3-ai-content-generator'); ?>
                                    </label>
                                    <select
                                        id="aipkit_chatbot_builder_bot_select"
                                        name="aipkit_chatbot_builder_bot_select"
                                        class="aipkit_form-input aipkit_builder_bot_select_input"
                                        <?php echo empty($all_bots_ordered_entries) ? 'disabled' : ''; ?>
                                    >
                                        <?php if (empty($all_bots_ordered_entries)) : ?>
                                            <option value="">
                                                <?php esc_html_e('No chatbots yet', 'gpt3-ai-content-generator'); ?>
                                            </option>
                                        <?php else : ?>
                                            <option value="__new__">
                                                <?php esc_html_e('+ New Bot', 'gpt3-ai-content-generator'); ?>
                                            </option>
                                            <option value="" disabled>----------</option>
                                            <?php foreach ($all_bots_ordered_entries as $bot_entry_for_select) : ?>
                                                <?php $bot_post_for_select = $bot_entry_for_select['post']; ?>
                                                <option
                                                    value="<?php echo esc_attr($bot_post_for_select->ID); ?>"
                                                    <?php selected($initial_active_bot_id, $bot_post_for_select->ID); ?>
                                                >
                                                    <?php echo esc_html($bot_post_for_select->post_title); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </section>
                            <section class="aipkit_builder_card aipkit_builder_card--settings aipkit_settings_accordion" id="aipkit_settings_accordion">
                                <?php
                                $bot_id = $initial_active_bot_id;
                                $bot_settings = $active_bot_settings;
                                $chatbot_summary_parts = [];
                                if (!empty($saved_provider)) {
                                    $chatbot_summary_parts[] = $saved_provider;
                                }
                                if (!empty($saved_model)) {
                                    $chatbot_summary_parts[] = $saved_model;
                                }
                                $chatbot_summary_fallback = __('Select engine and model', 'gpt3-ai-content-generator');
                                $chatbot_summary_text = !empty($chatbot_summary_parts)
                                    ? implode(' | ', $chatbot_summary_parts)
                                    : $chatbot_summary_fallback;
                                $behavior_reasoning_labels = [
                                    'none' => __('None', 'gpt3-ai-content-generator'),
                                    'low' => __('Low', 'gpt3-ai-content-generator'),
                                    'medium' => __('Medium', 'gpt3-ai-content-generator'),
                                    'high' => __('High', 'gpt3-ai-content-generator'),
                                    'xhigh' => __('Very high', 'gpt3-ai-content-generator'),
                                ];
                                $behavior_reasoning_key = isset($reasoning_effort_val)
                                    ? sanitize_key((string) $reasoning_effort_val)
                                    : BotSettingsManager::DEFAULT_REASONING_EFFORT;
                                if (!isset($behavior_reasoning_labels[$behavior_reasoning_key])) {
                                    $behavior_reasoning_key = BotSettingsManager::DEFAULT_REASONING_EFFORT;
                                }
                                $behavior_reasoning_label = $behavior_reasoning_labels[$behavior_reasoning_key]
                                    ?? __('None', 'gpt3-ai-content-generator');
                                $behavior_supports_reasoning = (
                                    $saved_provider === 'OpenAI'
                                    && class_exists('\WPAICG\Core\AIPKit_OpenAI_Reasoning')
                                    && \WPAICG\Core\AIPKit_OpenAI_Reasoning::supports_reasoning((string) $saved_model)
                                );
                                $behavior_response_length_value = isset($active_bot_settings['max_completion_tokens'])
                                    ? absint($active_bot_settings['max_completion_tokens'])
                                    : BotSettingsManager::DEFAULT_MAX_COMPLETION_TOKENS;
                                $behavior_response_length_value = max(1, min($behavior_response_length_value, 128000));
                                $behavior_memory_value = isset($active_bot_settings['max_messages'])
                                    ? absint($active_bot_settings['max_messages'])
                                    : BotSettingsManager::DEFAULT_MAX_MESSAGES;
                                $behavior_memory_value = max(1, min($behavior_memory_value, 1024));
                                $behavior_summary_fallback = __('Response length and memory', 'gpt3-ai-content-generator');
                                $behavior_summary_text = $behavior_supports_reasoning
                                    ? sprintf(
                                        /* translators: 1: response length value, 2: memory value, 3: reasoning label. */
                                        __('Response length: %1$s | Memory: %2$s | Reasoning: %3$s', 'gpt3-ai-content-generator'),
                                        number_format_i18n($behavior_response_length_value),
                                        number_format_i18n($behavior_memory_value),
                                        $behavior_reasoning_label
                                    )
                                    : sprintf(
                                        /* translators: 1: response length value, 2: memory value. */
                                        __('Response length: %1$s | Memory: %2$s', 'gpt3-ai-content-generator'),
                                        number_format_i18n($behavior_response_length_value),
                                        number_format_i18n($behavior_memory_value)
                                    );
                                $interface_summary_fallback = __('Select theme', 'gpt3-ai-content-generator');
                                $saved_theme_key = isset($saved_theme) ? (string) $saved_theme : '';
                                $interface_summary_text = '';
                                if ($saved_theme_key === 'custom' && $selected_theme_preset_label !== '') {
                                    $interface_summary_text = $selected_theme_preset_label;
                                } elseif (!empty($saved_theme_key) && isset($available_themes[$saved_theme_key])) {
                                    $interface_summary_text = (string) $available_themes[$saved_theme_key];
                                } elseif (!empty($saved_theme_key)) {
                                    $interface_summary_text = ucwords(str_replace(['-', '_'], ' ', $saved_theme_key));
                                }
                                if ($interface_summary_text === '') {
                                    $interface_summary_text = $interface_summary_fallback;
                                }
                                $context_summary_fallback = '';
                                $context_summary_parts = [];
                                if ($enable_vector_store === '1') {
                                    $vector_provider_labels = [
                                        'openai' => __('OpenAI', 'gpt3-ai-content-generator'),
                                        'pinecone' => __('Pinecone', 'gpt3-ai-content-generator'),
                                        'qdrant' => __('Qdrant', 'gpt3-ai-content-generator'),
                                        'claude_files' => __('Claude Files', 'gpt3-ai-content-generator'),
                                    ];
                                    $vector_provider_label = $vector_provider_labels[$vector_store_provider] ?? '';
                                    $vector_source_names = [];

                                    if ($vector_store_provider === 'openai') {
                                        $openai_store_names = [];
                                        foreach ($openai_vector_stores as $store_index => $store) {
                                            if (!is_array($store)) {
                                                continue;
                                            }
                                            $store_id = isset($store['id']) ? trim((string) $store['id']) : '';
                                            if ($store_id === '') {
                                                continue;
                                            }
                                            $store_name = isset($store['name']) ? trim((string) $store['name']) : '';
                                            if ($store_name === '') {
                                                $store_name = sprintf(
                                                    /* translators: %d is the vector store index. */
                                                    __('Untitled store %d', 'gpt3-ai-content-generator'),
                                                    ((int) $store_index) + 1
                                                );
                                            }
                                            $openai_store_names[$store_id] = $store_name;
                                        }
                                        foreach ($openai_vector_store_ids_saved as $saved_store_id) {
                                            $saved_store_id = is_scalar($saved_store_id) ? trim((string) $saved_store_id) : '';
                                            if ($saved_store_id === '') {
                                                continue;
                                            }
                                            $vector_source_names[] = $openai_store_names[$saved_store_id] ?? $saved_store_id;
                                        }
                                    } elseif ($vector_store_provider === 'pinecone') {
                                        if (!empty($pinecone_index_name)) {
                                            $vector_source_names[] = (string) $pinecone_index_name;
                                        }
                                    } elseif ($vector_store_provider === 'qdrant') {
                                        foreach ($qdrant_collection_names as $collection_name) {
                                            $collection_name = is_scalar($collection_name) ? trim((string) $collection_name) : '';
                                            if ($collection_name !== '') {
                                                $vector_source_names[] = $collection_name;
                                            }
                                        }
                                    }

                                    $vector_source_names = array_values(
                                        array_unique(
                                            array_filter(
                                                array_map('trim', $vector_source_names)
                                            )
                                        )
                                    );

                                    if ($vector_provider_label !== '') {
                                        $knowledge_summary = $vector_provider_label;
                                        if (!empty($vector_source_names)) {
                                            $knowledge_summary .= ': ' . implode(', ', $vector_source_names);
                                        }
                                        $context_summary_parts[] = $knowledge_summary;
                                    } elseif (!empty($vector_source_names)) {
                                        $context_summary_parts[] = implode(', ', $vector_source_names);
                                    }

                                    if (
                                        in_array($vector_store_provider, ['pinecone', 'qdrant'], true)
                                        && !empty($vector_embedding_model)
                                    ) {
                                        $embedding_models_by_provider = [
                                            'openai' => $openai_embedding_models,
                                            'google' => $google_embedding_models,
                                            'azure' => $azure_embedding_models,
                                            'openrouter' => $openrouter_embedding_models,
                                        ];
                                        $embedding_model_label = trim((string) $vector_embedding_model);
                                        $selected_embedding_provider = trim((string) $vector_embedding_provider);
                                        if (
                                            $selected_embedding_provider !== ''
                                            && isset($embedding_models_by_provider[$selected_embedding_provider])
                                            && is_array($embedding_models_by_provider[$selected_embedding_provider])
                                        ) {
                                            foreach ($embedding_models_by_provider[$selected_embedding_provider] as $embedding_model_option) {
                                                if (!is_array($embedding_model_option)) {
                                                    continue;
                                                }
                                                $embedding_option_id = isset($embedding_model_option['id'])
                                                    ? trim((string) $embedding_model_option['id'])
                                                    : '';
                                                if ($embedding_option_id !== $embedding_model_label) {
                                                    continue;
                                                }
                                                $embedding_option_name = isset($embedding_model_option['name'])
                                                    ? trim((string) $embedding_model_option['name'])
                                                    : '';
                                                if ($embedding_option_name !== '') {
                                                    $embedding_model_label = $embedding_option_name;
                                                }
                                                break;
                                            }
                                        }
                                        if ($embedding_model_label !== '') {
                                            $context_summary_parts[] = $embedding_model_label;
                                        }
                                    }
                                }
                                if ($content_aware_enabled === '1') {
                                    $context_summary_parts[] = __('Page context', 'gpt3-ai-content-generator');
                                }
                                $context_summary_text = !empty($context_summary_parts)
                                    ? implode(' | ', $context_summary_parts)
                                    : $context_summary_fallback;
                                $tools_summary_fallback = '';
                                $tools_summary_parts = [];
                                if ($file_upload_toggle_value === '1') {
                                    $tools_summary_parts[] = __('File Upload', 'gpt3-ai-content-generator');
                                }
                                if ($enable_image_upload === '1') {
                                    $tools_summary_parts[] = __('Image Analysis', 'gpt3-ai-content-generator');
                                }
                                $web_search_enabled = false;
                                if ($saved_provider === 'OpenAI') {
                                    $web_search_enabled = ($openai_web_search_enabled_val === '1');
                                } elseif ($saved_provider === 'Google') {
                                    $web_search_enabled = ($google_search_grounding_enabled_val === '1');
                                } elseif ($saved_provider === 'Claude') {
                                    $web_search_enabled = ($claude_web_search_enabled_val === '1');
                                } elseif ($saved_provider === 'OpenRouter') {
                                    $web_search_enabled = ($openrouter_web_search_enabled_val === '1');
                                }
                                if ($web_search_enabled) {
                                    $tools_summary_parts[] = __('Web Search', 'gpt3-ai-content-generator');
                                }
                                if ($enable_voice_input === '1') {
                                    $tools_summary_parts[] = __('Speech to Text', 'gpt3-ai-content-generator');
                                }
                                if ($tts_enabled === '1') {
                                    $tools_summary_parts[] = __('Text to Speech', 'gpt3-ai-content-generator');
                                }
                                if ($enable_realtime_voice === '1') {
                                    $tools_summary_parts[] = __('Realtime Voice', 'gpt3-ai-content-generator');
                                }
                                $tools_summary_count = count($tools_summary_parts);
                                if ($tools_summary_count > 3) {
                                    $tools_summary_text = sprintf(
                                        /* translators: %d is the number of enabled tools. */
                                        __('Enabled: %d', 'gpt3-ai-content-generator'),
                                        $tools_summary_count
                                    );
                                } elseif ($tools_summary_count > 0) {
                                    $tools_summary_text = sprintf(
                                        /* translators: %s is a comma-separated list of enabled tools. */
                                        __('Enabled: %s', 'gpt3-ai-content-generator'),
                                        implode(', ', $tools_summary_parts)
                                    );
                                } else {
                                    $tools_summary_text = $tools_summary_fallback;
                                }
                                $enable_ip_anonymization_value = $active_bot_settings['enable_ip_anonymization']
                                    ?? BotSettingsManager::DEFAULT_ENABLE_IP_ANONYMIZATION;
                                $enable_ip_anonymization_value = in_array($enable_ip_anonymization_value, ['0', '1'], true)
                                    ? $enable_ip_anonymization_value
                                    : BotSettingsManager::DEFAULT_ENABLE_IP_ANONYMIZATION;
                                $enable_consent_compliance_value = $active_bot_settings['enable_consent_compliance']
                                    ?? BotSettingsManager::DEFAULT_ENABLE_CONSENT_COMPLIANCE;
                                $enable_consent_compliance_value = in_array($enable_consent_compliance_value, ['0', '1'], true)
                                    ? $enable_consent_compliance_value
                                    : BotSettingsManager::DEFAULT_ENABLE_CONSENT_COMPLIANCE;
                                $openai_moderation_enabled_value = $active_bot_settings['openai_moderation_enabled']
                                    ?? BotSettingsManager::DEFAULT_ENABLE_OPENAI_MODERATION;
                                $openai_moderation_enabled_value = in_array($openai_moderation_enabled_value, ['0', '1'], true)
                                    ? $openai_moderation_enabled_value
                                    : BotSettingsManager::DEFAULT_ENABLE_OPENAI_MODERATION;
                                $safety_summary_fallback = '';
                                $safety_summary_parts = [];
                                if ($enable_ip_anonymization_value === '1') {
                                    $safety_summary_parts[] = __('IP anonymization', 'gpt3-ai-content-generator');
                                }
                                if ($consent_feature_available && $enable_consent_compliance_value === '1') {
                                    $safety_summary_parts[] = __('Consent notice', 'gpt3-ai-content-generator');
                                }
                                if (
                                    $openai_moderation_available
                                    && $saved_provider === 'OpenAI'
                                    && $openai_moderation_enabled_value === '1'
                                ) {
                                    $safety_summary_parts[] = __('Moderation', 'gpt3-ai-content-generator');
                                }
                                $safety_summary_text = !empty($safety_summary_parts)
                                    ? sprintf(
                                        /* translators: %s is a comma-separated list of enabled safety controls. */
                                        __('Enabled: %s', 'gpt3-ai-content-generator'),
                                        implode(', ', $safety_summary_parts)
                                    )
                                    : $safety_summary_fallback;
                                $token_limit_mode_value = $active_bot_settings['token_limit_mode']
                                    ?? BotSettingsManager::DEFAULT_TOKEN_LIMIT_MODE;
                                $token_limit_mode_value = is_scalar($token_limit_mode_value)
                                    ? sanitize_key((string) $token_limit_mode_value)
                                    : BotSettingsManager::DEFAULT_TOKEN_LIMIT_MODE;
                                if (!in_array($token_limit_mode_value, ['general', 'role_based'], true)) {
                                    $token_limit_mode_value = BotSettingsManager::DEFAULT_TOKEN_LIMIT_MODE;
                                }
                                $token_guest_limit_value = $active_bot_settings['token_guest_limit'] ?? '';
                                $token_guest_limit_value = is_scalar($token_guest_limit_value)
                                    ? trim((string) $token_guest_limit_value)
                                    : '';
                                $token_user_limit_value = $active_bot_settings['token_user_limit'] ?? '';
                                $token_user_limit_value = is_scalar($token_user_limit_value)
                                    ? trim((string) $token_user_limit_value)
                                    : '';
                                $token_guest_summary_value = $token_guest_limit_value !== ''
                                    ? $token_guest_limit_value
                                    : __('Unlimited', 'gpt3-ai-content-generator');
                                $token_user_summary_value = $token_user_limit_value !== ''
                                    ? $token_user_limit_value
                                    : __('Unlimited', 'gpt3-ai-content-generator');
                                $token_reset_period_value = $active_bot_settings['token_reset_period']
                                    ?? BotSettingsManager::DEFAULT_TOKEN_RESET_PERIOD;
                                $token_reset_period_value = is_scalar($token_reset_period_value)
                                    ? sanitize_key((string) $token_reset_period_value)
                                    : BotSettingsManager::DEFAULT_TOKEN_RESET_PERIOD;
                                $token_reset_period_labels = [
                                    'never' => __('never', 'gpt3-ai-content-generator'),
                                    'daily' => __('1 day', 'gpt3-ai-content-generator'),
                                    'weekly' => __('1 week', 'gpt3-ai-content-generator'),
                                    'monthly' => __('1 month', 'gpt3-ai-content-generator'),
                                ];
                                if (!isset($token_reset_period_labels[$token_reset_period_value])) {
                                    $token_reset_period_value = BotSettingsManager::DEFAULT_TOKEN_RESET_PERIOD;
                                }
                                if (!isset($token_reset_period_labels[$token_reset_period_value])) {
                                    $token_reset_period_value = 'monthly';
                                }
                                $limits_summary_text = $token_limit_mode_value === 'role_based'
                                    ? sprintf(
                                        /* translators: %s is a token reset period label such as "1 month". */
                                        __('Enabled: role based limit reset period: %s.', 'gpt3-ai-content-generator'),
                                        $token_reset_period_labels[$token_reset_period_value]
                                    )
                                    : sprintf(
                                        /* translators: 1: guest token limit summary, 2: user token limit summary. */
                                        __('Guests: %1$s Users: %2$s', 'gpt3-ai-content-generator'),
                                        $token_guest_summary_value,
                                        $token_user_summary_value
                                    );
                                $limits_summary_fallback = $limits_summary_text;
                                $rules_count = 0;
                                if ($triggers_available) {
                                    $saved_triggers_json = $active_bot_settings['triggers_json'] ?? '[]';
                                    if (is_array($saved_triggers_json)) {
                                        $rules_count = count($saved_triggers_json);
                                    } elseif (is_string($saved_triggers_json) && $saved_triggers_json !== '') {
                                        $decoded_rules = json_decode($saved_triggers_json, true);
                                        if (is_array($decoded_rules)) {
                                            if (isset($decoded_rules['triggers']) && is_array($decoded_rules['triggers'])) {
                                                $rules_count = count($decoded_rules['triggers']);
                                            } elseif (isset($decoded_rules['rules']) && is_array($decoded_rules['rules'])) {
                                                $rules_count = count($decoded_rules['rules']);
                                            } else {
                                                $rules_count = count($decoded_rules);
                                            }
                                        }
                                    }
                                }
                                $rules_summary_fallback = '';
                                $rules_summary_text = $rules_count > 0
                                    ? sprintf(
                                        _n('%d rule', '%d rules', $rules_count, 'gpt3-ai-content-generator'),
                                        $rules_count
                                    )
                                    : $rules_summary_fallback;
                                ?>
                                <div class="aipkit_accordion_section" data-aipkit-accordion="chatbot">
                                <button type="button" class="aipkit_accordion_header" aria-expanded="false">
                                    <span class="aipkit_accordion_header_icon dashicons dashicons-format-chat" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_header_text"><?php esc_html_e('Chatbot', 'gpt3-ai-content-generator'); ?></span>
                                    <span
                                        class="aipkit_accordion_header_hint"
                                        data-aipkit-chatbot-summary
                                        data-default-summary="<?php echo esc_attr($chatbot_summary_fallback); ?>"
                                        title="<?php echo esc_attr($chatbot_summary_text); ?>"
                                    ><?php echo esc_html($chatbot_summary_text); ?></span>
                                    <span class="aipkit_accordion_chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                </button>
                                <div class="aipkit_accordion_body" data-aipkit-settings-panel="chatbot" hidden>
                                    <div class="aipkit_builder_field">
                                        <div class="aipkit_builder_ai_model aipkit_chatbot_tab_model_config">
                                            <?php
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
                                </div>
                                </div>
                            <div class="aipkit_accordion_section" data-aipkit-accordion="context_behavior">
                                <button type="button" class="aipkit_accordion_header" aria-expanded="false">
                                    <span class="aipkit_accordion_header_icon dashicons dashicons-admin-settings" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_header_text"><?php esc_html_e('Context', 'gpt3-ai-content-generator'); ?></span>
                                    <span
                                        class="aipkit_accordion_header_hint"
                                        data-aipkit-context-summary
                                        data-default-summary="<?php echo esc_attr($context_summary_fallback); ?>"
                                        title="<?php echo esc_attr($context_summary_text); ?>"
                                    ><?php echo esc_html($context_summary_text); ?></span>
                                    <span class="aipkit_accordion_chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                </button>
                                <div class="aipkit_accordion_body" data-aipkit-settings-panel="context" hidden>
                                    <div class="aipkit_accordion_subsection">
                                        <?php include __DIR__ . '/partials/ai-config/context-settings.php'; ?>
                                        <?php include __DIR__ . '/partials/ai-config/training-settings.php'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="aipkit_accordion_section" data-aipkit-accordion="tools">
                                <button type="button" class="aipkit_accordion_header" aria-expanded="false">
                                    <span class="aipkit_accordion_header_icon dashicons dashicons-admin-tools" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_header_text"><?php esc_html_e('Tools', 'gpt3-ai-content-generator'); ?></span>
                                    <span
                                        class="aipkit_accordion_header_hint"
                                        data-aipkit-tools-summary
                                        data-default-summary="<?php echo esc_attr($tools_summary_fallback); ?>"
                                        title="<?php echo esc_attr($tools_summary_text); ?>"
                                    ><?php echo esc_html($tools_summary_text); ?></span>
                                    <span class="aipkit_accordion_chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                </button>
                                <div class="aipkit_accordion_body" data-aipkit-settings-panel="tools" hidden>
                                    <?php include __DIR__ . '/partials/ai-config/tools-settings.php'; ?>
                                </div>
                            </div>
                            <div class="aipkit_accordion_section" data-aipkit-accordion="behavior">
                                <button type="button" class="aipkit_accordion_header" aria-expanded="false">
                                    <span class="aipkit_accordion_header_icon dashicons dashicons-controls-repeat" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_header_text"><?php esc_html_e('Behaviour', 'gpt3-ai-content-generator'); ?></span>
                                    <span
                                        class="aipkit_accordion_header_hint"
                                        data-aipkit-behavior-summary
                                        data-default-summary="<?php echo esc_attr($behavior_summary_fallback); ?>"
                                        title="<?php echo esc_attr($behavior_summary_text); ?>"
                                    ><?php echo esc_html($behavior_summary_text); ?></span>
                                    <span class="aipkit_accordion_chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                </button>
                                <div class="aipkit_accordion_body" data-aipkit-settings-panel="behavior" hidden>
                                    <?php include __DIR__ . '/partials/ai-config/behavior-settings.php'; ?>
                                </div>
                            </div>
                            <div class="aipkit_accordion_section" data-aipkit-accordion="interface">
                                <button type="button" class="aipkit_accordion_header" aria-expanded="false">
                                    <span class="aipkit_accordion_header_icon dashicons dashicons-admin-appearance" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_header_text"><?php esc_html_e('Appearance', 'gpt3-ai-content-generator'); ?></span>
                                    <span
                                        class="aipkit_accordion_header_hint"
                                        data-aipkit-interface-summary
                                        data-default-summary="<?php echo esc_attr($interface_summary_fallback); ?>"
                                        title="<?php echo esc_attr($interface_summary_text); ?>"
                                    ><?php echo esc_html($interface_summary_text); ?></span>
                                    <span class="aipkit_accordion_chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                </button>
                                <div class="aipkit_accordion_body" data-aipkit-settings-panel="appearance" hidden>
                                    <?php include __DIR__ . '/partials/ai-config/appearance-settings.php'; ?>
                                </div>
                            </div>
                            <div class="aipkit_accordion_section" data-aipkit-accordion="rules">
                                <button
                                    type="button"
                                    class="aipkit_accordion_header aipkit_builder_sheet_trigger"
                                    aria-expanded="false"
                                    data-sheet-title="<?php echo esc_attr__('Rules', 'gpt3-ai-content-generator'); ?>"
                                    data-sheet-description="<?php echo esc_attr__('Create and manage rule-based automations for this chatbot.', 'gpt3-ai-content-generator'); ?>"
                                    data-sheet-content="triggers"
                                >
                                    <span class="aipkit_accordion_header_icon dashicons dashicons-controls-repeat" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_header_text"><?php esc_html_e('Rules', 'gpt3-ai-content-generator'); ?></span>
                                    <span
                                        class="aipkit_accordion_header_hint"
                                        data-aipkit-rules-summary
                                        data-default-summary="<?php echo esc_attr($rules_summary_fallback); ?>"
                                        title="<?php echo esc_attr($rules_summary_text); ?>"
                                    ><?php echo esc_html($rules_summary_text); ?></span>
                                    <span class="aipkit_accordion_chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                </button>
                                <div class="aipkit_accordion_body aipkit_accordion_body--rules" data-aipkit-settings-panel="rules" hidden></div>
                            </div>
                            <div class="aipkit_accordion_section" data-aipkit-accordion="safety">
                                <button type="button" class="aipkit_accordion_header" aria-expanded="false">
                                    <span class="aipkit_accordion_header_icon dashicons dashicons-shield" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_header_text"><?php esc_html_e('Safety', 'gpt3-ai-content-generator'); ?></span>
                                    <span
                                        class="aipkit_accordion_header_hint"
                                        data-aipkit-safety-summary
                                        data-default-summary="<?php echo esc_attr($safety_summary_fallback); ?>"
                                        title="<?php echo esc_attr($safety_summary_text); ?>"
                                    ><?php echo esc_html($safety_summary_text); ?></span>
                                    <span class="aipkit_accordion_chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                </button>
                                <div class="aipkit_accordion_body" data-aipkit-settings-panel="safety" hidden>
                                    <?php include __DIR__ . '/partials/ai-config/safety-settings.php'; ?>
                                </div>
                            </div>
                            <div class="aipkit_accordion_section" data-aipkit-accordion="limits">
                                <button type="button" class="aipkit_accordion_header" aria-expanded="false">
                                    <span class="aipkit_accordion_header_icon dashicons dashicons-chart-bar" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_header_text"><?php esc_html_e('Limits', 'gpt3-ai-content-generator'); ?></span>
                                    <span
                                        class="aipkit_accordion_header_hint"
                                        data-aipkit-limits-summary
                                        data-default-summary="<?php echo esc_attr($limits_summary_fallback); ?>"
                                        title="<?php echo esc_attr($limits_summary_text); ?>"
                                    ><?php echo esc_html($limits_summary_text); ?></span>
                                    <span class="aipkit_accordion_chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                </button>
                                <div class="aipkit_accordion_body" data-aipkit-settings-panel="limits" hidden>
                                    <?php include __DIR__ . '/partials/ai-config/limits-settings.php'; ?>
                                </div>
                            </div>
                            <div class="aipkit_accordion_section" data-aipkit-accordion="actions">
                                <button type="button" class="aipkit_accordion_header" aria-expanded="false">
                                    <span class="aipkit_accordion_header_icon dashicons dashicons-admin-generic" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_header_text"><?php esc_html_e('Actions', 'gpt3-ai-content-generator'); ?></span>
                                    <span class="aipkit_accordion_header_hint" aria-hidden="true"></span>
                                    <span class="aipkit_accordion_chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                </button>
                                <div class="aipkit_accordion_body aipkit_accordion_body--actions" hidden>
                                    <div class="aipkit_accordion_actions_row">
                                        <button
                                            type="button"
                                            class="aipkit_popover_footer_btn aipkit_popover_footer_btn--secondary"
                                            data-action="duplicate"
                                            title="<?php esc_attr_e('Duplicate chatbot', 'gpt3-ai-content-generator'); ?>"
                                        >
                                            <?php esc_html_e('Duplicate', 'gpt3-ai-content-generator'); ?>
                                        </button>
                                        <button
                                            type="button"
                                            class="aipkit_popover_footer_btn aipkit_popover_footer_btn--secondary"
                                            data-action="reset"
                                            title="<?php esc_attr_e('Reset chatbot settings', 'gpt3-ai-content-generator'); ?>"
                                        >
                                            <?php esc_html_e('Reset', 'gpt3-ai-content-generator'); ?>
                                        </button>
                                        <button
                                            type="button"
                                            class="aipkit_popover_footer_btn aipkit_popover_footer_btn--danger"
                                            data-action="delete"
                                            <?php echo $is_default_active ? 'disabled aria-disabled="true"' : ''; ?>
                                            title="<?php echo esc_attr($is_default_active ? __('Default bot cannot be deleted.', 'gpt3-ai-content-generator') : __('Delete chatbot', 'gpt3-ai-content-generator')); ?>"
                                        >
                                            <?php esc_html_e('Delete', 'gpt3-ai-content-generator'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <?php endif; ?>

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
                <?php esc_html_e('Conversation starters', 'gpt3-ai-content-generator'); ?>
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
                            <?php esc_html_e('Conversation starters (max 6)', 'gpt3-ai-content-generator'); ?>
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
                <div class="aipkit_builder_sheet_section aipkit_accordion_body--rules" data-sheet="triggers" hidden>
                    <span class="aipkit_popover_status_inline aipkit_triggers_status" aria-live="polite"></span>
                    <?php if ($triggers_available && $active_bot_post && $bot_id > 0) : ?>
                        <?php
                        $triggers_json = $active_bot_settings['triggers_json'] ?? '[]';
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
                    <?php elseif ($triggers_available) : ?>
                        <p class="aipkit_builder_help_text">
                            <?php esc_html_e('Create or select a chatbot to configure rules.', 'gpt3-ai-content-generator'); ?>
                        </p>
                    <?php else : ?>
                        <div class="aipkit_rules_promo">
                            <!-- Hero -->
                            <div class="aipkit_rules_promo_hero">
                                <span class="aipkit_rules_promo_hero_icon" aria-hidden="true">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </span>
                                <div class="aipkit_rules_promo_hero_text">
                                    <h3 class="aipkit_rules_promo_hero_title"><?php esc_html_e('Automate your chatbot with Rules', 'gpt3-ai-content-generator'); ?></h3>
                                    <p class="aipkit_rules_promo_hero_desc"><?php esc_html_e('Build event-driven workflows that respond to messages, show forms, call webhooks, and more — no code required.', 'gpt3-ai-content-generator'); ?></p>
                                </div>
                            </div>

                            <!-- How it works -->
                            <div class="aipkit_rules_promo_how">
                                <div class="aipkit_rules_promo_step">
                                    <span class="aipkit_rules_promo_step_num">1</span>
                                    <span class="aipkit_rules_promo_step_label"><?php esc_html_e('Choose a trigger', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <span class="aipkit_rules_promo_step_arrow" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </span>
                                <div class="aipkit_rules_promo_step">
                                    <span class="aipkit_rules_promo_step_num">2</span>
                                    <span class="aipkit_rules_promo_step_label"><?php esc_html_e('Set conditions', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <span class="aipkit_rules_promo_step_arrow" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </span>
                                <div class="aipkit_rules_promo_step">
                                    <span class="aipkit_rules_promo_step_num">3</span>
                                    <span class="aipkit_rules_promo_step_label"><?php esc_html_e('Pick an action', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                            </div>

                            <!-- Feature cards -->
                            <div class="aipkit_rules_promo_grid" role="list">
                                <!-- Triggers -->
                                <div class="aipkit_rules_promo_card" role="listitem">
                                    <span class="aipkit_rules_promo_card_icon aipkit_rules_promo_card_icon--triggers" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    </span>
                                    <p class="aipkit_rules_promo_card_title"><?php esc_html_e('Trigger Events', 'gpt3-ai-content-generator'); ?></p>
                                    <ul class="aipkit_rules_promo_card_list">
                                        <li><?php esc_html_e('Message received', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('Session started', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('Form submitted', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('System error', 'gpt3-ai-content-generator'); ?></li>
                                    </ul>
                                </div>

                                <!-- Actions -->
                                <div class="aipkit_rules_promo_card" role="listitem">
                                    <span class="aipkit_rules_promo_card_icon aipkit_rules_promo_card_icon--actions" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                                    </span>
                                    <p class="aipkit_rules_promo_card_title"><?php esc_html_e('Action Types', 'gpt3-ai-content-generator'); ?></p>
                                    <ul class="aipkit_rules_promo_card_list">
                                        <li><?php esc_html_e('Send reply / Show form', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('Inject context', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('Block message', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('Set variable / Call webhook', 'gpt3-ai-content-generator'); ?></li>
                                    </ul>
                                </div>

                                <!-- Conditions -->
                                <div class="aipkit_rules_promo_card" role="listitem">
                                    <span class="aipkit_rules_promo_card_icon aipkit_rules_promo_card_icon--conditions" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                    </span>
                                    <p class="aipkit_rules_promo_card_title"><?php esc_html_e('Condition Groups', 'gpt3-ai-content-generator'); ?></p>
                                    <ul class="aipkit_rules_promo_card_list">
                                        <li><?php esc_html_e('Text / keyword matching', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('User role & auth state', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('Page & context filters', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('Regex & numeric operators', 'gpt3-ai-content-generator'); ?></li>
                                    </ul>
                                </div>

                                <!-- Webhooks -->
                                <div class="aipkit_rules_promo_card" role="listitem">
                                    <span class="aipkit_rules_promo_card_icon aipkit_rules_promo_card_icon--webhooks" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    </span>
                                    <p class="aipkit_rules_promo_card_title"><?php esc_html_e('External Workflows', 'gpt3-ai-content-generator'); ?></p>
                                    <ul class="aipkit_rules_promo_card_list">
                                        <li><?php esc_html_e('Notify Slack on demo requests', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('Send leads to Make / Zapier', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('WhatsApp Cloud API relay', 'gpt3-ai-content-generator'); ?></li>
                                        <li><?php esc_html_e('Tickets, emails & follow-ups', 'gpt3-ai-content-generator'); ?></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- CTA -->
                            <div class="aipkit_rules_promo_cta">
                                <a
                                    class="aipkit_rules_promo_btn aipkit_rules_promo_btn--primary"
                                    href="<?php echo esc_url($pricing_url); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    <?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>
                                </a>
                                <a
                                    class="aipkit_rules_promo_btn aipkit_rules_promo_btn--secondary"
                                    href="<?php echo esc_url('https://docs.aipower.org/docs/triggers'); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?php esc_html_e('Learn More', 'gpt3-ai-content-generator'); ?>
                                </a>
                            </div>
                        </div>
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

    <?php if ($active_bot_post) : ?>
        <div
            class="aipkit-modal-overlay aipkit_builder_embed_modal"
            id="aipkit_builder_embed_modal"
            aria-hidden="true"
        >
            <div
                class="aipkit-modal-content"
                role="dialog"
                aria-modal="true"
                aria-labelledby="aipkit_builder_embed_title"
                aria-describedby="aipkit_builder_embed_description"
            >
                <div class="aipkit-modal-header">
                    <div>
                        <h3 class="aipkit-modal-title" id="aipkit_builder_embed_title">
                            <?php esc_html_e('Embed Anywhere Setup', 'gpt3-ai-content-generator'); ?>
                        </h3>
                        <p class="aipkit_builder_modal_subtitle" id="aipkit_builder_embed_description">
                            <?php esc_html_e('Copy the snippet and set allowed domains for external websites.', 'gpt3-ai-content-generator'); ?>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="aipkit-modal-close-btn aipkit_builder_embed_close"
                        aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                    >
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="aipkit-modal-body">
                    <?php if ($embed_anywhere_active) : ?>
                        <div class="aipkit_builder_external_stack">
                            <div class="aipkit_builder_external_field">
                                <div class="aipkit_builder_external_field_header">
                                    <label
                                        class="aipkit_builder_external_label"
                                        for="aipkit_embed_code_<?php echo esc_attr($initial_active_bot_id); ?>"
                                    >
                                        <?php esc_html_e('Embed code', 'gpt3-ai-content-generator'); ?>
                                    </label>
                                    <button
                                        type="button"
                                        class="aipkit_btn aipkit_btn-secondary aipkit_btn-small aipkit_copy_embed_code_btn"
                                        data-target="aipkit_embed_code_<?php echo esc_attr($initial_active_bot_id); ?>"
                                    >
                                        <?php esc_html_e('Copy code', 'gpt3-ai-content-generator'); ?>
                                    </button>
                                </div>
                                <textarea
                                    id="aipkit_embed_code_<?php echo esc_attr($initial_active_bot_id); ?>"
                                    class="aipkit_builder_external_textarea aipkit_builder_external_textarea--code"
                                    readonly
                                ><?php echo esc_textarea($embed_code); ?></textarea>
                            </div>
                            <div class="aipkit_builder_external_field">
                                <label
                                    class="aipkit_builder_external_label"
                                    for="aipkit_embed_allowed_domains_<?php echo esc_attr($initial_active_bot_id); ?>"
                                >
                                    <?php esc_html_e('Allowed domains (one URL per line)', 'gpt3-ai-content-generator'); ?>
                                </label>
                                <textarea
                                    id="aipkit_embed_allowed_domains_<?php echo esc_attr($initial_active_bot_id); ?>"
                                    name="embed_allowed_domains"
                                    class="aipkit_builder_external_textarea aipkit_builder_external_textarea--domains"
                                    placeholder="<?php esc_attr_e('https://example.com', 'gpt3-ai-content-generator'); ?>"
                                ><?php echo esc_textarea($embed_allowed_domains); ?></textarea>
                            </div>
                        </div>
                        <div class="aipkit_builder_action_row aipkit_builder_action_row--end">
                            <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_builder_embed_done">
                                <?php esc_html_e('Done', 'gpt3-ai-content-generator'); ?>
                            </button>
                        </div>
                    <?php else : ?>
                        <div class="aipkit_embed_promo">
                            <!-- Hero -->
                            <div class="aipkit_embed_promo_hero">
                                <span class="aipkit_embed_promo_hero_icon" aria-hidden="true">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                </span>
                                <div class="aipkit_embed_promo_hero_text">
                                    <h4 class="aipkit_embed_promo_hero_title"><?php esc_html_e('Embed anywhere with one snippet', 'gpt3-ai-content-generator'); ?></h4>
                                    <p class="aipkit_embed_promo_hero_desc"><?php esc_html_e('Add your AI chatbot to any website — Shopify, Wix, static HTML, or your custom app — with a single script tag.', 'gpt3-ai-content-generator'); ?></p>
                                </div>
                            </div>

                            <!-- How it works -->
                            <div class="aipkit_embed_promo_steps">
                                <div class="aipkit_embed_promo_step">
                                    <span class="aipkit_embed_promo_step_icon aipkit_embed_promo_step_icon--copy" aria-hidden="true">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </span>
                                    <span class="aipkit_embed_promo_step_text"><?php esc_html_e('Copy the embed snippet', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <div class="aipkit_embed_promo_step">
                                    <span class="aipkit_embed_promo_step_icon aipkit_embed_promo_step_icon--paste" aria-hidden="true">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                    </span>
                                    <span class="aipkit_embed_promo_step_text"><?php esc_html_e('Paste into your site\'s HTML', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <div class="aipkit_embed_promo_step">
                                    <span class="aipkit_embed_promo_step_icon aipkit_embed_promo_step_icon--secure" aria-hidden="true">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    </span>
                                    <span class="aipkit_embed_promo_step_text"><?php esc_html_e('Restrict with allowed domains', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                            </div>

                            <!-- Fake code preview -->
                            <div class="aipkit_embed_promo_code">
                                <div class="aipkit_embed_promo_code_bar">
                                    <span class="aipkit_embed_promo_code_dot"></span>
                                    <span class="aipkit_embed_promo_code_dot"></span>
                                    <span class="aipkit_embed_promo_code_dot"></span>
                                    <span class="aipkit_embed_promo_code_label">HTML</span>
                                </div>
                                <pre class="aipkit_embed_promo_code_body" aria-hidden="true">&lt;script src="https://your-site.com/aipkit-embed.js"
  data-bot-id="<?php echo esc_html($initial_active_bot_id ? $initial_active_bot_id : 'xxxxx'); ?>"
  async&gt;&lt;/script&gt;</pre>
                            </div>

                            <!-- CTA -->
                            <div class="aipkit_embed_promo_cta">
                                <a
                                    class="aipkit_embed_promo_btn aipkit_embed_promo_btn--primary"
                                    href="<?php echo esc_url($pricing_url); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    <?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>
                                </a>
                                <a
                                    class="aipkit_embed_promo_btn aipkit_embed_promo_btn--secondary"
                                    href="<?php echo esc_url($embed_docs_url); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?php esc_html_e('Learn More', 'gpt3-ai-content-generator'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($active_bot_post && !$aipkit_hide_custom_theme) : ?>
        <?php
        $bot_id = $initial_active_bot_id;
        $bot_settings = $active_bot_settings;
        include __DIR__ . '/partials/appearance/custom-theme-flyout.php';
        ?>
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
