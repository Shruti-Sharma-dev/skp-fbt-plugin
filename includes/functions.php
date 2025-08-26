<?php
// Placeholder for fetching recommendations
function fbt_get_recommendations($product_id) {
    return [
        ['id' => 201, 'name' => 'Sample Product A'],
        ['id' => 202, 'name' => 'Sample Product B'],
    ];
}

// Rendering widget on product page
function fbt_render_product_widget() {
    global $product;
    $product_id = $product ? $product->get_id() : 0;

    echo "<div style='border:2px solid red; padding:10px; margin:10px 0;'>";
    echo "<strong>FBT Debug:</strong> Product ID = " . esc_html($product_id) . "<br>";
    echo "</div>";
}
add_action('woocommerce_after_add_to_cart_button', 'fbt_render_product_widget');
