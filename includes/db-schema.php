<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function skp_fbt_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_recs = $wpdb->prefix . "skp_fbt_recommendations";
    $sql = "CREATE TABLE $table_recs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id BIGINT UNSIGNED NOT NULL,
        recommended_id BIGINT UNSIGNED NOT NULL,
        score FLOAT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    $table_metrics = $wpdb->prefix . "skp_fbt_metrics";
    $sql2 = "CREATE TABLE $table_metrics (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        event VARCHAR(255) NOT NULL,
        data LONGTEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    dbDelta( $sql2 );
}
