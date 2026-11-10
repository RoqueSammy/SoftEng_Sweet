
<?php
session_start();

$pageClass = "checkout-page";

/*
|--------------------------------------------------------------------------
| RECEIVE RECEIPT FROM CHECKOUT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json; charset=utf-8');

    try {

        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (
            !is_array($input) ||
            empty($input['order']) ||
            empty($input['items']) ||
            !is_array($input['items'])
        ) {
            throw new Exception("Invalid receipt information.");
        }

        $order = $input['order'];
        $items = $input['items'];

        if (empty($order['orderNumber'])) {
            throw new Exception("Order number is missing.");
        }

        if (empty($items)) {
            throw new Exception("No ordered products found.");
        }

        // SAVE ORDER DETAILS
        $_SESSION['order_details'] = [
            'order_number' => $order['orderNumber'],
            'order_date' => $order['order_date'] ?? date('Y-m-d H:i:s'),

            'full_name' => $order['full_name'] ?? '',
            'phone' => $order['phone'] ?? '',
            'email' => $order['email'] ?? '',

            'payment_method' => $order['payment_method'] ?? 'Cash',
            'fulfillment' => $order['fulfillment'] ?? 'delivery',

            'street' => $order['street'] ?? '',
            'barangay' => $order['barangay'] ?? '',
            'city' => $order['city'] ?? '',

            'subtotal' => (float)($order['subtotal'] ?? 0),
            'delivery_fee' => (float)($order['delivery_fee'] ?? 0),
            'total' => (float)($order['total'] ?? $order['totalPrice'] ?? 0),

            'status' => $order['status'] ?? 'pending'
        ];

        // SAVE ORDERED PRODUCTS
        $orderedItems = [];

        foreach ($items as $item) {

            $orderedItems[] = [
                'name' => $item['name'] ?? 'Product',
                'quantity' => (int)($item['quantity'] ?? 0),
                'price' => (float)($item['price'] ?? 0),
                'image' => $item['image'] ?? ''
            ];

        }

        $_SESSION['ordered_items'] = $orderedItems;

        echo json_encode([
            'success' => true,
            'message' => 'Receipt successfully prepared.'
        ]);

        exit;

    } catch (Throwable $error) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => $error->getMessage()
        ]);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| DISPLAY CONFIRMATION PAGE
|--------------------------------------------------------------------------
*/

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

        <span class="checkout-eyebrow">
            SWEET! • ORDER COMPLETED
        </span>

        <h1>Your Sweet Order is Confirmed!</h1>

        <p>
            Thank you for choosing SweetClaire.
            Your sweet treats are one step closer!
        </p>

        <div class="checkout-progress">

            <div class="progress-step completed">
                <span>✓</span>
                <small>Cart</small>
            </div>

            <div class="progress-line active"></div>

            <div class="progress-step completed">
                <span>✓</span>
                <small>Checkout</small>
            </div>

            <div class="progress-line active"></div>

            <div class="progress-step current">
                <span>✓</span>
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
                <?= htmlspecialchars($order['order_number']) ?>
            </h3>

        </div>

        <div class="order-details">

            <div>
                <span>Order Date</span>
                <strong>
                    <?= htmlspecialchars($order['order_date']) ?>
                </strong>
            </div>

            <div>
                <span>Customer Name</span>
                <strong>
                    <?= htmlspecialchars($order['full_name']) ?>
                </strong>
            </div>

            <div>
                <span>Phone Number</span>
                <strong>
                    <?= htmlspecialchars($order['phone']) ?>
                </strong>
            </div>

            <div>
                <span>Email Address</span>
                <strong>
                    <?= htmlspecialchars($order['email']) ?>
                </strong>
            </div>

            <div>
                <span>Payment Method</span>
                <strong>
                    <?= htmlspecialchars($order['payment_method']) ?>
                </strong>
            </div>

            <div>
                <span>Fulfillment</span>
                <strong>
                    <?= htmlspecialchars(ucfirst($order['fulfillment'])) ?>
                </strong>
            </div>

            <?php if ($order['fulfillment'] === 'delivery') { ?>

                <div>
                    <span>Delivery Address</span>

                    <strong>
                        <?= htmlspecialchars($order['street']) ?>
                    </strong>
                </div>

            <?php } ?>

        </div>

        <h3 class="summary-title">Order Summary</h3>

        <?php foreach ($orderedItems as $item) { ?>

            <div class="confirmation-item">

                <div class="item-info">

                    <strong>
                        <?= htmlspecialchars($item['name']) ?>
                    </strong>

                    <span>
                        Quantity: <?= (int)$item['quantity'] ?>
                    </span>

                </div>

                <strong>
                    ₱<?= number_format(
                        (float)$item['price'] * (int)$item['quantity'],
                        2
                    ) ?>
                </strong>

            </div>

        <?php } ?>

        <div class="summary-row">
            <span>Subtotal</span>

            <strong>
                ₱<?= number_format($order['subtotal'], 2) ?>
            </strong>
        </div>

        <div class="summary-row">
            <span>Delivery Fee</span>

            <strong>
                ₱<?= number_format($order['delivery_fee'], 2) ?>
            </strong>
        </div>

        <div class="confirmation-total">

            <span>Total Amount</span>

            <h2>
                ₱<?= number_format($order['total'], 2) ?>
            </h2>

            </div>

        <a href="order.php" class="continue-shopping">
            Continue Shopping
        </a>

    </div>

</div>

<?php include('./admin/includes/footer.php'); ?>
