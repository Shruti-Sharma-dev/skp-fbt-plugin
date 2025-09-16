document.addEventListener("DOMContentLoaded", function () {
  console.log("FBT DOMContentLoaded fired ✅");

  console.log("Store Nonce:", window.skpFbtSettings?.nonce || "Not defined");

  const productId = window.SKP_FBT_PRODUCT_ID;
  console.log("Detected product ID:", productId);

  if (!productId) return;

  // Step 1: Get recommended IDs
  fetch(
    `https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/skp-fbt/v1/for-product/${productId}`
  )
    .then((res) => res.json())
    .then((data) => {
      console.log("FBT API Data:", data);

      if (!(data.success && data.recommendations.length > 0)) {
        console.warn("No recommendations available ❌");
        return;
      }

      const recIds = data.recommendations
        .slice(0, 3)
        .map((r) => r.rec_id);
      console.log("Recommendation IDs:", recIds);

      // Step 2: Fetch product details
      return fetch(
        `https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/wc/store/products?include=${recIds.join(",")}`
      );
    })
    .then((res) => res.json())
    .then((products) => {
      console.log("WooCommerce Product Data:", products);

      const container = document.getElementById("fbt-products");
      if (!container) {
        console.warn("FBT container not found ❌");
        return;
      }

      let subtotal = 0; // Track bundle subtotal

      products.forEach((p) => {
        console.log("Rendering product:", p.id, p.name, p.prices, p.images);

        const imgSrc =
          p.images && p.images.length > 0 ? p.images[0].src : "";
        const prices = p.prices || {};
        const currency = prices.currency_symbol || "₹";

        // Handle sale vs regular price
        const regularPrice = prices.regular_price
          ? `${currency}${(prices.regular_price / 100).toFixed(2)}`
          : "";
        const salePrice = prices.sale_price
          ? `${currency}${(prices.sale_price / 100).toFixed(2)}`
          : regularPrice;

        subtotal += prices.sale_price
          ? prices.sale_price / 100
          : prices.price / 100;

        const card = document.createElement("div");
        card.className = "fbt-card";

        card.innerHTML = `
          <label>
            <input type="checkbox" class="fbt-checkbox" data-id="${p.id}" data-price="${salePrice.replace(
          /[^\d.]/g,
          ""
        )}">
            <a href="${p.permalink}" class="fbt-link">
              <img src="${imgSrc}" alt="${p.name}" width="60">
              <span class="fbt-product-name">${p.name}</span>
            </a>
           <div class="price-box">
   ${p.sale_price 
       ? `<span class="regular"><s>₹${p.regular_price}</s></span>
          <span class="sale">₹${p.sale_price}</span>`
       : `<span class="regular">₹${p.regular_price}</span>`}
</div>

                    </div>
          </label>
        `;

        container.appendChild(card);
      });

      // ✅ Add to cart button
      const addBtn = document.createElement("button");
      addBtn.className = "add-selected-to-cart";
      addBtn.textContent = "Add Selected to Cart";

      addBtn.addEventListener("click", () => {
        const selected = [
          ...document.querySelectorAll(".fbt-checkbox:checked"),
        ].map((cb) => cb.dataset.id);
        console.log("Selected IDs:", selected);

        selected.forEach((id) => {
          fetch(
            `https://srikrishnanew-staging.us23.cdn-alpha.com?add-to-cart=${id}`,
            {
              method: "POST",
              credentials: "same-origin",
            }
          )
            .then((response) => {
              if (response.ok) {
                console.log(`Product ${id} added to cart ✅`);

                // Refresh mini-cart
                // ✅ Mini-cart refresh
                fetch(window.location.href, { credentials: "same-origin" })
                  .then(r => r.text())
                  .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, "text/html");

                    // Mini-cart content
                    const newMiniCart = doc.querySelector(".widget_shopping_cart_content");
                    const miniCart = document.querySelector(".widget_shopping_cart_content");
                    if (miniCart && newMiniCart) {
                      miniCart.innerHTML = newMiniCart.innerHTML;
                      console.log("Mini-cart refreshed ✅");
                    }

                    // Cart count (header badge)
                    const newCount = doc.querySelector(".cart-count"); // apne theme ka selector check karo
                    const cartCount = document.querySelector(".cart-count");
                    if (cartCount && newCount) {
                      cartCount.innerHTML = newCount.innerHTML;
                      console.log("Cart count updated ✅");
                    }
                  });

              } else {
                console.error(`Failed to add product ${id} ❌`);
              }
            })
            .catch((err) => console.error("Cart API error:", err));
        });
      });

      // ✅ Bundle total
      const bundleTotal = document.createElement("div");
      bundleTotal.className = "bundle-total";
      bundleTotal.innerHTML = `<span class="label">Bundle Subtotal:</span>
        <span class="value">₹${subtotal.toFixed(2)}</span>`;

      container.appendChild(bundleTotal);
      container.appendChild(addBtn);
    })
    .catch((err) => console.error("FBT Widget Error:", err));
});
