<?php
function skp_cyl_widget_test() {
    echo "<div style='color:red;font-weight:bold;'>CYL Widget Hook Fired!</div>";
}
add_action('woocommerce_cart_collaterals', 'skp_cyl_widget');


// Change the hook as needed>

