<?php
session_start();
<<<<<<< HEAD

$pageClass = "product-page";

require_once __DIR__ . "/../app/config/config.php";

$product = null;

/* FIND PRODUCT USING UUID OR ID */

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

} elseif (
    isset($_GET['id']) &&
    filter_var($_GET['id'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ])
) {

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

=======
$pageClass = "order-page";
>>>>>>> second
include('includes/header.php');
include('includes/topbar.php');
include_once("../app/config/config.php");


if (!function_exists('sweetProductImage')) {
    function sweetProductImage($imageName)
    {
        $imageName = trim((string) $imageName);
        if (preg_match('#^https?://#i', $imageName)) {
            return $imageName;
        }

        $imageName = basename((string) $imageName);
        $dir = __DIR__ . "/assets/product_img/";
        $url = "/SoftEcoms/public/assets/product_img/";

        if ($imageName !== "" && is_file($dir . $imageName)) {
            return $url . rawurlencode($imageName);
        }
        return $url . "placeholder.jpg";
    }
}

$sortOptions = [
    'new'        => 'id DESC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
];
$sortLabels = [
    'new'        => 'New',
    'price_asc'  => 'Price ascending',
    'price_desc' => 'Price descending',
];

$sort = $_GET['sort'] ?? 'new';
if (!isset($sortOptions[$sort])) {
    $sort = 'new';
}
$q = trim($_GET['q'] ?? '');

$sql = "SELECT id, uuid, productName, productDescription, price, img
        FROM product_item
        WHERE productName LIKE ?
        ORDER BY " . $sortOptions[$sort] . "
        LIMIT 20";

$stmt = mysqli_prepare($conn, $sql);
$like = "%" . $q . "%";
mysqli_stmt_bind_param($stmt, "s", $like);
mysqli_stmt_execute($stmt);
$products = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<<<<<<< HEAD
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
=======
<div class="order-banner">
  <h1>Our Pastries</h1>
</div>

<section class="order-listing">
  <div class="order-panel">
>>>>>>> second

    <div class="order-toolbar">
      <form method="get">
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        <input type="search" name="q" class="form-control rounded-pill"
               placeholder="Search" value="<?= htmlspecialchars($q) ?>">
      </form>

<<<<<<< HEAD
            <div class="brand">Premium Collection</div>

            <h1 class="product-title">
                <?php echo htmlspecialchars($product['productName'], ENT_QUOTES, 'UTF-8'); ?>
            </h1>

            <div class="price-section">

                <span class="price">
                    ₱<?php echo number_format((float) $product['price'], 2); ?>
                </span>

            </div>

            <p class="product-description">
                <?php
                echo nl2br(htmlspecialchars(
                    $product['productDescription'] ?? '',
                    ENT_QUOTES,
                    'UTF-8'
                ));
                ?>
            </p>

            <form
                method="POST"
                action="http://localhost:3000/cart/add"
                id="cartForm"
            >

                <input
                    type="hidden"
                    name="product_uuid"
                    value="<?php echo htmlspecialchars($product['uuid'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >

                <input type="hidden" name="selected_size" value="L">
                <input type="hidden" name="selected_color" value="Orange">

                <label class="label" for="quantity_input">
                    Quantity
                </label>

                <div class="quantity-box">

                    <button
                        type="button"
                        id="decreaseQuantity"
                        aria-label="Decrease quantity"
                    >
                        −
                    </button>

                    <input
                        type="number"
                        name="quantity"
                        id="quantity_input"
                        value="1"
                        min="1"
                        step="1"
                        required
                    >

                    <button
                        type="button"
                        id="increaseQuantity"
                        aria-label="Increase quantity"
                    >
                        +
                    </button>

                </div>

                <button
                    type="submit"
                    name="add_to_cart"
                    class="buy-btn"
                >
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                    Add to Cart
                </button>

            </form>

        </section>

    </div>

</main>

<style>

/* FLYING PRODUCT */

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

/* CART BOUNCE */

#cartIcon.cart-bounce {
    animation: cartBounce 0.6s ease;
}

