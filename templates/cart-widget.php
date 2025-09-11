<?php
function render_fbt_widget_cart_page_simple() {
    $cart_items = WC()->cart->get_cart();
    if (empty($cart_items)) return;

    $product_ids = [];
    foreach ($cart_items as $cart_item) {
        $product_ids[] = $cart_item['product_id'];
    }
    ?>
    <div id="skp-cyl-widget" class="cyl-widget">
        <h3>Complete Your Look</h3>
        <div id="cyl-products"></div>
    </div>
    <script>
        window.SKP_FBT_CART_PRODUCT_IDS = <?php echo json_encode($product_ids); ?>;
    </script>
    <?php
}
add_action('woocommerce_cart_collaterals', 'render_fbt_widget_cart_page_simple');

