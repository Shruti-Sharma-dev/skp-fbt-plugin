<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SKP_FBT_API {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'skp-fbt/v1', '/recommendations', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_recommendations' ],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route( 'skp-fbt/v1', '/track-event', [
            'methods' => 'POST',
            'callback' => [ $this, 'track_event' ],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route( 'skp-fbt/v1', '/save-recommendations', [
    'methods' => 'POST',
    'callback' => [ $this, 'save_recommendations' ],
    'permission_callback' => function() {
        // Require auth in production (for now open for testing)
        return current_user_can('manage_options') || true;
    }
]);

    }

    public function get_recommendations( $request ) {
        $product_id = intval( $request['product_id'] );
        require_once __DIR__ . '/recs-query.php';
        return skp_fbt_get_recommendations( $product_id );
    }

    public function track_event( $request ) {
        $event = sanitize_text_field( $request['event'] );
        $data  = $request['data'];
        require_once __DIR__ . '/metrics.php';
        return skp_fbt_log_event( $event, $data );
    }


    public function save_recommendations( $request ) {
    global $wpdb;
    $table = $wpdb->prefix . 'skp_fbt_recommendations';

    $product_id = intval( $request['product_id'] );
    $recs       = $request['recommendations']; // array of product IDs

    if ( ! $product_id || empty($recs) ) {
        return new WP_Error( 'invalid_data', 'Product ID and recommendations required', [ 'status' => 400 ] );
    }

    // Save or update in DB
    $wpdb->replace(
        $table,
        [
            'product_id' => $product_id,
            'recommendations' => maybe_serialize( $recs )
        ],
        [ '%d', '%s' ]
    );

    return [ 'success' => true, 'product_id' => $product_id, 'recommendations' => $recs ];
}

}

new SKP_FBT_API();
