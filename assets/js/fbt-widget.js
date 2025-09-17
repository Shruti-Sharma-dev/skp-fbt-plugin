document.addEventListener("DOMContentLoaded", function () {
  console.log("FBT DOMContentLoaded fired ✅");

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

      const recIds = data.recommendations.slice(0, 3).map((r) => r.rec_id);
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

      // ✅ Bundle total container
      const bundleTotal = document.createElement("div");
      bundleTotal.className = "bundle-total";
      bundleTotal.innerHTML = `<span class="label">Bundle Subtotal:</span>
        <span class="value">₹0.00</span>`;
      const subtotalEl = bundleTotal.querySelector(".value");

      // ✅ Add to cart button
      const addBtn = document.createElement("button");
      addBtn.className = "add-selected-to-cart";
      addBtn.textContent = "Add Selected to Cart";
      addBtn.disabled = true; // start disabled

      // ✅ Update subtotal function
      function updateSubtotal() {
        const selected = [
          ...document.querySelectorAll(".fbt-checkbox:checked"),
        ];
        if (selected.length > 0) {
          const subtotal = selected.reduce(
            (sum, c) => sum + parseFloat(c.dataset.price || 0),
            0
          );
          subtotalEl.textContent = `₹${subtotal.toFixed(2)}`;
          addBtn.disabled = false;
        } else {
          subtotalEl.textContent = `₹0.00`;
          addBtn.disabled = true;
        }
      }

      // ✅ Render products
      products.forEach((p) => {
        const imgSrc = p.images?.[0]?.src || "";
        const prices = p.prices || {};
        const currency = prices.currency_symbol || "₹";

        const price = prices.sale_price
          ? prices.sale_price / 100
          : prices.price / 100;

        const card = document.createElement("div");
        card.className = "fbt-card";

        card.innerHTML = `
          <label>
            <input type="checkbox" class="fbt-checkbox" data-id="${p.id}" data-price="${price}">
            <a href="${p.permalink}" class="fbt-link">
              <img src="${imgSrc}" alt="${p.name}" width="60">
              <span class="fbt-product-name">${p.name}</span>
            </a>
            <div class="price-box">
              ${prices.sale_price 
                ? `<span class="regular"><s>${currency}${(prices.regular_price/100).toFixed(2)}</s></span>
                   <span class="sale">${currency}${(prices.sale_price/100).toFixed(2)}</span>`
                : `<span class="regular">${currency}${(prices.price/100).toFixed(2)}</span>`}
            </div>
          </label>
        `;

        container.appendChild(card);

        // ✅ Checkbox listener
        card.querySelector(".fbt-checkbox").addEventListener("change", updateSubtotal);
      });

      // ✅ Add to cart logic
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
              } else {
                console.error(`Failed to add product ${id} ❌`);
              }
            })
            .catch((err) => console.error("Cart API error:", err));
        });
      });

      container.appendChild(bundleTotal);
      container.appendChild(addBtn);
    })
    .catch((err) => console.error("FBT Widget Error:", err));
});
