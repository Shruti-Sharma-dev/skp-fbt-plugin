// assets/js/fbt-widget.js
(function(){
  if (typeof SKP_FBT === 'undefined') return;
  const base = SKP_FBT.restBase;
  const nonce = SKP_FBT.metricsNonce;

  function postEvent(payload){
    payload.nonce = nonce;
    fetch(base + '/metrics', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    }).catch(()=>{ /* fail gracefully */ });
  }

  // On widget view
  document.querySelectorAll('.skp-fbt-widget').forEach(function(widget){
    const productEl = widget.querySelector('.skp-fbt-item');
    const sessionId = window.SKPFbtSessionId || (window.SKPFbtSessionId = Math.random().toString(36).slice(2,12));
    postEvent({ event: 'widget_view', session_id: sessionId, product_id: productEl ? productEl.dataset.rec : null, cohort: null });

    // track clicks on rec links
    widget.addEventListener('click', function(e){
      const t = e.target.closest('.skp-fbt-item');
      if (!t) return;
      const rec = t.dataset.rec;
      postEvent({ event: 'rec_click', session_id: sessionId, rec_id: rec });
    });

    // track add button
    widget.addEventListener('click', function(e){
      const btn = e.target.closest('.skp-fbt-add');
      if (!btn) return;
      const pid = btn.dataset.product;
      // Here call standard WC add-to-cart or use AJAX; after that:
      postEvent({ event: 'rec_add_to_cart', session_id: sessionId, rec_id: pid });
    });
  });
})();
