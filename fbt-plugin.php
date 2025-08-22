<?php
/**
 * Plugin Name: SKP FBT Plugin
 * Description: Frequently Bought Together plugin for WooCommerce
 * Version: 1.0.0
 * Author: Shruti Sharma
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Autoload includes
require_once __DIR__ . '/includes/db-schema.php';
require_once __DIR__ . '/includes/class-skp-fbt-cart-widget.php';
require_once __DIR__ . '/includes/class-skp-fbt-settings.php';
require_once __DIR__ . '/includes/class-skp-fbt-api.php';

// Plugin activation hook -> create DB tables
register_activation_hook( __FILE__, 'skp_fbt_install' );
function skp_fbt_install() {

    //use file exists();
    require_once __DIR__ . '/includes/db-schema.php';
    skp_fbt_create_tables();
}

// Plugin deactivation hook -> cleanup if needed
register_deactivation_hook( __FILE__, 'skp_fbt_deactivate' );
function skp_fbt_deactivate() {
    // No hard delete, maybe disable cron jobs if any
}
