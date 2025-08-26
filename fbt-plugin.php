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
include plugin_dir_path(__FILE__) . 'templates/cart-widget.php';

include plugin_dir_path(__FILE__) . 'templates/product-widget.php';

include plugin_dir_path(__FILE__) . 'includes/api.php';
include plugin_dir_path(__FILE__) . 'includes/functions.php';
include plugin_dir_path(__FILE__) . 'includes/db-schema.php';
include plugin_dir_path(__FILE__) . 'includes/class-skp-fbt-settings.php';


// // Cart widget
// add_action('woocommerce_after_cart_table', function() {
//     include plugin_dir_path(__FILE__) . 'templates/cart-widget.php';
// });

// // Enqueue CSS and JS
// add_action('wp_enqueue_scripts', function() {
//     wp_enqueue_style(
//         'skp-fbt-style',
//         plugin_dir_url(__FILE__) . 'assets/cart-widget.css',
//         array(),
//         '1.0.0'
//     );

//     wp_enqueue_script(
//         'skp-fbt-js',
//         plugin_dir_url(__FILE__) . 'assets/fbt-widget.js',
//         array('jquery'),
//         '1.0.0',
//         true
//     );

//     wp_localize_script('skp-fbt-js', 'skpFBT', [
//         'ajax_url' => admin_url('admin-ajax.php')
//     ]);
// });

// // AJAX handler for recommendations
// add_action('wp_ajax_get_fbt_recommendations', 'skp_fbt_get_recommendations_ajax');
// add_action('wp_ajax_nopriv_get_fbt_recommendations', 'skp_fbt_get_recommendations_ajax');
