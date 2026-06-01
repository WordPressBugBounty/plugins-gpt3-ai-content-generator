<?php
 namespace WPAICG\Chat\Frontend\Assets; use WPAICG\aipkit_dashboard; use WPAICG\Chat\Frontend\Assets as AssetsOrchestrator; use WPAICG\Chat\Frontend\Shortcode\FeatureManager; use WPAICG\Chat\Storage\BotSettingsManager; use WPAICG\Chat\Storage\BotStorage; use WPAICG\Chat\Storage\SiteWideBotManager; use WPAICG\Includes\AIPKit_Shared_Assets_Manager; if (!defined('ABSPATH')) { exit; } class AssetsHooks { private $orchestrator; public function __construct(AssetsOrchestrator $orchestrator) { $this->orchestrator = $orchestrator; } public function register(): void { add_action('wp_enqueue_scripts', [$this->orchestrator, 'register_and_enqueue_frontend_assets_public_wrapper'], 99); add_action('template_redirect', [$this->orchestrator, 'check_for_site_wide_bot_public_wrapper']); } } class AssetsSiteWideChecker { public function __construct() { } public function check(): void { if (is_admin() || wp_doing_ajax()) return; if (!class_exists(SiteWideBotManager::class) || !class_exists(aipkit_dashboard::class) || !class_exists(BotStorage::class) || !class_exists(BotSettingsManager::class)) { return; } $manager = new SiteWideBotManager(); $bot_id = $manager->get_site_wide_bot_id(); if ($bot_id) { AssetsOrchestrator::$site_wide_injection_needed = true; $bot_storage = new BotStorage(); $settings = $bot_storage->get_chatbot_settings($bot_id); if (!class_exists(FeatureManager::class)) { $feature_manager_path = WPAICG_PLUGIN_DIR . 'classes/chat/frontend/shortcode/shortcode_featuremanager.php'; if (file_exists($feature_manager_path)) { require_once $feature_manager_path; } } $feature_flags = class_exists(FeatureManager::class) ? FeatureManager::determine_flags($settings) : []; AssetsOrchestrator::$consent_needed = true; if (!empty($feature_flags['enable_copy_button'])) AssetsOrchestrator::$copy_button_needed = true; if (!empty($feature_flags['feedback_ui_enabled'])) AssetsOrchestrator::$feedback_needed = true; if (!empty($feature_flags['starters_ui_enabled'])) AssetsOrchestrator::$starters_needed = true; if (!empty($feature_flags['sidebar_ui_enabled'])) AssetsOrchestrator::$sidebar_needed = true; if (!empty($feature_flags['tts_ui_enabled'])) AssetsOrchestrator::$tts_needed = true; if (!empty($feature_flags['enable_voice_input_ui'])) AssetsOrchestrator::$stt_needed = true; AssetsOrchestrator::$image_gen_needed = true; if (!empty($feature_flags['image_upload_ui_enabled'])) { AssetsOrchestrator::$chat_image_upload_needed = true; } if (!empty($feature_flags['file_upload_ui_enabled']) && aipkit_dashboard::is_pro_plan()) { AssetsOrchestrator::$chat_file_upload_needed = true; } if (!empty($feature_flags['enable_realtime_voice_ui']) && aipkit_dashboard::is_pro_plan()) { AssetsOrchestrator::$realtime_voice_needed = true; } } } } class AssetsRequireFlags { public static function set_flags( bool $needs_pdf = false, bool $needs_copy = false, bool $needs_starters = false, bool $needs_sidebar = false, bool $needs_feedback = false, bool $needs_tts = false, bool $needs_stt = false, bool $needs_image_gen = false, bool $needs_chat_image_upload = false, bool $needs_chat_file_upload = false, bool $needs_realtime_voice = false ): void { if (!class_exists(aipkit_dashboard::class)) { $dashboard_path = defined('WPAICG_PLUGIN_DIR') ? WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_dashboard.php' : null; if ($dashboard_path && file_exists($dashboard_path)) { require_once $dashboard_path; } else { if (!class_exists(aipkit_dashboard::class)) { return; } } } AssetsOrchestrator::$shortcode_rendered = true; AssetsOrchestrator::$consent_needed = true; if ($needs_copy) AssetsOrchestrator::$copy_button_needed = true; if ($needs_feedback) AssetsOrchestrator::$feedback_needed = true; if ($needs_starters) AssetsOrchestrator::$starters_needed = true; if ($needs_sidebar) AssetsOrchestrator::$sidebar_needed = true; if ($needs_tts) AssetsOrchestrator::$tts_needed = true; if ($needs_stt) AssetsOrchestrator::$stt_needed = true; if ($needs_image_gen) AssetsOrchestrator::$image_gen_needed = true; if ($needs_chat_image_upload) AssetsOrchestrator::$chat_image_upload_needed = true; if ($needs_chat_file_upload && aipkit_dashboard::is_pro_plan()) { AssetsOrchestrator::$chat_file_upload_needed = true; } if ($needs_realtime_voice && aipkit_dashboard::is_pro_plan()) { AssetsOrchestrator::$realtime_voice_needed = true; } } } class AssetsEnqueuer { private $is_public_main_css_enqueued = false; private $is_public_main_js_enqueued_by_this = false; public function __construct() { } public function process_assets(): void { if (is_admin()) { return; } if (!AssetsOrchestrator::$assets_registered) { if (class_exists(AssetsDependencyRegistrar::class) && method_exists(AssetsDependencyRegistrar::class, 'register')) { AssetsDependencyRegistrar::register(); AssetsOrchestrator::$assets_registered = true; } else { return; } } global $post; $content = is_a($post, 'WP_Post') ? $post->post_content : ''; $found_in_content = has_shortcode($content, 'aipkit_chatbot') || has_block('aipkit/chatbot', $content); $should_enqueue_core_css = AssetsOrchestrator::$shortcode_rendered || AssetsOrchestrator::$site_wide_injection_needed || $found_in_content; $should_enqueue_core_js = $should_enqueue_core_css; $public_main_css_handle = 'aipkit-public-main-css'; if (!wp_style_is($public_main_css_handle, 'registered')) { $public_main_css_file = WPAICG_PLUGIN_DIR . 'dist/css/public-main.bundle.css'; $public_main_css_version = file_exists($public_main_css_file) ? (string) filemtime($public_main_css_file) : (defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0'); wp_register_style( $public_main_css_handle, WPAICG_PLUGIN_URL . 'dist/css/public-main.bundle.css', [], $public_main_css_version ); } if ($should_enqueue_core_css && !$this->is_public_main_css_enqueued && !wp_style_is($public_main_css_handle, 'enqueued')) { wp_enqueue_style($public_main_css_handle); $this->is_public_main_css_enqueued = true; } $public_main_js_handle = 'aipkit-public-main'; if (!wp_script_is($public_main_js_handle, 'registered')) { wp_register_script( $public_main_js_handle, WPAICG_PLUGIN_URL . 'dist/js/public-main.bundle.js', [], defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0', true ); } if ($should_enqueue_core_js) { if (!wp_script_is($public_main_js_handle, 'enqueued')) { wp_enqueue_script($public_main_js_handle); $this->is_public_main_js_enqueued_by_this = true; } if (class_exists(AIPKit_Shared_Assets_Manager::class)) { AIPKit_Shared_Assets_Manager::attach_public_asset_urls($public_main_js_handle); } if (AssetsOrchestrator::$sidebar_needed && wp_script_is('aipkit-public-chat-sidebar', 'registered')) { if (!wp_script_is('aipkit-public-chat-sidebar', 'enqueued')) { wp_enqueue_script('aipkit-public-chat-sidebar'); } } static $global_chat_localized = false; if (!$global_chat_localized && wp_script_is($public_main_js_handle, 'enqueued')) { wp_localize_script($public_main_js_handle, 'aipkit_chat_config_global', [ 'text' => [ 'fullscreenError' => __('Error: Fullscreen functionality is unavailable.', 'gpt3-ai-content-generator'), 'copySuccess' => __('Copied!', 'gpt3-ai-content-generator'), 'copyFail' => __('Failed to copy', 'gpt3-ai-content-generator'), 'copyActionLabel' => __('Copy response', 'gpt3-ai-content-generator'), 'feedbackSubmitted' => __('Feedback submitted', 'gpt3-ai-content-generator'), 'feedbackError' => __('Error saving feedback', 'gpt3-ai-content-generator'), 'newChat' => __('New Chat', 'gpt3-ai-content-generator'), 'sidebarToggle' => __('Toggle Conversation Sidebar', 'gpt3-ai-content-generator'), 'historyGuests' => __('History unavailable for guests.', 'gpt3-ai-content-generator'), 'historyEmpty' => __('No past conversations.', 'gpt3-ai-content-generator'), 'playActionLabel' => __('Play audio', 'gpt3-ai-content-generator'), 'pauseActionLabel' => __('Pause audio', 'gpt3-ai-content-generator'), 'uploadFile' => __('Upload File (TXT, PDF)', 'gpt3-ai-content-generator'), 'fileContextActive' => __('Chatting with: %s', 'gpt3-ai-content-generator'), 'clearFileContext' => __('Clear file context', 'gpt3-ai-content-generator'), ] ]); wp_add_inline_script( $public_main_js_handle, 'window.aipkit_getChatNonceAction = "aipkit_get_frontend_chat_nonce";', 'before' ); $global_chat_localized = true; } static $nonce_wrapper_injected = false; if (!$nonce_wrapper_injected && wp_script_is($public_main_js_handle, 'enqueued')) { $wrapper_js = <<<'JS'
;(function(){
  try{
    if(typeof window.aipkit_frontendApiRequest === 'function'){
      var __aipkit_origFrontendApiRequest = window.aipkit_frontendApiRequest;
      function __aipkit_refreshNonce(cfg){
        return new Promise(function(resolve,reject){
          try{
            if(!cfg || !cfg.ajaxUrl){ return reject(new Error('No ajaxUrl for nonce refresh')); }
            var fd = new FormData();
            fd.append('action', (typeof window.aipkit_getChatNonceAction === 'string' && window.aipkit_getChatNonceAction) ? window.aipkit_getChatNonceAction : 'aipkit_get_frontend_chat_nonce');
            if(cfg.botId){ fd.append('bot_id', cfg.botId); }
            fetch(cfg.ajaxUrl, { method:'POST', body: fd, credentials:'same-origin' })
              .then(function(r){ return r.json(); })
              .then(function(j){ if(j && j.success && j.data && j.data.nonce){ cfg.nonce = j.data.nonce; resolve(j.data.nonce); } else { reject(new Error('Nonce refresh failed')); } })
              .catch(function(){ reject(new Error('Nonce refresh network error')); });
          }catch(e){ reject(e); }
        });
      }
      window.aipkit_frontendApiRequest = function(action, data, cfg){
        return __aipkit_origFrontendApiRequest(action, data, cfg).catch(function(err){
          var msg = (err && err.message ? String(err.message) : '').toLowerCase();
          if(msg.indexOf('security check failed') !== -1 || msg.indexOf('session has expired') !== -1 || msg.indexOf('nonce') !== -1){
            return __aipkit_refreshNonce(cfg).then(function(){ return __aipkit_origFrontendApiRequest(action, data, cfg); });
          }
          throw err;
        });
      };
    }
  }catch(e){ /* noop */ }
})();
;(function(){
  try{
    if(typeof window.aipkit_chatUI_cacheSseMessage === 'function'){
      var __aipkit_origCacheSseMessage = window.aipkit_chatUI_cacheSseMessage;
      function __aipkit_refreshNonce(cfg){
        return new Promise(function(resolve,reject){
          try{
            if(!cfg || !cfg.ajaxUrl){ return reject(new Error('No ajaxUrl for nonce refresh')); }
            var fd = new FormData();
            fd.append('action', (typeof window.aipkit_getChatNonceAction === 'string' && window.aipkit_getChatNonceAction) ? window.aipkit_getChatNonceAction : 'aipkit_get_frontend_chat_nonce');
            if(cfg.botId){ fd.append('bot_id', cfg.botId); }
            fetch(cfg.ajaxUrl, { method:'POST', body: fd, credentials:'same-origin' })
              .then(function(r){ return r.json(); })
              .then(function(j){ if(j && j.success && j.data && j.data.nonce){ cfg.nonce = j.data.nonce; resolve(j.data.nonce); } else { reject(new Error('Nonce refresh failed')); } })
              .catch(function(){ reject(new Error('Nonce refresh network error')); });
          }catch(e){ reject(e); }
        });
      }
      window.aipkit_chatUI_cacheSseMessage = function(userText, cfg, imageDataPayload, activeFileContext, clientUserMessageId, streamOptions){
        return __aipkit_origCacheSseMessage(userText, cfg, imageDataPayload, activeFileContext, clientUserMessageId, streamOptions).catch(function(err){
          var msg = (err && err.message ? String(err.message) : '').toLowerCase();
          if(msg.indexOf('security check failed') !== -1 || msg.indexOf('session has expired') !== -1 || msg.indexOf('nonce') !== -1){
            return __aipkit_refreshNonce(cfg).then(function(){
              return __aipkit_origCacheSseMessage(userText, cfg, imageDataPayload, activeFileContext, clientUserMessageId, streamOptions);
            });
          }
          throw err;
        });
      };
    }
  }catch(e){ /* noop */ }
})();
JS;
wp_add_inline_script($public_main_js_handle, $wrapper_js, 'after'); $nonce_wrapper_injected = true; } } } } class AssetsDependencyRegistrar { public static function register(): void { $version = defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0'; $plugin_base_url = defined('WPAICG_PLUGIN_URL') ? WPAICG_PLUGIN_URL : plugin_dir_url(dirname(dirname(dirname(dirname(dirname(__FILE__)))))); $dist_js_url = $plugin_base_url . 'dist/js/'; $public_main_js_handle = 'aipkit-public-main'; if (!wp_script_is($public_main_js_handle, 'registered')) { wp_register_script( $public_main_js_handle, $dist_js_url . 'public-main.bundle.js', [], $version, true ); } $public_chat_sidebar_js_handle = 'aipkit-public-chat-sidebar'; if (!wp_script_is($public_chat_sidebar_js_handle, 'registered')) { wp_register_script( $public_chat_sidebar_js_handle, $dist_js_url . 'public-chat-sidebar.bundle.js', [$public_main_js_handle], $version, true ); } } } 