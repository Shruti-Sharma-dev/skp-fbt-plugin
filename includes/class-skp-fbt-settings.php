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
    register_setting('skp_fbt_group', 'skp_fbt_enabled', ['type' => 'boolean']);
    register_setting('skp_fbt_group', 'skp_fbt_ab_percent', [
        'type' => 'integer',
        'sanitize_callback' => function($val){ return max(0, min(100, intval($val))); }
    ]);
    register_setting('skp_fbt_group', 'skp_fbt_price_band', [
        'type' => 'integer',
        'sanitize_callback' => function($val){ return max(0, min(100, intval($val))); }
    ]);
    register_setting('skp_fbt_group', 'skp_fbt_limit', [
        'type' => 'integer',
        'default' => 3,
        'sanitize_callback' => function($val){ return max(1, min(10, intval($val))); }
    ]);
    register_setting('skp_fbt_group', 'skp_fbt_min_score', [
        'type' => 'number',
        'default' => 0.5,
        'sanitize_callback' => function($val){ return max(0, min(1, floatval($val))); }
    ]);
}

public function render() { ?>
    <div class="wrap">
        <h1>SKP FBT – Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('skp_fbt_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">Enable recommendations</th>
                    <td>
                        <input type="checkbox" name="skp_fbt_enabled" value="1"
                            <?php checked(1, get_option('skp_fbt_enabled')); ?> />
                        <p class="description">Toggle FBT widget on/off</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">A/B cohort (%)</th>
                    <td>
                        <input type="number" name="skp_fbt_ab_percent" min="0" max="100"
                            value="<?php echo esc_attr(get_option('skp_fbt_ab_percent', 50)); ?>" />
                        <p class="description">Percentage of users bucketed into experiment</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Price band (±%)</th>
                    <td>
                        <input type="number" name="skp_fbt_price_band" min="0" max="100"
                            value="<?php echo esc_attr(get_option('skp_fbt_price_band', 40)); ?>" />
                        <p class="description">Only recommend items within ±% of anchor product price</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Recommendation limit</th>
                    <td>
                        <input type="number" name="skp_fbt_limit" min="1" max="10"
                            value="<?php echo esc_attr(get_option('skp_fbt_limit', 3)); ?>" />
                        <p class="description">Max number of products to display</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Minimum score</th>
                    <td>
                        <input type="number" step="0.1" name="skp_fbt_min_score" min="0" max="1"
                            value="<?php echo esc_attr(get_option('skp_fbt_min_score', 0.5)); ?>" />
                        <p class="description">Only recommend if co-occurrence score ≥ this value</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php }

}
new SKP_FBT_Settings();
