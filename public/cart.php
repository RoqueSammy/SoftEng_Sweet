
<?php
session_start();

$pageClass = "cart-page";

include('./admin/includes/header.php');
include('./includes/topbar.php');
?>

<style>
.cart-item-card {
    cursor: pointer;
    border: 2px solid transparent;
    transition: 0.2s ease;
}

.cart-item-card.selected {
    border-color: #212529;
    background-color: #fffaf5;
}

.cart-item-card .form-check-input {
    width: 19px;
    height: 19px;
    cursor: pointer;
}

.product-subtotal {
    font-size: 17px;
    font-weight: 700;
}

#proceedCheckoutButton {
    font-weight: 700;
    padding: 13px;
}

#proceedCheckoutButton:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

#cartContent {
    align-items: flex-start;
}

#orderSummary {
    position: sticky;
    top: 20px;
}

/* UNAVAILABLE PRODUCTS */

.cart-item-card.unavailable {
    opacity: 0.7;
    background-color: #f7f7f7;
    border: 2px solid #e5e5e5;
    cursor: not-allowed;
}

.cart-item-card.unavailable .form-check-input,
.cart-item-card.unavailable .quantity-input {
    cursor: not-allowed;
}

.cart-item-card.unavailable .quantity-input {
    background-color: #e9e9e9;
}

.cart-item-card.unavailable .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.stock-unavailable {
    color: #dc3545;
    font-weight: 700;
}

.stock-warning {
    color: #d18a00;
    font-weight: 700;
}

/* Unavailable product card */
.cart-item-card.unavailable {
    opacity: 0.7;
    background-color: #f7f7f7;
    border: 2px solid #e5e5e5;
    cursor: not-allowed;
}

/* Keep the Remove button clear and clickable */
.cart-item-card.unavailable .btn-link {
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
    cursor: pointer !important;
    color: #dc3545 !important;
    font-weight: 700 !important;
    text-decoration: underline !important;
    display: inline-block !important;
}

/* Keep the Remove button clear when focused */
.cart-item-card.unavailable .btn-link:focus {
    opacity: 1 !important;
    color: #a71d2a !important;
    outline: 2px solid #dc3545;
    outline-offset: 3px;
}
</style>

<div class="container cart-layout my-5">

    <h1 class="text-center mb-5">My Shopping Cart</h1>

    <div id="cartLoading" class="text-center py-5">
        Loading your cart...
    </div>

    <div id="cartError" class="alert alert-danger" style="display:none;"></div>

    <div id="cartContent" class="row" style="display:none;">

        <div class="col-lg-8">

            <div id="cartItems"></div>

            <div id="emptyCart" class="text-center py-5" style="display:none;">
                <h4>Your cart is empty!</h4>
                <p>Looks like you haven't added any pastries yet.</p>

                <a href="order.php" class="btn btn-dark mt-3">
                    Continue Shopping
                </a>
            </div>

        </div>

        <!-- ORDER SUMMARY -->
        <div class="col-lg-4" id="orderSummaryColumn">

            <div class="card shadow-sm rounded-4 p-4" id="orderSummary">

                <h4 class="mb-4">Order Summary</h4>

                <div class="d-flex justify-content-between mb-3">
                    <span>Selected Items</span>
                    <strong id="selectedCount">0</strong>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span>Subtotal</span>
                    <strong>₱<span id="subtotal">0.00</span></strong>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span>VAT (12%)</span>
                    <strong>₱<span id="tax">0.00</span></strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-4">
                    <h5>Total</h5>
                    <h5>₱<span id="grandTotal">0.00</span></h5>
                </div>

                <form id="proceedCheckoutForm" method="POST" action="checkout.php">

                    <div id="selectedCartInputs"></div>

                    <button
                        type="submit"
                        id="proceedCheckoutButton"
                        class="btn btn-dark w-100 rounded-pill"
                        disabled
                    >
                        PROCEED TO CHECKOUT
                    </button>

                </form>

                <a
                    href="order.php"
                    class="btn btn-outline-dark w-100 rounded-pill mt-3"
                >
                    Continue Shopping
                </a>

                <small class="text-muted text-center mt-3">
                    Select the products you want to checkout.
                </small>

            </div>

        </div>

    </div>

