<?php
// 1️⃣ Admin menu me add karna
add_action('admin_menu', 'skp_fbt_menu');
function skp_fbt_menu()
{
    add_menu_page(
        'SKP FBT Settings',  // Page title
        'SKP FBT',           // Menu title
        'manage_options',    // Capability
        'skp-fbt-settings',  // Menu slug
        'skp_fbt_settings_page', // Callback function
        'dashicons-chart-bar',    // Icon
        56
    );
}

// 2️⃣ Register setting
add_action('admin_init', 'skp_fbt_register_settings');
function skp_fbt_register_settings()
{
    register_setting('skp_fbt_settings_group', 'skp_fbt_min_cooccurrence'); // option name
    register_setting('skp_fbt_settings_group', 'skp_fbt_top_n');
    register_setting('skp_fbt_settings_group', 'skp_fbt_price_band');   
    register_setting('skp_fbt_settings_group', 'skp_fbt_placement');


    register_setting('skp_fbt_settings_group', 'skp_fbt_ab_enabled');
    register_setting('skp_fbt_settings_group', 'skp_fbt_ab_cohort');
    register_setting('skp_fbt_settings_group', 'skp_fbt_ab_cookie_ttl');
    register_setting('skp_fbt_settings_group', 'skp_fbt_export_enabled');
    register_setting('skp_fbt_settings_group', 'skp_fbt_export_path');
    register_setting('skp_fbt_settings_group', 'skp_fbt_last_run'); 

    add_settings_section(
        'skp_fbt_main_section', // ID
        'Main Settings',        // Title
         null,                   // Callback for description
        'skp-fbt-settings'      // Page slug
    );

    add_settings_section(

        'skp_fbt_placement_section',
        'Placement Setting',
        null,
        'skp-fbt-settings' 
    );

      add_settings_section(
        'skp_fbt_ab_section',
        'A/B Testing',
        null,
        'skp-fbt-settings'
    );

    add_settings_section(
        'skp_fbt_metrics_section',
        'Metrics Export',
        null,
        'skp-fbt-settings'
    );

    add_settings_section(
        'skp_fbt_batch_section',
        'Batch / Rebuild',
        null,
        'skp-fbt-settings'
    );

    add_settings_field(
        'skp_fbt_min_cooccurrence',      // ID
        'Min Co-occurrence Count',       // Label
        'skp_fbt_min_cooccurrence_cb',   // Callback to render input
        'skp-fbt-settings',              // Page slug
        'skp_fbt_main_section'           // Section ID
    );
    add_settings_field(
        'skp_fbt_top_n',     
        'Top N Recommendations',
        'skp_fbt_top_n_cb',   
        'skp-fbt-settings',            
        'skp_fbt_main_section'       
    );
    add_settings_field(
        'skp_fbt_price_band',
        'Price Band %',
        'skp_fbt_price_band_cb',
        'skp-fbt-settings',
        'skp_fbt_main_section'
    );

    add_settings_field(
        'skp_fbt_placement',
        'Placement Hook',
        'skp_fbt_placement_cb',
        'skp-fbt-settings',
        'skp_fbt_placement_section'
    );

    add_settings_field('skp_fbt_ab_enabled', 'Enable A/B Testing', 'skp_fbt_ab_enabled_cb', 'skp-fbt-settings', 'skp_fbt_ab_section');
    add_settings_field('skp_fbt_ab_cohort', 'Cohort %', 'skp_fbt_ab_cohort_cb', 'skp-fbt-settings', 'skp_fbt_ab_section');
    

    // 🔹 Metrics fields
    add_settings_field('skp_fbt_export_enabled', 'Enable Export', 'skp_fbt_export_enabled_cb', 'skp-fbt-settings', 'skp_fbt_metrics_section');
    add_settings_field('skp_fbt_export_path', 'Export Path', 'skp_fbt_export_path_cb', 'skp-fbt-settings', 'skp_fbt_metrics_section');

    // 🔹 Batch fields
    add_settings_field('skp_fbt_last_run', 'Last Run Status', 'skp_fbt_last_run_cb', 'skp-fbt-settings', 'skp_fbt_batch_section');
    add_settings_field('skp_fbt_rebuild_now', 'Rebuild Now', 'skp_fbt_rebuild_now_cb', 'skp-fbt-settings', 'skp_fbt_batch_section');
}



