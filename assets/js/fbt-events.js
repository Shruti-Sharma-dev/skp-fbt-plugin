document.addEventListener("DOMContentLoaded", function () {
    // Check if nonce is available
    if (!window.SKP_FBT_DATA || !SKP_FBT_DATA.nonce) {
        console.warn("FBT nonce not defined. Events will not be tracked.");
        return;
    }

    console.log("FBT DOMContentLoaded fired ✅");
    console.log("FBT nonce:", SKP_FBT_DATA.nonce);

    // Function to send FBT events
    function sendFBTEvent(eventType, productId = null, recId = null) {
        const payload = {
            event: eventType,
            product_id: productId, // optional
            rec_id: recId           // optional
        };

        fetch("https://srikrishnanew-staging.us23.cdn-alpha.com/wp-json/skp-fbt/v1/metrics", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": SKP_FBT_DATA.nonce
            },
            body: JSON.stringify(payload)
        })
        .then(res => {
            if (!res.ok) throw new Error("Network response not ok: " + res.status);
            return res.json();
        })
        .then(data => console.log("FBT event stored:", data))
        .catch(err => console.error("Error storing FBT event:", err));
    }

    // Example: send "fbt_impression" on page load
    sendFBTEvent("fbt_impression");

    // Later you can call sendFBTEvent("fbt_click", 123, 456) for clicks
});
