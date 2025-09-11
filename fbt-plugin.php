<?php
/**
 * Plugin Name: SKP FBT Plugin
 * Description: Frequently Bought Together plugin for WooCommerce
 * Version: 1.0.0
 * Author: Shruti Sharma
 */

if (!defined('ABSPATH')) exit;

include plugin_dir_path(__FILE__) . 'templates/product-widget.php';
include plugin_dir_path(__FILE__) . 'templates/cart-widget.php';
include plugin_dir_path(__FILE__) . 'includes/api.php';
include plugin_dir_path(__FILE__) . 'includes/db-schema.php';
include plugin_dir_path(__FILE__) . 'includes/class-skp-fbt-settings.php';

function enqueue_fbt_assets() {
    // ✅ Nonce generate
    $wp_nonce    = wp_create_nonce('wp_rest');      // WordPress REST API
    // $store_nonce = wp_create_nonce('wc_store_api'); // WooCommerce Store API

    // ✅ CSS
    wp_enqueue_style(
        'fbt-widget-css',
        plugin_dir_url(__FILE__) . 'assets/fbt-styles.css'
    );

    // ✅ JS
    wp_enqueue_script(
        'fbt-widget-js',
        plugin_dir_url(__FILE__) . 'assets/js/fbt-widget.js',
        [],           // dependencies
        time(),       // version
        true          // footer me load
    );

    // ✅ JS me variables inject
    wp_localize_script('fbt-widget-js', 'skpFbtSettings', [
        'wp_nonce'    => $wp_nonce,
    
        'api_root'    => esc_url_raw(rest_url())
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_fbt_assets');
add_action('wp_enqueue_scripts', function() {
    if (function_exists('is_cart') && is_cart()) {
        // ✅ Cart widget CSS
        wp_enqueue_style(
            'cyl-widget-css',
            plugin_dir_url(__FILE__) . 'assets/cart-widget.css',
                 [],
            time()
        );

        // ✅ Cart widget JS
        wp_enqueue_script(
            'skp-fbt-cart-js',
            plugin_dir_url(__FILE__) . 'assets/js/cart-widget.js',
            [],
            time(),
            true
        );
    }
}, 20);

register_activation_hook(__FILE__, 'skp_fbt_activate_plugin');

function skp_fbt_activate_plugin() {
    skp_fbt_create_tables();
}
