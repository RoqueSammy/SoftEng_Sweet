
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

include('includes/header.php');
include('includes/topbar.php');
?>

<main class="product-page">
    <div class="product-container" style="margin-top: 60px;">

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


<style>
/* Flying product */
.flying-product {
    position: fixed !important;
    z-index: 999999 !important;
    object-fit: cover;
    pointer-events: none;
    border-radius: 15px;
    will-change: transform, opacity, border-radius;
    transition:
        transform 0.8s cubic-bezier(0.22, 1, 0.36, 1),
        opacity 0.8s ease,
        border-radius 0.8s ease;
}
/* Cart bounce */
#cartIcon.cart-bounce {
    animation: cartBounce 0.6s ease;
}

@keyframes cartBounce {
    0%, 100% { transform: scale(1); }
    30% { transform: scale(1.35) rotate(-12deg); }
    60% { transform: scale(0.9) rotate(8deg); }
}

/* SUCCESS NOTIFICATION */
.cart-toast {
    position: fixed;
    z-index: 1000000;
    top: 160px;
    left: 50%;
    bottom: auto;
    transform: translate(-50%, -20px);
    opacity: 0;
    padding: 14px 25px;
    border-radius: 30px;
    color: white;
    background: linear-gradient(to right, #F56C6C, #FF9B4F, #FFA1A1);
    box-shadow: 0 5px 15px rgba(245, 108, 108, .3);
    font-weight: 600;
    transition: all .3s ease;
    pointer-events: none;
}

.cart-toast.show {
    opacity: 1;
    transform: translate(-50%, 0);
}

.cart-toast.error {
    background: #B94040;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("cartForm");
    const quantityInput = document.getElementById("quantity_input");
    const decreaseButton = document.getElementById("decreaseQuantity");
    const increaseButton = document.getElementById("increaseQuantity");
    const addButton = form.querySelector(".buy-btn");

    // Find the cart icon
    const cartIcon =
        document.getElementById("cartIcon") ||
        document.querySelector('a.nav-icon[href*="cart.php"]');

    // Quantity controls
    decreaseButton.addEventListener("click", function () {
        quantityInput.value = Math.max(
            1,
            (parseInt(quantityInput.value, 10) || 1) - 1
        );
    });

    increaseButton.addEventListener("click", function () {
        quantityInput.value =
            (parseInt(quantityInput.value, 10) || 1) + 1;
    });

    quantityInput.addEventListener("change", function () {
        const quantity = parseInt(quantityInput.value, 10);

        if (!Number.isInteger(quantity) || quantity < 1) {
            quantityInput.value = 1;
        }
    });

    // Add product to cart
    form.addEventListener("submit", async function (event) {

        event.preventDefault();

        const quantity = Number(quantityInput.value);

        if (!Number.isInteger(quantity) || quantity < 1) {
            quantityInput.value = 1;
            quantityInput.focus();
            return;
        }

        if (addButton.disabled) return;

        addButton.disabled = true;

        const originalText = addButton.innerHTML;
        addButton.innerHTML = "Adding...";

        try {

            // Start flying product animation
            const animation = animateProductToCart();

            const formData = new FormData(form);
            formData.append("add_to_cart", "1");

            const response = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                },
                credentials: "same-origin"
            });

            const data = await response.json();

            // If user is not logged in, redirect to login
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || "Unable to add product.");
            }

            await animation;

            // Update cart count
            const badge = document.getElementById("cartCount");

            if (badge) {
                badge.textContent = data.cartCount;
                badge.style.display = data.cartCount > 0 ? "flex" : "none";
            }

            showMessage("✓ Added to your cart!");

            // Automatically open cart page
            setTimeout(function () {
                window.location.href = "/SoftEcoms/public/cart.php";
            }, 700);

        } catch (error) {

            showMessage("✕ " + error.message, true);

        } finally {

            addButton.disabled = false;
            addButton.innerHTML = originalText;

        }
    });

    // Flying image animation
    function animateProductToCart() {

        return new Promise(function (resolve) {

            const productImage =
                document.querySelector(".image-wrapper img");

            if (!productImage || !cartIcon) {
                console.error("Product image or cart icon was not found.");
                resolve();
                return;
            }

            const start = productImage.getBoundingClientRect();
            const end = cartIcon.getBoundingClientRect();

            const flyingImage = productImage.cloneNode(true);

            flyingImage.classList.add("flying-product");

            Object.assign(flyingImage.style, {
                left: start.left + "px",
                top: start.top + "px",
                width: start.width + "px",
                height: start.height + "px"
            });

            document.body.appendChild(flyingImage);

            // Force browser to register starting position
            flyingImage.getBoundingClientRect();

            requestAnimationFrame(function () {

                const moveX = (end.left + end.width / 2) - (start.left + start.width / 2);
                const moveY = (end.top + end.height / 2) - (start.top + start.height / 2);

                flyingImage.style.transform =
                    `translate(${moveX}px, ${moveY}px) scale(0.12)`;

                flyingImage.style.opacity = "0.3";
                flyingImage.style.borderRadius = "50%";
            });

            setTimeout(function () {

                flyingImage.remove();

                cartIcon.classList.remove("cart-bounce");
                void cartIcon.offsetWidth;
                cartIcon.classList.add("cart-bounce");

                resolve();

            }, 800);
        });
    }

    // Notification
    function showMessage(message, isError = false) {

        const toast = document.createElement("div");

        toast.className = "cart-toast";

        if (isError) {
            toast.classList.add("error");
        }

        toast.textContent = message;

        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add("show");
        });

        setTimeout(function () {
            toast.classList.remove("show");

            setTimeout(function () {
                toast.remove();
            }, 300);

        }, 2200);
    }

});
</script>
