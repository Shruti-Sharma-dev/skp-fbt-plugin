document.addEventListener("DOMContentLoaded", function () {
    console.log('JS loaded');
    console.log(SKP_FBT_ADMIN.nonce);
    const btn = document.getElementById("skp-fbt-rebuild_now");
    if (!btn) return;

    btn.addEventListener("click", function () {
        btn.disabled = true;
        btn.textContent = "Running...";

        fetch(SKP_FBT_ADMIN.ajax_url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "skp_fbt_rebuild",  // PHP side me is naam ka handler banana
                nonce: SKP_FBT_ADMIN.nonce
            })
        })
            .then((res) => res.json())
            .then((data) => {
                console.log("Rebuild response:", data);
                alert(data.message || "Done");

                // page refresh ke bina status update karo
                const statusEl = document.getElementById("skp-fbt-last-run");
                if (statusEl && data.last_run) {
                    statusEl.textContent = data.last_run;
                }
            })
            .catch((err) => {
                console.error(err);
                alert("Error triggering rebuild");
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = "Rebuild Now";
            });
    });
});
