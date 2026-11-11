
<?php
session_start();

$pageClass = "checkout-page";

// ==========================================
// CLEAR CHECKOUT SELECTION
// ==========================================

if (isset($_GET['clear_selection']) && $_GET['clear_selection'] === '1') {

    unset($_SESSION['checkout_selected_cart_ids']);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(['success' => true]);
    exit;
}

// ==========================================
// RECEIVE SELECTED CART IDS
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submitted = $_POST['selected_cart_ids'] ?? [];

    if (!is_array($submitted)) {
        $submitted = [];
    }

    $selectedIDs = [];

    foreach ($submitted as $id) {

        $validated = filter_var($id, FILTER_VALIDATE_INT);

        if ($validated !== false && $validated > 0) {
            $selectedIDs[] = $validated;
        }
    }

    $selectedIDs = array_values(array_unique($selectedIDs));

    if (empty($selectedIDs)) {

        unset($_SESSION['checkout_selected_cart_ids']);

        header('Location: cart.php');
        exit;
    }

    $_SESSION['checkout_selected_cart_ids'] = $selectedIDs;
}

// ==========================================
// GET SELECTED PRODUCTS
// ==========================================

$selectedIDs = $_SESSION['checkout_selected_cart_ids'] ?? [];

if (empty($selectedIDs) || !is_array($selectedIDs)) {

    header('Location: cart.php');
    exit;
}

include('./admin/includes/header.php');
include('./admin/includes/topbar.php');
?>

<link rel="stylesheet" href="assets/css/checkout.css?v=3">

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
>

<!-- ==================================
     MAP STYLES
=================================== -->

<style>
#deliveryMap {
    display: block;
    width: 100%;
    height: 380px;
    min-height: 380px;
    margin-top: 12px;
    border: 1px solid #F8DED8;
    border-radius: 14px;
    overflow: hidden;
    position: relative;
    z-index: 1;
    background: #F8DED8;
}

#deliveryMap .leaflet-container {
    width: 100%;
    height: 100%;
}

.map-description {
    font-size: 14px;
    color: #8A7770;
    margin-top: 5px;
}

.map-location-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}

.map-location-actions button {
    padding: 10px 16px;
    border: 1px solid #F8DED8;
    border-radius: 8px;
    background: #fff;
    color: #704638;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: 0.2s;
}

.map-location-actions button:hover {
    background: #704638;
    color: #fff;
    border-color: #704638;
}

.map-location-actions button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

#selectedCoordinates {
    display: block;
    margin-top: 12px;
    font-size: 13px;
    color: #8A7770;
}

#checkoutForm[hidden],
#checkoutLoading[hidden],
#checkoutError[hidden],
#checkoutEmpty[hidden],
#gcashDetails[hidden] {
    display: none !important;
}

@media (max-width: 600px) {
    #deliveryMap {
        height: 300px;
        min-height: 300px;
    }

    .map-location-actions button {
        flex: 1;
    }
}
</style>

