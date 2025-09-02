<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function skp_fbt_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();




    $table_recs = $wpdb->prefix . "skp_fbt_recommendations";
    $sql = "CREATE TABLE $table_recs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id BIGINT UNSIGNED NOT NULL,
        recommendations JSON NOT NULL,
        score FLOAT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";




     // item→item (core)
    $sql1 = "CREATE TABLE {$wpdb->prefix}skp_fbt_item_item (
        product_id BIGINT NOT NULL,
        rec_id BIGINT NOT NULL,
        score FLOAT NOT NULL DEFAULT 0,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (product_id, rec_id),
        INDEX idx_product_score (product_id, score DESC)
    ) ENGINE=InnoDB $charset;";
    

    // user recs (optional cache)
    $sql2 = "CREATE TABLE {$wpdb->prefix}skp_fbt_user_recs (
        user_id BIGINT NOT NULL PRIMARY KEY,
        rec_ids JSON NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB $charset;";
 

    // metrics
    $sql3 = "CREATE TABLE {$wpdb->prefix}skp_fbt_metrics (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        ts DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        user_id BIGINT NULL,
        session_id VARCHAR(64) NOT NULL,
        event VARCHAR(32) NOT NULL,
        product_id BIGINT NULL,
        rec_id BIGINT NULL,
        order_id BIGINT NULL,
        cohort CHAR(1) NULL
    ) ENGINE=InnoDB $charset;";
 

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    dbDelta( $sql1 );
    dbDelta( $sql2 );
    dbDelta( $sql3 );
}
