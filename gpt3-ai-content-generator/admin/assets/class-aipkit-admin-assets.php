<?php

namespace WPAICG\Admin\Assets;

use WPAICG\aipkit_dashboard;
use WPAICG\AIPKit_Providers;
use WPAICG\AIPKit_Role_Manager;
use WPAICG\AIPKIT_AI_Settings;
use WPAICG\AutoGPT\Helpers\AIPKit_AutoGPT_Prompt_Definitions;
use WPAICG\Chat\Frontend\Assets as ChatFrontendAssetsOrchestrator;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompts;
use WPAICG\Images\AIPKit_Image_Settings_Ajax_Handler;
use WPAICG\Includes\AIPKit_Shared_Assets_Manager;
use WPAICG\PostEnhancer\Ajax\AIPKit_Enhancer_Actions_Ajax_Handler;
use WPAICG\Utils\AIPKit_Admin_Header_Action_Buttons;

if (! defined('ABSPATH')) {
    exit;
}

abstract class AIPKit_Admin_Asset_Base
{
    protected $version;

    public function __construct()
    {
        $this->version = self::plugin_version();
    }

    protected static function plugin_version(): string
    {
        return defined('WPAICG_VERSION') ? (string) WPAICG_VERSION : '1.9.15';
    }

    protected static function asset_version(string $relative_path, ?string $fallback = null): string
    {
        $fallback = $fallback ?: self::plugin_version();
        $full_path = WPAICG_PLUGIN_DIR . ltrim($relative_path, '/');

        if (file_exists($full_path)) {
            $mtime = filemtime($full_path);
            if (is_int($mtime) && $mtime > 0) {
                return (string) $mtime;
            }
        }

        return $fallback;
    }

    protected static function script_deps(): array
    {
        return ['wp-i18n', 'aipkit_markdown-it'];
    }

    protected function is_aipkit_page($screen = null): bool
    {
        $screen = $screen ?: get_current_screen();

        return $screen && strpos($screen->id, 'page_wpaicg') !== false;
    }

    protected function ensure_shared_vendor_assets(): void
    {
        if (! wp_script_is('aipkit_markdown-it', 'registered') && class_exists(AIPKit_Shared_Assets_Manager::class)) {
            AIPKit_Shared_Assets_Manager::register($this->version);
        }
    }

    protected function register_style_bundle(string $handle, string $file, array $deps = ['dashicons'], ?string $version = null): void
    {
        if (! wp_style_is($handle, 'registered')) {
            wp_register_style(
                $handle,
                WPAICG_PLUGIN_URL . 'dist/css/' . ltrim($file, '/'),
                $deps,
                $version ?: $this->version
            );
        }
    }

    protected function enqueue_style_handle(string $handle): void
    {
        if (! wp_style_is($handle, 'enqueued')) {
            wp_enqueue_style($handle);
        }
    }

    protected function register_script_bundle(string $handle, string $file, array $deps = [], ?string $version = null): void
    {
        $this->ensure_shared_vendor_assets();

        if (! wp_script_is($handle, 'registered')) {
            wp_register_script(
                $handle,
                WPAICG_PLUGIN_URL . 'dist/js/' . ltrim($file, '/'),
                ! empty($deps) ? $deps : self::script_deps(),
                $version ?: $this->version,
                true
            );
        }
    }

    protected function enqueue_script_handle(string $handle, bool $translations = true): void
    {
        if (! wp_script_is($handle, 'enqueued')) {
            wp_enqueue_script($handle);

            if ($translations) {
                wp_set_script_translations($handle, 'gpt3-ai-content-generator', WPAICG_PLUGIN_DIR . 'languages');
            }
        }
    }

    protected function register_admin_main_css(?string $version = null): void
    {
        $this->register_style_bundle('aipkit-admin-main-css', 'admin-main.bundle.css', ['dashicons'], $version);
    }

    protected function enqueue_admin_main_css(?string $version = null): void
    {
        $this->register_admin_main_css($version);
        $this->enqueue_style_handle('aipkit-admin-main-css');
    }

    protected function register_admin_main_script(?string $version = null): void
    {
        $this->register_script_bundle('aipkit-admin-main', 'admin-main.bundle.js', self::script_deps(), $version);
    }

    protected function enqueue_admin_main_script(?string $version = null): void
    {
        $this->register_admin_main_script($version);
        $this->enqueue_script_handle('aipkit-admin-main');
    }

    protected function register_public_main_script(?string $version = null): void
    {
        $this->register_script_bundle('aipkit-public-main', 'public-main.bundle.js', self::script_deps(), $version);
    }

    protected function enqueue_public_main_script(?string $version = null): void
    {
        $this->register_public_main_script($version);
        $this->enqueue_script_handle('aipkit-public-main');
        if (class_exists(AIPKit_Shared_Assets_Manager::class)) {
            AIPKit_Shared_Assets_Manager::attach_public_asset_urls('aipkit-public-main');
        }
    }

    protected function ensure_dashboard_core_data(): void
    {
        if (class_exists(DashboardAssets::class) && method_exists(DashboardAssets::class, 'localize_core_data')) {
            DashboardAssets::localize_core_data($this->version);
        }
    }

    protected static function is_script_localized(string $handle, string $object_name): bool
    {
        $data = wp_scripts()->get_data($handle, 'data');

        return is_string($data) && strpos($data, "var {$object_name} =") !== false;
    }

    protected static function dashboard_texts(): array
    {
        $path = WPAICG_PLUGIN_DIR . 'admin/data/dashboard-localized-texts.php';

        return file_exists($path) ? require $path : [];
    }
}