<main class="checkout-page">

    <div class="checkout-container">

        <!-- ==================================
             PAGE HEADER
        =================================== -->

        <header class="checkout-heading">

            <span class="checkout-eyebrow">
                SWEET! • ALMOST THERE
            </span>

            <h1>Complete Your Order</h1>

            <p>
                Review your selected pastries and provide your delivery details.
            </p>

            <div class="checkout-progress">

                <div class="progress-step completed">
                    <span>✓</span>
                    <small>Cart</small>
                </div>

                <div class="progress-line active"></div>

                <div class="progress-step current">
                    <span>2</span>
                    <small>Checkout</small>
                </div>

                <div class="progress-line"></div>

                <div class="progress-step">
                    <span>3</span>
                    <small>Confirmation</small>
                </div>

            </div>

        </header>

        <!-- ==================================
             LOADING / ERROR
        =================================== -->

        <div id="checkoutLoading" class="checkout-message">
            Loading your selected products...
        </div>

        <div
            id="checkoutError"
            class="checkout-message checkout-error"
            hidden
        ></div>

        <section id="checkoutEmpty" class="checkout-empty" hidden>

            <div class="empty-icon">♡</div>

            <h3>No selected products found</h3>

            <p>
                Your selected cart items may have been removed.
                Return to your cart and select products again.
            </p>

            <a href="cart.php" class="checkout-main-btn">
                Return to Cart
            </a>

        </section>

        <!-- ==================================
             CHECKOUT FORM
        =================================== -->

        <form id="checkoutForm" hidden>

            <div class="checkout-layout">

                <!-- LEFT SIDE -->

                <div class="checkout-main">

                    <!-- ==================================
                         01 CUSTOMER INFORMATION
                    =================================== -->

                    <section class="checkout-card">

                        <div class="section-heading">

                            <div class="section-icon">01</div>

                            <div>
                                <h3>Customer Information</h3>
                                <p>Tell us who will receive the order.</p>
                            </div>

                        </div>

                        <div class="form-grid">

                            <div class="form-group full-width">

                                <label for="checkoutFullName">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    id="checkoutFullName"
                                    name="full_name"
                                    placeholder="Enter your full name"
                                    maxlength="150"
                                    autocomplete="name"
                                    required
                                >

                            </div>

                            <div class="form-group">

                                <label for="checkoutPhone">
                                    Phone Number
                                </label>

                                <input
                                    type="tel"
                                    id="checkoutPhone"
                                    name="phone"
                                    placeholder="09XX XXX XXXX"
                                    maxlength="20"
                                    autocomplete="tel"
                                    required
                                >

                            </div>

                            <div class="form-group">

                                <label for="checkoutEmail">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="checkoutEmail"
                                    name="email"
                                    placeholder="Enter your email address"
                                    maxlength="150"
                                    autocomplete="email"
                                    required
                                >

                            </div>

                        </div>

                    </section>

                    <!-- ==================================
                         02 DELIVERY INFORMATION
                    =================================== -->

                    <section class="checkout-card">

                        <div class="section-heading">

                            <div class="section-icon">02</div>

                            <div>
                                <h3>Delivery Information</h3>
                                <p>Where should we deliver your order?</p>
                            </div>

                        </div>

                        <div class="form-grid">

                            <div class="form-group full-width">

                                <label for="checkoutAddress">
                                    Complete Delivery Address
                                </label>

                                <textarea
                                    id="checkoutAddress"
                                    name="address"
                                    rows="4"
                                    placeholder="House number, street, barangay, city / municipality, and province"
                                    required
                                    maxlength="1000"
                                    autocomplete="street-address"
                                ></textarea>

                            </div>

                            <!-- MAP -->

                            <div class="form-group full-width">

                                <label>
                                    Pin Your Delivery Location
                                </label>

                                <p class="map-description">
                                    Select your exact delivery location.
                                    Click anywhere on the map or drag the pin
                                    to adjust your location.
                                </p>

                                <div id="deliveryMap"></div>

                                <div class="map-location-actions">

                                    <button
                                        type="button"
                                        id="useCurrentLocation"
                                    >
                                        📍 Use My Current Location
                                    </button>

                                    <button
                                        type="button"
                                        id="resetMapLocation"
                                    >
                                        Reset Map
                                    </button>

                                </div>

                                <small id="selectedCoordinates">
                                    No delivery location selected yet.
                                </small>

                                <input
                                    type="hidden"
                                    id="deliveryLatitude"
                                >

                                <input
                                    type="hidden"
                                    id="deliveryLongitude"
                                >

                            </div>

                        </div>

                    </section>

                    <!-- ==================================
                         03 PAYMENT METHOD
                    =================================== -->

                    <section class="checkout-card">

                        <div class="section-heading">

                            <div class="section-icon">03</div>

                            <div>
                                <h3>Payment Method</h3>
                                <p>Choose how you want to pay.</p>
                            </div>

                        </div>

                        <div class="payment-options">

                            <!-- CASH ON DELIVERY -->

                            <label class="payment-option selected">

                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="Cash"
                                    checked
                                >

                                <span class="payment-symbol">₱</span>

                                <span class="payment-details">

                                    <strong>Cash on Delivery</strong>

                                    <small>
                                        Pay when your order arrives.
                                    </small>

                                </span>

                                <span class="choice-check">✓</span>

                            </label>

                            <!-- GCASH -->

                            <label class="payment-option">

                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="GCash"
                                >

                                <span class="gcash-symbol">G</span>

                                <span class="payment-details">

                                    <strong>GCash</strong>

                                    <small>
                                        Pay using your GCash account.
                                    </small>

                                </span>

                                <span class="choice-check">✓</span>

                            </label>

                        </div>

                        <!-- GCASH INFORMATION -->

                        <div class="gcash-details" id="gcashDetails" hidden>

                            <div class="gcash-header">

                                <span class="gcash-symbol">G</span>

                                <div>
                                    <strong>GCash Payment</strong>

                                    <p>GCash payment instructions</p>
                                </div>

                            </div>

                            <p class="gcash-placeholder">
                                GCash number and QR code will be displayed
                                here once the store payment details are configured.
                            </p>

                            <div class="form-group">

                                <label for="gcashReference">
                                    GCash Reference Number
                                </label>

                                <input
                                    type="text"
                                    id="gcashReference"
                                    name="gcash_reference"
                                    placeholder="Enter your GCash reference number"
                                    maxlength="100"
                                >

                            </div>

                        </div>

                    </section>

                </div>

                <!-- ==================================
                     RIGHT SIDE: ORDER SUMMARY
                =================================== -->

                <aside class="checkout-sidebar">

                    <div class="summary-card">

                        <div class="summary-heading">

                            <div>

                                <span class="checkout-eyebrow">
                                    YOUR SELECTED ITEMS
                                </span>

                                <h3>Order Summary</h3>

                            </div>

                            <a href="cart.php">Edit Cart</a>

                        </div>

                        <div
                            id="summaryProducts"
                            class="summary-products"
                        ></div>

                        <div class="summary-divider"></div>

                        <div class="summary-row">
                            <span>Selected Items</span>
                            <strong id="summaryCount">0</strong>
                        </div>

                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong id="summarySubtotal">₱0.00</strong>
                        </div>

                        <div class="summary-row">
                            <span>VAT (12%)</span>
                            <strong id="summaryTax">₱0.00</strong>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-total">
                            <span>Total</span>
                            <strong id="summaryTotal">₱0.00</strong>
                        </div>

                        <button
                            type="submit"
                            id="placeOrderButton"
                            class="place-order-btn"
                        >
                            PLACE ORDER <span>→</span>
                        </button>

                        <p class="summary-note">
                            ♡ Thank you for choosing SweetClaire!
                        </p>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</main>