// 3️⃣ Callback to render input
function skp_fbt_min_cooccurrence_cb()
{
    $value = get_option('skp_fbt_min_cooccurrence', 2); // default 2
    echo '<input type="number" name="skp_fbt_min_cooccurrence" value="' . esc_attr($value) . '" min="1" />';
}

function skp_fbt_top_n_cb()
{
    $value = get_option('skp_fbt_top_n', 3); // default 3
    echo '<input type="number" name="skp_fbt_top_n" value="' . esc_attr($value) . '" min="1" max="5" />';
}

function skp_fbt_price_band_cb()
{
    $value = get_option('skp_fbt_price_band', 20); // default 20%
    echo '<input type="number" name="skp_fbt_price_band" value="' . esc_attr($value) . '" min="0" max="100" /> %';
}

function skp_fbt_placement_cb() {
    $value = get_option('skp_fbt_placement', 'woocommerce_after_add_to_cart_form');

    $placements = [
    'woocommerce_after_add_to_cart_form' => 'After Add to Cart Form', // ye sabse common, button ke neeche
    'woocommerce_after_single_product_summary' => 'After Product Summary', // summary ke neeche
    'woocommerce_before_add_to_cart_form' => 'Before Add to Cart Form', // button ke upar
    'woocommerce_before_single_product_summary' => 'Before Product Summary', // product title/summary ke upar
    'woocommerce_single_product_summary' => 'Inside Product Summary' // description/title area me
];


    echo '<select name="skp_fbt_placement">';
    foreach ($placements as $key => $label) {
        $selected = ($value === $key) ? 'selected' : '';
        echo "<option value='{$key}' {$selected}>{$label}</option>";
    }
    echo '</select>';
}

function skp_fbt_ab_enabled_cb() {
    $value = get_option('skp_fbt_ab_enabled', 0);
    echo '<input type="checkbox" name="skp_fbt_ab_enabled" value="1" ' . checked(1, $value, false) . ' />';
}

function skp_fbt_ab_cohort_cb() {
    $value = get_option('skp_fbt_ab_cohort', 50);
    echo '<input type="number" name="skp_fbt_ab_cohort" value="' . esc_attr($value) . '" min="0" max="100" /> %';
}

function skp_fbt_ab_cookie_ttl_cb() {
    $value = get_option('skp_fbt_ab_cookie_ttl', 30);
    echo '<input type="number" name="skp_fbt_ab_cookie_ttl" value="' . esc_attr($value) . '" min="1" /> days';
}

function skp_fbt_export_enabled_cb() {
    $value = get_option('skp_fbt_export_enabled', 0);
    echo '<input type="checkbox" name="skp_fbt_export_enabled" value="1" ' . checked(1, $value, false) . ' />';
}

function skp_fbt_export_path_cb() {
    $value = get_option('skp_fbt_export_path', '/wp-content/uploads/fbt-metrics.csv');
    echo '<input type="text" name="skp_fbt_export_path" value="' . esc_attr($value) . '" size="50" />';
}

function skp_fbt_last_run_cb() {
    $value = get_option('skp_fbt_last_run', 'Not run yet');
    echo '<strong>' . esc_html($value) . '</strong>';
}

function skp_fbt_rebuild_now_cb() {
    echo '<button type="button" class="button button-secondary" id="skp-fbt-rebuild">Rebuild Now</button>';
    echo '<p class="description">Runs batch immediately (via AJAX)</p>';
}

// 4️⃣ Settings page HTML
function skp_fbt_settings_page()
{
    ?>
    <div class="wrap">
        <h1>SKP FBT Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('skp_fbt_settings_group');
            do_settings_sections('skp-fbt-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