</div>


<!-- REMOVE PRODUCT MODAL -->

<div class="modal fade remove-modal" id="removeCartModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-body text-center">

                <div class="remove-icon">
                    <i class="fas fa-trash-alt"></i>
                </div>

                <h4 class="remove-title">Remove Item?</h4>

                <p class="remove-message">
                    Are you sure you want to remove
                    <strong id="removeProductName"></strong>
                    from your cart?
                </p>

                <div class="remove-actions">

                    <button
                        type="button"
                        class="btn keep-item-btn"
                        data-bs-dismiss="modal"
                    >
                        Keep Item
                    </button>

                    <button
                        type="button"
                        class="btn confirm-remove-btn"
                        id="confirmRemoveButton"
                    >
                        Remove
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const API_URL = "/api";

    const cartLoading = document.getElementById("cartLoading");
    const cartError = document.getElementById("cartError");
    const cartContent = document.getElementById("cartContent");
    const cartItems = document.getElementById("cartItems");
    const emptyCart = document.getElementById("emptyCart");

    const orderSummaryColumn = document.getElementById("orderSummaryColumn");

    const subtotalElement = document.getElementById("subtotal");
    const taxElement = document.getElementById("tax");
    const grandTotalElement = document.getElementById("grandTotal");
    const selectedCountElement = document.getElementById("selectedCount");

    const proceedCheckoutForm = document.getElementById("proceedCheckoutForm");
    const proceedCheckoutButton = document.getElementById("proceedCheckoutButton");
    const selectedCartInputs = document.getElementById("selectedCartInputs");

    const removeModalElement = document.getElementById("removeCartModal");
    const removeModal = new bootstrap.Modal(removeModalElement);

    const removeProductName = document.getElementById("removeProductName");
    const confirmRemoveButton = document.getElementById("confirmRemoveButton");

    let currentCart = [];
    let selectedCartIDs = new Set();
    let pendingRemoveCartID = null;
    let summaryRequestNumber = 0;


    // ==========================================
    // FORMAT PRICE
    // ==========================================

    const formatPrice = amount => Number(amount).toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });


    // ==========================================
    // CHECK PRODUCT STOCK
    // ==========================================

    function getStockStatus(item) {

        const stock = Number(item.stock);
        const quantity = Number(item.quantity);

        if (!Number.isFinite(stock) || stock <= 0) {
            return "out_of_stock";
        }

        if (stock < quantity) {
            return "insufficient_stock";
        }

        return "available";
    }


    // ==========================================
    // API REQUEST
    // ==========================================

    async function apiRequest(endpoint, options = {}) {

        const response = await fetch(API_URL + endpoint, {
            ...options,
            credentials: "include",
            headers: {
                Accept: "application/json",
                ...(options.headers || {})
            }
        });

        const responseText = await response.text();

        let data;

        try {
            data = JSON.parse(responseText);
        } catch (error) {
            console.error("API response:", responseText);
            throw new Error("Invalid server response. Check your Node.js server.");
        }

        if (response.status === 401) {
            window.location.href = "/SoftEcoms/public/login.php";
            throw new Error("Please log in first.");
        }

        if (!response.ok || data.success === false) {

            const error = new Error(data.message || "Something went wrong.");

            error.status = response.status;
            error.stockError = data.stockError || false;
            error.unavailableItems = data.unavailableItems || [];

            throw error;
        }

        return data;
    }


    // ==========================================
    // UPDATE HEADER CART BADGE
    // ==========================================

    async function updateCartBadge() {

        const badge = document.getElementById("cartCount");

        if (!badge) return;

        try {

            const data = await apiRequest("/cart/count");

            const count = Math.max(0, Number(data.cartCount) || 0);

            badge.textContent = count;
            badge.style.display = count > 0 ? "flex" : "none";

        } catch (error) {
            console.error("Cart badge error:", error);
        }
    }


    // ==========================================
    // LOAD CART
    // ==========================================

    async function loadCart() {

        cartLoading.style.display = "block";
        cartError.style.display = "none";
        cartContent.style.display = "none";

        try {

            const data = await apiRequest("/cart");

            currentCart = Array.isArray(data.cart) ? data.cart : [];

            const validIDs = new Set(
                currentCart
                    .filter(item => getStockStatus(item) === "available")
                    .map(item => String(item.cartID))
            );

            // Remove unavailable products from checkout selection.
            selectedCartIDs = new Set(
                [...selectedCartIDs].filter(id => validIDs.has(id))
            );

            displayCart();

            cartContent.style.display = "flex";

        } catch (error) {

            console.error(error);

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

        const isCartEmpty = currentCart.length === 0;

        emptyCart.style.display = isCartEmpty ? "block" : "none";
        orderSummaryColumn.style.display = isCartEmpty ? "none" : "block";

        currentCart.forEach(item => {

            const cartID = String(item.cartID);
            const price = Number(item.price);
            const quantity = Number(item.quantity);

            const stock = Number(item.stock);
            const stockStatus = getStockStatus(item);

            const isAvailable = stockStatus === "available";

            const card = document.createElement("div");

            card.className = "card cart-item-card mb-4 shadow-sm rounded-4";
            card.dataset.cartId = cartID;

            // Apply unavailable styling.
            if (!isAvailable) {
                card.classList.add("unavailable");
            }

            if (selectedCartIDs.has(cartID) && isAvailable) {
                card.classList.add("selected");
            }

            const body = document.createElement("div");
            body.className = "card-body";

            const row = document.createElement("div");
            row.className = "row align-items-center";


            // ==========================================
            // PRODUCT IMAGE
            // ==========================================

            const imageColumn = document.createElement("div");
            imageColumn.className = "col-md-3 mb-3 mb-md-0";

            const image = document.createElement("img");

            image.src = /^https?:\/\//i.test(String(item.img || ""))
                ? item.img
                : "assets/product_img/" + encodeURIComponent(item.img || "");

            image.alt = item.productName || "Product";
            image.className = "img-fluid rounded-4";
            image.style.height = "120px";
            image.style.width = "100%";
            image.style.objectFit = "cover";

            image.onerror = () => {
                image.style.display = "none";
            };

            imageColumn.appendChild(image);


            // ==========================================
            // PRODUCT DETAILS
            // ==========================================

            const detailsColumn = document.createElement("div");
            detailsColumn.className = "col-md-4 mb-3 mb-md-0";

            const name = document.createElement("h5");
            name.textContent = item.productName || "Product";

            const priceText = document.createElement("p");
            priceText.className = "text-muted mb-2";
            priceText.textContent = "₱" + formatPrice(price);


            // STOCK STATUS

            const stockMessage = document.createElement("p");
            stockMessage.className = "small mb-2";

            if (stockStatus === "out_of_stock") {

                stockMessage.classList.add("stock-unavailable");
                stockMessage.textContent = "Out of stock";

            } else if (stockStatus === "insufficient_stock") {

                stockMessage.classList.add("stock-warning");
                stockMessage.textContent =
                    `Only ${stock} left in stock. Please reduce your quantity.`;

            } else {

                stockMessage.classList.add("text-success");
                stockMessage.textContent = `In stock: ${stock}`;
            }


            // SELECT PRODUCT

            const selectLabel = document.createElement("label");
            selectLabel.className = "d-flex align-items-center gap-2 mt-2";

            const selectCheckbox = document.createElement("input");

            selectCheckbox.type = "checkbox";
            selectCheckbox.className = "form-check-input";

            selectCheckbox.checked =
                isAvailable && selectedCartIDs.has(cartID);

            selectCheckbox.disabled = !isAvailable;

            const selectText = document.createElement("span");

            selectText.textContent = isAvailable
                ? "Select this product"
                : "Unavailable for checkout";

            selectLabel.append(selectCheckbox, selectText);

            detailsColumn.append(
                name,
                priceText,
                stockMessage,
                selectLabel
            );


            // ==========================================
            // QUANTITY
            // ==========================================

            const quantityColumn = document.createElement("div");
            quantityColumn.className = "col-md-3 mb-3 mb-md-0";

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

            if (Number.isFinite(stock) && stock > 0) {
                quantityInput.max = String(stock);
            }

            // Disable quantity for unavailable products.
            quantityInput.disabled = !isAvailable;

            const updateButton = document.createElement("button");

            updateButton.type = "submit";
            updateButton.className = "btn btn-outline-dark btn-sm";
            updateButton.textContent = "Update";

            // Disable update for unavailable products.
            updateButton.disabled = !isAvailable;

            quantityForm.append(quantityInput, updateButton);
            quantityColumn.appendChild(quantityForm);


            // ==========================================
            // SUBTOTAL AND REMOVE
            // ==========================================

            const subtotalColumn = document.createElement("div");
            subtotalColumn.className = "col-md-2 text-md-end";

            const itemSubtotal = document.createElement("p");
            itemSubtotal.className = "fw-bold product-subtotal";
            itemSubtotal.textContent = "₱" + formatPrice(price * quantity);

            const removeButton = document.createElement("button");

            removeButton.type = "button";
            removeButton.className =
                "btn btn-link text-danger text-decoration-none p-0";

            removeButton.textContent = "Remove";

            subtotalColumn.append(itemSubtotal, removeButton);


            // ==========================================
            // BUILD CARD
            // ==========================================

            row.append(
                imageColumn,
                detailsColumn,
                quantityColumn,
                subtotalColumn
            );

            body.appendChild(row);
            card.appendChild(body);
            cartItems.appendChild(card);


            // ==========================================
            // CHECKBOX SELECTION
            // ==========================================

            selectCheckbox.addEventListener("change", () => {

                if (!isAvailable) {
                    selectCheckbox.checked = false;
                    return;
                }

                if (selectCheckbox.checked) {
                    selectedCartIDs.add(cartID);
                    card.classList.add("selected");
                } else {
                    selectedCartIDs.delete(cartID);
                    card.classList.remove("selected");
                }

                updateSummary();
            });


            // ==========================================
            // CARD SELECTION
            // ==========================================

            card.addEventListener("click", event => {

                if (event.target.closest(
                    "button, a, input, form, select, textarea, label"
                )) {
                    return;
                }

                if (!isAvailable) return;

                selectCheckbox.checked = !selectCheckbox.checked;

                selectCheckbox.dispatchEvent(new Event("change"));
            });


            // ==========================================
            // UPDATE QUANTITY
            // ==========================================

            quantityForm.addEventListener("submit", async event => {

                event.preventDefault();

                if (!isAvailable) return;

                const newQuantity = Number(quantityInput.value);

                if (!Number.isSafeInteger(newQuantity) || newQuantity < 1) {

                    Swal.fire(
                        "Invalid quantity",
                        "Quantity must be at least 1.",
                        "warning"
                    );

                    return;
                }

                if (newQuantity > stock) {

                    Swal.fire(
                        "Insufficient stock",
                        `Only ${stock} item(s) are currently available.`,
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
                    await updateCartBadge();

                    Swal.fire({
                        icon: "success",
                        title: "Quantity updated",
                        timer: 1000,
                        showConfirmButton: false
                    });

                } catch (error) {

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
            // REMOVE PRODUCT
            // ==========================================

            removeButton.addEventListener("click", () => {

                pendingRemoveCartID = item.cartID;

                removeProductName.textContent =
                    item.productName || "this product";

                removeModal.show();
            });

        });

        updateSummary();
    }


    // ==========================================
    // UPDATE ORDER SUMMARY
    // ==========================================

    async function updateSummary() {

        const requestNumber = ++summaryRequestNumber;

        const selectedIDs = [...selectedCartIDs];

        const selectedItems = currentCart
            .filter(item =>
                selectedCartIDs.has(String(item.cartID))
            )
            .reduce((sum, item) => sum + Number(item.quantity), 0);

        selectedCountElement.textContent = selectedItems;

        if (selectedIDs.length === 0) {

            subtotalElement.textContent = formatPrice(0);
            taxElement.textContent = formatPrice(0);
            grandTotalElement.textContent = formatPrice(0);

            proceedCheckoutButton.disabled = true;
            proceedCheckoutButton.setAttribute("aria-disabled", "true");

            return;
        }

        proceedCheckoutButton.disabled = true;
        proceedCheckoutButton.setAttribute("aria-disabled", "true");

        try {

            const data = await apiRequest("/cart/summary", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    cartIDs: selectedIDs
                })
            });

            if (requestNumber !== summaryRequestNumber) return;

            subtotalElement.textContent = formatPrice(data.subtotal);
            taxElement.textContent = formatPrice(data.tax);
            grandTotalElement.textContent = formatPrice(data.total);

            proceedCheckoutButton.disabled = false;
            proceedCheckoutButton.setAttribute("aria-disabled", "false");

        } catch (error) {

            if (requestNumber !== summaryRequestNumber) return;

            console.error("Order summary error:", error);

            subtotalElement.textContent = formatPrice(0);
            taxElement.textContent = formatPrice(0);
            grandTotalElement.textContent = formatPrice(0);

            proceedCheckoutButton.disabled = true;
            proceedCheckoutButton.setAttribute("aria-disabled", "true");

            if (error.stockError || error.status === 409) {

                Swal.fire(
                    "Stock unavailable",
                    error.message,
                    "warning"
                );

                await loadCart();
                return;
            }

            Swal.fire(
                "Unable to calculate order summary",
                error.message,
                "error"
            );
        }
    }


    // ==========================================
    // CONFIRM REMOVE
    // ==========================================

    confirmRemoveButton.addEventListener("click", async () => {

        if (pendingRemoveCartID === null) return;

        confirmRemoveButton.disabled = true;
        confirmRemoveButton.textContent = "Removing...";

        try {

            await apiRequest("/cart/remove", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    cartID: pendingRemoveCartID
                })
            });

            selectedCartIDs.delete(String(pendingRemoveCartID));

            removeModal.hide();
            pendingRemoveCartID = null;

            await loadCart();
            await updateCartBadge();

        } catch (error) {

            Swal.fire(
                "Unable to remove product",
                error.message,
                "error"
            );

        } finally {

            confirmRemoveButton.disabled = false;
            confirmRemoveButton.textContent = "Remove";
        }
    });


    removeModalElement.addEventListener("hidden.bs.modal", () => {
        pendingRemoveCartID = null;
    });


    // ==========================================
    // PROCEED TO CHECKOUT
    // ==========================================

    proceedCheckoutForm.addEventListener("submit", async event => {

        event.preventDefault();

        if (selectedCartIDs.size === 0) {

            Swal.fire(
                "No products selected",
                "Please select at least one product to continue.",
                "warning"
            );

            return;
        }

        proceedCheckoutButton.disabled = true;
        proceedCheckoutButton.textContent = "Checking stock...";

        try {

            const selectedIDs = [...selectedCartIDs];

            await apiRequest("/cart/summary", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    cartIDs: selectedIDs
                })
            });

            selectedCartInputs.innerHTML = "";

            selectedIDs.forEach(id => {

                const input = document.createElement("input");

                input.type = "hidden";
                input.name = "selected_cart_ids[]";
                input.value = id;

                selectedCartInputs.appendChild(input);
            });

            proceedCheckoutButton.textContent = "Opening Checkout...";

            proceedCheckoutForm.submit();

        } catch (error) {

            proceedCheckoutButton.disabled = true;
            proceedCheckoutButton.textContent = "PROCEED TO CHECKOUT";

            Swal.fire(
                "Unable to proceed",
                error.message,
                error.stockError ? "warning" : "error"
            );

            await loadCart();
        }
    });


    // ==========================================
    // INITIAL LOAD
    // ==========================================

    async function initializeCart() {
        await loadCart();
        await updateCartBadge();
    }

    initializeCart();

});
</script>
