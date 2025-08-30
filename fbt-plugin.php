<?php
/**
 * Plugin Name: SKP FBT Plugin
 * Description: Frequently Bought Together plugin for WooCommerce
 * Version: 1.0.0
 * Author: Shruti Sharma
 */

if (!defined('ABSPATH')) exit;

// Include core functions
// require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
// include plugin_dir_path(__FILE__) . 'templates/cart-widget.php';

include plugin_dir_path(__FILE__) . 'templates/product-widget.php';

include plugin_dir_path(__FILE__) . 'includes/api.php';
// include plugin_dir_path(__FILE__) . 'includes/functions.php';
include plugin_dir_path(__FILE__) . 'includes/db-schema.php';
include plugin_dir_path(__FILE__) . 'includes/class-skp-fbt-settings.php';
// include plugin_dir_path(__FILE__) . 'includes/recs-query.php';


add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'fbt-widget',
        plugin_dir_url(__FILE__) . 'assets/js/fbt-widget.js',
        [], // no jQuery dependency
        false,
        true // load in footer
    );
});

register_activation_hook( __FILE__, 'skp_fbt_activate_plugin' );

function skp_fbt_activate_plugin() {
    skp_fbt_create_tables(); // calls your function to create/update tables
}