class DashboardAssets extends AIPKit_Admin_Asset_Base
{
    private static $is_core_data_localized = false;

    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_core_dashboard_assets']);
    }

    public function enqueue_core_dashboard_assets($hook_suffix)
    {
        $screen = get_current_screen();
        $is_dashboard_screen = $screen && (
            strpos($screen->id, 'page_wpaicg') !== false ||
            $screen->id === 'toplevel_page_wpaicg' ||
            strpos($screen->id, 'aipkit-role-manager') !== false
        );

        if (! $is_dashboard_screen) {
            return;
        }

        $this->enqueue_admin_main_css(self::asset_version('dist/css/admin-main.bundle.css', $this->version));
        $this->enqueue_admin_main_script(self::asset_version('dist/js/admin-main.bundle.js', $this->version));
        self::localize_core_data($this->version);
    }

    public static function localize_core_data(string $plugin_version)
    {
        if (self::$is_core_data_localized) {
            return;
        }

        if (! wp_script_is('aipkit_markdown-it', 'registered') && class_exists(AIPKit_Shared_Assets_Manager::class)) {
            AIPKit_Shared_Assets_Manager::register($plugin_version);
        }

        if (! wp_script_is('aipkit-admin-main', 'registered')) {
            wp_register_script(
                'aipkit-admin-main',
                WPAICG_PLUGIN_URL . 'dist/js/admin-main.bundle.js',
                self::script_deps(),
                self::asset_version('dist/js/admin-main.bundle.js', $plugin_version),
                true
            );
        }

        if (self::is_script_localized('aipkit-admin-main', 'aipkit_dashboard')) {
            self::$is_core_data_localized = true;
            return;
        }

        $openai_models = [];
        $openrouter_models = [];
        $google_models = [];
        $azure_deployments = [];
        $claude_models = [];
        $deepseek_models = [];
        $ollama_models = [];
        $google_image_models = [];
        $openrouter_image_models = [];
        $recommended_models = [];
        $provider_status = [];
        $default_models = [];
        $current_provider = 'openai';

        if (class_exists(AIPKit_Providers::class)) {
            $openai_models = AIPKit_Providers::get_openai_models();
            $openrouter_models = AIPKit_Providers::get_openrouter_models();
            $google_models = AIPKit_Providers::get_google_models();
            $azure_deployments = AIPKit_Providers::get_azure_deployments();
            $claude_models = AIPKit_Providers::get_claude_models();
            $deepseek_models = AIPKit_Providers::get_deepseek_models();
            $ollama_models = AIPKit_Providers::get_ollama_models();
            $google_image_models = AIPKit_Providers::get_google_image_models();
            $openrouter_image_models = AIPKit_Providers::get_openrouter_image_models();
            $recommended_models = [
                'openai' => AIPKit_Providers::get_recommended_models('OpenAI'),
                'google' => AIPKit_Providers::get_recommended_models('Google'),
                'claude' => AIPKit_Providers::get_recommended_models('Claude'),
                'openrouter' => AIPKit_Providers::get_recommended_models('OpenRouter'),
                'deepseek' => AIPKit_Providers::get_recommended_models('DeepSeek'),
            ];

            $providers = AIPKit_Providers::get_all_providers();
            $current_provider = strtolower(AIPKit_Providers::get_current_provider());
            foreach (array_keys(AIPKit_Providers::get_provider_defaults_all()) as $provider_name) {
                $provider_data = AIPKit_Providers::get_provider_data($provider_name);
                $default_models[strtolower($provider_name)] = isset($provider_data['model'])
                    ? sanitize_text_field((string) $provider_data['model'])
                    : '';
            }
            $provider_status = [
                'openai' => ! empty($providers['OpenAI']['api_key']),
                'google' => ! empty($providers['Google']['api_key']),
                'claude' => ! empty($providers['Claude']['api_key']),
                'openrouter' => ! empty($providers['OpenRouter']['api_key']),
                'azure' => ! empty($providers['Azure']['api_key']) && ! empty($providers['Azure']['endpoint']),
                'ollama' => ! empty($providers['Ollama']['base_url']),
                'deepseek' => ! empty($providers['DeepSeek']['api_key']),
                'replicate' => ! empty($providers['Replicate']['api_key']),
                'pinecone' => ! empty($providers['Pinecone']['api_key']),
                'qdrant' => ! empty($providers['Qdrant']['api_key']) && ! empty($providers['Qdrant']['url']),
            ];
        }

        $embedding_provider_map = class_exists(AIPKit_Providers::class)
            ? AIPKit_Providers::get_embedding_provider_map('dashboard_ui')
            : [];
        $embedding_models = class_exists(AIPKit_Providers::class)
            ? AIPKit_Providers::get_embedding_models_by_provider('dashboard_ui')
            : [];
        $is_pro_plan = class_exists(aipkit_dashboard::class) ? aipkit_dashboard::is_pro_plan() : false;

        wp_localize_script('aipkit-admin-main', 'aipkit_dashboard', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aipkit_nonce'),
            'isProPlan' => $is_pro_plan,
            'isAdmin' => current_user_can('manage_options'),
            'modulesUrl' => WPAICG_PLUGIN_URL . 'admin/views/modules/',
            'upgradeUrl' => admin_url('admin.php?page=wpaicg-pricing'),
            'adminUrl' => admin_url(),
            'main_provider' => $current_provider,
            'models' => [
                'openai' => $openai_models,
                'google' => $google_models,
                'claude' => $claude_models,
                'openrouter' => $openrouter_models,
                'azure' => $azure_deployments,
                'ollama' => $ollama_models,
                'deepseek' => $deepseek_models,
            ],
            'defaultModels' => $default_models,
            'recommendedModels' => $recommended_models,
            'embeddingProviderMap' => $embedding_provider_map,
            'embeddingModels' => $embedding_models,
            'imageGeneratorModels' => [
                'openai' => [
                    ['id' => 'gpt-image-1.5', 'name' => 'GPT Image 1.5'],
                    ['id' => 'gpt-image-1', 'name' => 'GPT Image 1'],
                    ['id' => 'gpt-image-1-mini', 'name' => 'GPT Image 1 mini'],
                    ['id' => 'dall-e-3', 'name' => 'DALL-E 3'],
                    ['id' => 'dall-e-2', 'name' => 'DALL-E 2'],
                ],
                'google' => $google_image_models,
                'openrouter' => $openrouter_image_models,
                'azure' => class_exists(AIPKit_Providers::class) ? AIPKit_Providers::get_azure_image_models() : [],
                'replicate' => class_exists(AIPKit_Providers::class) ? AIPKit_Providers::get_replicate_models() : [],
            ],
            'imageGeneratorVideoModels' => [
                'google' => class_exists(AIPKit_Providers::class) ? AIPKit_Providers::get_google_video_models() : [],
            ],
            'providerStatus' => $provider_status,
            'text' => self::dashboard_texts(),
            'currentUserId' => get_current_user_id(),
        ]);

        self::$is_core_data_localized = true;
    }
}

class SettingsAssets extends AIPKit_Admin_Asset_Base
{
    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_settings_assets']);
    }

    public function enqueue_settings_assets($hook_suffix)
    {
        $screen = get_current_screen();

        if (! $this->is_aipkit_page($screen)) {
            return;
        }

        $this->enqueue_admin_main_script();
        $this->ensure_dashboard_core_data();
    }
}

class ChatAdminAssets extends AIPKit_Admin_Asset_Base
{
    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_chat_admin_assets']);
    }

    public function enqueue_chat_admin_assets($hook_suffix)
    {
        $screen = get_current_screen();

        if (! $this->is_aipkit_page($screen)) {
            return;
        }

        if (class_exists(ChatFrontendAssetsOrchestrator::class) && method_exists(ChatFrontendAssetsOrchestrator::class, 'register_public_chat_dependencies')) {
            ChatFrontendAssetsOrchestrator::register_public_chat_dependencies();
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();
        $this->ensure_dashboard_core_data();
        $this->localize_chat_data();
    }

    private function enqueue_styles(): void
    {
        $public_css_version = self::asset_version('dist/css/public-main.bundle.css', $this->version);
        $chat_css_version = self::asset_version('dist/css/admin-chat.bundle.css', $this->version);

        $this->register_admin_main_css(self::asset_version('dist/css/admin-main.bundle.css', $this->version));
        $this->register_style_bundle('aipkit-public-main-css', 'public-main.bundle.css', ['dashicons'], $public_css_version);
        $this->register_style_bundle(
            'aipkit-admin-chat-css',
            'admin-chat.bundle.css',
            ['aipkit-admin-main-css', 'aipkit-public-main-css'],
            $chat_css_version
        );

        $this->enqueue_style_handle('aipkit-public-main-css');
        $this->enqueue_style_handle('aipkit-admin-chat-css');
    }

    private function enqueue_scripts(): void
    {
        $admin_js_version = self::asset_version('dist/js/admin-main.bundle.js', $this->version);
        $public_js_version = self::asset_version('dist/js/public-main.bundle.js', $this->version);

        $this->enqueue_admin_main_script($admin_js_version);
        $this->enqueue_public_main_script($public_js_version);

        if (
            class_exists(aipkit_dashboard::class) &&
            aipkit_dashboard::is_pro_plan() &&
            wp_script_is('aipkit_jspdf', 'registered') &&
            ! wp_script_is('aipkit_jspdf', 'enqueued')
        ) {
            wp_enqueue_script('aipkit_jspdf');
        }
    }

    private function localize_chat_data(): void
    {
        $public_js_version = self::asset_version('dist/js/public-main.bundle.js', $this->version);

        $this->register_public_main_script($public_js_version);
        $this->enqueue_public_main_script($public_js_version);

        if (self::is_script_localized('aipkit-public-main', 'aipkit_chat_config')) {
            return;
        }

        $user_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : null;
        $vector_store_localization = AIPKit_Providers::get_vector_store_localization_payload('chat_admin_ui');
        $embedding_localization = AIPKit_Providers::get_embedding_localization_payload('chat_admin_ui', false);
        $dashboard_texts = self::dashboard_texts();
        $is_pro_plan = class_exists(aipkit_dashboard::class) ? aipkit_dashboard::is_pro_plan() : false;

        wp_localize_script('aipkit-public-main', 'aipkit_chat_config', [
            'nonce' => wp_create_nonce('aipkit_frontend_chat_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'userIp' => $user_ip,
            'requireConsentCompliance' => false,
            'openaiVectorStores' => $vector_store_localization['openaiVectorStores'],
            'pineconeIndexes' => $vector_store_localization['pineconeIndexes'],
            'qdrantCollections' => $vector_store_localization['qdrantCollections'],
            'embedding_provider_map' => $embedding_localization['embedding_provider_map'],
            'embedding_models_by_provider' => $embedding_localization['embedding_models_by_provider'],
            'isProPlan' => $is_pro_plan,
            'automationsNonce' => wp_create_nonce('aipkit_automated_tasks_manage_nonce'),
            'nonce_toggle_ip_block' => wp_create_nonce('aipkit_toggle_ip_block_nonce'),
            'text' => array_merge($dashboard_texts, [
                'fullscreenError' => $dashboard_texts['fullscreenError'] ?? __('Error: Fullscreen functionality is unavailable.', 'gpt3-ai-content-generator'),
                'copySuccess' => $dashboard_texts['copySuccess'] ?? __('Copied!', 'gpt3-ai-content-generator'),
                'copyFail' => $dashboard_texts['copyFail'] ?? __('Failed to copy', 'gpt3-ai-content-generator'),
                'selectVectorStoreOpenAI' => __('Select OpenAI Store(s)', 'gpt3-ai-content-generator'),
                'selectVectorStorePinecone' => __('Select Pinecone Index', 'gpt3-ai-content-generator'),
                'selectVectorStoreQdrant' => __('Select Qdrant Collection', 'gpt3-ai-content-generator'),
                'selectEmbeddingProvider' => __('Select Embedding Provider', 'gpt3-ai-content-generator'),
                'selectEmbeddingModel' => __('Select Embedding Model', 'gpt3-ai-content-generator'),
                'noStoresFoundOpenAI' => __('No OpenAI Stores Found (Sync in AI Training)', 'gpt3-ai-content-generator'),
                'noIndexesFoundPinecone' => __('No Pinecone Indexes Found (Sync in AI Settings)', 'gpt3-ai-content-generator'),
                'noCollectionsFoundQdrant' => __('No Qdrant Collections Found (Sync in AI Settings)', 'gpt3-ai-content-generator'),
                'noEmbeddingModelsFound' => __('No Models (Select Provider or Sync)', 'gpt3-ai-content-generator'),
            ]),
        ]);

        if (wp_script_is('aipkit-admin-main', 'enqueued')) {
            wp_add_inline_script(
                'aipkit-admin-main',
                'window.aipkit_index_content_nonce = "' . esc_js(wp_create_nonce('aipkit_chatbot_index_content_nonce')) . '";',
                'before'
            );
        }
    }
}

class RoleManagerAssets extends AIPKit_Admin_Asset_Base
{
    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_role_manager_assets']);
    }

    public function enqueue_role_manager_assets($hook_suffix)
    {
        $screen = get_current_screen();
        $is_role_manager = $screen && strpos($screen->id, 'page_aipkit-role-manager') !== false;

        if (! $is_role_manager) {
            return;
        }

        $this->enqueue_admin_main_css();
        $this->enqueue_admin_main_script();

        if (! self::is_script_localized('aipkit-admin-main', 'aipkit_role_manager_config')) {
            wp_localize_script('aipkit-admin-main', 'aipkit_role_manager_config', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('aipkit_role_manager_nonce'),
                'text' => [
                    'saving' => __('Saving...', 'gpt3-ai-content-generator'),
                    'saveButton' => __('Save Permissions', 'gpt3-ai-content-generator'),
                    'success' => __('Permissions saved!', 'gpt3-ai-content-generator'),
                    'fail' => __('Failed to save permissions.', 'gpt3-ai-content-generator'),
                ],
            ]);
        }
    }
}

