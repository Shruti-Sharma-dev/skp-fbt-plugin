<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SKP_FBT_API {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'skp-fbt/v1', '/for-product/(?P<product_id>\d+)', [
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

     register_rest_route('temp/v1', '/db-check', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}skp_fbt_recommendations LIMIT 10", ARRAY_A);
            return $results;
        },
        'permission_callback' => function() { return current_user_can('manage_options'); }
    ]);

    }

   public function get_recommendations( $request ) {
        global $wpdb;
    $table = $wpdb->prefix . 'skp_fbt_recommendations';

    $product_id = intval( $request['product_id'] );

    $row = $wpdb->get_row(
        $wpdb->prepare("SELECT recommendations FROM $table WHERE product_id = %d", $product_id),
        ARRAY_A
    );

    if ( ! $row ) {
        return [ 'success' => false, 'message' => 'No recommendations found.' ];
    }

    $recs = json_decode( $row['recommendations'], true );
    error_log("SKP DEBUG product_id: " . $product_id);
    error_log(print_r($row, true));

    return [
        'success'         => true,
        'product_id'      => $product_id,
        'recommendations' => $recs
    ];
    }


    public function track_event( $request ) {
        $event = sanitize_text_field( $request['event'] );
        $data  = $request['data'];
        require_once __DIR__ . '/metrics.php';
        return skp_fbt_log_event( $event, $data );
    }


   public function save_recommendations( $request ) {
    error_log("Incoming POST: " . print_r($request->get_params(), true));

    global $wpdb;
    $table = $wpdb->prefix . 'skp_fbt_recommendations';

    $product_id = intval( $request['product_id'] );
    $recs       = $request['recommendations']; // array of product IDs
    $score      = isset($request['score']) ? floatval($request['score']) : 0;

    if ( ! $product_id || empty($recs) ) {
        return new WP_Error( 'invalid_data', 'Product ID and recommendations required', [ 'status' => 400 ] );
    }

    // Convert to JSON
    $new_recs = (array) $recs;

    // Check if record exists
    $existing = $wpdb->get_row(
        $wpdb->prepare("SELECT recommendations FROM $table WHERE product_id = %d", $product_id)
    );

    if ( $existing ) {
        // Merge old + new
        $old_recs = json_decode( $existing->recommendations, true );
        $merged   = array_values(array_unique(array_merge($old_recs, $new_recs)));

        $wpdb->update(
            $table,
            [
                'recommendations' => wp_json_encode($merged),
                'score'           => $score,
                'created_at'      => current_time('mysql'),
            ],
            [ 'product_id' => $product_id ],
            [ '%s', '%f', '%s' ],
            [ '%d' ]
        );
    } else {
        // Insert new
        $wpdb->insert(
            $table,
            [
                'product_id'      => $product_id,
                'recommendations' => wp_json_encode($new_recs),
                'score'           => $score,
                'created_at'      => current_time('mysql'),
            ],
            [ '%d', '%s', '%f', '%s' ]
        );
    }

    return [
        'success'         => true,
        'product_id'      => $product_id,
        'recommendations' => $new_recs,
        'score'           => $score
    ];
}


}

new SKP_FBT_API();
