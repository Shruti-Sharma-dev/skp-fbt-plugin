document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM loaded helllllllo✅");

    // 1️⃣ Get cart product IDs
    const cartProductIds = window.SKP_FBT_CART_PRODUCT_IDS || [];
    console.log("Cart Products:", cartProductIds);

    const container = document.getElementById("cyl-products");
    console.log("Container found:", !!container);

    if (!container || !cartProductIds.length) {
        console.log("No container or no cart products, stopping script.");
        return;
    }


    Promise.all(
        cartProductIds.map(id =>
            fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/skp-fbt/v1/for-product/${id}`, {
                headers: { 'X-WP-Nonce': window.wpApiSettings.nonce }
            })
            .then(res => res.json())
            .then(data => {
                console.log(`API response for product ${id}:`, data);
                return data;
            })
            .catch(err => {
                console.error(`Error fetching product ${id}:`, err);
                return { recommendations: [] }; // fallback empty
            })
        )
    ).then(results => {
        console.log("All API results:", results);

       
        const allRecIds = results.flatMap(r =>
            r.recommendations.map(rec => rec.rec_id)
        );

  
        const uniqueRecIds = [...new Set(allRecIds)].filter(id => !cartProductIds.includes(id));
        console.log("Unique recommendation IDs:", uniqueRecIds);

        if (!uniqueRecIds.length) {
            container.innerHTML = "<p>No recommendations found</p>";
            return;
        }

  
        fetch(`https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/wc/v3/products?include=${uniqueRecIds.join(",")}`, {
            headers: { 'X-WP-Nonce': window.wpApiSettings.nonce }
        })
        .then(res => res.json())
        .then(products => {
            console.log("Fetched product details:", products);

           container.innerHTML = products.map(p => `
    <div class="skp-cyl-item">
    <input type="checkbox" class="fbt-checkbox" data-id="${p.id}">
        <a href="${p.permalink}" class="fbt-link">
            <img src="${p.images?.[0]?.src}" width="50" height="50" alt="${p.name}">
            <span class="fbt-product-name">${p.name}</span>
        </a>
        <span class="fbt-price">${p.price_html}</span>
      
    </div>
`).join("");
            
        })
        .catch(err => console.error("Error fetching product details:", err));
    });
});
