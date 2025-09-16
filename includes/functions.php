<?php
// Enqueue frontend JS
function skp_fbt_enqueue_scripts() {
    wp_enqueue_script(
        'skp-fbt-js',
        plugin_dir_url(__FILE__) . '../assets/js/fbt-events.js',
        array(),
        '1.4', // version bumped to avoid cache
        true
    );

    // Localize script with default values
    wp_localize_script('skp-fbt-js', 'SKP_FBT_DATA', array(
        'ajax_url'   => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('skp_fbt_nonce'),
       
    ));
}
add_action('wp_enqueue_scripts', 'skp_fbt_enqueue_scripts');

