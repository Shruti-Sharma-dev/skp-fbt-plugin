<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SKP_FBT_API {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
   register_rest_route('skp-fbt/v1', '/for-product/(?P<product_id>\d+)', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_item_item_recommendations' ],
            'permission_callback' => '__return_true'
        ]);


        register_rest_route( 'skp-fbt/v1', '/track-event', [
            'methods' => 'POST',
            'callback' => [ $this, 'track_event' ],
            'permission_callback' => '__return_true'
        ]);

    register_rest_route( 'skp-fbt/v1', '/save-recs', [
    'methods' => 'POST',
    'callback' => [ $this, 'save_item_item_recommendations' ],
    'permission_callback' => '__return_true'
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
 


public function get_item_item_recommendations( $request ) {
    global $wpdb;
    $table = $wpdb->prefix . 'skp_fbt_item_item';
    $product_id = intval( $request['product_id'] );

    if (!$product_id) {
        return new WP_Error('invalid_data', 'Product ID is required', ['status' => 400]);
    }

    $results = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT rec_id, score 
         FROM {$wpdb->prefix}skp_fbt_item_item 
         WHERE product_id = %d AND score > 0 
         ORDER BY score DESC 
         LIMIT 3",
        $product_id
        ),
        ARRAY_A
    );

    if (empty($results)) {
        return [
            'success' => false,
            'message' => 'No recommendations found',
            'product_id' => $product_id,
            'recommendations' => []
        ];
    }

    return [
        'success' => true,
        'product_id' => $product_id,
        'recommendations' => array_map(function($row){
            return [
                'rec_id' => intval($row['rec_id']),
                'score' => floatval($row['score'])
            ];
        }, $results)
    ];
}







    public function track_event( $request ) {
        $event = sanitize_text_field( $request['event'] );
        $data  = $request['data'];
        require_once __DIR__ . '/metrics.php';
        return skp_fbt_log_event( $event, $data );
    }



public function save_item_item_recommendations( $request ) {
    global $wpdb;
    $table = $wpdb->prefix . 'skp_fbt_item_item';

    $product_id = intval( $request['product_id'] );
    $rec_id     = intval( $request['rec_id'] );
    $score      = floatval( $request['score'] );

    // Validate input
    if ( ! $product_id || ! $rec_id ) {
        return new WP_Error('invalid_data', 'Product ID and rec_id required', ['status' => 400]);
    }

    // Check if the recommendation already exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE product_id = %d AND rec_id = %d",
        $product_id, $rec_id
    ));

    if ($exists) {
        // Update existing row
        $wpdb->update(
            $table,
            [
                'score'      => $score,
                'updated_at' => current_time('mysql')
            ],
            [
                'product_id' => $product_id,
                'rec_id'     => $rec_id
            ],
            [ '%f', '%s' ],
            [ '%d', '%d' ]
        );
    } else {
        // Insert new row
        $wpdb->insert(
            $table,
            [
                'product_id' => $product_id,
                'rec_id'     => $rec_id,
                'score'      => $score,
                'updated_at' => current_time('mysql')
            ],
            [ '%d', '%d', '%f', '%s' ]
        );
    }

    return [
        'success'    => true,
        'product_id' => $product_id,
        'rec_id'     => $rec_id,
        'score'      => $score
    ];
}



}

new SKP_FBT_API();
