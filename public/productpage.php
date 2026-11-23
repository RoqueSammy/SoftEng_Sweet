
<?php
session_start();

$pageClass = "order-page";

include('includes/header.php');
include('includes/topbar.php');
include_once("../app/config/config.php");

/* ==========================================
   PRODUCT IMAGE HELPER
========================================== */

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

/* ==========================================
   SORT OPTIONS
========================================== */

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

/* ==========================================
   FETCH PRODUCTS
========================================== */

$sql = "SELECT id, uuid, productName, productDescription, price, img, stock
        FROM product_item
        WHERE productName LIKE ?
        ORDER BY " . $sortOptions[$sort] . "
        LIMIT 20";

$stmt = mysqli_prepare($conn, $sql);

$like = "%" . $q . "%";

mysqli_stmt_bind_param($stmt, "s", $like);
mysqli_stmt_execute($stmt);

$products = mysqli_fetch_all(
    mysqli_stmt_get_result($stmt),
    MYSQLI_ASSOC
);

mysqli_stmt_close($stmt);
?>

<!-- ==========================================
     PRODUCT BANNER
========================================== -->

<div class="order-banner">
    <h1>Our Pastries</h1>
</div>

<!-- ==========================================
     PRODUCT LISTING
========================================== -->

<section class="order-listing">
    <div class="order-panel">

        <div class="order-toolbar">

            <form method="get">

                <input
                    type="hidden"
                    name="sort"
                    value="<?= htmlspecialchars($sort) ?>"
                >

                <input
                    type="search"
                    name="q"
                    class="form-control rounded-pill"
                    placeholder="Search"
                    value="<?= htmlspecialchars($q) ?>"
                >

            </form>

            <div class="sort-chips">

                <?php foreach ($sortLabels as $key => $label): ?>

                    <a
                        class="sort-chip <?= $sort === $key ? 'active' : '' ?>"
                        href="?sort=<?= $key ?>&q=<?= urlencode($q) ?>"
                    >
                        <?= $label ?>
                    </a>

                <?php endforeach; ?>

            </div>

        </div>

        <?php if (empty($products)): ?>

            <p class="text-center text-muted py-4">
                No pastries found.
            </p>

        <?php else: ?>

            <div class="order-grid">

                <?php foreach ($products as $p): ?>

                    <button
                        class="order-card product-modal-trigger"
                        type="button"

                        data-product-id="<?= (int) $p['id'] ?>"

                        data-product-uuid="<?= htmlspecialchars(
                            $p['uuid'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"

                        data-product-name="<?= htmlspecialchars(
                            $p['productName'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"

                        data-product-description="<?= htmlspecialchars(
                            $p['productDescription'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"

                        data-product-price="<?= htmlspecialchars(
                            (string) ($p['price'] ?? '0'),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"

                        data-product-stock="<?= (int) ($p['stock'] ?? 0) ?>"

                        data-product-image="<?= htmlspecialchars(
                            sweetProductImage($p['img'] ?? ''),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"

                        aria-label="View <?= htmlspecialchars(
                            $p['productName'] ?? 'product',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?> details"
                    >

                        <img
                            src="<?= htmlspecialchars(sweetProductImage($p['img'] ?? '')) ?>"
                            alt="<?= htmlspecialchars($p['productName'] ?? 'Product') ?>"
                            loading="lazy"
                            decoding="async"
                            fetchpriority="low"
                        >

                        <h6>
                            <?= htmlspecialchars($p['productName'] ?? '') ?>
                        </h6>

                        <p class="price">
                            ₱<?= number_format((float) $p['price'], 2) ?>
                        </p>

                    </button>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</section>

<!-- ==========================================
     PRODUCT QUICK VIEW MODAL
========================================== -->

<div
    class="modal fade"
    id="productQuickView"
    tabindex="-1"
    aria-labelledby="quickViewTitle"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">

        <div class="modal-content quick-view-content">

            <!-- MODAL HEADER -->

            <div class="modal-header">

                <div>

                    <span class="quick-view-eyebrow">
                        SWEETCLAIRE PASTRIES
                    </span>

                    <h2 class="modal-title" id="quickViewTitle">
                        Product details
                    </h2>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>

            <!-- MODAL BODY -->

            <div class="modal-body">

                <div class="quick-view-layout">

                    <!-- PRODUCT IMAGE -->

                    <div class="quick-view-image-wrap">

                        <img
                            id="quickViewImage"
                            src=""
                            alt=""
                        >

                    </div>

                    <!-- PRODUCT DETAILS -->

                    <div class="quick-view-details">

                        <h3 id="quickViewName"></h3>

                        <p
                            class="quick-view-price"
                            id="quickViewPrice"
                        ></p>

                        <p
                            class="quick-view-description"
                            id="quickViewDescription"
                        ></p>

                        <!-- AVAILABLE STOCK -->

                        <p
                            id="quickViewStock"
                            class="small fw-bold mb-3"
                        ></p>

                        <!-- MESSAGE: ERRORS ONLY -->

                        <div
                            id="quickViewMessage"
                            role="status"
                            aria-live="polite"
                        ></div>

                        <!-- QUANTITY -->

                        <label
                            class="quick-view-quantity-label"
                            for="quickViewQuantity"
                        >
                            Quantity
                        </label>

                        <div class="quick-view-quantity">

                            <button
                                type="button"
                                id="quickViewDecrease"
                                aria-label="Decrease quantity"
                            >
                                −
                            </button>

                            <input
                                type="number"
                                id="quickViewQuantity"
                                min="1"
                                step="1"
                                value="1"
                                inputmode="numeric"
                            >

                            <button
                                type="button"
                                id="quickViewIncrease"
                                aria-label="Increase quantity"
                            >
                                +
                            </button>

                        </div>

                    </div>

                </div>

            </div>

            <!-- MODAL FOOTER -->

            <div class="modal-footer quick-view-footer">

                <button
                    type="button"
                    class="btn quick-view-add"
                    id="quickViewAdd"
                >
                    <i class="fa-solid fa-cart-plus"></i>
                    Add to cart
                </button>

            </div>

        </div>

    </div>

</div>

<!-- ==========================================
     BOOTSTRAP
========================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const modalElement = document.getElementById("productQuickView");

    if (!modalElement || typeof bootstrap === "undefined") {
        return;
    }

    const quickView = bootstrap.Modal.getOrCreateInstance(modalElement);

    const quantity = document.getElementById("quickViewQuantity");
    const message = document.getElementById("quickViewMessage");
    const addButton = document.getElementById("quickViewAdd");
    const cartIcon = document.getElementById("cartIcon");
    const stockDisplay = document.getElementById("quickViewStock");

    const decreaseButton = document.getElementById("quickViewDecrease");
    const increaseButton = document.getElementById("quickViewIncrease");

    let selectedProduct = null;
    let openedFromHistory = false;

    /* ==========================================
       TIKTOK-STYLE ADD TO CART ANIMATION
    ========================================== */

    function animateAddToCart() {

        const productImage = document.getElementById("quickViewImage");

        if (!productImage || !cartIcon) {
            return;
        }

        const imageRect = productImage.getBoundingClientRect();
        const cartRect = cartIcon.getBoundingClientRect();

        // Create flying product image

        const flyingImage = document.createElement("img");

        flyingImage.src = productImage.src;
        flyingImage.className = "flying-cart-image";

        flyingImage.style.left = imageRect.left + "px";
        flyingImage.style.top = imageRect.top + "px";

        document.body.appendChild(flyingImage);

        // Calculate destination

        const startX = imageRect.left;
        const startY = imageRect.top;

        const endX = cartRect.left + cartRect.width / 2 - 42.5;
        const endY = cartRect.top + cartRect.height / 2 - 42.5;

        const moveX = endX - startX;
        const moveY = endY - startY;

        // Animate image toward cart

        const animation = flyingImage.animate(
            [
                {
                    transform: "translate(0, 0) scale(1)",
                    opacity: 1
                },
                {
                    transform: `translate(${moveX * 0.5}px, ${moveY * 0.5 - 80}px) scale(0.7)`,
                    opacity: 0.9
                },
                {
                    transform: `translate(${moveX}px, ${moveY}px) scale(0.12)`,
                    opacity: 0
                }
            ],
            {
                duration: 750,
                easing: "cubic-bezier(0.45, 0, 0.55, 1)",
                fill: "forwards"
            }
        );

        // Remove flying image and bounce cart icon

        animation.onfinish = () => {

            flyingImage.remove();

            cartIcon.animate(
                [
                    { transform: "scale(1)" },
                    { transform: "scale(1.3)" },
                    { transform: "scale(0.9)" },
                    { transform: "scale(1.1)" },
                    { transform: "scale(1)" }
                ],
                {
                    duration: 450,
                    easing: "ease-out"
                }
            );

        };

        // Remove previous toast if present

        const oldToast = document.querySelector(".cart-success-toast");

        if (oldToast) {
            oldToast.remove();
        }

        // Create centered success notification

        const toast = document.createElement("div");

        toast.className = "cart-success-toast";

        toast.innerHTML = `
            <i class="fa-solid fa-circle-check"></i>
            <span>Added to your cart!</span>
        `;

        document.body.appendChild(toast);

        // Automatically hide notification

        setTimeout(() => {

            toast.classList.add("hide");

            toast.addEventListener(
                "animationend",
                () => toast.remove(),
                { once: true }
            );

        }, 2200);

    }


    /* ==========================================
       OPEN PRODUCT QUICK VIEW
    ========================================== */

    document.querySelectorAll(".product-modal-trigger").forEach(card => {

        card.addEventListener("click", () => {

            selectedProduct = {
                id: card.dataset.productId,
                uuid: card.dataset.productUuid,
                name: card.dataset.productName,
                description: card.dataset.productDescription,
                price: Number(card.dataset.productPrice),
                stock: Math.max(0, Number(card.dataset.productStock) || 0),
                image: card.dataset.productImage
            };

            // PRODUCT DETAILS

            document.getElementById("quickViewTitle").textContent =
                selectedProduct.name;

            document.getElementById("quickViewName").textContent =
                selectedProduct.name;

            document.getElementById("quickViewDescription").textContent =
                selectedProduct.description || "Freshly prepared by Sweetclaire.";

            document.getElementById("quickViewPrice").textContent =
                "₱" + selectedProduct.price.toLocaleString("en-PH", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

            // PRODUCT IMAGE

            const image = document.getElementById("quickViewImage");

            image.src = selectedProduct.image;
            image.alt = selectedProduct.name;

            // AVAILABLE STOCK

            if (selectedProduct.stock <= 0) {

                stockDisplay.textContent = "Out of stock";

                stockDisplay.className =
                    "small fw-bold text-danger mb-3";

            } else {

                stockDisplay.textContent =
                    `In stock: ${selectedProduct.stock}`;

                stockDisplay.className =
                    "small fw-bold text-success mb-3";

            }

            // RESET QUANTITY

            quantity.value = "1";
            quantity.min = "1";
            quantity.max = String(selectedProduct.stock);

            quantity.disabled = selectedProduct.stock <= 0;

            decreaseButton.disabled = selectedProduct.stock <= 0;
            increaseButton.disabled = selectedProduct.stock <= 0;

            // RESET MESSAGE

            message.textContent = "";
            message.className = "";

            // ADD TO CART BUTTON

            if (selectedProduct.stock <= 0) {

                addButton.disabled = true;
                addButton.innerHTML = "Out of stock";

            } else {

                addButton.disabled = false;

                addButton.innerHTML =
                    '<i class="fa-solid fa-cart-plus"></i> Add to cart';

            }

            // URL HISTORY

            const url = new URL(window.location.href);

            url.hash = "product-" + selectedProduct.id;

            history.pushState({ quickView: true }, "", url);

            openedFromHistory = true;

            quickView.show();

        });

    });


    /* ==========================================
       DECREASE QUANTITY
    ========================================== */

    decreaseButton.addEventListener("click", () => {

        if (!selectedProduct || selectedProduct.stock <= 0) return;

        const current = parseInt(quantity.value, 10) || 1;

        quantity.value = Math.max(1, current - 1);

    });


    /* ==========================================
       INCREASE QUANTITY
    ========================================== */

    increaseButton.addEventListener("click", () => {

        if (!selectedProduct || selectedProduct.stock <= 0) return;

        const current = parseInt(quantity.value, 10) || 1;

        if (current < selectedProduct.stock) {
            quantity.value = current + 1;
        }

    });


    /* ==========================================
       VALIDATE MANUAL QUANTITY INPUT
    ========================================== */

    quantity.addEventListener("input", () => {

        if (!selectedProduct) return;

        let amount = parseInt(quantity.value, 10);

        if (!Number.isFinite(amount)) return;

        if (amount > selectedProduct.stock) {
            quantity.value = selectedProduct.stock;
        }

        if (amount < 1) {
            quantity.value = 1;
        }

    });


    /* ==========================================
       CLOSE MODAL
    ========================================== */

    modalElement.addEventListener("hidden.bs.modal", () => {

        if (window.location.hash.startsWith("#product-")) {

            history.replaceState(
                history.state,
                "",
                window.location.pathname + window.location.search
            );

        }

        openedFromHistory = false;

    });


    /* ==========================================
       BROWSER BACK BUTTON
    ========================================== */

    window.addEventListener("popstate", () => {

        if (modalElement.classList.contains("show")) {

            openedFromHistory = false;

            quickView.hide();

        }

    });


    /* ==========================================
       VISIBLE BACK BUTTON
    ========================================== */

    document.getElementById("quickViewBack")?.addEventListener("click", () => {

        if (
            openedFromHistory &&
            window.location.hash.startsWith("#product-")
        ) {

            history.back();

        } else {

            quickView.hide();

        }

    });


    /* ==========================================
       ADD TO CART
    ========================================== */

    addButton.addEventListener("click", async () => {

        if (!selectedProduct) return;

        const amount = Number(quantity.value);

        // VALIDATE QUANTITY

        if (!Number.isSafeInteger(amount) || amount < 1) {

            message.textContent = "Please choose a valid quantity.";
            message.className = "quick-view-error";

            return;

        }

        // VALIDATE STOCK

        if (selectedProduct.stock <= 0) {

            message.textContent = "This product is currently out of stock.";
            message.className = "quick-view-error";

            return;

        }

        if (amount > selectedProduct.stock) {

            message.textContent =
                `Only ${selectedProduct.stock} item(s) are currently available.`;

            message.className = "quick-view-error";

            return;

        }

        // DISABLE BUTTON WHILE ADDING

        addButton.disabled = true;

        addButton.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';

        message.textContent = "";

        try {

            const response = await fetch("/api/cart/add", {

                method: "POST",

                credentials: "include",

                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json"
                },

                body: JSON.stringify({
                    product_uuid: selectedProduct.uuid,
                    quantity: amount
                })

            });

            const data = await response.json();

            // LOGIN CHECK

            if (response.status === 401) {

                window.location.href =
                    data.redirect || "/SoftEcoms/public/login.php";

                return;

            }

            // CHECK API RESPONSE

            if (!response.ok || !data.success) {

                throw new Error(
                    data.message || "Could not add this product to your cart."
                );

            }

            // CLEAR SUCCESS MESSAGE FROM PRODUCT MODAL

            message.textContent = "";
            message.className = "";

            // RUN CENTERED TIKTOK-STYLE ANIMATION

            animateAddToCart();

            // UPDATE CART BADGE

            if (cartIcon && Number.isFinite(Number(data.cartCount))) {

                const badge = document.getElementById("cartCount");

                if (badge) {

                    badge.textContent = data.cartCount;

                    badge.style.display =
                        Number(data.cartCount) > 0 ? "flex" : "none";

                }

            }

            // UPDATE BUTTON

            addButton.innerHTML =
                '<i class="fa-solid fa-check"></i> Added';

            // Allow another add after a short delay

            setTimeout(() => {

                addButton.disabled = false;

                addButton.innerHTML =
                    '<i class="fa-solid fa-cart-plus"></i> Add to cart';

            }, 1500);

        } catch (error) {

            message.textContent = error.message;
            message.className = "quick-view-error";

            addButton.disabled = false;

            addButton.innerHTML =
                '<i class="fa-solid fa-cart-plus"></i> Try again';

        }

    });

});
</script>