<!-- ==================================
     ORDER CONFIRMATION POPUP
=================================== -->

<div
    class="modal fade order-confirm-modal"
    id="orderConfirmModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-body text-center">

                <div class="remove-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>

                <div class="remove-heart">
                    <i class="fas fa-heart"></i>
                </div>

                <h4 class="remove-title">
                    Confirm Your Order?
                </h4>

                <p class="remove-message">
                    Are you sure you want to place your order?
                    Please double-check your customer information,
                    delivery address, payment method, and order details
                    before continuing.
                </p>

                <div class="remove-actions">

                    <button
                        type="button"
                        class="btn keep-item-btn"
                        data-bs-dismiss="modal"
                    >
                        Review Order
                    </button>

                    <button
                        type="button"
                        class="btn confirm-remove-btn"
                        id="confirmOrder"
                    >
                        Yes, Place Order
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ==================================
     JAVASCRIPT LIBRARIES
=================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

document.addEventListener("DOMContentLoaded", function () {

    // ==========================================
    // CONFIGURATION
    // ==========================================

    const API_URL = "http://localhost:3000";

    const selectedCartIDs = <?php
        echo json_encode(
            array_values($selectedIDs),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
    ?>;

    // Initial map location: Bislig area.
    // Change this to your actual shop's service area.

    const defaultLocation = [8.8794, 125.0726];

    // ==========================================
    // HTML ELEMENTS
    // ==========================================

    const loading = document.getElementById("checkoutLoading");
    const errorBox = document.getElementById("checkoutError");
    const emptyBox = document.getElementById("checkoutEmpty");

    const form = document.getElementById("checkoutForm");

    const placeOrderButton =
        document.getElementById("placeOrderButton");

    const confirmOrderButton =
        document.getElementById("confirmOrder");

    const fullNameInput =
        document.getElementById("checkoutFullName");

    const emailInput =
        document.getElementById("checkoutEmail");

    const addressInput =
        document.getElementById("checkoutAddress");

    const phoneInput =
        document.getElementById("checkoutPhone");

    const gcashReferenceInput =
        document.getElementById("gcashReference");

    const latitudeInput =
        document.getElementById("deliveryLatitude");

    const longitudeInput =
        document.getElementById("deliveryLongitude");

    const coordinatesLabel =
        document.getElementById("selectedCoordinates");

    const useCurrentLocationButton =
        document.getElementById("useCurrentLocation");

    const resetMapButton =
        document.getElementById("resetMapLocation");

    const gcashDetails =
        document.getElementById("gcashDetails");

    const summaryProducts =
        document.getElementById("summaryProducts");

    // ==========================================
    // VARIABLES
    // ==========================================

    let selectedCartItems = [];

    let deliveryMap = null;

    let deliveryMarker = null;

    let isProcessingOrder = false;

    const orderModalElement =
        document.getElementById("orderConfirmModal");

    const orderModal = new bootstrap.Modal(
        orderModalElement
    );

    // ==========================================
    // PRICE FORMATTER
    // ==========================================

    function formatPrice(value) {

        return Number(value).toLocaleString("en-PH", {

            minimumFractionDigits: 2,

            maximumFractionDigits: 2

        });

    }

    // ==========================================
    // API REQUEST
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

            console.error("Server response:", responseText);

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
    // DELIVERY MAP
    // ==========================================

    function initializeDeliveryMap() {

        const mapElement = document.getElementById("deliveryMap");

        if (!mapElement || deliveryMap) return;

        if (typeof L === "undefined") {

            throw new Error(
                "The map library could not be loaded. Please check your internet connection."
            );

        }

        deliveryMap = L.map(mapElement).setView(
            defaultLocation,
            14
        );

        L.tileLayer(
            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            {

                maxZoom: 19,

                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'

            }
        ).addTo(deliveryMap);

        deliveryMap.on("click", function (event) {

            setDeliveryLocation(
                event.latlng.lat,
                event.latlng.lng
            );

        });

        // Fix map sizing after the hidden form becomes visible.

        requestAnimationFrame(function () {

            deliveryMap.invalidateSize();

            setTimeout(function () {

                deliveryMap.invalidateSize();

            }, 500);

        });

    }

    // ==========================================
    // SET DELIVERY LOCATION
    // ==========================================

    function setDeliveryLocation(lat, lng) {

        latitudeInput.value = Number(lat).toFixed(8);

        longitudeInput.value = Number(lng).toFixed(8);

        coordinatesLabel.textContent =
            "Selected location: " +
            Number(lat).toFixed(6) +
            ", " +
            Number(lng).toFixed(6);

        coordinatesLabel.style.color = "#198754";

        if (!deliveryMarker) {

            deliveryMarker = L.marker(
                [lat, lng],
                { draggable: true }
            ).addTo(deliveryMap);

            deliveryMarker.bindPopup(
                "Your delivery location"
            );

            deliveryMarker.on("dragend", function () {

                const position = deliveryMarker.getLatLng();

                setDeliveryLocation(
                    position.lat,
                    position.lng
                );

            });

        } else {

            deliveryMarker.setLatLng([lat, lng]);

        }

        deliveryMap.setView([lat, lng], 17);

    }

    // ==========================================
    // CURRENT LOCATION
    // ==========================================

    useCurrentLocationButton.addEventListener(
        "click",
        function () {

            if (!navigator.geolocation) {

                Swal.fire(
                    "Unavailable",
                    "Your browser does not support location services.",
                    "warning"
                );

                return;

            }

            useCurrentLocationButton.disabled = true;

            useCurrentLocationButton.textContent =
                "Getting Location...";

            navigator.geolocation.getCurrentPosition(

                function (position) {

                    setDeliveryLocation(
                        position.coords.latitude,
                        position.coords.longitude
                    );

                    useCurrentLocationButton.disabled = false;

                    useCurrentLocationButton.textContent =
                        "📍 Use My Current Location";

                },

                function (error) {

                    console.error("Geolocation error:", error);

                    Swal.fire(
                        "Location Unavailable",
                        "Please allow location access in your browser or select your location manually on the map.",
                        "warning"
                    );

                    useCurrentLocationButton.disabled = false;

                    useCurrentLocationButton.textContent =
                        "📍 Use My Current Location";

                },

                {

                    enableHighAccuracy: true,

                    timeout: 15000,

                    maximumAge: 0

                }

            );

        }
    );

    // ==========================================
    // RESET MAP
    // ==========================================

    resetMapButton.addEventListener("click", function () {

        latitudeInput.value = "";

        longitudeInput.value = "";

        coordinatesLabel.textContent =
            "No delivery location selected yet.";

        coordinatesLabel.style.color = "#8A7770";

        if (deliveryMarker) {

            deliveryMap.removeLayer(deliveryMarker);

            deliveryMarker = null;

        }

        deliveryMap.setView(defaultLocation, 14);

    });

    // ==========================================
    // PAYMENT METHOD
    // ==========================================

    function updatePaymentSelection() {

        document.querySelectorAll(".payment-option").forEach(option => {

            const radio = option.querySelector(
                'input[type="radio"]'
            );

            option.classList.toggle(
                "selected",
                radio.checked
            );

        });

        const payment = document.querySelector(
            'input[name="payment_method"]:checked'
        )?.value;

        const isGcash = payment === "GCash";

        gcashDetails.hidden = !isGcash;

        gcashReferenceInput.required = isGcash;

    }

    document.querySelectorAll(
        'input[name="payment_method"]'
    ).forEach(radio => {

        radio.addEventListener(
            "change",
            updatePaymentSelection
        );

    });

    // ==========================================
    // RENDER ORDER SUMMARY
    // ==========================================

    function renderSummary() {

        summaryProducts.innerHTML = "";

        let subtotalCents = 0;

        let totalQuantity = 0;

        selectedCartItems.forEach(item => {

            const priceCents =
                Math.round(Number(item.price) * 100);

            const quantity = Number(item.quantity);

            subtotalCents += priceCents * quantity;

            totalQuantity += quantity;

            const product = document.createElement("div");

            product.className = "summary-product";

            const imageWrap = document.createElement("div");

            imageWrap.className = "summary-image";

            const image = document.createElement("img");

            image.src =
                "assets/product_img/" +
                encodeURIComponent(item.img || "");

            image.alt = item.productName || "Product";

            image.onerror = function () {

                this.style.visibility = "hidden";

            };

            const quantityBadge = document.createElement("span");

            quantityBadge.textContent = quantity;

            imageWrap.append(image, quantityBadge);

            const info = document.createElement("div");

            info.className = "summary-product-info";

            const name = document.createElement("strong");

            name.textContent = item.productName || "Product";

            const each = document.createElement("small");

            each.textContent =
                "₱" + formatPrice(item.price) + " each";

            info.append(name, each);

            const lineTotal = document.createElement("strong");

            lineTotal.className = "summary-price";

            lineTotal.textContent =
                "₱" + formatPrice(
                    priceCents * quantity / 100
                );

            product.append(imageWrap, info, lineTotal);

            summaryProducts.appendChild(product);

        });

        const taxCents = Math.round(subtotalCents * 0.12);

        const totalCents = subtotalCents + taxCents;

        document.getElementById("summaryCount").textContent =
            totalQuantity;

        document.getElementById("summarySubtotal").textContent =
            "₱" + formatPrice(subtotalCents / 100);

        document.getElementById("summaryTax").textContent =
            "₱" + formatPrice(taxCents / 100);

        document.getElementById("summaryTotal").textContent =
            "₱" + formatPrice(totalCents / 100);

    }

    // ==========================================
    // LOAD SELECTED PRODUCTS
    // ==========================================

    async function loadSelectedItems() {

        try {

            if (
                !Array.isArray(selectedCartIDs) ||
                selectedCartIDs.length === 0
            ) {

                loading.hidden = true;

                emptyBox.hidden = false;

                return;

            }

            const data = await apiRequest("/cart");

            const allItems = Array.isArray(data.cart)
                ? data.cart
                : [];

            const selectedSet = new Set(
                selectedCartIDs.map(Number)
            );

            selectedCartItems = allItems.filter(item =>
                selectedSet.has(Number(item.cartID))
            );

            loading.hidden = true;

            if (
                selectedCartItems.length === 0 ||
                selectedCartItems.length !== selectedSet.size
            ) {

                emptyBox.hidden = false;

                return;

            }

            renderSummary();

            // Make the form visible before initializing Leaflet.

            form.hidden = false;

            initializeDeliveryMap();

            updatePaymentSelection();

        } catch (error) {

            console.error("Checkout loading error:", error);

            loading.hidden = true;

            errorBox.textContent = error.message;

            errorBox.hidden = false;

        }

    }

    // ==========================================
    // VALIDATE CHECKOUT INFORMATION
    // ==========================================

    function validateCheckout() {

        const fullName = fullNameInput.value.trim();

        const phone = phoneInput.value.trim();

        const email = emailInput.value.trim();

        const address = addressInput.value.trim();

        const paymentMethod = document.querySelector(
            'input[name="payment_method"]:checked'
        )?.value;

        const latitude = latitudeInput.value;

        const longitude = longitudeInput.value;

        const gcashReference =
            gcashReferenceInput.value.trim();

        if (!fullName || !phone || !email || !address) {

            Swal.fire(
                "Incomplete Information",
                "Please complete your customer information and delivery address.",
                "warning"
            );

            return false;

        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {

            Swal.fire(
                "Invalid Email",
                "Please enter a valid email address.",
                "warning"
            );

            return false;

        }

        if (!paymentMethod) {

            Swal.fire(
                "Payment Required",
                "Please select a payment method.",
                "warning"
            );

            return false;

        }

        if (paymentMethod === "GCash" && !gcashReference) {

            Swal.fire(
                "GCash Reference Required",
                "Please enter your GCash reference number.",
                "warning"
            );

            return false;

        }

        if (!latitude || !longitude) {

            Swal.fire(
                "Location Required",
                "Please select your delivery location on the map.",
                "warning"
            );

            return false;

        }

        return true;

    }

    // ==========================================
    // SHOW ORDER CONFIRMATION POPUP
    // ==========================================

    form.addEventListener("submit", function (event) {

        event.preventDefault();

        if (isProcessingOrder) return;

        if (!form.reportValidity()) return;

        if (selectedCartItems.length === 0) {

            Swal.fire(
                "No Selected Products",
                "Return to your cart and select products first.",
                "warning"
            );

            return;

        }

        if (!validateCheckout()) return;

        orderModal.show();

    });

    // ==========================================
    // CONFIRM AND PLACE ORDER
    // ==========================================

    confirmOrderButton.addEventListener("click", async function () {

        if (isProcessingOrder) return;

        if (!validateCheckout()) {

            orderModal.hide();

            return;

        }

        const fullName = fullNameInput.value.trim();

        const email = emailInput.value.trim();

        const phone = phoneInput.value.trim();

        const address = addressInput.value.trim();

        const paymentMethod = document.querySelector(
            'input[name="payment_method"]:checked'
        )?.value;

        const gcashReference =
            gcashReferenceInput.value.trim();

        const latitude = latitudeInput.value;

        const longitude = longitudeInput.value;

        isProcessingOrder = true;

        confirmOrderButton.disabled = true;

        confirmOrderButton.textContent = "Processing...";

        placeOrderButton.disabled = true;

        placeOrderButton.textContent = "Processing Order...";

        try {

            // ==================================
            // 1. SEND ORDER TO NODE.JS
            // ==================================

            const result = await apiRequest("/checkout", {

                method: "POST",

                headers: {

                    "Content-Type": "application/json"

                },

                body: JSON.stringify({

                    full_name: fullName,

                    email: email,

                    address: address,

                    phone: phone,

                    latitude: Number(latitude),

                    longitude: Number(longitude),

                    payment_method: paymentMethod,

                    gcash_reference: gcashReference,

                    selected_cart_ids: selectedCartItems.map(
                        item => Number(item.cartID)
                    )

                })

            });

            // ==================================
            // 2. VERIFY ORDER RESPONSE
            // ==================================

            if (
                !result.order ||
                !result.order.orderNumber ||
                !Array.isArray(result.items) ||
                result.items.length === 0
            ) {

                throw new Error(
                    "Order response is incomplete. Please check the server."
                );

            }

            // ==================================
            // 3. SAVE RECEIPT IN PHP SESSION
            // ==================================

            const confirmationResponse = await fetch(
                "confirmation.php",
                {

                    method: "POST",

                    credentials: "same-origin",

                    headers: {

                        "Content-Type": "application/json",

                        "Accept": "application/json"

                    },

                    body: JSON.stringify({

                        order: {

                            ...result.order,

                            full_name: fullName,

                            email: email,

                            phone: phone,

                            address: address,

                            payment_method: paymentMethod,

                            gcash_reference: gcashReference,

                            latitude: Number(latitude),

                            longitude: Number(longitude)

                        },

                        items: result.items

                    })

                }
            );

            const responseText =
                await confirmationResponse.text();

            let confirmationData;

            try {

                confirmationData = JSON.parse(responseText);

            } catch (error) {

                console.error(
                    "Confirmation PHP response:",
                    responseText
                );

                throw new Error(
                    "Unable to prepare your confirmation receipt. Check confirmation.php."
                );

            }

            if (
                !confirmationResponse.ok ||
                !confirmationData.success
            ) {

                throw new Error(
                    confirmationData.message ||
                    "Unable to save your order receipt."
                );

            }

            // ==================================
            // 4. CLEAR CHECKOUT SELECTION
            // ==================================

            try {

                await fetch(
                    "checkout.php?clear_selection=1",
                    {
                        credentials: "same-origin"
                    }
                );

            } catch (error) {

                console.error(
                    "Unable to clear checkout selection:",
                    error
                );

            }

            // ==================================
            // 5. SUCCESS MESSAGE
            // ==================================

            orderModal.hide();

            await Swal.fire({

                icon: "success",

                title: "Order Placed!",

                text: "Your order has been successfully placed.",

                confirmButtonText: "View Confirmation",

                allowOutsideClick: false

            });

            // ==================================
            // 6. REDIRECT TO CONFIRMATION
            // ==================================

            window.location.href = "confirmation.php";

        } catch (error) {

            console.error("Checkout error:", error);

            orderModal.hide();

            Swal.fire(
                "Checkout Notice",
                error.message,
                "error"
            );

            isProcessingOrder = false;

            confirmOrderButton.disabled = false;

            confirmOrderButton.textContent =
                "Yes, Place Order";

            placeOrderButton.disabled = false;

            placeOrderButton.innerHTML =
                'PLACE ORDER <span>→</span>';

        }

    });

    // ==========================================
    // INITIALIZE
    // ==========================================

    updatePaymentSelection();

    loadSelectedItems();

});

</script>

</body>
</html>
