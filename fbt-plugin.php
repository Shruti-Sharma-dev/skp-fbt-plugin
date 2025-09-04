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
// Enqueue scripts
function enqueue_fbt_assets() {
    wp_enqueue_style(
        'fbt-widget-css',
        plugin_dir_url(__FILE__) . 'assets/fbt-styles.css'
    );

    wp_enqueue_script(
        'fbt-widget-js',
        plugin_dir_url(__FILE__) . 'assets/js/fbt-widget.js',
        [],
        false,
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_fbt_assets');

// Localize script on single product page
function localize_fbt_script() {
    if (!function_exists('is_product')) return;

    if (is_product()) {
        global $post;
        if ($post && isset($post->ID)) {
            wp_localize_script('fbt-widget-js', 'SKP_FBT_PRODUCT', [
                'id' => $post->ID
            ]);
        }
    }
}
add_action('wp_enqueue_scripts', 'localize_fbt_script', 30); // increase priority




/**
 * On plugin activation, create/update DB tables
 */
register_activation_hook(__FILE__, 'skp_fbt_activate_plugin');

function skp_fbt_activate_plugin() {
    skp_fbt_create_tables(); // calls your function to create/update tables
}
