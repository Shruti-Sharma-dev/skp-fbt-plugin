<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SKP_FBT_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    public function menu() {
        add_menu_page(
            'SKP FBT Settings',
            'SKP FBT',
            'manage_options',
            'skp-fbt-settings',
            [$this, 'render'],
            'dashicons-products'
        );
    }
    public function register_settings() {
        register_setting('skp_fbt_group', 'skp_fbt_enabled');              // on/off
        register_setting('skp_fbt_group', 'skp_fbt_ab_percent');           // A/B %
        register_setting('skp_fbt_group', 'skp_fbt_price_band');           // ±% band
    }
    public function render() { ?>
        <div class="wrap">
            <h1>SKP FBT – Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('skp_fbt_group'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Enable recommendations</th>
                        <td><input type="checkbox" name="skp_fbt_enabled" value="1" <?php checked(1, get_option('skp_fbt_enabled')); ?> /></td>
                    </tr>
                    <tr>
                        <th scope="row">A/B cohort (%)</th>
                        <td><input type="number" name="skp_fbt_ab_percent" min="0" max="100" value="<?php echo esc_attr(get_option('skp_fbt_ab_percent', 50)); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Price band (±%)</th>
                        <td><input type="number" name="skp_fbt_price_band" min="0" max="100" value="<?php echo esc_attr(get_option('skp_fbt_price_band', 40)); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php }
}
new SKP_FBT_Settings();
