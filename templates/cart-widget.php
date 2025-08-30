<?php


add_action('woocommerce_cart_collaterals', function() {
    echo '<div id="static-cart-widget" style="border:1px solid #ddd;padding:15px;margin-top:20px;">';
    echo '<h3>Complete Your Look (Static UI Test)</h3>';
    echo '<ul>';

    // Hardcoded cart items
    $dummy_cart = [
        ['id' => 201, 'name' => 'Sample Item A', 'qty' => 1, 'price' => '$15'],
        ['id' => 202, 'name' => 'Sample Item B', 'qty' => 2, 'price' => '$25'],
    ];

    foreach ($dummy_cart as $item) {
        echo '<li>' 
            . esc_html($item['name']) . 
            ' x ' . intval($item['qty']) . 
            ' — ' . esc_html($item['price']) . 
        '</li>';
    }

    echo '</ul>';
    echo '<p><strong>Total: $65 (static)</strong></p>';
    echo '<button type="button" disabled style="background:#000;color:#fff;padding:8px 12px;border-radius:4px;">Checkout</button>';
    echo '<p><em>(Static hardcoded cart UI, no real WooCommerce data)</em></p>';
    echo '</div>';
});
