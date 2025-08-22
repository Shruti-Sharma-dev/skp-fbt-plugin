<?php
if (!defined('ABSPATH')) exit;

/**
 * REST endpoint to upsert item->item rows.
 * POST /wp-json/skp-fbt/v1/publish
 * Body: { rows: [{product_id, rec_id, score}], nonce(optional) }
 *
 * Security: requires WP authentication with a user that has 'manage_options' capability.
 * Use WP Application Password or cookie + capability.
 */
add_action('rest_api_init', function () {
    register_rest_route('skp-fbt/v1', '/publish', [
        'methods' => 'POST',
        'callback' => 'skp_fbt_publish_endpoint',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ]);
});

function skp_fbt_publish_endpoint( \WP_REST_Request $request ) {
    $params = $request->get_json_params();
    if (empty($params['rows']) || !is_array($params['rows'])) {
        return new WP_REST_Response(['error' => 'invalid_payload'], 400);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'skp_fbt_item_item';
    $now = current_time('mysql');

    // Prepare a bulk upsert: mysql ON DUPLICATE KEY UPDATE
    $values = [];
    $placeholders = [];
    $formats = [];
    foreach ($params['rows'] as $r) {
        if (!isset($r['product_id'], $r['rec_id'], $r['score'])) continue;
        $pid = intval($r['product_id']);
        $rid = intval($r['rec_id']);
        $scr = floatval($r['score']);
        $values[] = $pid;
        $values[] = $rid;
        $values[] = $scr;
        $values[] = $now;
        $placeholders[] = "(%d,%d,%f,%s)";
    }
    if (empty($placeholders)) return new WP_REST_Response(['error'=>'no_valid_rows'], 400);

    $query = "INSERT INTO $table (product_id, rec_id, score, updated_at) VALUES " .
             implode(',', $placeholders) .
             " ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = VALUES(updated_at)";

    // prepare expects separate args; use call_user_func_array
    $args = array_merge([$query], $values);
    // $wpdb->query($wpdb->prepare($query, ...$values)); // avoid prepare call complexity; use placeholder insertion via vsprintf
    $prepared_query = vsprintf(str_replace('%', '%%', $query), array_map(function($v){
        return is_string($v) ? "'".$v."'" : $v;
    }, $values));

    $res = $wpdb->query($prepared_query);
    if ($res === false) {
        return new WP_REST_Response(['error'=>'db_error','details'=> $wpdb->last_error], 500);
    }
    return new WP_REST_Response(['ok' => true, 'affected' => $res], 200);
}
