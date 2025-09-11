document.addEventListener("DOMContentLoaded", function () {
    console.log("FBT DOMContentLoaded fired ✅");
    console.log("FBT DOMContentLoaded fired 2nd time ✅");
    console.log("Store Nonce:", window.wcSettings?.storeApiNonce);


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
        .then(res => 
          
          res.json())
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
    <a href="${p.permalink}" class="fbt-link">
      <img src="${imgSrc}" alt="${p.name}" width="60">
      <span>${p.name}</span> - <strong>${displayPrice}</strong>
    </a>
  </label>
`;


  container.appendChild(card);
});

const addBtn = document.createElement("button");
addBtn.className="add-selected-to-cart";
addBtn.textContent = "Add Selected to Cart";

addBtn.addEventListener("click", () => {
  const selected = [...document.querySelectorAll(".fbt-checkbox:checked")].map(cb => cb.dataset.id);
  console.log("Selected IDs:", selected);

  selected.forEach(id => {

    fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com?add-to-cart=${id}`, {
      method: "POST",
      credentials: "same-origin" // cookies/session carry karne ke liye
    })
    .then(response => {
      if (response.ok) {
        console.log(`Product ${id} added to cart ✅`);
        console.log(`Product ${id} added to cart ✅`);

        // ✅ Mini cart refresh (vanilla alternative)
        // WooCommerce AJAX fragments ko reload karna
        fetch(window.location.href, { credentials: "same-origin" })
          .then(r => r.text())
          .then(html => {
            // Mini-cart container find karo
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newMiniCart = doc.querySelector(".widget_shopping_cart_content");

            const miniCart = document.querySelector(".widget_shopping_cart_content");
            if (miniCart && newMiniCart) {
              miniCart.innerHTML = newMiniCart.innerHTML;
              console.log("Mini-cart refreshed ✅");
            }
          });
      } else {
        console.error(`Failed to add product ${id} ❌`);
      }
    })
    .catch(err => console.error("Cart API error:", err));
  });
});

document.getElementById("fbt-products").appendChild(addBtn);


        })
        .catch(err => console.error("FBT Widget Error:", err));
});
