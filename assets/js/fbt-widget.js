document.addEventListener("DOMContentLoaded", function () {
    console.log("FBT DOMContentLoaded fired ✅");

    const productId = window.SKP_FBT_PRODUCT_ID;
    console.log("Detected product ID:", productId);

    if (!productId) return;

    // Step 1: Get recommended IDs from custom API
    fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/skp-fbt/v1/for-product/${productId}`)
        .then(res => res.json())
        .then(data => {
            console.log("FBT API Data:", data);

            if (!(data.success && data.recommendations.length > 0)) {
                console.warn("No recommendations available ❌");
                return;
            }

            const recIds = data.recommendations.slice(0, 3).map(r => r.rec_id);
            console.log("Recommendation IDs:", recIds);

            // Step 2: Fetch product details from Woo API
            return fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/wc/store/products?include=${recIds.join(",")}`);
        })
        .then(res => res.json())
        .then(products => {
            console.log("WooCommerce Product Data:", products);

            const container = document.getElementById("fbt-products");
            if (!container) {
                console.warn("FBT container not found ❌");
                return;
            }

products.forEach(p => {
  console.log("Rendering product:", p.id, p.name, p.prices, p.images);

  const imgSrc = (p.images && p.images.length > 0) ? p.images[0].src : '';
  const displayPrice = p.prices?.price ? `${p.prices.price} ${p.prices.currency_code}` : '';

  const card = document.createElement("div");
  card.className = "fbt-card";

  card.innerHTML = `
      <label>
          <input type="checkbox" class="fbt-checkbox" data-id="${p.id}">
          <img src="${imgSrc}" alt="${p.name}" width="60">
          <span>${p.name}</span> - <strong>${displayPrice}</strong>
      </label>
  `;

  container.appendChild(card);
});

// ✅ Add "Add Selected to Cart" button once
const addBtn = document.createElement("button");
addBtn.textContent = "Add Selected to Cart";
addBtn.addEventListener("click", () => {
  const selected = [...container.querySelectorAll(".fbt-checkbox:checked")].map(cb => cb.dataset.id);
  console.log("Selected IDs:", selected);

  selected.forEach(id => {
    fetch("https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/wc/store/cart/items", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: parseInt(id), quantity: 1 })
    })
    .then(r => r.json())
    .then(r => console.log("Cart add response:", r))
    .catch(err => console.error("Cart API error:", err));
  });
});
container.appendChild(addBtn);

        })
        .catch(err => console.error("FBT Widget Error:", err));
});
