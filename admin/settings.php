<?php
function fbt_settings_page() {
    ?>
    <div class="wrap">
        <h1>FBT Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('fbt_settings_group');
            do_settings_sections('fbt-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function fbt_register_settings() {
    // Register settings
    register_setting('fbt_settings_group', 'fbt_api_url');
    register_setting('fbt_settings_group', 'fbt_enable_widget');

    add_settings_section('fbt_main_section', 'Main Settings', null, 'fbt-settings');
    
    // API URL field
    add_settings_field('fbt_api_url', 'Batch API URL', 'fbt_api_url_field', 'fbt-settings', 'fbt_main_section');
    // Enable/disable widget
    add_settings_field('fbt_enable_widget', 'Enable Widget', 'fbt_enable_widget_field', 'fbt-settings', 'fbt_main_section');
}

add_action('admin_init', 'fbt_register_settings');

function fbt_api_url_field() {
    $url = get_option('fbt_api_url', FBT_PLUGIN_API_URL);
    echo "<input type='text' name='fbt_api_url' value='$url' size='50'/>";
}

function fbt_enable_widget_field() {
    $enabled = get_option('fbt_enable_widget', 1);
    echo "<input type='checkbox' name='fbt_enable_widget' value='1'" . checked(1, $enabled, false) . "/>";
}

add_action('admin_notices', function () {
    $batch_url = get_option('skp-fbt_batch_url');
    $enabled   = get_option('skp-fbt_checkbox');

    echo '<div class="notice notice-info"><p>';
    echo 'Saved Batch URL: ' . esc_html($batch_url) . '<br>';
    echo 'Checkbox Enabled: ' . esc_html($enabled);
    echo '</p></div>';
});
