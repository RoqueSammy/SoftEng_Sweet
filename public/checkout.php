
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$pageClass = "checkout-page";

// Get all products in the shopping cart
$allCart = $_SESSION['cart'] ?? [];

// RECEIVE SELECTED PRODUCTS FROM CART.PHP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['selected_ids']) || !is_array($_POST['selected_ids'])) {

        unset($_SESSION['checkout_selected_ids']);

        header("Location: cart.php");
        exit();
    }

    $selectedIds = array_unique(
        array_filter(
            array_map('intval', $_POST['selected_ids']),
            function ($id) {
                return $id > 0;
            }
        )
    );

    $_SESSION['checkout_selected_ids'] = $selectedIds;
}

// GET SELECTED PRODUCT IDS
$selectedIds = $_SESSION['checkout_selected_ids'] ?? [];

// BUILD CHECKOUT CART USING SELECTED PRODUCTS ONLY
$cart = [];

foreach ($selectedIds as $id) {

    if (isset($allCart[$id])) {
        $cart[$id] = $allCart[$id];
    }
}

// PREVENT CHECKOUT IF NO VALID PRODUCTS ARE SELECTED
if (empty($cart)) {

    unset($_SESSION['checkout_selected_ids']);

    header("Location: cart.php");
    exit();
}

// CALCULATE SELECTED PRODUCTS SUBTOTAL
$subtotal = 0;

foreach ($cart as $item) {

    $price = (float) ($item['price'] ?? 0);
    $quantity = (int) ($item['quantity'] ?? 0);

    $subtotal += $price * $quantity;
}

$deliveryFee = 50;

include('./admin/includes/header.php');
include('./admin/includes/topbar.php');
?>

<link rel="stylesheet" href="assets/css/checkout.css">