class PostEnhancerAssets extends AIPKit_Admin_Asset_Base
{
    public const MODULE_SLUG = 'ai_post_enhancer';

    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_post_enhancer_assets']);
    }

    public function enqueue_post_enhancer_assets($hook_suffix)
    {
        if (! AIPKit_Role_Manager::user_can_access_module(self::MODULE_SLUG)) {
            return;
        }

        $screen = get_current_screen();
        $is_aipkit_page = $this->is_aipkit_page($screen);
        $is_post_edit_screen = in_array($hook_suffix, ['post.php', 'post-new.php'], true);

        $ui_post_types = get_post_types(['show_ui' => true]);
        unset($ui_post_types['attachment']);

        $supported_post_types = apply_filters('aipkit_post_enhancer_post_types', array_keys($ui_post_types));
        $current_post_type = isset($screen->post_type) ? (string) $screen->post_type : '';
        $is_post_list_screen = $screen && $screen->base === 'edit' && in_array($current_post_type, $supported_post_types, true);

        if ($is_post_list_screen || $is_post_edit_screen || $is_aipkit_page) {
            $this->enqueue_scripts();
        }

        if ($is_post_list_screen) {
            $this->enqueue_styles();
        }
    }

    private function enqueue_styles(): void
    {
        $this->register_admin_main_css();
        $this->register_style_bundle(
            'aipkit-admin-post-enhancer-css',
            'admin-post-enhancer.bundle.css',
            ['aipkit-admin-main-css']
        );
        $this->enqueue_style_handle('aipkit-admin-post-enhancer-css');
    }

    private function enqueue_scripts(): void
    {
        $this->enqueue_admin_main_script();
        $this->ensure_dashboard_core_data();

        if (self::is_script_localized('aipkit-admin-main', 'aipkit_post_enhancer')) {
            return;
        }

        $opts = get_option('aipkit_options', []);
        $default_insert_position = isset($opts['enhancer_settings']['default_insert_position'])
            ? sanitize_key($opts['enhancer_settings']['default_insert_position'])
            : 'replace';

        if (! in_array($default_insert_position, ['replace', 'after', 'before'], true)) {
            $default_insert_position = 'replace';
        }

        $default_ai_config = AIPKit_Providers::get_default_provider_config();
        $default_ai_params = AIPKIT_AI_Settings::get_ai_parameters();
        $vector_store_localization = AIPKit_Providers::get_vector_store_localization_payload('post_enhancer_ui');
        $embedding_localization = AIPKit_Providers::get_embedding_localization_payload('post_enhancer_ui', false);
        $enhancer_actions = get_option('aipkit_enhancer_actions', []);
        $enhancer_prompt_items = class_exists(AIPKit_AutoGPT_Prompt_Definitions::class)
            ? AIPKit_AutoGPT_Prompt_Definitions::get_post_enhancer_prompt_items(true)
            : [];

        if (empty($enhancer_actions) && class_exists(AIPKit_Enhancer_Actions_Ajax_Handler::class)) {
            $enhancer_actions = (new AIPKit_Enhancer_Actions_Ajax_Handler())->get_default_actions_public();
        }

        wp_localize_script('aipkit-admin-main', 'aipkit_post_enhancer', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce_generate_title' => wp_create_nonce('aipkit_generate_title_nonce'),
            'nonce_update_title' => wp_create_nonce('aipkit_update_title_nonce'),
            'nonce_generate_excerpt' => wp_create_nonce('aipkit_generate_excerpt_nonce'),
            'nonce_update_excerpt' => wp_create_nonce('aipkit_update_excerpt_nonce'),
            'nonce_generate_meta' => wp_create_nonce('aipkit_generate_meta_nonce'),
            'nonce_update_meta' => wp_create_nonce('aipkit_update_meta_nonce'),
            'nonce_generate_tags' => wp_create_nonce('aipkit_generate_tags_nonce'),
            'nonce_update_tags' => wp_create_nonce('aipkit_update_tags_nonce'),
            'nonce_process_text' => wp_create_nonce('aipkit_process_enhancer_text_nonce'),
            'nonce_manage_templates' => wp_create_nonce('aipkit_content_writer_template_nonce'),
            'nonce_manage_actions' => wp_create_nonce('aipkit_enhancer_actions_nonce'),
            'nonce_prompt_library' => wp_create_nonce('aipkit_nonce'),
            'default_ai_provider' => $default_ai_config['provider'] ?? 'N/A',
            'default_ai_model' => $default_ai_config['model'] ?? 'N/A',
            'default_ai_params' => $default_ai_params,
            'prompt_items' => $enhancer_prompt_items,
            'openai_vector_stores' => $vector_store_localization['openai_vector_stores'],
            'pinecone_indexes' => $vector_store_localization['pinecone_indexes'],
            'qdrant_collections' => $vector_store_localization['qdrant_collections'],
            'embeddingProviderMap' => $embedding_localization['embeddingProviderMap'],
            'embeddingModelsByProvider' => $embedding_localization['embeddingModelsByProvider'],
            'actions' => $enhancer_actions,
            'parse_html_formats' => (bool) apply_filters('aipkit_enhancer_enable_formatting', true),
            'default_insert_position' => $default_insert_position,
            'text' => [
                'modal_title_title' => __('Title Suggestions', 'gpt3-ai-content-generator'),
                'loading_title' => __('Generating Title Suggestions...', 'gpt3-ai-content-generator'),
                'updating_title' => __('Updating Title...', 'gpt3-ai-content-generator'),
                'error_loading_title' => __('Error loading title suggestions.', 'gpt3-ai-content-generator'),
                'error_updating_title' => __('Error updating title.', 'gpt3-ai-content-generator'),
                'no_suggestions_title' => __('No title suggestions generated or AI Error.', 'gpt3-ai-content-generator'),
                'select_title' => __('Click a title to apply:', 'gpt3-ai-content-generator'),
                'modal_title_excerpt' => __('Excerpt Suggestions', 'gpt3-ai-content-generator'),
                'loading_excerpt' => __('Generating Excerpt Suggestions...', 'gpt3-ai-content-generator'),
                'updating_excerpt' => __('Updating Excerpt...', 'gpt3-ai-content-generator'),
                'error_loading_excerpt' => __('Error loading excerpt suggestions.', 'gpt3-ai-content-generator'),
                'error_updating_excerpt' => __('Error updating excerpt.', 'gpt3-ai-content-generator'),
                'no_suggestions_excerpt' => __('No excerpt suggestions generated or AI Error.', 'gpt3-ai-content-generator'),
                'select_excerpt' => __('Click an excerpt to apply:', 'gpt3-ai-content-generator'),
                'modal_title_meta' => __('Meta Description Suggestions', 'gpt3-ai-content-generator'),
                'loading_meta' => __('Generating Meta Descriptions...', 'gpt3-ai-content-generator'),
                'updating_meta' => __('Updating Meta Description...', 'gpt3-ai-content-generator'),
                'error_loading_meta' => __('Error loading meta description suggestions.', 'gpt3-ai-content-generator'),
                'error_updating_meta' => __('Error updating meta description.', 'gpt3-ai-content-generator'),
                'no_suggestions_meta' => __('No meta description suggestions generated or AI Error.', 'gpt3-ai-content-generator'),
                'select_meta' => __('Click a meta description to apply:', 'gpt3-ai-content-generator'),
                'modal_title_tags' => __('Tag Suggestions', 'gpt3-ai-content-generator'),
                'loading_tags' => __('Generating Tag Suggestions...', 'gpt3-ai-content-generator'),
                'updating_tags' => __('Updating Tags...', 'gpt3-ai-content-generator'),
                'error_loading_tags' => __('Error loading tag suggestions.', 'gpt3-ai-content-generator'),
                'error_updating_tags' => __('Error updating tags.', 'gpt3-ai-content-generator'),
                'no_suggestions_tags' => __('No tag suggestions generated or AI Error.', 'gpt3-ai-content-generator'),
                'select_tags' => __('Click a tag set to apply:', 'gpt3-ai-content-generator'),
                /* translators: 1: provider label, 2: model label, 3: temperature value. */
                'loading_info_template' => __('Using <strong>%1$s</strong> (Model: <strong>%2$s</strong>, Temp: %3$s)', 'gpt3-ai-content-generator'),
                'close' => __('Close', 'gpt3-ai-content-generator'),
                'config_modal_title' => __('Configure AI Actions', 'gpt3-ai-content-generator'),
                'add_new_action' => __('Add New', 'gpt3-ai-content-generator'),
                'edit_action' => __('Edit', 'gpt3-ai-content-generator'),
                'delete_action' => __('Delete', 'gpt3-ai-content-generator'),
                'action_label' => __('Action Label', 'gpt3-ai-content-generator'),
                'action_prompt' => __('Action Prompt', 'gpt3-ai-content-generator'),
                'insert_position' => __('Position', 'gpt3-ai-content-generator'),
                'use_default_position' => __('Use default', 'gpt3-ai-content-generator'),
                'replace_selection' => __('Replace selection', 'gpt3-ai-content-generator'),
                'insert_after' => __('Insert after', 'gpt3-ai-content-generator'),
                'insert_before' => __('Insert before', 'gpt3-ai-content-generator'),
                'reset_actions' => __('Reset to Defaults', 'gpt3-ai-content-generator'),
                'confirm_reset_actions' => __('Reset all actions to the default set? This will replace current customizations.', 'gpt3-ai-content-generator'),
                'actions_reset' => __('Actions reset to defaults.', 'gpt3-ai-content-generator'),
                'save_action' => __('Save Action', 'gpt3-ai-content-generator'),
                'saving_action' => __('Saving...', 'gpt3-ai-content-generator'),
                'confirm_delete_action' => __('Are you sure you want to delete this action? This cannot be undone.', 'gpt3-ai-content-generator'),
                'deleting_action' => __('Deleting...', 'gpt3-ai-content-generator'),
                'action_deleted' => __('Action deleted.', 'gpt3-ai-content-generator'),
                'action_saved' => __('Action saved.', 'gpt3-ai-content-generator'),
                'loading_actions' => __('Loading actions...', 'gpt3-ai-content-generator'),
                /* translators: %s: placeholder token that will be replaced by the selected text. */
                'prompt_placeholder_info' => __('Use %s as a placeholder for the selected text.', 'gpt3-ai-content-generator'),
            ],
            'settings_url' => admin_url('admin.php?page=wpaicg#settings'),
        ]);
    }
}

