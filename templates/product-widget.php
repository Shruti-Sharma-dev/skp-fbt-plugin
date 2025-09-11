<?php
function render_fbt_widget() {
    global $product;
    if (!$product) return;

    $product_id = $product->get_id(); // correct product ID
    ?>
    <div id="skp-fbt-widget" class="fbt-widget">
        <h3>Frequently Bought Together</h3>
        <div id="fbt-products"></div>
    </div>
<script>
    if (!window.SKP_FBT_PRODUCT_ID) {
        window.SKP_FBT_PRODUCT_ID = <?php echo intval($product_id); ?>;
        console.log('FBT Product ID set in JS:', window.SKP_FBT_PRODUCT_ID);
    }
</script>

    <?php
}
add_action('woocommerce_after_add_to_cart_button', 'render_fbt_widget');
