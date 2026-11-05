
<?php
session_start();

$pageClass = "cart-page";

// Keep your existing website header and navigation.
include('./admin/includes/header.php');
include('./admin/includes/topbar.php');
?>

<link rel="stylesheet" href="assets/css/cart_page.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container cart-layout my-5">

    <h2 class="text-center mb-5">My Shopping Cart</h2>

    <div id="cartLoading" class="text-center py-5">
        Loading your cart...
    </div>

    <div id="cartError"
         class="alert alert-danger"
         style="display:none;">
    </div>

    <div id="cartContent" class="row" style="display:none;">

        <!-- =====================================
             LEFT SIDE: CART PRODUCTS
        ====================================== -->

        <div class="col-lg-8">

            <div id="cartItems"></div>

            <div id="emptyCart"
                 class="text-center py-5"
                 style="display:none;">

                <h4>Your cart is empty!</h4>

                <p>Looks like you haven't added any pastries yet.</p>

                <a href="order.php" class="btn btn-dark mt-3">
                    Continue Shopping
                </a>

            </div>

        </div>


        <!-- =====================================
             RIGHT SIDE: ORDER SUMMARY + CHECKOUT
        ====================================== -->

        <div class="col-lg-4">

            <div class="card shadow-sm rounded-4 p-4">

                <h4 class="mb-4">Order Summary</h4>

                <div class="d-flex justify-content-between mb-3">
                    <span>Total Items</span>
                    <span id="selectedCount">0</span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span>Subtotal</span>
                    <span>₱<span id="subtotal">0.00</span></span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span>VAT (12%)</span>
                    <span>₱<span id="tax">0.00</span></span>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-4">
                    <h5>Total</h5>
                    <h5>₱<span id="grandTotal">0.00</span></h5>
                </div>


                <!-- CHECKOUT FORM -->

                <form id="checkoutForm">

                    <h5 class="mb-3">Delivery Information</h5>

                    <label for="checkoutAddress" class="form-label">
                        Delivery Address
                    </label>

                    <textarea
                        name="address"
                        id="checkoutAddress"
                        class="form-control mb-3"
                        rows="3"
                        placeholder="Enter your complete delivery address"
                        required
                    ></textarea>


                    <label for="checkoutPhone" class="form-label">
                        Phone Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        id="checkoutPhone"
                        class="form-control mb-3"
                        placeholder="Enter your contact number"
                        maxlength="20"
                        required
                    >


                    <h5 class="mb-3">Form of Payment</h5>

                    <div class="form-check mb-2">

                        <input
                            class="form-check-input"
                            type="radio"
                            name="payment_method"
                            id="cash"
                            value="Cash"
                            required
                        >

                        <label class="form-check-label" for="cash">
                            Cash on Delivery
                        </label>

                    </div>

                    <div class="form-check mb-4">

                        <input
                            class="form-check-input"
                            type="radio"
                            name="payment_method"
                            id="gcash"
                            value="GCash"
                        >

                        <label class="form-check-label" for="gcash">
                            GCash
                        </label>

                    </div>


                    <button
                        type="submit"
                        id="checkoutButton"
                        class="btn btn-dark w-100 rounded-pill"
                        disabled
                    >
                        CHECK OUT
                    </button>

                </form>


                <a href="order.php"
                   class="btn btn-outline-dark w-100 rounded-pill mt-3">
                    Continue Shopping
                </a>

                <small class="text-muted text-center mt-3">
                    Your order total will be calculated securely.
                </small>

            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