class ImageGeneratorAssets extends AIPKit_Admin_Asset_Base
{
    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_image_generator_assets']);
    }

    public function enqueue_image_generator_assets($hook_suffix)
    {
        $screen = get_current_screen();

        if (! $this->is_aipkit_page($screen)) {
            return;
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();
        $this->ensure_dashboard_core_data();
        $this->localize_data();
    }

    private function enqueue_styles(): void
    {
        $this->register_style_bundle(
            'aipkit-public-image-generator-css',
            'public-image-generator.bundle.css',
            [],
            self::asset_version('dist/css/public-image-generator.bundle.css', $this->version)
        );
        $this->enqueue_style_handle('aipkit-public-image-generator-css');
    }

    private function enqueue_scripts(): void
    {
        $this->enqueue_admin_main_script();
        $public_js_version = self::asset_version('dist/js/public-image-generator.bundle.js', $this->version);
        $this->register_script_bundle(
            'aipkit-public-image-generator-js',
            'public-image-generator.bundle.js',
            ['wp-i18n'],
            $public_js_version
        );
        $this->enqueue_script_handle('aipkit-public-image-generator-js');
    }

    private function localize_data(): void
    {
        $public_js_version = self::asset_version('dist/js/public-image-generator.bundle.js', $this->version);
        $this->register_script_bundle(
            'aipkit-public-image-generator-js',
            'public-image-generator.bundle.js',
            ['wp-i18n'],
            $public_js_version
        );

        if (self::is_script_localized('aipkit-public-image-generator-js', 'aipkit_image_generator_config_public')) {
            return;
        }

        $ui_text_settings = [];
        if (class_exists(AIPKit_Image_Settings_Ajax_Handler::class)) {
            $all_image_settings = AIPKit_Image_Settings_Ajax_Handler::get_settings();
            $ui_text_settings = $all_image_settings['ui_text'] ?? [];
        }

        $get_ui_text = static function (string $key, string $default) use ($ui_text_settings): string {
            if (! isset($ui_text_settings[$key])) {
                return $default;
            }

            $value = sanitize_text_field((string) $ui_text_settings[$key]);
            return $value !== '' ? $value : $default;
        };

        wp_localize_script('aipkit-public-image-generator-js', 'aipkit_image_generator_config_public', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aipkit_image_generator_nonce'),
            'text' => [
                'generating' => __('Generating...', 'gpt3-ai-content-generator'),
                'editing' => __('Editing...', 'gpt3-ai-content-generator'),
                'error' => __('Error generating image.', 'gpt3-ai-content-generator'),
                'generateButton' => $get_ui_text('generate_label', __('Generate', 'gpt3-ai-content-generator')),
                'noPrompt' => __('Please enter a prompt.', 'gpt3-ai-content-generator'),
                'initialPlaceholder' => $get_ui_text('results_empty', __('Generated images will appear here.', 'gpt3-ai-content-generator')),
                'viewFullImage' => __('Click to view full image', 'gpt3-ai-content-generator'),
                'viewFullVideo' => __('Click to view full video', 'gpt3-ai-content-generator'),
                'openrouterModelUnsupported' => __('Selected OpenRouter model does not support image generation.', 'gpt3-ai-content-generator'),
                'editUploadRequired' => __('Please upload an image to edit.', 'gpt3-ai-content-generator'),
                'editProviderUnsupported' => __('Image editing is currently supported only for Google, OpenAI and OpenRouter providers.', 'gpt3-ai-content-generator'),
                'editModelUnsupported' => __('Selected model does not support image editing.', 'gpt3-ai-content-generator'),
                'editInvalidFileType' => __('Invalid image type. Allowed types: JPG, PNG, WEBP, GIF.', 'gpt3-ai-content-generator'),
                'editFileTooLarge' => __('Source image is too large. Maximum allowed size is 10MB.', 'gpt3-ai-content-generator'),
                'editDropUsePicker' => __('Could not attach dropped file automatically. Click to choose file.', 'gpt3-ai-content-generator'),
                'editHistoryLoadFailed' => __('Could not load the selected image for editing.', 'gpt3-ai-content-generator'),
                'editHistoryUnavailable' => __('Image editing is not available in the current setup.', 'gpt3-ai-content-generator'),
                'editHistoryLoaded' => __('Source image loaded. Describe your edits and click Edit Image.', 'gpt3-ai-content-generator'),
                'noEditCapableModels' => __('(No edit-capable models available)', 'gpt3-ai-content-generator'),
                'noOpenRouterImageModels' => __('(No image-capable OpenRouter models found)', 'gpt3-ai-content-generator'),
                'noModelsAvailable' => __('(No models available)', 'gpt3-ai-content-generator'),
                'imageModelsGroup' => __('Image Models', 'gpt3-ai-content-generator'),
                'videoModelsGroup' => __('Video Models', 'gpt3-ai-content-generator'),
                'configurationMissing' => __('Error: Configuration missing.', 'gpt3-ai-content-generator'),
                'coreUiMissing' => __('Error: Core UI elements missing.', 'gpt3-ai-content-generator'),
                'missingRequiredSettings' => __('Error: Missing required image generation settings.', 'gpt3-ai-content-generator'),
                'noVideoDataFound' => __('Error: No video data found.', 'gpt3-ai-content-generator'),
                'noImageDataFound' => __('Error: No image data found.', 'gpt3-ai-content-generator'),
                'deleteConfigMissing' => __('Error: Cannot delete image. Configuration missing.', 'gpt3-ai-content-generator'),
                'deleteImageErrorPrefix' => __('Error deleting image:', 'gpt3-ai-content-generator'),
                'revisedPromptPrefix' => __('Revised:', 'gpt3-ai-content-generator'),
                'generatingVideo' => __('Generating Video...', 'gpt3-ai-content-generator'),
                'videoGenerationInProgress' => __('Video generation in progress...', 'gpt3-ai-content-generator'),
                'generatingVideoProgress' => __('Generating video...', 'gpt3-ai-content-generator'),
                'videoGenerationTimedOut' => __('Video generation timed out. Please try again.', 'gpt3-ai-content-generator'),
                'videoGenerationFailed' => __('Video generation failed:', 'gpt3-ai-content-generator'),
            ],
            'edit_upload_max_bytes' => 10 * 1024 * 1024,
            'edit_upload_allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'openai_models' => [
                ['id' => 'gpt-image-1.5', 'name' => 'GPT Image 1.5'],
                ['id' => 'gpt-image-1', 'name' => 'GPT Image 1'],
                ['id' => 'gpt-image-1-mini', 'name' => 'GPT Image 1 mini'],
                ['id' => 'dall-e-3', 'name' => 'DALL-E 3'],
                ['id' => 'dall-e-2', 'name' => 'DALL-E 2'],
            ],
            'azure_models' => class_exists(AIPKit_Providers::class) ? AIPKit_Providers::get_azure_image_models() : [],
            'google_models' => [
                'image' => class_exists(AIPKit_Providers::class) ? AIPKit_Providers::get_google_image_models() : [],
                'video' => class_exists(AIPKit_Providers::class) ? AIPKit_Providers::get_google_video_models() : [],
            ],
            'openrouter_image_models' => class_exists(AIPKit_Providers::class) ? AIPKit_Providers::get_openrouter_image_models() : [],
            'replicate_models' => class_exists(AIPKit_Providers::class) ? AIPKit_Providers::get_replicate_models() : [],
        ]);
    }
}

