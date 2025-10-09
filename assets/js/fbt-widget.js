function formatPrice(num) {
  return num.toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.addEventListener("DOMContentLoaded", function () {
  console.log("FBT DOMContentLoaded fired ✅");

  const productId = window.SKP_FBT_PRODUCT_ID;
  console.log("Detected product ID:", productId);

  const container = document.getElementById("fbt-products");
  if (!container) return;

  container.innerHTML = `<div class="fbt-loader">Loading recommendations...</div>`;
  container.style.display = "block";

  if (!productId) {
    container.style.display = "none";
    return;
  }

  fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/skp-fbt/v1/for-product/${productId}`)
    .then(res => res.json())
    .then(data => {
      if (!(data.success && data.recommendations.length > 0)) {
        container.style.display = "none";
        return;
      }

      const recIds = data.recommendations.slice(0, 3).map(r => r.rec_id);
      return fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/wc/store/products?include=${recIds.join(",")}`);
    })
    .then(res => res.json())
    .then(products => {
      if (!products || products.length === 0) {
        container.style.display = "none";
        return;
      }

      container.innerHTML = "";

      const bundleTotal = document.createElement("div");
      bundleTotal.className = "bundle-total";
      bundleTotal.style.display = "none"; // hide initially
      bundleTotal.innerHTML = `<span class="label">Bundle Subtotal:</span>
        <span class="value">₹0.00</span>`;
      const subtotalEl = bundleTotal.querySelector(".value");

      const addBtn = document.createElement("button");
      addBtn.className = "add-selected-to-cart";
      addBtn.textContent = "Add Selected to Cart";
      addBtn.disabled = true;

      function updateSubtotal() {
        const selected = [...document.querySelectorAll(".fbt-checkbox:checked")];
        if (selected.length > 0) {
          const subtotal = selected.reduce((sum, c) => sum + parseFloat(c.dataset.price || 0), 0);
          subtotalEl.textContent = `₹${formatPrice(subtotal)}`;
          bundleTotal.style.display = "block"; // show when selected
          addBtn.disabled = false;
        } else {
          subtotalEl.textContent = `₹0.00`;
          bundleTotal.style.display = "none"; // hide when nothing selected
          addBtn.disabled = true;
        }
      }

      products.forEach(p => {
        const imgSrc = p.images?.[0]?.src || "";
        const prices = p.prices || {};
        const currency = prices.currency_symbol || "₹";

        const price = prices.sale_price ? prices.sale_price / 100 : prices.price / 100;

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
                ? `<span class="regular"><s>${currency}${formatPrice(prices.regular_price/100)}</s></span>
                   <span class="sale">${currency}${formatPrice(prices.sale_price/100)}</span>`
                : `<span class="regular">${currency}${formatPrice(prices.price/100)}</span>`}
            </div>
          </label>
        `;

        container.appendChild(card);
        card.querySelector(".fbt-checkbox").addEventListener("change", updateSubtotal);
      });

      addBtn.addEventListener("click", () => {
        const selected = [...document.querySelectorAll(".fbt-checkbox:checked")].map(cb => cb.dataset.id);
        selected.forEach(id => {
          fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com?add-to-cart=${id}`, {
            method: "POST",
            credentials: "same-origin",
          });
        });


          // Optional: Reload cart widget / mini-cart if exists
  setTimeout(() => {
    location.reload(); // reload page to reflect cart changes
  }, 500);
      });

      container.appendChild(bundleTotal);
      container.appendChild(addBtn);
    })
    .catch(err => {
      console.error("FBT Widget Error:", err);
      container.style.display = "none";
    });
});
