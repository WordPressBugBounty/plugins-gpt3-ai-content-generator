<?php
 namespace WPAICG\WP_AI_Client; if (!defined('ABSPATH')) { exit; } class AIPKit_WP_AI_Client_Connectors { private static bool $syncing = false; public static function register_hooks(): void { add_action('admin_footer', [self::class, 'maybe_render_banner']); add_action('wp_ajax_aipkit_wp_ai_client_set_mode', [self::class, 'ajax_set_mode']); add_action('wp_connectors_init', [self::class, 'customize_registry'], 1000); add_action('admin_init', [self::class, 'sync_provider_keys_to_connectors'], 1000); add_action('updated_option', [self::class, 'maybe_bridge_connector_key'], 10, 3); add_action('added_option', [self::class, 'maybe_bridge_added_connector_key'], 10, 2); add_action('update_option_aipkit_options', [self::class, 'sync_provider_keys_to_connectors'], 1000, 0); add_filter('script_module_data_options-connectors-wp-admin', [self::class, 'mark_managed_connectors_connected'], 1000); add_filter('wpai_has_ai_credentials', [self::class, 'declare_credentials_present'], 1000, 1); add_filter('wpai_pre_has_valid_credentials_check', [self::class, 'declare_credentials_valid'], 1000, 1); } public static function customize_registry(\WP_Connector_Registry $registry): void { if (!AIPKit_WP_AI_Client_Settings::is_effectively_managed()) { return; } foreach (AIPKit_WP_AI_Client_Settings::providers() as $connector_id => $config) { try { if ($registry->is_registered($connector_id)) { $registry->unregister($connector_id); } $auth = !empty($config['keyless']) ? ['method' => 'none'] : [ 'method' => 'api_key', 'setting_name' => AIPKit_WP_AI_Client_Settings::connector_option_name($connector_id), 'constant_name' => AIPKit_WP_AI_Client_Settings::connector_constant_name($connector_id), 'env_var_name' => AIPKit_WP_AI_Client_Settings::connector_constant_name($connector_id), ]; if (!empty($config['credentials_url']) && empty($config['keyless'])) { $auth['credentials_url'] = $config['credentials_url']; } $registry->register($connector_id, [ 'name' => ($config['name'] ?? ucwords($connector_id)) . ' via AI Puffer', 'description' => $config['description'] ?? __('AI provider managed by AI Puffer.', 'gpt3-ai-content-generator'), 'type' => 'ai_provider', 'authentication' => $auth, 'plugin' => [ 'file' => plugin_basename(WPAICG_PLUGIN_DIR . 'gpt3-ai-content-generator.php'), 'is_active' => '__return_true', ], ]); } catch (\Throwable $e) { continue; } } } public static function maybe_render_banner(): void { if (!is_admin() || !\WPAICG\AIPKit_Role_Manager::user_can_manage_settings() || !self::is_connectors_screen()) { return; } if (!AIPKit_WP_AI_Client_Settings::is_supported()) { return; } $nonce = wp_create_nonce('aipkit_wp_ai_client_set_mode'); $managed = AIPKit_WP_AI_Client_Settings::is_effectively_managed(); if (!$managed && AIPKit_WP_AI_Client_Settings::is_banner_dismissed()) { return; } $configure_url = admin_url('admin.php?page=wpaicg&aipkit_module=settings'); $learn_more_url = apply_filters('aipkit_wp_ai_client_learn_more_url', 'https://docs.aipower.org/wordpress-ai-connectors'); $payload = [ 'nonce' => $nonce, 'ajaxUrl' => admin_url('admin-ajax.php'), 'settings' => $configure_url, 'learnUrl' => $learn_more_url, 'managed' => $managed, 'title' => $managed ? __('Managed by AI Puffer.', 'gpt3-ai-content-generator') : __('AI Puffer can manage your connectors.', 'gpt3-ai-content-generator'), 'sub' => $managed ? __('All WordPress AI requests run through AI Puffer.', 'gpt3-ai-content-generator') : __('Add usage logs, cost tracking, quotas, feature-level defaults, and more providers in one click.', 'gpt3-ai-content-generator'), 'enable' => __('Enable', 'gpt3-ai-content-generator'), 'dismiss' => __('Dismiss', 'gpt3-ai-content-generator'), 'configure' => __('Configure', 'gpt3-ai-content-generator'), 'stop' => __('Stop', 'gpt3-ai-content-generator'), 'learnMore' => __('Learn more', 'gpt3-ai-content-generator'), 'manageLead' => __('Need another provider? Add or edit providers in', 'gpt3-ai-content-generator'), 'manageLink' => __('AI Puffer settings', 'gpt3-ai-content-generator'), ]; ?>
        <style>
            .aipkit-wpai-banner {
                margin: 0 0 16px;
                padding: 12px 16px;
                display: flex;
                align-items: center;
                gap: 12px;
                background: #f0f4ff;
                border: 1px solid #d6deff;
                border-radius: 4px;
                color: #1e1e1e;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                font-size: 13px;
                line-height: 1.4;
            }
            .aipkit-wpai-banner.is-managed {
                background: #e8f8ef;
                border-color: #c3ecd3;
            }
            .aipkit-wpai-banner-icon {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                background: #2f5fff;
                color: #fff;
            }
            .aipkit-wpai-banner.is-managed .aipkit-wpai-banner-icon {
                background: #10b981;
            }
            .aipkit-wpai-banner-icon svg {
                width: 18px;
                height: 18px;
                display: block;
            }
            .aipkit-wpai-banner-text {
                flex: 1;
                min-width: 0;
            }
            .aipkit-wpai-banner-text strong {
                font-weight: 600;
            }
            .aipkit-wpai-banner-text span {
                color: #50575e;
            }
            .aipkit-wpai-banner-actions {
                display: flex;
                flex-shrink: 0;
                gap: 6px;
            }
            .aipkit-wpai-btn {
                appearance: none;
                border: 1px solid transparent;
                border-radius: 3px;
                padding: 4px 12px;
                font: inherit;
                font-size: 12px;
                font-weight: 500;
                cursor: pointer;
                text-decoration: none;
                transition: background 0.12s ease, border-color 0.12s ease;
            }
            .aipkit-wpai-btn-primary {
                background: #2f5fff;
                color: #fff;
            }
            .aipkit-wpai-btn-primary:hover {
                background: #2448cc;
                color: #fff;
            }
            .aipkit-wpai-banner.is-managed .aipkit-wpai-btn-primary {
                background: #10b981;
            }
            .aipkit-wpai-banner.is-managed .aipkit-wpai-btn-primary:hover {
                background: #0d9b6d;
            }
            .aipkit-wpai-btn-ghost {
                background: transparent;
                color: #50575e;
                border-color: transparent;
            }
            .aipkit-wpai-btn-ghost:hover {
                background: rgba(0, 0, 0, 0.04);
                color: #1e1e1e;
            }
            .aipkit-wpai-btn[disabled] {
                cursor: default;
                opacity: 0.5;
            }
        </style>
        <script>
        (function() {
            var D = <?php echo wp_json_encode($payload); ?>;

            function buildBanner() {
                var host = document.createElement('div');
                host.className = 'aipkit-wpai-banner' + (D.managed ? ' is-managed' : '');
                host.setAttribute('role', 'status');
                var iconSvg = D.managed
                    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="5 12 10 17 19 7"></polyline></svg>'
                    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>';

                host.innerHTML = [
                    '<span class="aipkit-wpai-banner-icon">', iconSvg, '</span>',
                    '<div class="aipkit-wpai-banner-text">',
                        '<strong></strong> <span class="aipkit-wpai-banner-sub"></span>',
                    '</div>',
                    '<div class="aipkit-wpai-banner-actions"></div>'
                ].join('');
                host.querySelector('strong').textContent = D.title;
                host.querySelector('.aipkit-wpai-banner-sub').textContent = D.sub;

                var actions = host.querySelector('.aipkit-wpai-banner-actions');
                function addAction(label, className, mode, href, newTab) {
                    var control = href ? document.createElement('a') : document.createElement('button');
                    control.className = 'aipkit-wpai-btn ' + className;
                    control.textContent = label;
                    if (href) {
                        control.href = href;
                        if (newTab) {
                            control.target = '_blank';
                            control.rel = 'noopener noreferrer';
                        }
                    } else {
                        control.type = 'button';
                        control.addEventListener('click', toggleMode(mode));
                    }
                    actions.appendChild(control);
                }

                if (D.managed) {
                    addAction(D.configure, 'aipkit-wpai-btn-primary', null, D.settings, false);
                    addAction(D.stop, 'aipkit-wpai-btn-ghost', 'observe', null, false);
                } else {
                    addAction(D.enable, 'aipkit-wpai-btn-primary', 'managed', null, false);
                    addAction(D.learnMore, 'aipkit-wpai-btn-ghost', null, D.learnUrl, true);
                    addAction(D.dismiss, 'aipkit-wpai-btn-ghost', 'dismiss', null, false);
                }

                return host;
            }

            function toggleMode(mode) {
                return function(event) {
                    var button = event.currentTarget;
                    button.disabled = true;
                    var body = new window.URLSearchParams();
                    body.set('action', 'aipkit_wp_ai_client_set_mode');
                    body.set('nonce', D.nonce);
                    body.set('mode', mode);
                    window.fetch(D.ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: body.toString()
                    }).then(function(response) {
                        if (!response.ok) {
                            return response.json().then(function(payload) {
                                var message = payload && payload.data && payload.data.message ? payload.data.message : 'Unable to update connector management.';
                                throw new Error(message);
                            });
                        }
                        window.location.reload();
                    }).catch(function() {
                        button.disabled = false;
                    });
                };
            }

            function ensureBanner() {
                var page = document.querySelector('.connectors-page');
                var existing = document.querySelector('.aipkit-wpai-banner');
                if (existing && page && page.firstElementChild !== existing) {
                    page.insertBefore(existing, page.firstChild);
                    return;
                }
                if (existing) { return; }
                if (page) {
                    page.insertBefore(buildBanner(), page.firstChild);
                    return;
                }
                var header = document.querySelector('.boot-layout__stage header');
                if (header && header.parentNode) {
                    header.parentNode.insertBefore(buildBanner(), header.nextSibling);
                }
            }

            function tweakManagedPage() {
                if (!D.managed) { return; }
                var page = document.querySelector('.connectors-page');
                if (!page) { return; }

                var paragraphs = page.querySelectorAll('p');
                for (var i = 0; i < paragraphs.length; i++) {
                    var paragraph = paragraphs[i];
                    if (paragraph.dataset.aipkitReplaced) { continue; }
                    var text = (paragraph.textContent || '').toLowerCase();
                    if (text.indexOf('plugin directory') !== -1 || text.indexOf('search') !== -1) {
                        paragraph.innerHTML = '';
                        var link = document.createElement('a');
                        link.href = D.settings;
                        link.textContent = D.manageLink;
                        paragraph.appendChild(document.createTextNode(D.manageLead + ' '));
                        paragraph.appendChild(link);
                        paragraph.appendChild(document.createTextNode('.'));
                        paragraph.dataset.aipkitReplaced = '1';
                    }
                }

                var buttons = page.querySelectorAll('button');
                for (var j = 0; j < buttons.length; j++) {
                    var editButton = buttons[j];
                    if (editButton.dataset.aipkitIntercepted) { continue; }
                    if ((editButton.textContent || '').trim() === 'Edit') {
                        editButton.dataset.aipkitIntercepted = '1';
                        editButton.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            window.location.href = D.settings;
                        }, true);
                    }
                }
            }

            function run() {
                ensureBanner();
                tweakManagedPage();
            }

            var tries = 0;
            var interval = window.setInterval(function() {
                run();
                tries++;
                if (document.querySelector('.aipkit-wpai-banner') || tries > 40) {
                    window.clearInterval(interval);
                }
            }, 120);

            var app = document.getElementById('options-connectors-wp-admin-app') || document.getElementById('options-connectors-app');
            if (app && 'MutationObserver' in window) {
                new window.MutationObserver(run).observe(app, { childList: true, subtree: true });
            }
        })();
        </script>
        <?php
 } public static function ajax_set_mode(): void { check_ajax_referer('aipkit_wp_ai_client_set_mode', 'nonce'); if (!\WPAICG\AIPKit_Role_Manager::user_can_manage_settings()) { wp_send_json_error(['message' => 'forbidden'], 403); } $mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : AIPKit_WP_AI_Client_Settings::MODE_OBSERVE; if ($mode === 'dismiss') { AIPKit_WP_AI_Client_Settings::set_banner_dismissed(true); wp_send_json_success(['dismissed' => true]); } AIPKit_WP_AI_Client_Settings::set_mode($mode); AIPKit_WP_AI_Client_Settings::set_banner_dismissed(false); if ($mode === AIPKit_WP_AI_Client_Settings::MODE_MANAGED) { AIPKit_WP_AI_Client_Settings::sync_provider_keys_to_connectors(); } wp_send_json_success(['mode' => AIPKit_WP_AI_Client_Settings::get_mode()]); } public static function maybe_bridge_connector_key(string $option, $old_value, $value): void { self::bridge_option_to_provider($option, $value); } public static function maybe_bridge_added_connector_key(string $option, $value): void { self::bridge_option_to_provider($option, $value); } public static function sync_provider_keys_to_connectors(): void { if (self::$syncing || !AIPKit_WP_AI_Client_Settings::is_effectively_managed()) { return; } self::$syncing = true; AIPKit_WP_AI_Client_Settings::sync_provider_keys_to_connectors(); self::$syncing = false; } public static function mark_managed_connectors_connected(array $data): array { if (!AIPKit_WP_AI_Client_Settings::is_effectively_managed()) { return $data; } if (empty($data['connectors']) || !is_array($data['connectors'])) { return $data; } foreach (AIPKit_WP_AI_Client_Settings::providers() as $connector_id => $config) { if (empty($data['connectors'][$connector_id]['authentication'])) { continue; } if (!AIPKit_WP_AI_Client_Settings::provider_has_credentials($connector_id)) { continue; } $data['connectors'][$connector_id]['authentication']['isConnected'] = true; if (empty($data['connectors'][$connector_id]['authentication']['keySource']) || $data['connectors'][$connector_id]['authentication']['keySource'] === 'none') { $data['connectors'][$connector_id]['authentication']['keySource'] = !empty($config['keyless']) ? 'none' : 'database'; } } return $data; } public static function declare_credentials_present($has_credentials): bool { if (!AIPKit_WP_AI_Client_Settings::is_effectively_managed()) { return (bool) $has_credentials; } if ($has_credentials) { return true; } foreach (array_keys(AIPKit_WP_AI_Client_Settings::providers()) as $connector_id) { if (AIPKit_WP_AI_Client_Settings::provider_has_credentials($connector_id)) { return true; } } return false; } public static function declare_credentials_valid($valid) { if (!AIPKit_WP_AI_Client_Settings::is_effectively_managed()) { return $valid; } return true; } private static function bridge_option_to_provider(string $option, $value): void { if (self::$syncing || !AIPKit_WP_AI_Client_Settings::is_effectively_managed()) { return; } foreach (array_keys(AIPKit_WP_AI_Client_Settings::providers()) as $connector_id) { if ($option !== AIPKit_WP_AI_Client_Settings::connector_option_name($connector_id)) { continue; } self::$syncing = true; AIPKit_WP_AI_Client_Settings::sync_connector_key_to_provider($connector_id, is_scalar($value) ? (string) $value : ''); self::$syncing = false; return; } } private static function is_connectors_screen(): bool { global $pagenow; if ($pagenow === 'options-connectors.php') { return true; } $screen = function_exists('get_current_screen') ? get_current_screen() : null; if ($screen && isset($screen->id) && strpos((string) $screen->id, 'options-connectors') !== false) { return true; } return false; } } 