class AIPKit_Content_Writer_Assets extends AIPKit_Admin_Asset_Base
{
    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_content_writer_assets']);
    }

    public function enqueue_content_writer_assets($hook_suffix)
    {
        $screen = get_current_screen();

        if (! $this->is_aipkit_page($screen)) {
            return;
        }

        $this->enqueue_admin_main_css();
        $this->enqueue_admin_main_script();
        $this->ensure_dashboard_core_data();
        $this->localize_data();
    }

    private function localize_data(): void
    {
        if (self::is_script_localized('aipkit-admin-main', 'aipkit_content_writer_config')) {
            return;
        }

        wp_localize_script('aipkit-admin-main', 'aipkit_content_writer_config', [
            'default_prompts' => [
                'title' => AIPKit_Content_Writer_Prompts::get_default_title_prompt(),
                'content' => AIPKit_Content_Writer_Prompts::get_default_content_prompt(),
                'meta' => AIPKit_Content_Writer_Prompts::get_default_meta_prompt(),
                'keyword' => AIPKit_Content_Writer_Prompts::get_default_keyword_prompt(),
                'image' => AIPKit_Content_Writer_Prompts::get_default_image_prompt(),
                'featured_image' => AIPKit_Content_Writer_Prompts::get_default_featured_image_prompt(),
                'image_title' => AIPKit_Content_Writer_Prompts::get_default_image_title_prompt(),
                'image_alt_text' => AIPKit_Content_Writer_Prompts::get_default_image_alt_text_prompt(),
                'image_caption' => AIPKit_Content_Writer_Prompts::get_default_image_caption_prompt(),
                'image_description' => AIPKit_Content_Writer_Prompts::get_default_image_description_prompt(),
                'image_title_update' => AIPKit_Content_Writer_Prompts::get_default_image_title_prompt_update(),
                'image_alt_text_update' => AIPKit_Content_Writer_Prompts::get_default_image_alt_text_prompt_update(),
                'image_caption_update' => AIPKit_Content_Writer_Prompts::get_default_image_caption_prompt_update(),
                'image_description_update' => AIPKit_Content_Writer_Prompts::get_default_image_description_prompt_update(),
            ],
        ]);
    }
}

class AIPKit_Vector_Post_Processor_Assets extends AIPKit_Admin_Asset_Base
{
    public const MODULE_SLUG = 'vector_content_indexer';

    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', function () {
            if (! AIPKit_Role_Manager::user_can_access_module(self::MODULE_SLUG)) {
                return;
            }

            $general = get_option('aipkit_training_general_settings', []);
            $show = $general['show_index_button'] ?? true;

