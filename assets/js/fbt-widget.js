document.addEventListener("DOMContentLoaded", function () {
    const widget = document.getElementById("fbt-widget");
    if (!widget) return;

    const mainPrice = parseFloat(widget.dataset.mainPrice);
    const mainId = widget.dataset.mainId;
    const checkboxes = widget.querySelectorAll(".fbt-checkbox");
    const subtotalDiv = document.getElementById("fbt-subtotal");
    const totalSpan = document.getElementById("fbt-total");
    const addBtn = document.getElementById("fbt-add-to-cart");

    function updateSubtotal() {
        let subtotal = mainPrice;
        let selected = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                subtotal += parseFloat(cb.dataset.price);
                selected++;
            }
        });

        if (selected > 0) {
            subtotalDiv.style.display = "block";
            totalSpan.textContent = "₹" + subtotal.toFixed(2);
        } else {
            subtotalDiv.style.display = "none";
        }
    }

    checkboxes.forEach(cb => cb.addEventListener("change", updateSubtotal));

    addBtn.addEventListener("click", function () {
        let productIds = [mainId];
        checkboxes.forEach(cb => {
            if (cb.checked) productIds.push(cb.dataset.id);
        });

        fetch(ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=fbt_add_to_cart&products[]=" + productIds.join("&products[]=")
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.data.cart_url; // redirect to cart
            }
        });
    });
});
