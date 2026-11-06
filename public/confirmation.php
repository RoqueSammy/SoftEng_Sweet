
<?php
session_start();

$pageClass = "checkout-page confirmation-page";

$order = $_SESSION['order_details'] ?? null;
$orderedItems = $_SESSION['ordered_items'] ?? [];

if (!$order || empty($orderedItems)) {
    header("Location: order.php");
    exit();
}

include('./admin/includes/header.php');
include('./admin/includes/topbar.php');
?>

<link rel="stylesheet" href="assets/css/checkout.css">

<div class="confirmation-container">

    <!-- PAGE HEADER -->
    <div class="checkout-heading">
    <h1>Your Sweet Order is Confirmed!</h1>
    <p>Thank you for choosing SweetClaire. Your sweet treats are one step closer!</p>

        <div class="checkout-progress">

            <div class="progress-step completed">
                <span>1</span>
                <small>Cart</small>
            </div>

            <div class="progress-line active"></div>

            <div class="progress-step completed">
                <span>2</span>
                <small>Checkout</small>
            </div>

            <div class="progress-line active"></div>

            <div class="progress-step current">
                <span>3</span>
                <small>Confirmation</small>
            </div>

        </div>
    </div>

    <!-- CONFIRMATION CARD -->
    <div class="confirmation-card">

        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <h1>Order Confirmed!</h1>

        <p class="confirmation-message">
            Thank you for ordering from SweetClaire!
            Your order has been successfully submitted.
        </p>

        <div class="order-number">
            <span>Order Number</span>
            <h3>
                <?php echo htmlspecialchars($order['order_number']); ?>
            </h3>
        </div>

        <div class="order-details">

            <div>
                <span>Order Date</span>
                <strong><?php echo htmlspecialchars($order['order_date']); ?></strong>
            </div>

            <div>
                <span>Customer Name</span>
                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
            </div>

            <div>
                <span>Phone Number</span>
                <strong><?php echo htmlspecialchars($order['phone']); ?></strong>
            </div>

            <div>
                <span>Email Address</span>
                <strong><?php echo htmlspecialchars($order['email']); ?></strong>
            </div>

            <div>
                <span>Payment Method</span>
                <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
            </div>

            <div>
                <span>Fulfillment</span>
                <strong><?php echo htmlspecialchars(ucfirst($order['fulfillment'])); ?></strong>
            </div>

            <?php if ($order['fulfillment'] === 'delivery') { ?>
                <div>
                    <span>Delivery Address</span>
                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $order['street'] . ', ' .
                            $order['barangay'] . ', ' .
                            $order['city']
                        );
                        ?>
                    </strong>
                </div>
            <?php } ?>

        </div>

        <h3 class="summary-title">Order Summary</h3>

        <?php foreach ($orderedItems as $item) { ?>

            <div class="confirmation-item">

                <div class="item-info">
                    <strong>
                        <?php echo htmlspecialchars($item['name']); ?>
                    </strong>

                    <span>
                        Quantity: <?php echo (int) $item['quantity']; ?>
                    </span>
                </div>

                <strong>
                    ₱<?php echo number_format(
                        (float) $item['price'] * (int) $item['quantity'],
                        2
                    ); ?>
                </strong>

            </div>

        <?php } ?>

        <div class="summary-row">
            <span>Subtotal</span>
            <strong>₱<?php echo number_format($order['subtotal'], 2); ?></strong>
        </div>

        <div class="summary-row">
            <span>Delivery Fee</span>
            <strong>₱<?php echo number_format($order['delivery_fee'], 2); ?></strong>
        </div>

        <div class="confirmation-total">
            <span>Total Amount</span>
            <h2>₱<?php echo number_format($order['total'], 2); ?></h2>
        </div>

        <a href="order.php" class="continue-shopping">
            Continue Shopping
        </a>

    </div>

</div>

<?php include('./admin/includes/footer.php'); ?>
