
<?php
session_start();

$pageClass = "product-page";

// Load database configuration before page includes.
require_once __DIR__ . "/../app/config/config.php";

// Find product using UUID first, or ID as a fallback.
$product = null;

if (isset($_GET['uuid']) && trim($_GET['uuid']) !== '') {
    $uuid = trim($_GET['uuid']);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, uuid, productName, productDescription, price, img
         FROM product_item
         WHERE uuid = ?
         LIMIT 1"
    );

    if (!$stmt) {
        http_response_code(500);
        exit("Unable to prepare the product query.");
    }

    mysqli_stmt_bind_param($stmt, "s", $uuid);

} elseif (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
])) {
    $id = (int) $_GET['id'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, uuid, productName, productDescription, price, img
         FROM product_item
         WHERE id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        http_response_code(500);
        exit("Unable to prepare the product query.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

} else {
    http_response_code(400);
    exit("Invalid product selection.");
}

if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    exit("Unable to load this product.");
}

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) {
    http_response_code(404);
    exit("Product not found.");
}

include __DIR__ . "/admin/includes/header.php";
include __DIR__ . "/admin/includes/topbar.php";
?>

<main class="product-page">
    <div class="product-container">

        <section class="product-image-panel">
            <div class="image-wrapper">
                <img
                    src="assets/product_img/<?php echo rawurlencode(basename($product['img'] ?? '')); ?>"
                    alt="<?php echo htmlspecialchars($product['productName'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>
        </section>

        <section class="product-details">

            <div class="brand">Premium Collection</div>

            <h1 class="product-title">
                <?php echo htmlspecialchars($product['productName'], ENT_QUOTES, 'UTF-8'); ?>
            </h1>

            <div class="price-section">
                <span class="price">
                    ₱<?php echo number_format((float) $product['price'], 2); ?>
                </span>
            </div>

            <p class="product-description"><?php
                echo nl2br(htmlspecialchars(
                    $product['productDescription'] ?? '',
                    ENT_QUOTES,
                    'UTF-8'
                ));
            ?></p>

            <form
                method="POST"
                action="../app/controllers/cartController.php"
                id="cartForm"
            >
                <input
                    type="hidden"
                    name="product_uuid"
                    value="<?php echo htmlspecialchars($product['uuid'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >

                <input type="hidden" name="selected_size" value="L">
                <input type="hidden" name="selected_color" value="Orange">

                <label class="label" for="quantity_input">Quantity</label>

                <div class="quantity-box">
                    <button type="button" id="decreaseQuantity" aria-label="Decrease quantity">−</button>

                    <input
                        type="number"
                        name="quantity"
                        id="quantity_input"
                        value="1"
                        min="1"
                        step="1"
                        required
                    >

                    <button type="button" id="increaseQuantity" aria-label="Increase quantity">+</button>
                </div>

                <button type="submit" name="add_to_cart" class="buy-btn">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                    Add to Cart
                </button>
            </form>

        </section>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const quantityInput = document.getElementById("quantity_input");
    const decreaseButton = document.getElementById("decreaseQuantity");
    const increaseButton = document.getElementById("increaseQuantity");
    const cartForm = document.getElementById("cartForm");

    decreaseButton.addEventListener("click", function () {
        const quantity = parseInt(quantityInput.value, 10) || 1;
        quantityInput.value = Math.max(1, quantity - 1);
    });

    increaseButton.addEventListener("click", function () {
        const quantity = parseInt(quantityInput.value, 10) || 1;
        quantityInput.value = quantity + 1;
    });

    quantityInput.addEventListener("change", function () {
        const quantity = parseInt(quantityInput.value, 10);

        if (!Number.isInteger(quantity) || quantity < 1) {
            quantityInput.value = 1;
        }
    });

    cartForm.addEventListener("submit", function (event) {
        const quantity = Number(quantityInput.value);

        if (!Number.isInteger(quantity) || quantity < 1) {
            event.preventDefault();
            quantityInput.value = 1;
            quantityInput.focus();
        }
    });
});
</script>



<?php include __DIR__ . "/admin/includes/footer.php"; ?>