            if ($show) {
                AIPKit_Admin_Header_Action_Buttons::register_button(
                    'aipkit_add_to_vector_store_btn',
                    __('Index', 'gpt3-ai-content-generator'),
                    ['capability' => 'edit_posts']
                );
            }
        });
    }

    public function enqueue_assets($hook_suffix)
    {
        $screen = get_current_screen();
        $is_post_list_screen = $screen && $screen->base === 'edit';

        if (! $is_post_list_screen || ! AIPKit_Role_Manager::user_can_access_module(self::MODULE_SLUG)) {
            return;
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();
        $this->ensure_dashboard_core_data();
        $this->localize_vpp_data((string) $screen->post_type);
    }

    private function enqueue_styles(): void
    {
        $this->register_admin_main_css();
        $this->register_style_bundle(
            'aipkit-admin-vector-post-processor-css',
            'admin-vector-post-processor.bundle.css',
            ['aipkit-admin-main-css']
        );
        $this->enqueue_style_handle('aipkit-admin-vector-post-processor-css');
    }

    private function enqueue_scripts(): void
    {
        $this->enqueue_admin_main_script();
    }

    private function localize_vpp_data(string $post_type): void
    {
        if (self::is_script_localized('aipkit-admin-main', 'aipkit_vpp_config')) {
            return;
        }

        $vector_store_localization = AIPKit_Providers::get_vector_store_localization_payload('vector_post_processor_ui');
        $embedding_localization = AIPKit_Providers::get_embedding_localization_payload('vector_post_processor_ui', false);

        wp_localize_script('aipkit-admin-main', 'aipkit_vpp_config', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce_index_posts' => wp_create_nonce('aipkit_index_posts_to_vector_store_nonce'),
            'nonce_openai_store_list' => wp_create_nonce('aipkit_vector_store_nonce_openai'),
            'post_type' => $post_type,
            'openai_vector_stores' => $vector_store_localization['openai_vector_stores'],
            'pinecone_indexes' => $vector_store_localization['pinecone_indexes'],
            'qdrant_collections' => $vector_store_localization['qdrant_collections'],
            'embeddingProviderMap' => $embedding_localization['embeddingProviderMap'],
            'embeddingModelsByProvider' => $embedding_localization['embeddingModelsByProvider'],
            'text' => [
                'modal_title' => __('Add Content to Vector Store', 'gpt3-ai-content-generator'),
                'provider_label' => __('Provider', 'gpt3-ai-content-generator'),
                'select_store' => __('Select OpenAI Store', 'gpt3-ai-content-generator'),
                'no_stores_found' => __('No OpenAI stores found. Create one in AI Training > Knowledge Base.', 'gpt3-ai-content-generator'),
                'loading_stores' => __('Loading stores...', 'gpt3-ai-content-generator'),
                'start_indexing' => __('Start Indexing', 'gpt3-ai-content-generator'),
                'processingButton' => __('Processing...', 'gpt3-ai-content-generator'),
                'close' => __('Close', 'gpt3-ai-content-generator'),
                'stop' => __('Stop', 'gpt3-ai-content-generator'),
                'stopping' => __('Stopping...', 'gpt3-ai-content-generator'),
                /* translators: 1: processed item count, 2: total item count. */
                'indexing_progress' => __('Processing: %1$d/%2$d', 'gpt3-ai-content-generator'),
                'indexing_complete' => __('Indexing complete!', 'gpt3-ai-content-generator'),
                'error_fetching_stores' => __('Error fetching vector stores.', 'gpt3-ai-content-generator'),
                'error_no_store_selected_vpp' => __('Please select an existing OpenAI store.', 'gpt3-ai-content-generator'),
                'error_no_posts_selected' => __('Please select at least one post to index.', 'gpt3-ai-content-generator'),
                'confirm_start_indexing' => __('Are you sure you want to index the selected content?', 'gpt3-ai-content-generator'),
                'status_preparing' => __('Preparing content...', 'gpt3-ai-content-generator'),
                /* translators: 1: current file number, 2: total file count. */
                'status_uploading' => __('Uploading file %1$s of %2$s...', 'gpt3-ai-content-generator'),
                'status_adding_files' => __('Adding files to vector store...', 'gpt3-ai-content-generator'),
                'status_error' => __('An error occurred.', 'gpt3-ai-content-generator'),
                /* translators: %d: number of selected items. */
                'items_selected_singular' => __('You have selected %d item to index.', 'gpt3-ai-content-generator'),
                /* translators: %d: number of selected items. */
                'items_selected_plural' => __('You have selected %d items to index.', 'gpt3-ai-content-generator'),
                'select_pinecone_index' => __('Select Pinecone Index', 'gpt3-ai-content-generator'),
                'loading_indexes' => __('Loading indexes...', 'gpt3-ai-content-generator'),
                'error_fetching_indexes' => __('Error fetching indexes.', 'gpt3-ai-content-generator'),
                'no_pinecone_indexes_found' => __('No Pinecone indexes found. Create one in AI Training or via Pinecone console.', 'gpt3-ai-content-generator'),
                'error_no_pinecone_index_selected' => __('Please select a Pinecone index.', 'gpt3-ai-content-generator'),
                'select_qdrant_collection' => __('Select Qdrant Collection', 'gpt3-ai-content-generator'),
                'no_qdrant_collections_found' => __('No Qdrant collections found. Create one in AI Training.', 'gpt3-ai-content-generator'),
                'error_no_qdrant_collection_selected' => __('Please select a Qdrant collection.', 'gpt3-ai-content-generator'),
                'target_label' => __('Target', 'gpt3-ai-content-generator'),
                'embedding_label' => __('Embedding', 'gpt3-ai-content-generator'),
                'embedding_provider_label' => __('Embedding Provider', 'gpt3-ai-content-generator'),
                'embedding_model_label' => __('Embedding Model', 'gpt3-ai-content-generator'),
                'select_model' => __('Select Model', 'gpt3-ai-content-generator'),
                'error_no_embedding_config' => __('Embedding provider and model are required.', 'gpt3-ai-content-generator'),
                'ensure_api_key_for_embedding' => __('Ensure API key is set for the selected embedding provider in AI Settings.', 'gpt3-ai-content-generator'),
                'status_pending' => __('Pending', 'gpt3-ai-content-generator'),
                'status_processing' => __('Processing', 'gpt3-ai-content-generator'),
                'status_completed' => __('Completed', 'gpt3-ai-content-generator'),
                'status_failed' => __('Failed', 'gpt3-ai-content-generator'),
                'status_stopped' => __('Stopped', 'gpt3-ai-content-generator'),
            ],
        ]);
    }
}