<div class="checkout-container">

    <!-- PAGE HEADER -->
    <div class="checkout-heading">
        <h1>Complete Your Order</h1>
        <p>Just a few more details before your sweet treats are yours.</p>

        <div class="checkout-progress">
            <div class="progress-step completed">
                <span>1</span>
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
    </div>

    <?php if (empty($cart)) { ?>

        <div class="checkout-empty">
            <div class="empty-icon">♡</div>
            <h3>Your cart is empty</h3>
            <p>Add some delicious pastries before proceeding to checkout.</p>
            <a href="order.php" class="checkout-main-btn">
                Browse Products
            </a>
        </div>

    <?php } else { ?>

    <form id="checkoutForm">

        <div class="checkout-layout">

            <!-- LEFT SIDE -->
            <div class="checkout-main">

                <!-- CUSTOMER INFORMATION -->
                <section class="checkout-card">

                    <div class="section-heading">
                        <div class="section-icon">01</div>
                        <div>
                            <h3>Customer Information</h3>
                            <p>Tell us who will receive the order.</p>
                        </div>
                    </div>

                    <div class="form-grid">

                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text"
                                name="full_name"
                                placeholder="Enter your full name"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel"
                                name="phone"
                                placeholder="09XX XXX XXXX"
                                required>
                        </div>

                        <div class="form-group full-width">
                            <label>Email Address</label>
                            <input type="email"
                                name="email"
                                placeholder="Enter your email address"
                                required>
                        </div>

                    </div>

                </section>


                <!-- FULFILLMENT -->
                <section class="checkout-card">

                    <div class="section-heading">
                        <div class="section-icon">02</div>
                        <div>
                            <h3>Delivery Method</h3>
                            <p>How would you like to receive your order?</p>
                        </div>
                    </div>

                    <div class="choice-grid">

                        <label class="choice-card selected">
                            <input type="radio"
                                name="fulfillment"
                                value="delivery"
                                checked>

                            <span class="choice-icon">🚚</span>

                            <span class="choice-content">
                                <strong>Home Delivery</strong>
                                <small>Have your order delivered to you.</small>
                            </span>

                            <span class="choice-check">✓</span>
                        </label>

                        <label class="choice-card">
                            <input type="radio"
                                name="fulfillment"
                                value="pickup">

                            <span class="choice-icon">🏪</span>

                            <span class="choice-content">
                                <strong>Store Pickup</strong>
                                <small>Pick up your order at our store.</small>
                            </span>

                            <span class="choice-check">✓</span>
                        </label>

                    </div>

                    <div id="deliveryFields">

                        <div class="form-grid address-grid">

                            <div class="form-group full-width">
                                <label>Street / House Number</label>
                                <input type="text"
                                    name="street"
                                    placeholder="House number, street name">
                            </div>

                            <div class="form-group">
                                <label>Barangay</label>
                                <input type="text"
                                    name="barangay"
                                    placeholder="Enter barangay">
                            </div>

                            <div class="form-group">
                                <label>City / Municipality</label>
                                <input type="text"
                                    name="city"
                                    placeholder="Enter city">
                            </div>

                            <div class="form-group full-width">
                                <label>Delivery Instructions <span>(Optional)</span></label>
                                <textarea name="instructions"
                                    rows="3"
                                    placeholder="Landmarks or additional instructions..."></textarea>
                            </div>

                        </div>

                    </div>

                    <div id="pickupNotice" class="pickup-notice" hidden>
                        <span>♡</span>
                        <div>
                            <strong>Store Pickup Selected</strong>
                            <p>You can collect your order at our store. Store location and pickup schedule will be provided with your order details.</p>
                        </div>
                    </div>

                </section>


                <!-- PAYMENT METHOD -->
                <section class="checkout-card">

                    <div class="section-heading">
                        <div class="section-icon">03</div>
                        <div>
                            <h3>Payment Method</h3>
                            <p>Choose how you want to pay.</p>
                        </div>
                    </div>

                    <div class="payment-options">

                        <label class="payment-option selected">
                            <input type="radio"
                                name="payment"
                                value="cod"
                                checked>

                            <span class="payment-symbol">₱</span>

                            <span class="payment-details">
                                <strong>Cash on Delivery</strong>
                                <small>Pay when your order arrives.</small>
                            </span>

                            <span class="choice-check">✓</span>
                        </label>

                        <label class="payment-option">
                            <input type="radio"
                                name="payment"
                                value="gcash">

                            <span class="gcash-symbol">G</span>

                            <span class="payment-details">
                                <strong>GCash</strong>
                                <small>Pay using your GCash account.</small>
                            </span>

                            <span class="choice-check">✓</span>
                        </label>

                    </div>

                    <div id="gcashDetails" class="gcash-details" hidden>

                        <div class="gcash-header">
                            <span class="gcash-symbol">G</span>
                            <div>
                                <strong>GCash Payment</strong>
                                <p>GCash payment instructions</p>
                            </div>
                        </div>

                        <p class="gcash-placeholder">
                            GCash number and QR code will be displayed here once the store payment details are configured.
                        </p>

                        <div class="form-group">
                            <label>GCash Reference Number</label>
                            <input type="text"
                                name="gcash_reference"
                                placeholder="Enter reference number">
                        </div>

                    </div>

                </section>

            </div>


            <!-- RIGHT SIDE: ORDER SUMMARY -->
            <aside class="checkout-sidebar">

                <div class="summary-card">

                    <div class="summary-heading">
                        <div>
                            <span class="checkout-eyebrow">YOUR ORDER</span>
                            <h3>Order Summary</h3>
                        </div>

                        <a href="cart.php">Edit Cart</a>
                    </div>

                    <div class="summary-products">

                        <?php foreach ($cart as $id => $item) {

                            $itemPrice = (float) ($item['price'] ?? 0);
                            $itemQuantity = (int) ($item['quantity'] ?? 0);
                            $itemTotal = $itemPrice * $itemQuantity;

                            $image = basename($item['img'] ?? '');
                        ?>

                            <div class="summary-product">

                                <div class="summary-image">
                                    <img
                                        src="assets/product_img/<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="<?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?>">
                                    <span><?php echo $itemQuantity; ?></span>
                                </div>

                                <div class="summary-product-info">
                                    <strong>
                                        <?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?>
                                    </strong>
                                    <small>₱<?php echo number_format($itemPrice, 2); ?> each</small>
                                </div>

                                <strong class="summary-price">
                                    ₱<?php echo number_format($itemTotal, 2); ?>
                                </strong>

                            </div>

                        <?php } ?>

                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>₱<?php echo number_format($subtotal, 2); ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <strong id="deliveryFee">
                            ₱<?php echo number_format($deliveryFee, 2); ?>
                        </strong>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-total">
                        <span>Total</span>
                        <strong id="grandTotal">
                            ₱<?php echo number_format($subtotal + $deliveryFee, 2); ?>
                        </strong>
                    </div>

                    <button type="submit" class="place-order-btn">
                        Place Order <span>→</span>
                    </button>

                    <p class="summary-note">
                        ♡ Thank you for choosing SweetClaire!
                    </p>

                </div>

            </aside>

        </div>

    </form>

    <?php } ?>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const fulfillmentOptions = document.querySelectorAll('input[name="fulfillment"]');
    const paymentOptions = document.querySelectorAll('input[name="payment"]');

    const deliveryFields = document.getElementById("deliveryFields");
    const pickupNotice = document.getElementById("pickupNotice");

    const gcashDetails = document.getElementById("gcashDetails");

    const deliveryFeeElement = document.getElementById("deliveryFee");
    const grandTotalElement = document.getElementById("grandTotal");

    const checkoutForm = document.getElementById("checkoutForm");

    if (!checkoutForm) return;

    const subtotal = <?php echo json_encode($subtotal); ?>;
    const deliveryFee = <?php echo json_encode($deliveryFee); ?>;

    function updateFulfillment() {

        const selected = document.querySelector('input[name="fulfillment"]:checked');

        const isDelivery = selected && selected.value === "delivery";

        deliveryFields.hidden = !isDelivery;
        pickupNotice.hidden = isDelivery;

        deliveryFields.querySelectorAll("input").forEach(input => {
            input.required = isDelivery;
        });

        deliveryFeeElement.textContent = isDelivery
            ? "₱" + deliveryFee.toFixed(2)
            : "₱0.00";

        const total = subtotal + (isDelivery ? deliveryFee : 0);

        grandTotalElement.textContent = "₱" + total.toFixed(2);

        document.querySelectorAll(".choice-card").forEach(card => {
            card.classList.toggle(
                "selected",
                card.querySelector("input").checked
            );
        });
    }

    function updatePayment() {

        const selected = document.querySelector('input[name="payment"]:checked');

        const isGcash = selected && selected.value === "gcash";

        gcashDetails.hidden = !isGcash;

        const referenceInput = gcashDetails.querySelector("input");

        referenceInput.required = isGcash;

        document.querySelectorAll(".payment-option").forEach(option => {
            option.classList.toggle(
                "selected",
                option.querySelector("input").checked
            );
        });
    }

    fulfillmentOptions.forEach(option => {
        option.addEventListener("change", updateFulfillment);
    });

    paymentOptions.forEach(option => {
        option.addEventListener("change", updatePayment);
    });

    checkoutForm.addEventListener("submit", function(event) {
        event.preventDefault();

        alert("Checkout design is ready! Order processing will be connected in the next step.");
    });

    updateFulfillment();
    updatePayment();

});
</script>
