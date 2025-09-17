document.addEventListener("DOMContentLoaded", function () {
  console.log("FBT Events Loaded ✅");

  const sessionId = SKP_FBT_DATA.session_id;
  const userId = SKP_FBT_DATA.user_id;
  const cohort = SKP_FBT_DATA.cohort;

  console.log("Session ID:", sessionId);
  console.log("User ID:", userId);
  console.log("Cohort:", cohort);

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
      product_id: productId,
      rec_id: recId,
      order_id: orderId,
      cohort: cohort
    };

    waitForGtag(() => {
      gtag('event', eventType, {
        product_id: productId,
        rec_id: recId,
        order_id: orderId,
        session_id: sessionId,
        cohort: cohort
      });
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

  // ---------------------------
  // Init function with retry
  // ---------------------------
  function initFbtTracking() {
    const fbtProducts = document.querySelectorAll(".fbt-card");
    console.log("Checking FBT cards, found:", fbtProducts.length);

    if (fbtProducts.length > 0) {
      // Impression Event
      fbtProducts.forEach(card => {
        const recId = card.querySelector(".fbt-checkbox")?.dataset.id || null;
  
        console.log("Impression recId:", recId);
        sendEvent('impression', productId, null, recId);

        // Click Event
        const link = card.querySelector(".fbt-link");
        link?.addEventListener('click', () => {
          console.log("Click fired on recId:", recId);
          sendEvent('click', productId, null, recId);
        });
      });

      // Add to cart Event
      const addBtn = document.querySelector(".add-selected-to-cart");
      addBtn?.addEventListener('click', () => {
        console.log("Add selected to cart clicked!");
        const selected = [...document.querySelectorAll(".fbt-checkbox:checked")];
        selected.forEach(cb => {
          const recId = cb.dataset.id;
          const productId = cb.dataset.id;
          console.log("Sending Add to cart for:", recId);
          sendEvent('Add to cart', productId, recId);
        });
      });
    } else {
      console.log("⚠️ No FBT cards yet, retrying in 500ms...");
      setTimeout(initFbtTracking, 500);
    }
  }

  initFbtTracking();
});
