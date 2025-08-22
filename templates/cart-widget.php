<?php
if (!defined('ABSPATH')) exit;

// $cart_product_id should be provided by the caller (e.g., product page or aggregated cart)
$cart_items = WC()->cart->get_cart();
$cart_ids = [];
foreach ($cart_items as $ci) {
    $cart_ids[] = intval($ci['product_id']);
}

// Example: show recs for the first product in cart or top product
$product_id = !empty($cart_ids) ? $cart_ids[0] : null;
if (!$product_id) {
    // nothing to show (resilience) -- render placeholder or nothing
    return;
}

$recs = skp_fbt_get_recs($product_id, $cart_ids, 3);
if (empty($recs)) return; // gracefully hide widget

?>
<div class="skp-fbt-widget" role="region" aria-label="Frequently bought together">
  <h3 class="skp-fbt-title">People also buy</h3>
  <ul class="skp-fbt-list" role="list">
    <?php foreach ($recs as $r): 
        $pid = intval($r['rec_id']);
        $prod = wc_get_product($pid);
        if (!$prod) continue;
    ?>
      <li role="listitem" tabindex="0" class="skp-fbt-item" data-rec="<?php echo esc_attr($pid); ?>">
        <a href="<?php echo esc_url(get_permalink($pid)); ?>" class="skp-fbt-link">
          <?php echo wp_kses_post($prod->get_image('thumbnail')); ?>
          <div class="skp-fbt-meta">
            <div class="skp-fbt-name"><?php echo esc_html($prod->get_name()); ?></div>
            <div class="skp-fbt-price"><?php echo wc_price($prod->get_price()); ?></div>
          </div>
        </a>
        <button class="skp-fbt-add" data-product="<?php echo esc_attr($pid); ?>">Add</button>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
