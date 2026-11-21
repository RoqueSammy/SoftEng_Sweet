
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
include('./includes/topbar.php');
?>

<link rel="stylesheet" href="assets/css/checkout.css?v=4">

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
                                    Enter a full address or a short place name, like “Hamersons Hotel,” and we’ll find it in CDO.
                                    You can also click the map or drag the pin to adjust the location.
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

                                <div id="deliveryEstimate" class="map-description" aria-live="polite">
                                    Select your delivery location to calculate the delivery fee.
                                </div>

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

                <!-- CART TOTALS -->

                <aside class="checkout-sidebar">

                    <div class="summary-card">

                        <div class="summary-heading">

                            <div>

                                <span class="checkout-eyebrow">CART &amp; PRICING</span>

                                <h3>Your Cart</h3>

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

                        <div class="summary-row">
                            <span>Delivery Fee</span>
                            <strong id="summaryDeliveryFee">Select location</strong>
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
                            ORDER SUMMARY <span>→</span>
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

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-body review-modal-body">

                <div class="review-modal-heading">
                    <div class="remove-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div>
                        <span class="checkout-eyebrow">FINAL CHECK</span>
                        <h4 class="remove-title">Review your order</h4>
                        <p class="remove-message">Confirm the details below before placing your order.</p>
                    </div>
                </div>

                <div class="review-details-grid">
                    <section class="review-detail-card">
                        <h5><i class="fa-regular fa-user"></i> Customer information</h5>
                        <dl>
                            <div><dt>Full name</dt><dd id="reviewFullName">—</dd></div>
                            <div><dt>Phone</dt><dd id="reviewPhone">—</dd></div>
                            <div><dt>Email</dt><dd id="reviewEmail">—</dd></div>
                        </dl>
                    </section>

                    <section class="review-detail-card">
                        <h5><i class="fa-solid fa-location-dot"></i> Delivery information</h5>
                        <dl>
                            <div><dt>Delivery address</dt><dd id="reviewAddress">—</dd></div>
                            <div><dt>Distance from shop</dt><dd id="reviewDistance">—</dd></div>
                            <div><dt>Delivery fee</dt><dd id="reviewDeliveryFee">—</dd></div>
                        </dl>
                        <div class="review-map-info">
                            <i class="fa-solid fa-map-pin"></i>
                            <span id="reviewCoordinates">Map pin not selected</span>
                            <a id="reviewMapLink" href="#" target="_blank" rel="noopener">Open map</a>
                        </div>
                    </section>

                    <section class="review-detail-card review-payment-card">
                        <h5><i class="fa-regular fa-credit-card"></i> Payment method</h5>
                        <dl>
                            <div><dt>Method</dt><dd id="reviewPaymentMethod">—</dd></div>
                            <div id="reviewReferenceRow" hidden><dt>GCash reference</dt><dd id="reviewGcashReference">—</dd></div>
                        </dl>
                    </section>
                </div>

                <section class="review-items-section">
                    <div class="review-section-heading">
                        <h5>Items in this order</h5>
                        <span id="reviewItemCount">0 items</span>
                    </div>
                    <div id="reviewOrderItems" class="review-order-items"></div>
                </section>

                <section class="review-totals">
                    <div><span>Subtotal</span><strong id="reviewSubtotal">₱0.00</strong></div>
                    <div><span>VAT (12%)</span><strong id="reviewTax">₱0.00</strong></div>
                    <div><span>Delivery fee</span><strong id="reviewTotalDelivery">₱0.00</strong></div>
                    <div class="review-grand-total"><span>Total to pay</span><strong id="reviewTotal">₱0.00</strong></div>
                </section>

                <div class="remove-actions">

                    <button
                        type="button"
                        class="btn keep-item-btn"
                        data-bs-dismiss="modal"
                    >
                        Back to checkout
                    </button>

                    <button
                        type="button"
                        class="btn confirm-remove-btn"
                        id="confirmOrder"
                    >
                        Confirm and place order
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

    const API_URL = "/api";

    const selectedCartIDs = <?php
        echo json_encode(
            array_values($selectedIDs),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
    ?>;

    // Sweet! shop location (fixed delivery origin).
    // The map opens centered on the shop. Customers must select
    // their own delivery location before placing an order.

    const SHOP_LATITUDE = 8.474078;
    const SHOP_LONGITUDE = 124.644512;
    const defaultLocation = [SHOP_LATITUDE, SHOP_LONGITUDE];

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

    const deliveryEstimate =
        document.getElementById("deliveryEstimate");

    const summaryDeliveryFee =
        document.getElementById("summaryDeliveryFee");

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

    // Null means no eligible delivery location has been selected.
    let currentDeliveryFeeCents = null;
    let currentDistanceKm = null;
    let addressSearchTimer = null;
    let addressSearchController = null;

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
    // LOAD CUSTOMER PROFILE
    // ==========================================

    async function loadCustomerProfile() {

        const data = await apiRequest("/me");
        const user = data.user || {};

        fullNameInput.value = [
            user.firstName,
            user.middleName,
            user.lastName
        ].filter(Boolean).join(" ");

        emailInput.value = user.emailAddress || "";
        phoneInput.value = user.contactNumber || "";

        const savedAddress = [
            user.street,
            user.barangay,
            user.city
        ]
            .filter(value => value && String(value).trim())
            .join(", ");

        // The profile address is only a default. The customer can edit it.
        if (!addressInput.value.trim()) {
            addressInput.value = savedAddress;
        }

    }

    // ==========================================
    // REVERSE GEOCODING
    // Convert map coordinates into a readable place/address
    // ==========================================

    let reverseGeocodeController = null;
    let reverseGeocodeTimer = null;
    let lastGeocodedCoordinates = "";

    async function reverseGeocode(lat, lng) {

        const coordinatesKey =
            Number(lat).toFixed(6) + "," + Number(lng).toFixed(6);

        clearTimeout(reverseGeocodeTimer);

        reverseGeocodeTimer = setTimeout(async function () {

            try {

                if (reverseGeocodeController) {
                    reverseGeocodeController.abort();
                }

                reverseGeocodeController = new AbortController();

                coordinatesLabel.textContent =
                    "Finding the address for your selected location...";
                coordinatesLabel.style.color = "#8A7770";

                const url = new URL(
                    "https://nominatim.openstreetmap.org/reverse"
                );

                url.searchParams.set("format", "jsonv2");
                url.searchParams.set("lat", lat);
                url.searchParams.set("lon", lng);
                url.searchParams.set("zoom", "18");
                url.searchParams.set("addressdetails", "1");
                url.searchParams.set("namedetails", "1");
                url.searchParams.set("accept-language", "en");

                const response = await fetch(url.toString(), {
                    signal: reverseGeocodeController.signal,
                    headers: { "Accept": "application/json" }
                });

                if (!response.ok) {
                    throw new Error("Address lookup failed.");
                }

                const result = await response.json();

                if (!result || !result.display_name) {
                    throw new Error("No mapped address was found.");
                }

                const currentCoordinates =
                    Number(latitudeInput.value).toFixed(6) + "," +
                    Number(longitudeInput.value).toFixed(6);

                // Ignore a response if the customer has moved the pin again.
                if (currentCoordinates !== coordinatesKey) {
                    return;
                }

                const placeName =
                    result.name ||
                    (result.namedetails && result.namedetails.name) ||
                    (result.address && (
                        result.address.amenity ||
                        result.address.hotel ||
                        result.address.shop ||
                        result.address.restaurant ||
                        result.address.tourism ||
                        result.address.building ||
                        result.address.leisure ||
                        result.address.office
                    ));

                let readableAddress = result.display_name;

                if (placeName) {
                    const remaining = result.display_name
                        .split(",")
                        .map(part => part.trim())
                        .filter(Boolean)
                        .filter(part =>
                            part.toLowerCase() !== String(placeName).toLowerCase()
                        );

                    readableAddress = [placeName, ...remaining].join(", ");
                }

                // A map selection deliberately replaces the current delivery
                // address. The customer may edit it again before ordering.
                addressInput.value = readableAddress;
                lastGeocodedCoordinates = coordinatesKey;

                coordinatesLabel.textContent =
                    "Selected place: " + readableAddress;
                coordinatesLabel.style.color = "#198754";

            } catch (error) {

                if (error.name === "AbortError") {
                    return;
                }

                console.error("Reverse geocoding error:", error);

                coordinatesLabel.textContent =
                    "Location pinned, but no readable address was found. Please enter the address manually.";
                coordinatesLabel.style.color = "#dc3545";

            }

        }, 1100);

    }

    function scheduleAddressSearch(address) {
        const query = String(address || "").trim();
        clearTimeout(addressSearchTimer);
        if (addressSearchController) {
            addressSearchController.abort();
            addressSearchController = null;
        }

        if (query.length < 4) {
            coordinatesLabel.textContent = query
                ? "Enter a place name or address to search the CDO map."
                : "No delivery location selected yet.";
            coordinatesLabel.style.color = "#8A7770";
            return;
        }

        coordinatesLabel.textContent = "Searching the map for this address…";
        coordinatesLabel.style.color = "#8A7770";
        addressSearchTimer = setTimeout(() => geocodeDeliveryAddress(query), 1100);
    }

    async function geocodeDeliveryAddress(query) {
        const controller = new AbortController();
        addressSearchController = controller;

        try {
            const url = new URL("https://nominatim.openstreetmap.org/search");
            url.searchParams.set("format", "jsonv2");
            // OSM lists this local hotel as “Hamersons” (one m), while
            // customers commonly type “Hammersons”. Keep their input intact
            // and normalize the lookup text only.
            const lookupQuery = query.replace(/\bhammersons\b/gi, "Hamersons");
            const cityScopedQuery = /cagayan\s+de\s+oro/i.test(lookupQuery)
                ? lookupQuery
                : `${lookupQuery}, Cagayan de Oro`;
            url.searchParams.set("q", cityScopedQuery);
            url.searchParams.set("countrycodes", "ph");
            // Focus and bound place-name lookups to the local CDO delivery area.
            url.searchParams.set("viewbox", "124.54,8.57,124.75,8.38");
            url.searchParams.set("bounded", "1");
            url.searchParams.set("limit", "5");
            url.searchParams.set("addressdetails", "1");
            url.searchParams.set("accept-language", "en");

            const response = await fetch(url.toString(), {
                signal: controller.signal,
                headers: { "Accept": "application/json" }
            });
            if (!response.ok) throw new Error("Map address search failed.");

            const results = await response.json();
            if (addressInput.value.trim() !== query) return;

            if (!Array.isArray(results) || !results.length) {
                coordinatesLabel.textContent =
                    "Address not found. Add your barangay and city, or pin the location manually.";
                coordinatesLabel.style.color = "#dc3545";
                return;
            }

            const match = results.find(result => {
                const lat = Number(result.lat);
                const lng = Number(result.lon);
                return Number.isFinite(lat) && Number.isFinite(lng) &&
                    calculateDistance(SHOP_LATITUDE, SHOP_LONGITUDE, lat, lng) <= 10;
            });

            if (!match) {
                coordinatesLabel.textContent =
                    "That place is outside our CDO delivery area. Search for a place in Cagayan de Oro.";
                coordinatesLabel.style.color = "#dc3545";
                return;
            }

            const latitude = Number(match.lat);
            const longitude = Number(match.lon);
            if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                throw new Error("Map returned an invalid location.");
            }

            setDeliveryLocation(latitude, longitude, { fromAddressSearch: true });
            coordinatesLabel.textContent =
                `Found ${match.name || match.display_name.split(",").slice(0, 2).join(", ")} in CDO.`;
            coordinatesLabel.style.color = "#198754";
        } catch (error) {
            if (error.name === "AbortError") return;
            console.error("Delivery address search error:", error);
            if (addressInput.value.trim() === query) {
                coordinatesLabel.textContent =
                    "Map search is unavailable right now. Please pin your location manually.";
                coordinatesLabel.style.color = "#dc3545";
            }
        } finally {
            if (addressSearchController === controller) {
                addressSearchController = null;
            }
        }
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

    function setDeliveryLocation(lat, lng, options = {}) {
        const fromAddressSearch = options.fromAddressSearch === true;
        if (!fromAddressSearch) {
            clearTimeout(addressSearchTimer);
            if (addressSearchController) {
                addressSearchController.abort();
                addressSearchController = null;
            }
        }

        latitudeInput.value = Number(lat).toFixed(8);
        longitudeInput.value = Number(lng).toFixed(8);

        coordinatesLabel.textContent =
            "Selected coordinates: " +
            Number(lat).toFixed(6) + ", " +
            Number(lng).toFixed(6);
        coordinatesLabel.style.color = "#8A7770";

        if (!deliveryMarker) {

            deliveryMarker = L.marker(
                [lat, lng],
                { draggable: true }
            ).addTo(deliveryMap);

            deliveryMarker.bindPopup("Your delivery location");

            deliveryMarker.on("dragend", function () {
                const position = deliveryMarker.getLatLng();
                setDeliveryLocation(position.lat, position.lng);
            });

        } else {

            deliveryMarker.setLatLng([lat, lng]);

        }

        deliveryMap.setView([lat, lng], 18);

        // Recalculate the estimate whenever the pin changes.
        updateDeliveryEstimate();

        if (!fromAddressSearch) reverseGeocode(lat, lng);

    }

    addressInput.addEventListener("input", function () {
        clearTimeout(reverseGeocodeTimer);
        if (reverseGeocodeController) {
            reverseGeocodeController.abort();
            reverseGeocodeController = null;
        }

        // Invalidate an older map pin as soon as its address is edited.
        latitudeInput.value = "";
        longitudeInput.value = "";
        if (deliveryMarker) {
            deliveryMap.removeLayer(deliveryMarker);
            deliveryMarker = null;
        }
        currentDeliveryFeeCents = null;
        currentDistanceKm = null;
        updateDeliveryEstimate();
        scheduleAddressSearch(addressInput.value);
    });

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
        lastGeocodedCoordinates = "";

        clearTimeout(addressSearchTimer);
        if (addressSearchController) {
            addressSearchController.abort();
            addressSearchController = null;
        }

        clearTimeout(reverseGeocodeTimer);

        if (reverseGeocodeController) {
            reverseGeocodeController.abort();
        }

        coordinatesLabel.textContent =
            "No delivery location selected yet.";
        coordinatesLabel.style.color = "#8A7770";

        if (deliveryMarker) {
            deliveryMap.removeLayer(deliveryMarker);
            deliveryMarker = null;
        }

        deliveryMap.setView(defaultLocation, 14);

        currentDeliveryFeeCents = null;
        currentDistanceKm = null;
        updateDeliveryEstimate();

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
    // DELIVERY DISTANCE (HAVERSINE FORMULA)
    // ==========================================

    function calculateDistance(lat1, lon1, lat2, lon2) {

        const earthRadiusKm = 6371;

        function toRadians(degrees) {
            return degrees * Math.PI / 180;
        }

        const dLat = toRadians(lat2 - lat1);
        const dLon = toRadians(lon2 - lon1);

        const a =
            Math.sin(dLat / 2) ** 2 +
            Math.cos(toRadians(lat1)) *
            Math.cos(toRadians(lat2)) *
            Math.sin(dLon / 2) ** 2;

        const c = 2 * Math.atan2(
            Math.sqrt(a),
            Math.sqrt(1 - a)
        );

        return earthRadiusKm * c;
    }

    // ==========================================
    // DELIVERY FEE RULES
    // ==========================================

    function calculateDeliveryFee(distanceKm) {

        if (distanceKm <= 2) return 15;
        if (distanceKm <= 5) return 30;
        if (distanceKm <= 8) return 75;
        if (distanceKm <= 10) return 90;

        return null;
    }

    // ==========================================
    // UPDATE DELIVERY ESTIMATE
    // ==========================================

    function updateDeliveryEstimate() {

        const lat = Number(latitudeInput.value);
        const lng = Number(longitudeInput.value);

        if (
            latitudeInput.value === "" ||
            longitudeInput.value === "" ||
            !Number.isFinite(lat) ||
            !Number.isFinite(lng)
        ) {
            currentDistanceKm = null;
            currentDeliveryFeeCents = null;

            deliveryEstimate.textContent =
                "Select your delivery location to calculate the delivery fee.";
            deliveryEstimate.style.color = "#8A7770";
            summaryDeliveryFee.textContent = "Select location";

            renderSummary();
            return;
        }

        const distanceKm = calculateDistance(
            SHOP_LATITUDE,
            SHOP_LONGITUDE,
            lat,
            lng
        );

        const fee = calculateDeliveryFee(distanceKm);
        currentDistanceKm = distanceKm;

        if (fee === null) {
            currentDeliveryFeeCents = null;

            deliveryEstimate.textContent =
                `Your location is ${distanceKm.toFixed(2)} km away. ` +
                "Sorry, this location is outside our 10 km delivery range.";
            deliveryEstimate.style.color = "#dc3545";
            summaryDeliveryFee.textContent = "Unavailable";
        } else {
            currentDeliveryFeeCents = fee * 100;

            deliveryEstimate.textContent =
                `Distance: ${distanceKm.toFixed(2)} km | ` +
                `Delivery fee: ₱${formatPrice(fee)}`;
            deliveryEstimate.style.color = "#198754";
            summaryDeliveryFee.textContent =
                "₱" + formatPrice(fee);
        }

        renderSummary();
    }

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

            image.src = /^https?:\/\//i.test(String(item.img || ""))
                ? item.img
                : "assets/product_img/" + encodeURIComponent(item.img || "");

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

        // The browser estimate is for display only. The Node.js
        // checkout controller recalculates and validates the fee.
        const deliveryFeeCents = currentDeliveryFeeCents ?? 0;

        const totalCents =
            subtotalCents + taxCents + deliveryFeeCents;

        document.getElementById("summaryCount").textContent =
            totalQuantity;

        document.getElementById("summarySubtotal").textContent =
            "₱" + formatPrice(subtotalCents / 100);

        document.getElementById("summaryTax").textContent =
            "₱" + formatPrice(taxCents / 100);

        summaryDeliveryFee.textContent =
            currentDeliveryFeeCents === null
                ? (currentDistanceKm !== null ? "Unavailable" : "Select location")
                : "₱" + formatPrice(currentDeliveryFeeCents / 100);

        document.getElementById("summaryTotal").textContent =
            "₱" + formatPrice(totalCents / 100);

    }

    function renderOrderReview() {
        const fullName = fullNameInput.value.trim();
        const phone = phoneInput.value.trim();
        const email = emailInput.value.trim();
        const address = addressInput.value.trim();
        const latitude = Number(latitudeInput.value);
        const longitude = Number(longitudeInput.value);
        const paymentMethod = document.querySelector(
            'input[name="payment_method"]:checked'
        )?.value || "—";
        const gcashReference = gcashReferenceInput.value.trim();

        document.getElementById("reviewFullName").textContent = fullName;
        document.getElementById("reviewPhone").textContent = phone;
        document.getElementById("reviewEmail").textContent = email;
        document.getElementById("reviewAddress").textContent = address;
        document.getElementById("reviewDistance").textContent =
            `${currentDistanceKm.toFixed(2)} km from SweetClaire`;
        document.getElementById("reviewDeliveryFee").textContent =
            `₱${formatPrice(currentDeliveryFeeCents / 100)}`;
        document.getElementById("reviewCoordinates").textContent =
            `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;

        const mapLink = document.getElementById("reviewMapLink");
        mapLink.href = `https://www.openstreetmap.org/?mlat=${encodeURIComponent(latitude)}&mlon=${encodeURIComponent(longitude)}#map=17/${encodeURIComponent(latitude)}/${encodeURIComponent(longitude)}`;

        document.getElementById("reviewPaymentMethod").textContent =
            paymentMethod === "Cash" ? "Cash on Delivery" : paymentMethod;
        document.getElementById("reviewReferenceRow").hidden =
            paymentMethod !== "GCash";
        document.getElementById("reviewGcashReference").textContent =
            gcashReference || "—";

        const itemsContainer = document.getElementById("reviewOrderItems");
        itemsContainer.replaceChildren();
        let subtotalCents = 0;
        let totalQuantity = 0;

        selectedCartItems.forEach(item => {
            const quantity = Number(item.quantity);
            const priceCents = Math.round(Number(item.price) * 100);
            const lineTotalCents = priceCents * quantity;
            subtotalCents += lineTotalCents;
            totalQuantity += quantity;

            const row = document.createElement("div");
            row.className = "review-order-item";

            const image = document.createElement("img");
            image.src = /^https?:\/\//i.test(String(item.img || ""))
                ? item.img
                : `assets/product_img/${encodeURIComponent(item.img || "")}`;
            image.alt = item.productName || "Product";
            image.onerror = () => { image.style.visibility = "hidden"; };

            const details = document.createElement("div");
            details.className = "review-item-details";
            const name = document.createElement("strong");
            name.textContent = item.productName || "Product";
            const unit = document.createElement("small");
            unit.textContent = `${quantity} × ₱${formatPrice(priceCents / 100)}`;
            details.append(name, unit);

            const lineTotal = document.createElement("strong");
            lineTotal.className = "review-item-price";
            lineTotal.textContent = `₱${formatPrice(lineTotalCents / 100)}`;
            row.append(image, details, lineTotal);
            itemsContainer.appendChild(row);
        });

        const taxCents = Math.round(subtotalCents * 0.12);
        const totalCents = subtotalCents + taxCents + currentDeliveryFeeCents;
        document.getElementById("reviewItemCount").textContent =
            `${totalQuantity} ${totalQuantity === 1 ? "item" : "items"}`;
        document.getElementById("reviewSubtotal").textContent =
            `₱${formatPrice(subtotalCents / 100)}`;
        document.getElementById("reviewTax").textContent =
            `₱${formatPrice(taxCents / 100)}`;
        document.getElementById("reviewTotalDelivery").textContent =
            `₱${formatPrice(currentDeliveryFeeCents / 100)}`;
        document.getElementById("reviewTotal").textContent =
            `₱${formatPrice(totalCents / 100)}`;
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

            // Pre-fill customer details from the logged-in profile.
            // This does not save checkout edits back to the profile.
            await loadCustomerProfile();

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

            if (addressInput.value.trim().length >= 4) {
                scheduleAddressSearch(addressInput.value);
            }

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

        if (
            currentDistanceKm === null ||
            currentDeliveryFeeCents === null
        ) {

            Swal.fire(
                "Delivery Unavailable",
                currentDistanceKm !== null
                    ? "Your selected location is outside our 10 km delivery range."
                    : "Please select your delivery location again so we can calculate the delivery fee.",
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

        renderOrderReview();
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
                "Confirm and place order";

            placeOrderButton.disabled = false;

            placeOrderButton.innerHTML =
                'ORDER SUMMARY <span>→</span>';

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
