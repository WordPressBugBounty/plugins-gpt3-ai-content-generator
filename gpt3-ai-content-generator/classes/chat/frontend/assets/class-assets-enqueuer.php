<?php
 namespace WPAICG\Chat\Frontend\Assets; use WPAICG\Chat\Frontend\Assets as AssetsOrchestrator; use WPAICG\Includes\AIPKit_Shared_Assets_Manager; if (!defined('ABSPATH')) { exit; } class AssetsEnqueuer { private $is_public_main_css_enqueued = false; private $is_public_main_js_enqueued_by_this = false; public function __construct() { } public function process_assets(): void { if (is_admin()) { return; } if (!AssetsOrchestrator::$assets_registered) { if (class_exists(AssetsDependencyRegistrar::class) && method_exists(AssetsDependencyRegistrar::class, 'register')) { AssetsDependencyRegistrar::register(); AssetsOrchestrator::$assets_registered = true; } else { return; } } global $post; $content = is_a($post, 'WP_Post') ? $post->post_content : ''; $found_in_content = has_shortcode($content, 'aipkit_chatbot') || has_block('aipkit/chatbot', $content); $should_enqueue_core_css = AssetsOrchestrator::$shortcode_rendered || AssetsOrchestrator::$site_wide_injection_needed || $found_in_content; $should_enqueue_core_js = $should_enqueue_core_css; $public_main_css_handle = 'aipkit-public-main-css'; if (!wp_style_is($public_main_css_handle, 'registered')) { $public_main_css_file = WPAICG_PLUGIN_DIR . 'dist/css/public-main.bundle.css'; $public_main_css_version = file_exists($public_main_css_file) ? (string) filemtime($public_main_css_file) : (defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0'); wp_register_style( $public_main_css_handle, WPAICG_PLUGIN_URL . 'dist/css/public-main.bundle.css', [], $public_main_css_version ); } if ($should_enqueue_core_css && !$this->is_public_main_css_enqueued && !wp_style_is($public_main_css_handle, 'enqueued')) { wp_enqueue_style($public_main_css_handle); $this->is_public_main_css_enqueued = true; } $public_main_js_handle = 'aipkit-public-main'; if (!wp_script_is($public_main_js_handle, 'registered')) { wp_register_script( $public_main_js_handle, WPAICG_PLUGIN_URL . 'dist/js/public-main.bundle.js', [], defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0', true ); } if ($should_enqueue_core_js) { if (!wp_script_is($public_main_js_handle, 'enqueued')) { wp_enqueue_script($public_main_js_handle); $this->is_public_main_js_enqueued_by_this = true; } if (class_exists(AIPKit_Shared_Assets_Manager::class)) { AIPKit_Shared_Assets_Manager::attach_public_asset_urls($public_main_js_handle); } if (AssetsOrchestrator::$sidebar_needed && wp_script_is('aipkit-public-chat-sidebar', 'registered')) { if (!wp_script_is('aipkit-public-chat-sidebar', 'enqueued')) { wp_enqueue_script('aipkit-public-chat-sidebar'); } } static $global_chat_localized = false; if (!$global_chat_localized && wp_script_is($public_main_js_handle, 'enqueued')) { wp_localize_script($public_main_js_handle, 'aipkit_chat_config_global', [ 'text' => [ 'fullscreenError' => __('Error: Fullscreen functionality is unavailable.', 'gpt3-ai-content-generator'), 'copySuccess' => __('Copied!', 'gpt3-ai-content-generator'), 'copyFail' => __('Failed to copy', 'gpt3-ai-content-generator'), 'copyActionLabel' => __('Copy response', 'gpt3-ai-content-generator'), 'feedbackSubmitted' => __('Feedback submitted', 'gpt3-ai-content-generator'), 'feedbackError' => __('Error saving feedback', 'gpt3-ai-content-generator'), 'newChat' => __('New Chat', 'gpt3-ai-content-generator'), 'sidebarToggle' => __('Toggle Conversation Sidebar', 'gpt3-ai-content-generator'), 'historyGuests' => __('History unavailable for guests.', 'gpt3-ai-content-generator'), 'historyEmpty' => __('No past conversations.', 'gpt3-ai-content-generator'), 'playActionLabel' => __('Play audio', 'gpt3-ai-content-generator'), 'pauseActionLabel' => __('Pause audio', 'gpt3-ai-content-generator'), 'uploadFile' => __('Upload File (TXT, PDF)', 'gpt3-ai-content-generator'), 'fileContextActive' => __('Chatting with: %s', 'gpt3-ai-content-generator'), 'clearFileContext' => __('Clear file context', 'gpt3-ai-content-generator'), ] ]); wp_add_inline_script( $public_main_js_handle, 'window.aipkit_getChatNonceAction = "aipkit_get_frontend_chat_nonce";', 'before' ); $global_chat_localized = true; } static $nonce_wrapper_injected = false; if (!$nonce_wrapper_injected && wp_script_is($public_main_js_handle, 'enqueued')) { $wrapper_js = <<<'JS'
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
wp_add_inline_script($public_main_js_handle, $wrapper_js, 'after'); $nonce_wrapper_injected = true; } } } } 