document.addEventListener("DOMContentLoaded", function () {

    // ==========================================
    // API CONFIGURATION
    // ==========================================

    const API_URL = "http://localhost:3000";


    // ==========================================
    // HTML ELEMENTS
    // ==========================================

    const cartLoading = document.getElementById("cartLoading");
    const cartError = document.getElementById("cartError");
    const cartContent = document.getElementById("cartContent");
    const cartItems = document.getElementById("cartItems");
    const emptyCart = document.getElementById("emptyCart");

    const subtotalElement = document.getElementById("subtotal");
    const taxElement = document.getElementById("tax");
    const grandTotalElement = document.getElementById("grandTotal");
    const selectedCountElement = document.getElementById("selectedCount");

    const checkoutForm = document.getElementById("checkoutForm");
    const checkoutButton = document.getElementById("checkoutButton");

    let currentCart = [];


    // ==========================================
    // FORMAT PRICE
    // ==========================================

    function formatPrice(amount) {
        return Number(amount).toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }


    // ==========================================
    // API REQUEST HELPER
    // ==========================================

    async function apiRequest(endpoint, options = {}) {

        const response = await fetch(API_URL + endpoint, {
            ...options,
            credentials: "include",
            headers: {
                "Accept": "application/json",
                ...(options.headers || {})
            }
        });

        const responseText = await response.text();

        let data;

        try {
            data = JSON.parse(responseText);
        } catch (error) {
            console.error("Invalid API response:", responseText);

            throw new Error(
                "The server returned an invalid response. Check your Node.js server."
            );
        }

        if (response.status === 401) {
            window.location.href = "/SoftEcoms/public/login.php";

            throw new Error("Please log in first.");
        }

        if (!response.ok || data.success === false) {
            throw new Error(
                data.message || "Something went wrong."
            );
        }

        return data;
    }


    // ==========================================
    // LOAD CUSTOMER CART
    // ==========================================

    async function loadCart() {

        cartLoading.style.display = "block";
        cartError.style.display = "none";
        cartContent.style.display = "none";

        try {

            const data = await apiRequest("/cart");

            currentCart = data.cart || [];

            displayCart();

            cartContent.style.display = "block";

            await updateCartBadge();

        } catch (error) {

            console.error("Load cart error:", error);

            cartError.textContent = error.message;
            cartError.style.display = "block";

        } finally {

            cartLoading.style.display = "none";

        }
    }


    // ==========================================
    // DISPLAY CART PRODUCTS
    // ==========================================

    function displayCart() {

        cartItems.innerHTML = "";

        if (currentCart.length === 0) {

            emptyCart.style.display = "block";

            checkoutButton.disabled = true;

            updateSummary();

            return;
        }

        emptyCart.style.display = "none";

        currentCart.forEach(function (item) {

            const price = Number(item.price);
            const quantity = Number(item.quantity);

            const card = document.createElement("div");

            card.className = "card cart-item-card mb-4 shadow-sm rounded-4";

            // Product card body
            const cardBody = document.createElement("div");
            cardBody.className = "card-body";

            const row = document.createElement("div");
            row.className = "row align-items-center";

            // PRODUCT IMAGE
            const imageColumn = document.createElement("div");
            imageColumn.className = "col-md-3";

            const image = document.createElement("img");

            image.src =
                "assets/product_img/" +
                encodeURIComponent(item.img || "");

            image.alt = item.productName || "Product";

            image.className = "img-fluid rounded-4";

            image.style.height = "120px";
            image.style.width = "100%";
            image.style.objectFit = "cover";

            imageColumn.appendChild(image);


            // PRODUCT DETAILS
            const detailsColumn = document.createElement("div");
            detailsColumn.className = "col-md-4";

            const productName = document.createElement("h5");
            productName.textContent = item.productName || "Product";

            const productPrice = document.createElement("p");
            productPrice.className = "text-muted mb-0";
            productPrice.textContent = "₱" + formatPrice(price);

            detailsColumn.appendChild(productName);
            detailsColumn.appendChild(productPrice);


            // QUANTITY
            const quantityColumn = document.createElement("div");
            quantityColumn.className = "col-md-3";

            const quantityForm = document.createElement("form");

            quantityForm.className =
                "d-flex align-items-center gap-2 quantity-form";

            const quantityInput = document.createElement("input");

            quantityInput.type = "number";
            quantityInput.min = "1";
            quantityInput.step = "1";
            quantityInput.value = quantity;
            quantityInput.className = "form-control quantity-input";
            quantityInput.style.width = "75px";
            quantityInput.required = true;

            const updateButton = document.createElement("button");

            updateButton.type = "submit";
            updateButton.className = "btn btn-outline-dark btn-sm";
            updateButton.textContent = "Update";

            quantityForm.appendChild(quantityInput);
            quantityForm.appendChild(updateButton);

            quantityColumn.appendChild(quantityForm);


            // SUBTOTAL AND REMOVE
            const subtotalColumn = document.createElement("div");
            subtotalColumn.className = "col-md-2 text-end";

            const productSubtotal = document.createElement("p");

            productSubtotal.className =
                "fw-bold product-subtotal";

            productSubtotal.textContent =
                "₱" + formatPrice(price * quantity);

            const removeButton = document.createElement("button");

            removeButton.type = "button";
            removeButton.className =
                "btn btn-link text-danger text-decoration-none p-0";

            removeButton.textContent = "Remove";
            removeButton.dataset.id = item.cartID;

            subtotalColumn.appendChild(productSubtotal);
            subtotalColumn.appendChild(removeButton);


            // APPEND ELEMENTS
            row.appendChild(imageColumn);
            row.appendChild(detailsColumn);
            row.appendChild(quantityColumn);
            row.appendChild(subtotalColumn);

            cardBody.appendChild(row);
            card.appendChild(cardBody);

            cartItems.appendChild(card);


            // ==========================================
            // UPDATE QUANTITY EVENT
            // ==========================================

            quantityForm.addEventListener("submit", async function (event) {

                event.preventDefault();

                const newQuantity = Number(quantityInput.value);

                if (
                    !Number.isInteger(newQuantity) ||
                    newQuantity < 1
                ) {
                    Swal.fire(
                        "Invalid quantity",
                        "Quantity must be at least 1.",
                        "warning"
                    );

                    return;
                }

                updateButton.disabled = true;
                updateButton.textContent = "Updating...";

                try {

                    await apiRequest("/cart/update", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            cartID: item.cartID,
                            quantity: newQuantity
                        })
                    });

                    await loadCart();

                    Swal.fire({
                        icon: "success",
                        title: "Quantity updated",
                        timer: 1000,
                        showConfirmButton: false
                    });

                } catch (error) {

                    console.error("Update quantity error:", error);

                    Swal.fire(
                        "Unable to update",
                        error.message,
                        "error"
                    );

                    updateButton.disabled = false;
                    updateButton.textContent = "Update";

                }

            });


            // ==========================================
            // REMOVE PRODUCT EVENT
            // ==========================================

            removeButton.addEventListener("click", async function () {

                const confirmation = await Swal.fire({
                    title: "Remove Item?",
                    text: "Are you sure you want to remove this product?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, remove it",
                    cancelButtonText: "Keep Item"
                });

                if (!confirmation.isConfirmed) return;

                removeButton.disabled = true;

                try {

                    await apiRequest("/cart/remove", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            cartID: item.cartID
                        })
                    });

                    await loadCart();

                    Swal.fire({
                        icon: "success",
                        title: "Product removed",
                        timer: 1000,
                        showConfirmButton: false
                    });

                } catch (error) {

                    console.error("Remove product error:", error);

                    Swal.fire(
                        "Unable to remove product",
                        error.message,
                        "error"
                    );

                    removeButton.disabled = false;

                }

            });

        });

        checkoutButton.disabled = false;

        updateSummary();
    }


    // ==========================================
    // UPDATE ORDER SUMMARY
    // ==========================================

    function updateSummary() {

        let subtotal = 0;
        let totalItems = 0;

        currentCart.forEach(function (item) {

            const price = Number(item.price);
            const quantity = Number(item.quantity);

            subtotal += price * quantity;
            totalItems += quantity;

        });

        const tax = Math.round(subtotal * 0.12 * 100) / 100;
        const grandTotal = subtotal + tax;

        subtotalElement.textContent = formatPrice(subtotal);
        taxElement.textContent = formatPrice(tax);
        grandTotalElement.textContent = formatPrice(grandTotal);

        selectedCountElement.textContent = totalItems;

        checkoutButton.disabled = currentCart.length === 0;
    }


    // ==========================================
    // UPDATE NAVIGATION CART BADGE
    // ==========================================

    async function updateCartBadge() {

        try {

            const data = await apiRequest("/cart/count");

            const badge = document.getElementById("cartCount");

            if (badge) {

                badge.textContent = data.cartCount;

                badge.style.display =
                    data.cartCount > 0 ? "flex" : "none";

            }

        } catch (error) {

            console.error("Cart badge error:", error);

        }
    }


    // ==========================================
    // CHECKOUT
    // ==========================================

    checkoutForm.addEventListener("submit", async function (event) {

        event.preventDefault();

        if (currentCart.length === 0) {

            Swal.fire(
                "Empty Cart",
                "Please add products before checking out.",
                "warning"
            );

            return;
        }

        const address = document
            .getElementById("checkoutAddress")
            .value.trim();

        const phone = document
            .getElementById("checkoutPhone")
            .value.trim();

        const paymentMethod = document.querySelector(
            'input[name="payment_method"]:checked'
        )?.value;

        if (!address || !phone || !paymentMethod) {

            Swal.fire(
                "Incomplete Information",
                "Please complete your delivery and payment information.",
                "warning"
            );

            return;
        }

        const confirmation = await Swal.fire({
            title: "Place your order?",
            text: "Please confirm that your order details are correct.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Place Order",
            cancelButtonText: "Review Order"
        });

        if (!confirmation.isConfirmed) return;

        checkoutButton.disabled = true;
        checkoutButton.textContent = "Processing Order...";

        try {

            const data = await apiRequest("/checkout", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    address: address,
                    phone: phone,
                    payment_method: paymentMethod
                })
            });

            await Swal.fire({
                icon: "success",
                title: "Order Placed!",
                html: `
                    <p>${data.message}</p>
                    <p>
                        <strong>Order Number:</strong>
                        ${data.order.orderNumber}
                    </p>
                    <p>
                        <strong>Total:</strong>
                        ₱${formatPrice(data.order.totalPrice)}
                    </p>
                    <p>
                        <strong>Payment:</strong>
                        ${data.order.paymentMethod}
                    </p>
                `,
                confirmButtonText: "Continue Shopping"
            });

            window.location.href = "order.php";

        } catch (error) {

            console.error("Checkout error:", error);

            Swal.fire(
                "Checkout Failed",
                error.message,
                "error"
            );

            checkoutButton.disabled = false;
            checkoutButton.textContent = "CHECK OUT";

        }

    });


    // ==========================================
    // INITIALIZE
    // ==========================================

    loadCart();

});
</script>