class AIPKit_Autogpt_Assets extends AIPKit_Admin_Asset_Base
{
    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_autogpt_assets']);
    }

    public function enqueue_autogpt_assets($hook_suffix)
    {
        $screen = get_current_screen();

        if (! $this->is_aipkit_page($screen)) {
            return;
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();
        $this->ensure_dashboard_core_data();
        $this->localize_data();
    }

    private function enqueue_styles(): void
    {
        $this->register_admin_main_css();
        $this->register_style_bundle(
            'aipkit-admin-autogpt-css',
            'admin-autogpt.bundle.css',
            ['aipkit-admin-main-css']
        );
        $this->enqueue_style_handle('aipkit-admin-autogpt-css');
    }

    private function enqueue_scripts(): void
    {
        $this->enqueue_admin_main_script();
    }

    private function localize_data(): void
    {
        if (self::is_script_localized('aipkit-admin-main', 'aipkit_automated_tasks_config')) {
            return;
        }

        $vector_store_localization = AIPKit_Providers::get_vector_store_localization_payload('autogpt_ui');
        $embedding_localization = AIPKit_Providers::get_embedding_localization_payload('autogpt_ui', false);
        $default_cw_prompts = [];
        $default_ce_prompts = class_exists(AIPKit_AutoGPT_Prompt_Definitions::class)
            ? AIPKit_AutoGPT_Prompt_Definitions::get_content_enhancement_defaults()
            : [];
        $default_cc_prompts = class_exists(AIPKit_AutoGPT_Prompt_Definitions::class)
            ? AIPKit_AutoGPT_Prompt_Definitions::get_comment_reply_defaults()
            : [];

        if (class_exists(AIPKit_Content_Writer_Prompts::class)) {
            $default_cw_prompts = [
                'title' => AIPKit_Content_Writer_Prompts::get_default_title_prompt(),
                'content' => AIPKit_Content_Writer_Prompts::get_default_content_prompt(),
                'meta' => AIPKit_Content_Writer_Prompts::get_default_meta_prompt(),
                'keyword' => AIPKit_Content_Writer_Prompts::get_default_keyword_prompt(),
                'image' => AIPKit_Content_Writer_Prompts::get_default_image_prompt(),
                'featured_image' => AIPKit_Content_Writer_Prompts::get_default_featured_image_prompt(),
            ];
        }

        $frequencies = [];
        foreach (wp_get_schedules() as $slug => $details) {
            $frequencies[$slug] = $details['display'];
        }

        wp_localize_script('aipkit-admin-main', 'aipkit_automated_tasks_config', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce_manage_tasks' => wp_create_nonce('aipkit_automated_tasks_manage_nonce'),
            'openai_vector_stores' => $vector_store_localization['openai_vector_stores'],
            'pinecone_indexes' => $vector_store_localization['pinecone_indexes'],
            'qdrant_collections' => $vector_store_localization['qdrant_collections'],
            'embedding_provider_map' => $embedding_localization['embedding_provider_map'],
            'embedding_models_by_provider' => $embedding_localization['embedding_models_by_provider'],
            'task_types' => [
                'content_indexing' => [
                    'label' => __('Index WordPress Content', 'gpt3-ai-content-generator'),
                    'category' => 'knowledge_base',
                    'description' => __('Index WordPress posts, pages, or products into a vector store for RAG.', 'gpt3-ai-content-generator'),
                ],
                'content_writing_bulk' => [
                    'label' => __('List', 'gpt3-ai-content-generator'),
                    'category' => 'content_creation',
                    'description' => __('Generate full articles from a list of titles and optional keywords.', 'gpt3-ai-content-generator'),
                ],
                'content_writing_csv' => [
                    'label' => __('CSV', 'gpt3-ai-content-generator'),
                    'category' => 'content_creation',
                    'description' => __('Generate articles by importing topics and metadata from a CSV file.', 'gpt3-ai-content-generator'),
                ],
                'content_writing_rss' => [
                    'label' => __('RSS', 'gpt3-ai-content-generator'),
                    'category' => 'content_creation',
                    'description' => __('Automatically generate articles from new items in one or more RSS feeds.', 'gpt3-ai-content-generator'),
                    'pro' => true,
                ],
                'content_writing_url' => [
                    'label' => __('URL', 'gpt3-ai-content-generator'),
                    'category' => 'content_creation',
                    'description' => __('Generate articles by scraping content from a list of URLs to use as context.', 'gpt3-ai-content-generator'),
                    'pro' => true,
                ],
                'content_writing_gsheets' => [
                    'label' => __('Google Sheets', 'gpt3-ai-content-generator'),
                    'category' => 'content_creation',
                    'description' => __('Generate articles from a list of topics in a Google Sheets spreadsheet.', 'gpt3-ai-content-generator'),
                    'pro' => true,
                ],
                'enhance_existing_content' => [
                    'label' => __('Update Existing Content', 'gpt3-ai-content-generator'),
                    'category' => 'content_enhancement',
                    'description' => __('Automatically update titles, excerpts, or meta descriptions for existing posts based on your custom prompts.', 'gpt3-ai-content-generator'),
                    'pro' => true,
                    'disabled' => false,
                ],
                'community_reply_comments' => [
                    'label' => __('Auto-Reply to Comments', 'gpt3-ai-content-generator'),
                    'category' => 'community_engagement',
                    'description' => __('Automatically generate and post replies to new comments.', 'gpt3-ai-content-generator'),
                    'disabled' => false,
                ],
            ],
            'default_cw_prompts' => $default_cw_prompts,
            'default_ce_prompts' => $default_ce_prompts,
            'default_cc_prompts' => $default_cc_prompts,
            'frequencies' => $frequencies,
            'text' => [
                'confirm_delete_task' => __('Are you sure you want to delete this automated task? This action cannot be undone.', 'gpt3-ai-content-generator'),
                'task_name_required' => __('Task name is required.', 'gpt3-ai-content-generator'),
                'task_type_required' => __('Task type is required.', 'gpt3-ai-content-generator'),
                'target_store_required' => __('Please select a target vector store/index.', 'gpt3-ai-content-generator'),
                'content_type_required' => __('Please select at least one content type.', 'gpt3-ai-content-generator'),
                'embedding_provider_required' => __('Embedding provider is required for this vector database.', 'gpt3-ai-content-generator'),
                'embedding_model_required' => __('Embedding model is required for this vector database.', 'gpt3-ai-content-generator'),
                'content_title_required_cw_task' => __('Please add a topic.', 'gpt3-ai-content-generator'),
                'csv_required_cw_task' => __('Please upload a CSV file.', 'gpt3-ai-content-generator'),
                'rss_required_cw_task' => __('Please add at least one RSS feed.', 'gpt3-ai-content-generator'),
                'url_required_cw_task' => __('Please add at least one URL.', 'gpt3-ai-content-generator'),
                'gsheets_id_required_cw_task' => __('Please add a Google Sheet ID.', 'gpt3-ai-content-generator'),
                'gsheets_credentials_required_cw_task' => __('Please add Google Sheets credentials.', 'gpt3-ai-content-generator'),
                'ai_config_required_cw_task' => __('AI Provider and Model are required for Content Writing task.', 'gpt3-ai-content-generator'),
                'saving_task' => __('Saving Task...', 'gpt3-ai-content-generator'),
                'deleting_task' => __('Deleting Task...', 'gpt3-ai-content-generator'),
                'running_task' => __('Initiating Run...', 'gpt3-ai-content-generator'),
                'pausing_task' => __('Pausing Task...', 'gpt3-ai-content-generator'),
                'resuming_task' => __('Resuming Task...', 'gpt3-ai-content-generator'),
                'save_task_button' => __('Save', 'gpt3-ai-content-generator'),
                'create_task_button' => __('Create Task', 'gpt3-ai-content-generator'),
                'edit_task_title' => __('Edit Automated Task', 'gpt3-ai-content-generator'),
                'create_task_title' => __('Create New Automated Task', 'gpt3-ai-content-generator'),
                'loading_stores' => __('Loading stores...', 'gpt3-ai-content-generator'),
                'loading_indexes' => __('Loading indexes...', 'gpt3-ai-content-generator'),
                'select_target_store' => __('-- Select Target Store --', 'gpt3-ai-content-generator'),
                'select_target_index' => __('-- Select Target Index --', 'gpt3-ai-content-generator'),
                'no_targets_found_configure' => __('No targets found. Configure in AI Training.', 'gpt3-ai-content-generator'),
                'loading_models' => __('Loading models...', 'gpt3-ai-content-generator'),
                'select_embedding_model' => __('-- Select Model --', 'gpt3-ai-content-generator'),
                'no_embedding_models_sync' => __('No models - Sync in AI Settings.', 'gpt3-ai-content-generator'),
                'loading_tasks' => __('Loading tasks...', 'gpt3-ai-content-generator'),
                'error_loading_tasks' => __('Error loading tasks:', 'gpt3-ai-content-generator'),
                'no_tasks_configured' => __('No automated tasks configured yet.', 'gpt3-ai-content-generator'),
                'edit_button' => __('Edit', 'gpt3-ai-content-generator'),
                'pause_button' => __('Pause', 'gpt3-ai-content-generator'),
                'resume_button' => __('Resume', 'gpt3-ai-content-generator'),
                'run_now_button' => __('Run Now', 'gpt3-ai-content-generator'),
                'task_not_active_run_title' => __('Task must be active to run', 'gpt3-ai-content-generator'),
                'delete_button' => __('Delete', 'gpt3-ai-content-generator'),
                'never_run' => __('Never', 'gpt3-ai-content-generator'),
                'not_scheduled' => __('Not Scheduled', 'gpt3-ai-content-generator'),
                'task_deleted_success' => __('Task deleted successfully.', 'gpt3-ai-content-generator'),
                'error_deleting_task' => __('Error deleting task:', 'gpt3-ai-content-generator'),
                'task_status_updated' => __('Task status updated to', 'gpt3-ai-content-generator'),
                'error_updating_status' => __('Error updating task status:', 'gpt3-ai-content-generator'),
                'task_run_initiated' => __('Task run initiated. Check queue below for progress.', 'gpt3-ai-content-generator'),
                'error_initiating_run' => __('Error initiating task run:', 'gpt3-ai-content-generator'),
                'loading_queue' => __('Loading queue items...', 'gpt3-ai-content-generator'),
                'error_loading_queue' => __('Error loading queue:', 'gpt3-ai-content-generator'),
                'queue_empty' => __('Task queue is currently empty.', 'gpt3-ai-content-generator'),
                'target_id_prefix' => __('Target ID:', 'gpt3-ai-content-generator'),
                'task_id_prefix' => __('Task ID:', 'gpt3-ai-content-generator'),
                'not_applicable' => __('N/A', 'gpt3-ai-content-generator'),
                'added_at_label' => __('Added', 'gpt3-ai-content-generator'),
                'scheduled_for_label' => __('Scheduled', 'gpt3-ai-content-generator'),
                'item_singular' => __('item', 'gpt3-ai-content-generator'),
                'item_plural' => __('items', 'gpt3-ai-content-generator'),
                'queue_summary_pending' => __('Pending', 'gpt3-ai-content-generator'),
                'queue_summary_running' => __('Running', 'gpt3-ai-content-generator'),
                'queue_summary_failed' => __('Failed', 'gpt3-ai-content-generator'),
                'page_label' => __('Page', 'gpt3-ai-content-generator'),
                'of_label' => __('of', 'gpt3-ai-content-generator'),
                'previous_button' => __('Previous', 'gpt3-ai-content-generator'),
                'next_button' => __('Next', 'gpt3-ai-content-generator'),
                'confirm_delete_queue_item' => __('Are you sure you want to remove this item from the queue?', 'gpt3-ai-content-generator'),
                /* translators: %s: queue status label. */
                'confirmDeleteQueueByStatus' => __('Are you sure you want to delete all %s items from the queue? This cannot be undone.', 'gpt3-ai-content-generator'),
                'confirmDeleteQueueAll' => __('Are you sure you want to delete ALL items from the queue? This cannot be undone.', 'gpt3-ai-content-generator'),
                'queue_item_deleted' => __('Queue item deleted.', 'gpt3-ai-content-generator'),
                'error_deleting_queue_item' => __('Error deleting item:', 'gpt3-ai-content-generator'),
                'errorDeletingAllItems' => __('Error deleting items:', 'gpt3-ai-content-generator'),
                'retry_button' => __('Retry', 'gpt3-ai-content-generator'),
                'item_marked_retry' => __('Item marked for retry. Queue processing will pick it up.', 'gpt3-ai-content-generator'),
                'error_retrying_item' => __('Error retrying item:', 'gpt3-ai-content-generator'),
                'task_singular' => __('task', 'gpt3-ai-content-generator'),
                'task_plural' => __('tasks', 'gpt3-ai-content-generator'),
            ],
        ]);
    }
}

