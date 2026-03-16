<?php

namespace WPAICG\Includes\DependencyLoaders;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Admin_Asset_Handlers_Loader
{
    public static function load()
    {
        require_once WPAICG_PLUGIN_DIR . 'admin/assets/class-aipkit-admin-assets.php';
    }
}
