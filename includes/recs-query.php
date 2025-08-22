<?php
if (!defined('ABSPATH')) exit;

require_once ABSPATH . 'wp-includes/pluggable.php';

/**
 * Get top-N recommendations for a product.
 * - Excludes products in cart
 * - Filters out out-of-stock and hidden products
 * - Falls back to 30-day category best-sellers if <2 candidates remain
 * - Uses transient caching to reduce DB hits (short TTL)
 *
 * @param int $product_id
 * @param array $cart_ids array of product IDs to exclude
 * @param int $limit
 * @return array of arrays ['rec_id'=>int,'score'=>float]
 */
function skp_fbt_get_recs( int $product_id, array $cart_ids = [], int $limit = 3 ) {
    global $wpdb;
    $cache_key = "skp_fbt_recs_{$product_id}_{$limit}_" . md5(implode(',', $cart_ids));
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $table = $wpdb->prefix . 'skp_fbt_item_item';

    // Build exclusion SQL for cart
    $exclude_sql = '';
    $params = [$product_id];
    if (!empty($cart_ids)) {
        $placeholders = implode(',', array_fill(0, count($cart_ids), '%d'));
        $exclude_sql = "AND rec_id NOT IN ($placeholders)";
        $params = array_merge($params, $cart_ids);
    }

    // Main query: top by score
    $sql = $wpdb->prepare(
        "SELECT rec_id, score FROM $table WHERE product_id = %d $exclude_sql ORDER BY score DESC LIMIT %d",
        array_merge($params, [$limit])
    );

    $rows = $wpdb->get_results($sql, ARRAY_A);
    if (!$rows) $rows = [];

    // Filter stock & visibility quickly (use wc_get_product which is fast enough for small N)
    $filtered = [];
    foreach ($rows as $r) {
        $pid = intval($r['rec_id']);
        $p = wc_get_product($pid);
        if (!$p) continue;
        // Respect stock & catalog visibility
        if (!$p->is_in_stock()) continue;
        $vis = $p->get_catalog_visibility();
        if ($vis === 'hidden') continue;
        $filtered[] = ['rec_id' => $pid, 'score' => floatval($r['score'])];
    }

    // If not enough, fallback to category best-sellers (last 30d)
    if (count($filtered) < min(2, $limit)) {
        $need = $limit - count($filtered);
        $fallback = skp_fbt_category_bestsellers($product_id, $cart_ids, $need);
        $filtered = array_merge($filtered, $fallback);
    }

    // Trim to limit and cache for short TTL (30s) to hit perf target
    $result = array_slice($filtered, 0, $limit);
    set_transient($cache_key, $result, 30); // 30 seconds
    return $result;
}

/**
 * Fallback: category best-sellers in the last 30 days.
 * Returns array of ['rec_id'=>int,'score'=>float]
 */
function skp_fbt_category_bestsellers( int $product_id, array $exclude_ids = [], int $limit = 3 ) {
    // Get categories of the product
    $cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
    if (empty($cats)) return [];

    // Use WC orders via SQL: count qty ordered per product in last 30 days for these categories
    global $wpdb;
    $date_after = date('Y-m-d H:i:s', strtotime('-30 days'));

    // Build excluded list
    $exclude_sql = '';
    $params = [$date_after];
    if (!empty($exclude_ids)) {
        $placeholders = implode(',', array_fill(0, count($exclude_ids), '%d'));
        $exclude_sql = "AND oi.product_id NOT IN ($placeholders)";
        $params = array_merge($params, $exclude_ids);
    }

    // We'll join order items to posts to filter by category
    $cat_placeholders = implode(',', array_fill(0, count($cats), '%d'));
    $params = array_merge($params, $cats);

    $sql = "
    SELECT oi.product_id AS pid, SUM(oi.meta_value) AS qty
    FROM {$wpdb->prefix}woocommerce_order_items oi_items
    JOIN {$wpdb->prefix}woocommerce_order_itemmeta oi_meta ON oi_items.order_item_id = oi_meta.order_item_id
    JOIN {$wpdb->prefix}posts p ON oi_items.order_id = p.ID
    JOIN {$wpdb->prefix}woocommerce_order_itemmeta oi ON oi_items.order_item_id = oi_meta.order_item_id
    WHERE p.post_type = 'shop_order' AND p.post_status IN ('wc-completed') AND p.post_date >= %s
      AND oi_meta.meta_key = '_product_id'
      AND oi_items.order_item_type = 'line_item'
      $exclude_sql
      AND EXISTS (
        SELECT 1 FROM {$wpdb->prefix}term_relationships tr
        JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        WHERE tr.object_id = oi_meta.meta_value AND tt.term_id IN ($cat_placeholders)
      )
    GROUP BY oi_meta.meta_value
    ORDER BY qty DESC
    LIMIT  %d
    ";
    // Note: this SQL may vary across Woo versions. If failure, fallback quietly.
    try {
        $final_sql = $wpdb->prepare($sql, array_merge($params, [$limit]));
        $res = $wpdb->get_results($final_sql, ARRAY_A);
        $out = [];
        if ($res) {
            foreach ($res as $r) {
                $pid = intval($r['pid']);
                $p = wc_get_product($pid);
                if (!$p || !$p->is_in_stock() || $p->get_catalog_visibility() === 'hidden') continue;
                $out[] = ['rec_id' => $pid, 'score' => floatval($r['qty'])];
            }
        }
        return $out;
    } catch (Exception $e) {
        // If SQL fails, return empty — plugin should fail gracefully
        return [];
    }
}
