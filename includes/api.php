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


         register_rest_route('skp-fbt/v1', '/metrics', [
            'methods'  => 'POST',
            'callback' => [ $this, 'skp_fbt_save_event' ],
            'permission_callback' => '__return_true', // public, or tighten later
        ]);

    register_rest_route( 'skp-fbt/v1', '/save-recs', [
    'methods' => 'POST',
    'callback' => [ $this, 'save_item_item_recommendations' ],
    'permission_callback' => '__return_true'
    ]);

    register_rest_route( 'skp-fbt/v1', '/config', [


        'method' => 'GET',
        'callback' => [$this, 'skp_fbt_get_config'],
        'permission_callback' => '__return_true'
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
         ORDER BY score DESC ",
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








public function save_item_item_recommendations( $request ) {
    global $wpdb;
    $table = $wpdb->prefix . 'skp_fbt_item_item';

    $product_id = intval( $request['product_id'] );
    $rec_id     = intval( $request['rec_id'] );
    $score      = floatval( $request['score'] );


    if ( ! $product_id || ! $rec_id ) {
        return new WP_Error('invalid_data', 'Product ID and rec_id required', ['status' => 400]);
    }


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



 function skp_fbt_save_event() {
    global $wpdb;

    $table = $wpdb->prefix . 'skp_fbt_metrics';
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['event'], $data['session_id'])) {
        wp_send_json_error(['message' => 'Invalid data']);
        return;
    }

    $wpdb->insert(
        $table,
        [
            'ts'         => current_time('mysql'),
            'user_id'    => $data['user_id'] ?? null,
            'session_id' => $data['session_id'],
            'event'      => sanitize_text_field($data['event']),
            'product_id' => $data['product_id'] ?? null,
            'rec_id'     => $data['rec_id'] ?? null,
            'order_id'   => $data['order_id'] ?? null,
            'cohort'     => $data['cohort'] ?? null,
        ],
        [
            '%s','%d','%s','%s','%d','%d','%d','%s'
        ]
    );

    wp_send_json_success(['message' => 'Event saved']);
}


public function skp_fbt_get_config() {
    return [
         'min_occurrence' => get_option('skp_fbt_min_cooccurrence', 2),
         'top_n'          => get_option('skp_fbt_top_n', 3),
         'price_band'     => get_option('skp_fbt_price_band', 20)
    ];

}

}

new SKP_FBT_API();
