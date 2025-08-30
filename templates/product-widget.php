<?php

add_action('woocommerce_after_add_to_cart_button', 'render_fbt_widget');

function render_fbt_widget() {
    global $product;

    if (!$product) return;

    $main_id = $product->get_id();
    $main_price = $product->get_price();
    
 
    $related_ids = array(37, 45, 52); // dynamic id // will be fetching later

    if (empty($related_ids)) return;

    echo '<div id="fbt-widget" data-main-id="' . esc_attr($main_id) . '" data-main-price="' . esc_attr($main_price) . '">';
    echo '<h3>Frequently Bought Together</h3>';
    echo '<ul>';

    foreach ($related_ids as $rid) {
        $r_product = wc_get_product($rid);
        if (!$r_product) continue;

        echo '<li>
            <label>
                <input type="checkbox" class="fbt-checkbox" data-id="' . esc_attr($rid) . '" data-price="' . esc_attr($r_product->get_price()) . '">
                ' . esc_html($r_product->get_name()) . ' - ' . wc_price($r_product->get_price()) . '
            </label>
        </li>';
    }

    echo '</ul>';
    echo '<div id="fbt-subtotal" style="display:none;">
            <p><strong>Bundle Subtotal:</strong> <span id="fbt-total"></span></p>
            <button type="button" id="fbt-add-to-cart" class="button alt">Add Bundle to Cart</button>
          </div>';
    echo '</div>';
}

