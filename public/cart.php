
<?php
session_start();

$pageClass = "cart-page";

// REMOVE ITEM FROM CART
if (isset($_GET['remove'])) {

    $product_id = filter_var($_GET['remove'], FILTER_VALIDATE_INT);

    if ($product_id !== false && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }

    header("Location: cart.php");
    exit();
}

// UPDATE PRODUCT QUANTITY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    $product_id = filter_var(
        $_POST['product_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $quantity = filter_var(
        $_POST['quantity'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        $product_id !== false &&
        $product_id > 0 &&
        $quantity !== false &&
        $quantity > 0 &&
        isset($_SESSION['cart'][$product_id])
    ) {

        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }

    header("Location: cart.php");
    exit();
}

include('./admin/includes/header.php');
include('./admin/includes/topbar.php');

$cart = $_SESSION['cart'] ?? [];
?>

<div class="container my-5 px-4">

    <h2 class="text-center mb-5">My Shopping Cart</h2>

    <?php if (empty($cart)) { ?>

        <div class="text-center py-5" style="margin-top: 210px;">
            <h4>Your cart is empty!</h4>
            <p>Looks like you haven't added any pastries yet.</p>

            <a href="order.php" class="btn btn-dark mt-3">
                Continue Shopping
            </a>
        </div>

    <?php } else { ?>

        <div class="row">

            <div class="col-lg-8">

                <?php foreach ($cart as $id => $item) { ?>

                    <div
                        class="card cart-item-card mb-4 shadow-sm rounded-4"
                        data-price="<?php echo (float) $item['price']; ?>"
                        data-quantity="<?php echo (int) $item['quantity']; ?>"
                        tabindex="0"
                        aria-pressed="false"
                    >

                        <div class="card-body">

                            <div class="row align-items-center">

                                <!-- PRODUCT IMAGE -->
                                <div class="col-md-3">

                                    <img
                                        src="assets/product_img/<?php echo htmlspecialchars(basename($item['img']), ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="img-fluid rounded-4"
                                        style="height:120px; width:100%; object-fit:cover;"
                                    >

                                </div>

                                <!-- PRODUCT DETAILS -->
                                <div class="col-md-4">

                                    <h5>
                                        <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h5>

                                    <p class="text-muted mb-0">
                                        ₱<?php echo number_format((float) $item['price'], 2); ?>
                                    </p>

                                </div>

                                <!-- QUANTITY -->
                                <div class="col-md-3">

                                    <form method="POST"
                                          action="cart.php"
                                          class="d-flex align-items-center gap-2 quantity-form">

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?php echo (int) $id; ?>"
                                        >

                                        <input
                                            type="number"
                                            name="quantity"
                                            value="<?php echo (int) $item['quantity']; ?>"
                                            min="1"
                                            step="1"
                                            class="form-control quantity-input"
                                            style="width:75px;"
                                            required
                                        >

                                        <button
                                            type="submit"
                                            name="update"
                                            class="btn btn-outline-dark btn-sm"
                                        >
                                            Update
                                        </button>

                                    </form>

                                </div>

                                <!-- SUBTOTAL AND REMOVE -->
                                <div class="col-md-2 text-end">

                                    <p class="fw-bold product-subtotal">
                                        ₱<?php echo number_format(
                                            (float) $item['price'] * (int) $item['quantity'],
                                            2
                                        ); ?>
                                    </p>

                                    <a
                                        href="#"
                                        class="text-danger text-decoration-none"
                                        data-bs-toggle="modal"
                                        data-bs-target="#removeModal<?php echo (int) $id; ?>"
                                    >
                                        Remove
                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- REMOVE CONFIRMATION MODAL -->
                    <div
                        class="modal fade remove-modal"
                        id="removeModal<?php echo (int) $id; ?>"
                        tabindex="-1"
                        aria-hidden="true"
                    >
                        <div class="modal-dialog modal-dialog-centered">

                            <div class="modal-content">

                                <div class="modal-body text-center">

                                    <!-- Decorative corner -->
                                    <div class="modal-decoration top-decoration"></div>
                                    <div class="modal-decoration bottom-decoration"></div>

                                    <!-- Trash Icon -->
                                    <div class="remove-icon">
                                        <i class="fas fa-trash-alt"></i>
                                    </div>

                                    <!-- Heart -->
                                    <div class="remove-heart">
                                        <i class="fas fa-heart"></i>
                                    </div>

                                    <!-- Title -->
                                    <h4 class="remove-title">Remove Item?</h4>

                                    <!-- Message -->
                                    <p class="remove-message">
                                        Are you sure you want to remove
                                        <strong>
                                            <?php echo htmlspecialchars(
                                                $item['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </strong>
                                        from your cart?
                                    </p>

                                    <!-- Buttons -->
                                    <div class="remove-actions">

                                        <button
                                            type="button"
                                            class="btn keep-item-btn"
                                            data-bs-dismiss="modal"
                                        >
                                            Keep Item
                                        </button>

                                        <a
                                            href="cart.php?remove=<?php echo (int) $id; ?>"
                                            class="btn confirm-remove-btn"
                                        >
                                            Remove
                                        </a>

                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                <?php } ?>

            </div>

            <!-- ORDER SUMMARY -->
            <div class="col-lg-4">

                <div class="card shadow-sm rounded-4 p-4">

                    <h4 class="mb-4">Order Summary</h4>

                    <div class="d-flex justify-content-between mb-3">
                        <span>Selected Items</span>
                        <span id="selectedCount">0</span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal</span>
                        <span id="subtotal">₱0.00</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-4">
                        <h5>Total</h5>
                        <h5 id="total">₱0.00</h5>
                    </div>

                    <a href="checkout.php"
                       class="btn btn-dark w-100 rounded-pill"
                       id="checkoutButton"
                       aria-disabled="true">
                        Proceed to Checkout
                    </a>

                    <a href="order.php"
                       class="btn btn-outline-dark w-100 rounded-pill mt-3">
                        Continue Shopping
                    </a>

                    <small class="text-muted text-center mt-3">
                        Select the products you want to purchase.
                    </small>

                </div>

            </div>

        </div>

    <?php } ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const cards = document.querySelectorAll(".cart-item-card");

    const subtotalElement = document.getElementById("subtotal");
    const totalElement = document.getElementById("total");
    const selectedCountElement = document.getElementById("selectedCount");
    const checkoutButton = document.getElementById("checkoutButton");

    function updateSummary() {

        let total = 0;
        let selectedCount = 0;

        cards.forEach(function (card) {

            if (card.classList.contains("selected")) {

                const price = parseFloat(card.dataset.price);
                const quantity = parseInt(card.dataset.quantity, 10);

                total += price * quantity;
                selectedCount++;

            }

        });

        subtotalElement.textContent = "₱" + total.toFixed(2);
        totalElement.textContent = "₱" + total.toFixed(2);
        selectedCountElement.textContent = selectedCount;

        checkoutButton.style.pointerEvents =
            selectedCount > 0 ? "auto" : "none";

        checkoutButton.style.opacity =
            selectedCount > 0 ? "1" : "0.5";

        checkoutButton.setAttribute(
            "aria-disabled",
            selectedCount > 0 ? "false" : "true"
        );
    }

    // SELECT PRODUCT CARD
    cards.forEach(function (card) {

        card.addEventListener("click", function (event) {

            if (event.target.closest("button, a, input, form, select, textarea")) {
                return;
            }

            card.classList.toggle("selected");

            card.setAttribute(
                "aria-pressed",
                card.classList.contains("selected") ? "true" : "false"
            );

            updateSummary();
        });

        // Keyboard selection
        card.addEventListener("keydown", function (event) {

            if (event.target !== card) return;

            if (event.key === "Enter" || event.key === " ") {

                event.preventDefault();

                card.classList.toggle("selected");

                card.setAttribute(
                    "aria-pressed",
                    card.classList.contains("selected") ? "true" : "false"
                );

                updateSummary();
            }

        });

    });

    // PREVENT CHECKOUT IF NOTHING IS SELECTED
    checkoutButton.addEventListener("click", function (event) {

        if (checkoutButton.getAttribute("aria-disabled") === "true") {
            event.preventDefault();
            alert("Please select at least one product first.");
        }

    });

    updateSummary();

});
</script>
