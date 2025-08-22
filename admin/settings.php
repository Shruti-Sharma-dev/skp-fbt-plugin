<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function skp_fbt_register_settings() {
    register_setting( 'skp_fbt_options', 'skp_fbt_enabled' );
    register_setting( 'skp_fbt_options', 'skp_fbt_limit' );
    register_setting( 'skp_fbt_options', 'skp_fbt_min_score' );
    register_setting( 'skp_fbt_options', 'skp_fbt_abtest' );
}
add_action( 'admin_init', 'skp_fbt_register_settings' );

function skp_fbt_settings_page() {
    ?>
    <div class="wrap">
        <h1>SKP FBT Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'skp_fbt_options' ); ?>
            <?php do_settings_sections( 'skp_fbt_options' ); ?>

            <table class="form
                <tr valign="top">
                    <th scope="row">Enable Widget</th>
                    <td><input type="checkbox" name="skp_fbt_enabled"
                        value="1" <?php checked( get_option('skp_fbt_enabled'), 1 ); ?> /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Number of Recommendations</th>
                    <td><input type="number" name="skp_fbt_limit"
                        value="<?php echo esc_attr( get_option('skp_fbt_limit', 3) ); ?>" min="1" max="10" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Minimum Score Threshold</th>
                    <td><input type="number" step="0.1" name="skp_fbt_min_score"
                        value="<?php echo esc_attr( get_option('skp_fbt_min_score', 0.5) ); ?>" min="0" max="1" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Enable A/B Test</th>
                    <td><input type="checkbox" name="skp_fbt_abtest"
                        value="1" <?php checked( get_option('skp_fbt_abtest'), 1 ); ?> /></td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
