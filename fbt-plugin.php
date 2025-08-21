<?php
/*
Plugin Name: Frequently Bought Together
Description: WooCommerce FBT plugin skeleton
Version: 0.1
Author: Shruti Sharma
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Include config
require_once plugin_dir_path(__FILE__) . 'config.php';

// Include admin settings page
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';

// Include core functions
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-skp-fbt-settings.php';

// Hook to add settings page
add_action('admin_menu', 'fbt_add_admin_menu');

function fbt_add_admin_menu() {
    add_menu_page(
        'FBT Settings',      // Page title
        'FBT Settings',      // Menu title
        'manage_options',    // Capability
        'fbt-settings',      // Menu slug
        'fbt_settings_page', // Function
        'dashicons-cart',    // Icon
        56                   // Position
    );
}
