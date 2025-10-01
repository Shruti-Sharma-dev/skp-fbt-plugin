document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM loaded in cart widget.js ✅");

    const cartProductIds = window.SKP_CYL_PRODUCT_IDS || [];
    const container = document.getElementById("skp-cyl-widget");
    if (!container || !cartProductIds.length) return;

    Promise.all(
        cartProductIds.map(id =>
            fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/skp-fbt/v1/for-product/${id}`, {
                headers: { 'X-WP-Nonce': window.wpApiSettings.nonce }
            })
            .then(res => res.json())
            .catch(() => ({ recommendations: [] }))
        )
    ).then(results => {
        const allRecIds = results.flatMap(r => r.recommendations.map(rec => rec.rec_id));
        const uniqueRecIds = [...new Set(allRecIds)].filter(id => !cartProductIds.includes(id));
        if (!uniqueRecIds.length) return container.innerHTML = "<p>No recommendations found</p>";

        fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/wc/v3/products?include=${uniqueRecIds.join(",")}`, {
            headers: { 'X-WP-Nonce': window.wpApiSettings.nonce }
        })
        .then(res => res.json())
      .then(products => {
    // Filter only available products & limit to 3
    const availableProducts = products.filter(p => p && p.stock_status === "instock");
    const top3 = availableProducts.slice(0, 3);

    if (!top3.length) return container.innerHTML = "<p>No recommendations available</p>";
            container.innerHTML += `
             
                ${top3.map(p => `
                    <div class="skp-cyl-item">
                        <input type="checkbox" class="cyl-checkbox" data-id="${p.id}" data-price="${p.price}">
                        <a href="${p.permalink}" class="fbt-link">
                            <img src="${p.images?.[0]?.src}" width="50" height="50" alt="${p.name}">
                            <span class="fbt-product-name">${p.name}</span>
                        </a>
                     <div class="price-box">
   ${p.sale_price 
       ? `<span class="regular"><s>₹${p.regular_price}</s></span>
          <span class="sale">₹${p.sale_price}</span>`
       : `<span class="regular">₹${p.regular_price}</span>`}
        </div>

                    </div>
                `).join("")}
                <div id="bundle-subtotal"></div>
                <button id="add-selected-to-cart" disabled>Add Selected to Cart</button>
            `;

            const checkboxes = Array.from(document.querySelectorAll(".cyl-checkbox"));
            const subtotalEl = document.getElementById("bundle-subtotal");
            const addBtn = document.getElementById("add-selected-to-cart");

            // Update subtotal on checkbox change
            checkboxes.forEach(cb => {
                cb.addEventListener("change", () => {
                    const selected = checkboxes.filter(c => c.checked);
                    if (selected.length >= 1) {
                        const subtotal = selected.reduce((sum, c) => sum + parseFloat(c.dataset.price || 0), 0);
                        subtotalEl.innerHTML = `
                   <span class="label">Bundle Subtotal:</span>
                   <span class="value">₹${subtotal.toFixed(2)}</span>
                     `;

                        addBtn.disabled = false;
                    } else {
                        subtotalEl.textContent = "";
                        addBtn.disabled = true;
                    }
                });
            });

            // Add selected products to cart
            addBtn.addEventListener("click", () => {
                const selected = checkboxes.filter(c => c.checked);
                selected.forEach(c => {
                    const productId = parseInt(c.dataset.id);
                    fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com?add-to-cart=${productId}`, {
                        method: "POST",
                        credentials: "same-origin"
                    })
                    .then(response => {
                        if (response.ok) {
                            console.log(`Product ${productId} added ✅`);
                            // Mini-cart refresh
                            fetch(window.location.href, { credentials: "same-origin" })
                                .then(r => r.text())
                                .then(html => {
                                    const parser = new DOMParser();
                                    const doc = parser.parseFromString(html, "text/html");
                                    const newMiniCart = doc.querySelector(".widget_shopping_cart_content");
                                    const miniCart = document.querySelector(".widget_shopping_cart_content");
                                    if (miniCart && newMiniCart) miniCart.innerHTML = newMiniCart.innerHTML;
                                });
                        } else console.error(`Failed to add ${productId} ❌`);
                    })
                    .catch(err => console.error("Cart API error:", err));
                });
                alert(`Added ${selected.length} products to cart!`);
                    // ✅ Reload the page to update cart totals
    setTimeout(() => {
        window.location.reload();
    }, 500); // small delay to ensure all products are added
            });
        });
    });
});
