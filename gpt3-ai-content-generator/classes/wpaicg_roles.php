<?php

namespace WPAICG;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\WPAICG\\WPAICG_Roles')) {
    class WPAICG_Roles
    {
        private static $instance = null;

        public $wpaicg_roles = array(
            'settings' => array('name' => 'Settings'),
            'single_content' => array(
                'name' => 'Content Writer',
                'hide' => 'single_content',
                'roles' => array(
                    'express' => array('name' => 'Express Mode'),
                    'custom' => array('name' => 'Custom Mode'),
                    'comparison' => array('name' => 'Comparison'),
                    'speech' => array('name' => 'Speech to Post'),
                    'playground' => array('name' => 'Playground'),
                    'logs' => array('name' => 'Logs')
                )
            ),
            'bulk_content' => array(
                'name' => 'AutoGPT',
                'hide' => 'bulk_content',
                'roles' => array(
                    'bulk' => array('name' => 'Dashboard'),
                    'editor' => array('name' => 'Bulk Editor'),
                    'csv' => array('name' => 'CSV'),
                    'copy-paste' => array('name' => 'Copy-Paste'),
                    'google-sheets' => array('name' => 'Google Sheets'),
                    'rss' => array('name' => 'RSS'),
                    'tweet' => array('name' => 'Twitter'),
                    'tracking' => array('name' => 'Queue'),
                    'setting' => array('name' => 'Settings')
                )
            ),
            'chatgpt' => array(
                'name' => 'ChatGPT',
                'hide' => 'chatgpt',
                'roles' => array(
                    'shortcode' => array('name' => 'Shortcode'),
                    'widget' => array('name' => 'Widget'),
                    'bots' => array('name' => 'Chat Bots'),
                    'pdf' => array('name' => 'PDF'),
                    'logs' => array('name' => 'Logs'),
                    'assistants' => array('name' => 'Assistants'),
                    'settings' => array('name' => 'Settings')
                )
            ),
            'image_generator' => array(
                'name' => 'Image Generator',
                'hide' => 'image_generator',
                'roles' => array(
                    'dalle' => array('name' => 'Dall-E'),
                    'stable-diffusion' => array('name' => 'Stable Diffusion'),
                    'shortcodes' => array('name' => 'Shortcodes'),
                    'logs' => array('name' => 'Logs'),
                    'settings' => array('name' => 'Settings')
                )
            ),
            'forms' => array(
                'name' => 'AI Forms',
                'hide' => 'forms',
                'roles' => array(
                    'forms' => array('name' => 'AI Forms'),
                    'logs' => array('name' => 'Logs'),
                    'settings' => array('name' => 'Settings')
                )
            ),
            'embeddings' => array(
                'name' => 'Embeddings',
                'hide' => 'embeddings',
                'roles' => array(
                    'content' => array('name' => 'Content Builder'),
                    'logs' => array('name' => 'Entries'),
                    'pdf' => array('name' => 'PDF'),
                    'builder' => array('name' => 'Index Builder'),
                    'settings' => array('name' => 'Settings'),
                    'troubleshoot' => array('name' => 'Troubleshoot')
                )
            ),
            'audio' => array(
                'hide' => 'audio',
                'name' => 'Audio Converter',
                'roles' => array(
                    'converter' => array('name' => 'Audio Converter'),
                    'logs' => array('name' => 'Logs')
                )
            ),
            'help' => array(
                'hide' => 'help',
                'name' => 'Wizard',
                'roles' => array(
                    'chatgpt' => array('name' => 'Add ChatGPT to My Website'),
                    'article' => array('name' => 'Create a Blog Post'),
                    'woocommerce' => array('name' => 'Optimize WooCommerce Product'),
                    'autogpt' => array('name' => 'Automate Content Creation'),
                    'image' => array('name' => 'Generate Images'),
                    'aiform' => array('name' => 'Create AI Form'),
                    'assistant' => array('name' => 'AI Assistant Setup'),
                    'audio' => array('name' => 'Convert an Audio'),
                    'compare' => array('name' => 'Compare AI Models')
                )
            ),
            'comment_reply' => array('name' => 'Comment Replier'),
            'ai_assistant' => array('name' => 'AI Assistant'),
            'instant_embedding' => array('name' => 'Instant Embedding'),
            'woocommerce' => array(
                'name' => 'WooCommerce',
                'hide' => '',
                'roles' => array(
                    'product_writer' => array('name' => 'Product Writer'),
                    'meta_box' => array('name' => 'Product Token Metabox'),
                    'content' => array('name' => 'Bulk Product Writer')
                )
            ),
            'suggester' => array('name' => 'Title Suggester'),
            'meta_box' => array('name' => 'Post Metabox'),
            'myai_account' => array('name' => 'AI Account')
        );

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'wpaicg_menu' ) );
            add_action('init',[$this,'register_roles_admin']);
        }

        public function wpaicg_menu()
        {
            add_submenu_page(
                'wpaicg',
                esc_html__('Role Manager','gpt3-ai-content-generator'),
                esc_html__('Role Manager','gpt3-ai-content-generator'),
                'manage_options',
                'wpaicg_roles',
                array( $this, 'wpaicg_roles' ),
                10
            );
        }

        public function wpaicg_roles()
        {
            $this->register_roles_admin();
            include WPAICG_PLUGIN_DIR.'admin/views/roles/index.php';
        }

        public function register_roles_admin()
        {
            $user_role = get_role('administrator');
            if ($user_role) { // Check if the $user_role object is not null
                foreach ($this->wpaicg_roles as $key => $wpaicg_role) {
                    if(isset($wpaicg_role['hide']) && !empty($wpaicg_role['hide'])){
                        $user_role->add_cap('wpaicg_'.$wpaicg_role['hide']);
                    }
                    if (isset($wpaicg_role['roles']) && count($wpaicg_role['roles'])) {
                        foreach ($wpaicg_role['roles'] as $key_role => $role_name) {
                            $user_role->add_cap('wpaicg_' . $key . '_' . $key_role);
                        }
                    } else {
                        $user_role->add_cap('wpaicg_' . $key);
                    }
                }
            } else {
                // Log an error or handle the case where the role doesn't exist
                error_log('The specified user role does not exist.');
            }
        }

        public function user_can($module, $tool = false, $action = 'action')
        {
            if(in_array('administrator',(array)wp_get_current_user()->roles)){
                return false;
            }
            $capability = $module;
            if($tool){
                $capability .= '_'.$tool;
            }
            if(current_user_can($capability)){
                return false;
            }
            else{
                $role_granted = '';
                $keyName = str_replace('wpaicg_','',$module);
                foreach($this->wpaicg_roles[$keyName]['roles'] as $key=>$role){
                    if(current_user_can($module.'_'.$key)){
                        $role_granted = $key;
                        break;
                    }
                }
                return admin_url('admin.php?page='.$module.'&'.$action.'='.$role_granted);
            }
        }
    }

    WPAICG_Roles::get_instance();
}
if(!function_exists(__NAMESPACE__.'\wpaicg_roles')){
    function wpaicg_roles(){
        return WPAICG_Roles::get_instance();
    }
}