@keyframes cartBounce {

    0%, 100% {
        transform: scale(1);
    }

    30% {
        transform: scale(1.35) rotate(-12deg);
    }

    60% {
        transform: scale(0.9) rotate(8deg);
    }

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

    background: linear-gradient(
        to right,
        #F56C6C,
        #FF9B4F,
        #FFA1A1
    );

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
=======
      <div class="sort-chips">
        <?php foreach ($sortLabels as $key => $label): ?>
          <a class="sort-chip <?= $sort === $key ? 'active' : '' ?>"
             href="?sort=<?= $key ?>&q=<?= urlencode($q) ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (empty($products)): ?>
      <p class="text-center text-muted py-4">No pastries found.</p>
    <?php else: ?>
      <div class="order-grid">
        <?php foreach ($products as $p): ?>
          <button class="order-card product-modal-trigger" type="button"
                  data-product-id="<?= (int) $p['id'] ?>"
                  data-product-uuid="<?= htmlspecialchars($p['uuid'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                  data-product-name="<?= htmlspecialchars($p['productName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                  data-product-description="<?= htmlspecialchars($p['productDescription'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                  data-product-price="<?= htmlspecialchars((string) ($p['price'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>"
                  data-product-image="<?= htmlspecialchars(sweetProductImage($p['img'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                  aria-label="View <?= htmlspecialchars($p['productName'] ?? 'product', ENT_QUOTES, 'UTF-8') ?> details">
            <img src="<?= htmlspecialchars(sweetProductImage($p['img'])) ?>"
                 alt="<?= htmlspecialchars($p['productName']) ?>"
                 loading="lazy" decoding="async" fetchpriority="low">
            <h6><?= htmlspecialchars($p['productName']) ?></h6>
            <p class="price">₱<?= number_format((float) $p['price'], 2) ?></p>
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<div class="modal fade" id="productQuickView" tabindex="-1" aria-labelledby="quickViewTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content quick-view-content">
      <div class="modal-header">
        <div><span class="quick-view-eyebrow">SWEETCLAIRE PASTRIES</span><h2 class="modal-title" id="quickViewTitle">Product details</h2></div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="quick-view-layout">
          <div class="quick-view-image-wrap"><img id="quickViewImage" src="" alt=""></div>
          <div class="quick-view-details">
            <h3 id="quickViewName"></h3>
            <p class="quick-view-price" id="quickViewPrice"></p>
            <p class="quick-view-description" id="quickViewDescription"></p>
            <div id="quickViewMessage" role="status" aria-live="polite"></div>
            <label class="quick-view-quantity-label" for="quickViewQuantity">Quantity</label>
            <div class="quick-view-quantity">
              <button type="button" id="quickViewDecrease" aria-label="Decrease quantity">−</button>
              <input type="number" id="quickViewQuantity" min="1" step="1" value="1" inputmode="numeric">
              <button type="button" id="quickViewIncrease" aria-label="Increase quantity">+</button>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer quick-view-footer">
        <button type="button" class="btn quick-view-back" id="quickViewBack"><i class="fa-solid fa-arrow-left"></i> Back to pastries</button>
        <button type="button" class="btn quick-view-add" id="quickViewAdd"><i class="fa-solid fa-cart-plus"></i> Add to cart</button>
      </div>
    </div>
  </div>
</div>
>>>>>>> second

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
<<<<<<< HEAD

document.addEventListener("DOMContentLoaded", function () {

    const API_URL = "http://localhost:3000";

    const form = document.getElementById("cartForm");
    const quantityInput = document.getElementById("quantity_input");

    const decreaseButton = document.getElementById("decreaseQuantity");
    const increaseButton = document.getElementById("increaseQuantity");

    const addButton = form.querySelector(".buy-btn");

    const cartIcon =
        document.getElementById("cartIcon") ||
        document.querySelector('a.nav-icon[href*="cart.php"]');


    // ==========================================
    // QUANTITY CONTROLS
    // ==========================================

    decreaseButton.addEventListener("click", function () {

        quantityInput.value = Math.max(
            1,
            (parseInt(quantityInput.value, 10) || 1) - 1
        );

=======
document.addEventListener("DOMContentLoaded", () => {
  const modalElement = document.getElementById("productQuickView");
  const quickView = bootstrap.Modal.getOrCreateInstance(modalElement);
  const quantity = document.getElementById("quickViewQuantity");
  const message = document.getElementById("quickViewMessage");
  const addButton = document.getElementById("quickViewAdd");
  const cartIcon = document.getElementById("cartIcon");
  let selectedProduct = null;
  let openedFromHistory = false;

  document.querySelectorAll(".product-modal-trigger").forEach(card => {
    card.addEventListener("click", () => {
      selectedProduct = {
        id: card.dataset.productId,
        uuid: card.dataset.productUuid,
        name: card.dataset.productName,
        description: card.dataset.productDescription,
        price: Number(card.dataset.productPrice),
        image: card.dataset.productImage
      };
      document.getElementById("quickViewTitle").textContent = selectedProduct.name;
      document.getElementById("quickViewName").textContent = selectedProduct.name;
      document.getElementById("quickViewDescription").textContent = selectedProduct.description || "Freshly prepared by Sweetclaire.";
      document.getElementById("quickViewPrice").textContent = "₱" + selectedProduct.price.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const image = document.getElementById("quickViewImage");
      image.src = selectedProduct.image;
      image.alt = selectedProduct.name;
      quantity.value = "1";
      message.textContent = "";
      message.className = "";
      addButton.disabled = false;
      addButton.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add to cart';
      const url = new URL(window.location.href);
      url.hash = "product-" + selectedProduct.id;
      history.pushState({ quickView: true }, "", url);
      openedFromHistory = true;
      quickView.show();
>>>>>>> second
    });
  });

<<<<<<< HEAD
    increaseButton.addEventListener("click", function () {

        quantityInput.value =
            (parseInt(quantityInput.value, 10) || 1) + 1;

    });

    quantityInput.addEventListener("change", function () {

        const quantity = Number(quantityInput.value);

        if (!Number.isSafeInteger(quantity) || quantity < 1) {
            quantityInput.value = 1;
        }

    });


    // ==========================================
    // UPDATE CART BADGE
    // Always get the latest count from the API.
    // ==========================================

    async function updateCartBadge() {

        const badge = document.getElementById("cartCount");

        if (!badge) {
            return;
        }

        try {

            const response = await fetch(
                API_URL + "/cart/count",
                {
                    method: "GET",
                    credentials: "include",
                    headers: {
                        "Accept": "application/json"
                    }
                }
            );

            const data = await response.json();

            if (!response.ok || data.success === false) {
                throw new Error(
                    data.message || "Unable to get cart count."
                );
            }

            const count = Math.max(
                0,
                Number(data.cartCount) || 0
            );

            badge.textContent = count;
            badge.style.display = count > 0 ? "flex" : "none";

        } catch (error) {

            console.error("Cart badge error:", error);

        }

    }


    // ==========================================
    // ADD PRODUCT TO CART
    // ==========================================

    form.addEventListener("submit", async function (event) {

        event.preventDefault();

        const quantity = Number(quantityInput.value);

        const productUUID = form.querySelector(
            'input[name="product_uuid"]'
        ).value;

        if (!Number.isSafeInteger(quantity) || quantity < 1) {

            quantityInput.value = 1;
            quantityInput.focus();

            showMessage("Please enter a valid quantity.", true);

            return;

        }

        if (!productUUID) {

            showMessage("Product UUID is missing.", true);

            return;

        }

        if (addButton.disabled) {
            return;
        }

        addButton.disabled = true;

        const originalText = addButton.innerHTML;

        addButton.innerHTML = "Adding...";

        try {

            const response = await fetch(
                API_URL + "/cart/add",
                {
                    method: "POST",
                    credentials: "include",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        product_uuid: productUUID,
                        quantity: quantity
                    })
                }
            );

            const responseText = await response.text();

            console.log("Cart API Status:", response.status);
            console.log("Cart API Response:", responseText);

            let data;

            try {

                data = JSON.parse(responseText);

            } catch (error) {

                throw new Error(
                    "The Node.js server returned an invalid response."
                );

            }

            // LOGIN CHECK

            if (response.status === 401) {

                window.location.href =
                    data.redirect || "/SoftEcoms/public/login.php";

                return;

            }

            if (!response.ok || !data.success) {

                throw new Error(
                    data.message || "Unable to add product to cart."
                );

            }

            // ANIMATE PRODUCT

            await animateProductToCart();

            // GET THE LATEST DATABASE COUNT

            await updateCartBadge();

            // SUCCESS MESSAGE

            showMessage("✓ Added to your cart!");

            // IMPORTANT:
            // Do not redirect to cart.php.
            // Customer stays on the product page.

        } catch (error) {

            console.error("Add to cart error:", error);

            showMessage("✕ " + error.message, true);

        } finally {

            addButton.disabled = false;
            addButton.innerHTML = originalText;

        }

    });


    // ==========================================
    // FLYING IMAGE ANIMATION
    // ==========================================

    function animateProductToCart() {

        return new Promise(function (resolve) {

            const productImage =
                document.querySelector(".image-wrapper img");

            if (!productImage || !cartIcon) {

                console.error(
                    "Product image or cart icon was not found."
                );

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

            flyingImage.getBoundingClientRect();

            requestAnimationFrame(function () {

                const moveX =
                    (end.left + end.width / 2) -
                    (start.left + start.width / 2);

                const moveY =
                    (end.top + end.height / 2) -
                    (start.top + start.height / 2);

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

=======
  document.getElementById("quickViewDecrease").addEventListener("click", () => {
    quantity.value = Math.max(1, (parseInt(quantity.value, 10) || 1) - 1);
  });
  document.getElementById("quickViewIncrease").addEventListener("click", () => {
    quantity.value = (parseInt(quantity.value, 10) || 1) + 1;
  });

  modalElement.addEventListener("hidden.bs.modal", () => {
    if (window.location.hash.startsWith("#product-")) {
      history.replaceState(history.state, "", window.location.pathname + window.location.search);
>>>>>>> second
    }
    openedFromHistory = false;
  });

<<<<<<< HEAD

    // ==========================================
    // NOTIFICATION
    // ==========================================

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

=======
  window.addEventListener("popstate", () => {
    if (modalElement.classList.contains("show")) {
      openedFromHistory = false;
      quickView.hide();
>>>>>>> second
    }
  });

<<<<<<< HEAD

    // Refresh badge when product page opens.
    updateCartBadge();

=======
  // The visible Back button closes the quick view; browser Back does the same.
  document.getElementById("quickViewBack").addEventListener("click", () => {
    if (openedFromHistory && window.location.hash.startsWith("#product-")) {
      history.back();
    } else {
      quickView.hide();
    }
  });

  addButton.addEventListener("click", async () => {
    if (!selectedProduct) return;
    const amount = Number(quantity.value);
    if (!Number.isSafeInteger(amount) || amount < 1) {
      message.textContent = "Please choose a valid quantity.";
      message.className = "quick-view-error";
      return;
    }
    addButton.disabled = true;
    addButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';
    message.textContent = "";
    try {
      const response = await fetch("/api/cart/add", {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({ product_uuid: selectedProduct.uuid, quantity: amount })
      });
      const data = await response.json();
      if (response.status === 401) {
        window.location.href = data.redirect || "/SoftEcoms/public/login.php";
        return;
      }
      if (!response.ok || !data.success) throw new Error(data.message || "Could not add this product to your cart.");
      message.textContent = data.message || "Added to your cart.";
      message.className = "quick-view-success";
      if (cartIcon && Number.isFinite(Number(data.cartCount))) {
        const badge = document.getElementById("cartCount");
        if (badge) {
          badge.textContent = data.cartCount;
          badge.style.display = Number(data.cartCount) > 0 ? "flex" : "none";
        }
      }
      addButton.innerHTML = '<i class="fa-solid fa-check"></i> Added';
    } catch (error) {
      message.textContent = error.message;
      message.className = "quick-view-error";
      addButton.disabled = false;
      addButton.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Try again';
    }
  });
>>>>>>> second
});

</script>
