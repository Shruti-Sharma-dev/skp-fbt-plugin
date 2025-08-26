<?php
function skp_fbt_product_ui_widget() {
    // Static example products
    $recommended_products = array(
        array(
            'id' => 101,
            'name' => 'Product 101',
            'price' => '$29.99',
            'image' => 'https://via.placeholder.com/150'
        ),
        array(
            'id' => 102,
            'name' => 'Product 102',
            'price' => '$19.99',
            'image' => 'https://via.placeholder.com/150'
        ),
        array(
            'id' => 103,
            'name' => 'Product 103',
            'price' => '$39.99',
            'image' => 'https://via.placeholder.com/150'
        )
    );

    echo '<div class="fbt-widget">';
    echo '<h3>Frequently Bought Together</h3>';
    echo '<div class="fbt-products">';
    
    foreach($recommended_products as $prod) {
        echo '<div class="fbt-product">';
        echo '<img src="'.$prod['image'].'" alt="'.$prod['name'].'" />';
        echo '<p>'.$prod['name'].'</p>';
        echo '<p>Price: '.$prod['price'].'</p>';
        echo '<button>Add to Cart</button>';
        echo '</div>';
    }
    
    echo '</div>'; // .fbt-products
    echo '</div>'; // .fbt-widget
}

// Hook it to product page for now
add_action('woocommerce_after_add_to_cart_button', 'skp_fbt_product_ui_widget');
