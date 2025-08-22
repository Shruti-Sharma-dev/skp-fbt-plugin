<?php
if (!defined('ABSPATH')) exit;

/**
 * Register metrics REST endpoint.
 * POST /wp-json/skp-fbt/v1/metrics
 * Body: {nonce, session_id, event, product_id, rec_id, order_id, cohort}
 *
 * Notes: No PII allowed - plugin will ignore email/phone fields. Nonce protects against CSRF.
 */
add_action('rest_api_init', function () {
    register_rest_route('skp-fbt/v1', '/metrics', [
        'methods' => 'POST',
        'callback' => 'skp_fbt_metrics_endpoint',
        'permission_callback' => '__return_true', // nonce validated in handler
    ]);
});

function skp_fbt_metrics_endpoint( \WP_REST_Request $request ) {
    $body = $request->get_json_params();

    // Validate nonce (expected in body as 'nonce')
    if (empty($body['nonce']) || !wp_verify_nonce($body['nonce'], 'skp_fbt_metrics')) {
        return new WP_REST_Response(['error' => 'invalid_nonce'], 403);
    }

    // Allowed events only
    $allowed_events = ['widget_view', 'rec_click', 'rec_add_to_cart', 'rec_impression'];
    $event = sanitize_text_field($body['event'] ?? '');
    if (!in_array($event, $allowed_events, true)) {
        return new WP_REST_Response(['error' => 'invalid_event'], 400);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'skp_fbt_metrics';

    // No PII: only accept user_id if it's numeric (Woo user id) else NULL
    $user_id = isset($body['user_id']) && is_numeric($body['user_id']) ? intval($body['user_id']) : null;
    $session_id = sanitize_text_field($body['session_id'] ?? wp_generate_password(12, false));
    $product_id = isset($body['product_id']) && is_numeric($body['product_id']) ? intval($body['product_id']) : null;
    $rec_id = isset($body['rec_id']) && is_numeric($body['rec_id']) ? intval($body['rec_id']) : null;
    $order_id = isset($body['order_id']) && is_numeric($body['order_id']) ? intval($body['order_id']) : null;
    $cohort = isset($body['cohort']) ? substr(sanitize_text_field($body['cohort']), 0, 1) : null;
    $ts = current_time('mysql');

    $wpdb->insert($table, [
        'ts' => $ts,
        'user_id' => $user_id,
        'session_id' => $session_id,
        'event' => $event,
        'product_id' => $product_id,
        'rec_id' => $rec_id,
        'order_id' => $order_id,
        'cohort' => $cohort
    ], ['%s','%d','%s','%s','%d','%d','%d','%s']);

    return new WP_REST_Response(['ok' => true], 200);
}
