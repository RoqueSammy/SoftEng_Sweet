<?php
session_start();
$pageClass = "order-page";
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

<div class="order-banner">
  <h1>Our Pastries</h1>
</div>

<section class="order-listing">
  <div class="order-panel">

    <div class="order-toolbar">
      <form method="get">
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        <input type="search" name="q" class="form-control rounded-pill"
               placeholder="Search" value="<?= htmlspecialchars($q) ?>">
      </form>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
    });
  });

  document.getElementById("quickViewDecrease").addEventListener("click", () => {
    quantity.value = Math.max(1, (parseInt(quantity.value, 10) || 1) - 1);
  });
  document.getElementById("quickViewIncrease").addEventListener("click", () => {
    quantity.value = (parseInt(quantity.value, 10) || 1) + 1;
  });

  modalElement.addEventListener("hidden.bs.modal", () => {
    if (window.location.hash.startsWith("#product-")) {
      history.replaceState(history.state, "", window.location.pathname + window.location.search);
    }
    openedFromHistory = false;
  });

  window.addEventListener("popstate", () => {
    if (modalElement.classList.contains("show")) {
      openedFromHistory = false;
      quickView.hide();
    }
  });

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
});
</script>
