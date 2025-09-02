<?php
/**
 * Plugin Name: SKP FBT Plugin
 * Description: Frequently Bought Together plugin for WooCommerce
 * Version: 1.0.0
 * Author: Shruti Sharma
 */

if (!defined('ABSPATH')) exit;

include plugin_dir_path(__FILE__) . 'templates/product-widget.php';
include plugin_dir_path(__FILE__) . 'includes/api.php';
// include plugin_dir_path(__FILE__) . 'includes/functions.php';
include plugin_dir_path(__FILE__) . 'includes/db-schema.php';
include plugin_dir_path(__FILE__) . 'includes/class-skp-fbt-settings.php';
// include plugin_dir_path(__FILE__) . 'includes/recs-query.php';

/**
 * Enqueue scripts and pass nonce + REST URL to JS
 */
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'fbt-widget',
        plugin_dir_url(__FILE__) . 'assets/js/fbt-widget.js',
        [], // no jQuery dependency
        false,
        true // load in footer
        
    );


//     // Pass nonce and rest URL to JS
    wp_localize_script('fbt-widget', 'fbtData', [
        'nonce'   => wp_create_nonce('skp_fbt_nonce'),
        'restUrl' => esc_url_raw(rest_url('skp-fbt/v1/'))
    ]);
});

/**
 * On plugin activation, create/update DB tables
 */
register_activation_hook(__FILE__, 'skp_fbt_activate_plugin');

function skp_fbt_activate_plugin() {
    skp_fbt_create_tables(); // calls your function to create/update tables
}
