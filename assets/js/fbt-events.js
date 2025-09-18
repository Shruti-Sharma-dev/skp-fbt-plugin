document.addEventListener("DOMContentLoaded", function () {
  console.log("FBT Events Loaded ✅");

  const sessionId = SKP_FBT_DATA.session_id;
  const userId = SKP_FBT_DATA.user_id;
  const cohort = SKP_FBT_DATA.cohort;
  const productId = SKP_FBT_DATA?.product_id || window.SKP_FBT_PRODUCT_ID || null;

  console.log("Session ID:", sessionId);
  console.log("User ID:", userId);
  console.log("Cohort:", cohort);
  console.log("Product ID:", productId);

  if (!productId) {
    console.warn("No product ID found, aborting FBT event tracking ❌");
    return;
  }

  function waitForGtag(cb) {
    if (typeof gtag === 'function') {
      console.log("✅ gtag found");
      cb();
    } else {
      setTimeout(() => waitForGtag(cb), 100);
    }
  }

  function sendEvent(eventType, productId = null, recId = null, orderId = null) {
    const payload = {
      event: eventType,
      session_id: sessionId,
      user_id: userId,
      product_id: productId, // PDP product id
      rec_id: recId,         // recommended product id
      order_id: orderId,     // actual Woo order id (yahan abhi null)
      cohort: cohort
    };

    waitForGtag(() => {
      gtag('event', eventType, payload);
      console.log("GA4 Event Fired ✅", eventType, payload);
    });

    console.log("Sending fetch with payload:", payload);

    fetch('https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/skp-fbt/v1/metrics', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
      credentials: 'same-origin'
    })
      .then(res => res.json())
      .then(data => console.log("FBT Metric Saved ✅", data))
      .catch(err => console.error("FBT Metric Error ❌", err));
  }


  
  function initFbtTracking() {
    const fbtProducts = document.querySelectorAll(".fbt-card");
    console.log("Checking FBT cards, found:", fbtProducts.length);

    if (fbtProducts.length > 0) {
      // Impression Event
      fbtProducts.forEach(card => {
        const recId = card.querySelector(".fbt-checkbox")?.dataset.id || null;
        console.log("Impression recId:", recId);
        sendEvent('impression', productId, recId, null);

        // Click Event
        const link = card.querySelector(".fbt-link");
        link?.addEventListener('click', () => {
          console.log("Click fired on recId:", recId);
          sendEvent('click', productId, recId, null);
        });
      });

      // Add to cart Event
      const addBtn = document.querySelector(".add-selected-to-cart");
      addBtn?.addEventListener('click', () => {
        console.log("Add selected to cart clicked!");
        const selected = [...document.querySelectorAll(".fbt-checkbox:checked")];
        selected.forEach(cb => {
          const recId = cb.dataset.id;
          console.log("Sending Add to cart for:", recId);
          sendEvent('Add to cart', productId, recId, null);
        });
      });
    } else {
      console.log("⚠️ No FBT cards yet, retrying in 500ms...");
      setTimeout(initFbtTracking, 500);
    }
  }

  initFbtTracking();
});
