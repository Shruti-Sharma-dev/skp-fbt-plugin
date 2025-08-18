<?php
/*
Plugin Name: SKP FBT (Frequently Bought Together)
Description: ML-driven Frequently Bought Together + Personalized Cross-Sells for WooCommerce.
Version: 0.1.0
Author: <Your Name>
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define('SKP_FBT_VERSION', '0.1.0');
define('SKP_FBT_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once SKP_FBT_PLUGIN_DIR . 'includes/class-skp-fbt-settings.php';

register_activation_hook(__FILE__, function () {
    // DB tables or options will be added in Week 2–3
});
