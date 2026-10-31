
<?php
session_start();

require_once __DIR__ . "/../config/config.php";

// Check if customer is logged in GUEST kibali
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {

    header("Content-Type: application/json");

    echo json_encode([
        "success" => false,
        "message" => "Please log in to add products to your cart.",
        "redirect" => "/SoftEcoms/public/login.php?notice=login_required"
    ]);

    exit();
}

// Return JSON response
function sendResponse($success, $message, $cartCount = 0, $status = 200) {
    http_response_code($status);
    header("Content-Type: application/json");

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "cartCount" => $cartCount
    ]);

    exit();
}

// Check if Add to Cart button was clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    $product_uuid = trim($_POST['product_uuid'] ?? '');
    $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);

    // Validate input
    if (empty($product_uuid) || $quantity === false || $quantity < 1) {
        sendResponse(false, "Invalid product information.", 0, 400);
    }

    // Get product from database
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, uuid, productName, price, img
         FROM product_item
         WHERE uuid = ?
         LIMIT 1"
    );

    if (!$stmt) {
        sendResponse(false, "Database query failed.", 0, 500);
    }

    mysqli_stmt_bind_param($stmt, "s", $product_uuid);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$product) {
        sendResponse(false, "Product not found.", 0, 404);
    }

    // Create cart session if it does not exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $product_id = $product['id'];

    // Check if product already exists in cart
    if (isset($_SESSION['cart'][$product_id])) {

        $_SESSION['cart'][$product_id]['quantity'] += $quantity;

    } else {

        $_SESSION['cart'][$product_id] = [
            'id'       => $product['id'],
            'uuid'     => $product['uuid'],
            'name'     => $product['productName'],
            'price'    => $product['price'],
            'img'      => $product['img'],
            'quantity' => $quantity
        ];
    }

    // Calculate cart quantity
    $cartCount = 0;

    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }

    sendResponse(true, "Product added to cart!", $cartCount);
}

// If accessed directly
header("Location: ../../public/order.php");
exit();
?>