class AIPKit_AI_Forms_Assets extends AIPKit_Admin_Asset_Base
{
    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_ai_forms_assets']);
    }

    public function enqueue_ai_forms_assets($hook_suffix)
    {
        $screen = get_current_screen();

        if (! $this->is_aipkit_page($screen)) {
            return;
        }

        $this->enqueue_admin_main_script();
        $this->ensure_dashboard_core_data();
        $this->localize_data();
    }

    private function localize_data(): void
    {
        if (self::is_script_localized('aipkit-admin-main', 'aipkit_ai_forms_config')) {
            return;
        }

        $vector_store_localization = AIPKit_Providers::get_vector_store_localization_payload('ai_forms_ui');
        $embedding_localization = AIPKit_Providers::get_embedding_localization_payload('ai_forms_ui', false);

        wp_localize_script('aipkit-admin-main', 'aipkit_ai_forms_config', [
            'nonce_manage_forms' => wp_create_nonce('aipkit_manage_ai_forms_nonce'),
            'nonce_settings' => wp_create_nonce('aipkit_ai_forms_settings_nonce'),
            'current_user_id' => get_current_user_id(),
            'vectorStores' => $vector_store_localization['vectorStores'],
            'embeddingProviderMap' => $embedding_localization['embeddingProviderMap'],
            'embeddingModels' => $embedding_localization['embeddingModels'],
            'text' => [
                'savingForm' => __('Saving form...', 'gpt3-ai-content-generator'),
                'formSaved' => __('Form saved successfully!', 'gpt3-ai-content-generator'),
                'errorSavingForm' => __('Error saving form.', 'gpt3-ai-content-generator'),
                'generatingForm' => __('Generating form draft...', 'gpt3-ai-content-generator'),
                'formGenerated' => __('Form draft generated. Review and save it when ready.', 'gpt3-ai-content-generator'),
                'errorGeneratingForm' => __('Error generating form draft.', 'gpt3-ai-content-generator'),
                'loadingForms' => __('Loading forms...', 'gpt3-ai-content-generator'),
                'deletingForm' => __('Deleting form...', 'gpt3-ai-content-generator'),
                'deletingAllForms' => __('Deleting all forms...', 'gpt3-ai-content-generator'),
                'duplicatingForm' => __('Duplicating...', 'gpt3-ai-content-generator'),
                'formDeleted' => __('Form deleted.', 'gpt3-ai-content-generator'),
                'allFormsDeleted' => __('All forms deleted.', 'gpt3-ai-content-generator'),
                'errorDeletingForm' => __('Error deleting form.', 'gpt3-ai-content-generator'),
                'errorDeletingAllForms' => __('Error deleting all forms.', 'gpt3-ai-content-generator'),
                'errorDuplicatingForm' => __('Error duplicating form.', 'gpt3-ai-content-generator'),
                'confirmDeleteForm' => __('Are you sure you want to delete this form? This action cannot be undone.', 'gpt3-ai-content-generator'),
                'confirmDeleteAllForms' => __('Are you sure you want to delete ALL forms? This action cannot be undone.', 'gpt3-ai-content-generator'),
                'confirmReplaceGeneratedDraft' => __('Generating a new draft will replace the current title, prompt, and fields in the editor. Continue?', 'gpt3-ai-content-generator'),
                'formTitleRequired' => __('Form title is required.', 'gpt3-ai-content-generator'),
                'promptTemplateRequired' => __('Prompt template is required.', 'gpt3-ai-content-generator'),
                'generatorPromptRequired' => __('Describe the AI task before generating a form draft.', 'gpt3-ai-content-generator'),
                'generatorModelRequired' => __('Select an engine and model before generating a form draft.', 'gpt3-ai-content-generator'),
                'confirmSaveEmptyForm' => __('This form currently has no fields. Saving now will remove previously configured fields. Do you want to continue?', 'gpt3-ai-content-generator'),
                'editFormTitle' => __('Edit AI Form', 'gpt3-ai-content-generator'),
                'createNewFormTitle' => __('Create New AI Form', 'gpt3-ai-content-generator'),
                'confirmDeleteElement' => __('Are you sure you want to delete this form element?', 'gpt3-ai-content-generator'),
                'noOptionsConfigured' => __('No options configured', 'gpt3-ai-content-generator'),
                'settingsLabel' => __('Label Text', 'gpt3-ai-content-generator'),
                'settingsFieldId' => __('Field Variable Name (for prompt)', 'gpt3-ai-content-generator'),
                'settingsFieldIdHelp' => __('Use as {your_variable_name} in the Prompt Template. Must be unique and contain only letters, numbers, underscores.', 'gpt3-ai-content-generator'),
                'settingsPlaceholder' => __('Placeholder Text', 'gpt3-ai-content-generator'),
                'settingsRequired' => __('Required Field', 'gpt3-ai-content-generator'),
                'settingsSelectOptions' => __('Options (Value|Text)', 'gpt3-ai-content-generator'),
                'settingsSelectOptionValue' => __('Value', 'gpt3-ai-content-generator'),
                'settingsSelectOptionText' => __('Display Text', 'gpt3-ai-content-generator'),
                'settingsAddOption' => __('Add Option', 'gpt3-ai-content-generator'),
                'settingsRemoveOption' => __('Remove Option', 'gpt3-ai-content-generator'),
                'settingsDoneButton' => __('Done', 'gpt3-ai-content-generator'),
                'errorUniqueFieldId' => __('Field Variable Name must be unique and valid (letters, numbers, underscores).', 'gpt3-ai-content-generator'),
            ],
        ]);
    }
}

class AIPKit_Woocommerce_Writer_Assets extends AIPKit_Admin_Asset_Base
{
    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets($hook_suffix)
    {
        $screen = get_current_screen();

        if (! $this->is_aipkit_page($screen) || ! class_exists(aipkit_dashboard::class)) {
            return;
        }

        $modules = aipkit_dashboard::get_module_settings();
        if (empty($modules['content_writer']) || ! class_exists('WooCommerce')) {
            return;
        }

        $this->enqueue_admin_main_css();
        $this->enqueue_admin_main_script();
        $this->ensure_dashboard_core_data();
    }
}
