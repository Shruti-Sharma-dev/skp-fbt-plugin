<?php
/**
 * Plugin Name: SKP FBT Plugin
 * Description: Frequently Bought Together plugin for WooCommerce
 * Version: 1.0.0
 * Author: Shruti Sharma
 */

if (!defined('ABSPATH')) exit;

// Include product & cart widget functions
require_once plugin_dir_path(__FILE__) . 'templates/fbt-widgets.php';

// Enqueue widget CSS
function skp_fbt_enqueue_assets() {
    wp_enqueue_style('skp-fbt-css', plugin_dir_url(__FILE__) . 'assets/cart-widget.css');
}
add_action('wp_enqueue_scripts', 'skp_fbt_enqueue_assets');
