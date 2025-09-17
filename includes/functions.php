<?php
// Enqueue frontend JS
function skp_fbt_enqueue_scripts() {
    wp_enqueue_script(
        'skp-fbt-event-js',
        plugin_dir_url(__FILE__) . '../assets/js/fbt-events.js',
        array(),
        '1.5', // bump version to clear cache
        true
    );

    // Generate session_id once per user
    if (!isset($_COOKIE['skp_fbt_session'])) {
        $session_id = 'sess_' . wp_generate_password(9, false, false);
        setcookie('skp_fbt_session', $session_id, time() + 86400 * 30, "/"); // 30 days
    } else {
        $session_id = $_COOKIE['skp_fbt_session'];
    }

    // Simple cohort assignment (A/B)
    if (!isset($_COOKIE['skp_fbt_cohort'])) {
        $cohort = (rand(0, 1) === 0) ? 'A' : 'B';
        setcookie('skp_fbt_cohort', $cohort, time() + 86400 * 30, "/");
    } else {
        $cohort = $_COOKIE['skp_fbt_cohort'];
    }

    // Logged in user ID
    $user_id = get_current_user_id() ?: null;

    wp_localize_script('skp-fbt-event-js', 'SKP_FBT_DATA', array(
        'session_id' => $session_id,
        'cohort'     => $cohort,
        'user_id'    => $user_id,
        'nonce'      => wp_create_nonce('skp_fbt_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'skp_fbt_enqueue_scripts');
