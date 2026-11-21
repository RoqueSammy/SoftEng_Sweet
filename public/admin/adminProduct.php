
<?php
session_start();

$pageClass = "products-page";

include('../admin/includes/header.php');
include_once("../../app/config/config.php");

// ==========================================
// PAGINATION
// ==========================================

$limit = 20;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$start = ($page - 1) * $limit;

// ==========================================
// TOTAL PRODUCTS
// ==========================================

$total_query = "SELECT COUNT(*) as total FROM product_item";
$total_result = mysqli_query($conn, $total_query);

if (!$total_result) {
    die("Unable to retrieve products: " . mysqli_error($conn));
}

$total_row = mysqli_fetch_assoc($total_result);

$total_products = (int)($total_row['total'] ?? 0);
$total_pages = max(1, (int)ceil($total_products / $limit));

// ==========================================
// GET PRODUCTS
// ==========================================

$product_query = "
    SELECT *
    FROM product_item
    ORDER BY id DESC
    LIMIT $start, $limit
";

$product_result = mysqli_query($conn, $product_query);

if (!$product_result) {
    die("Unable to retrieve product records: " . mysqli_error($conn));
}
?>

<div class="dashboard-layout">

    <!-- SIDEBAR -->
    <?php include('../admin/includes/sidebar.php'); ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="container-fluid py-4">

            <!-- HEADER -->
            <div class="products-header">
                <div>
                    <span class="products-eyebrow">CATALOGUE</span>
                    <h2>Products</h2>
                    <p>Manage your éclair catalogue, pricing, and available stock.</p>
                </div>
                <div class="header-actions">
                    <label class="products-search"><i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" id="searchInput" class="form-control search-input" placeholder="Search products..." aria-label="Search products">
                    </label>
                    <button class="btn btn-primary add-btn" type="button" id="toggleProductForm" aria-expanded="false">
                        <i class="fa-solid fa-plus"></i> Add product
                    </button>
                </div>
            </div>

            <section class="product-create-panel" id="productCreatePanel" hidden>
                <div class="product-create-heading">
                    <div><h3>Add a product</h3><p>Fill in the details below to publish it to your catalogue.</p></div>
                    <button class="product-close" type="button" id="closeProductForm" aria-label="Close form"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form id="productCreateForm" class="product-create-form">
                    <div class="product-form-fields">
                        <label>Product name<input class="form-control" name="productName" maxlength="255" required placeholder="e.g. Strawberry Glaze"></label>
                        <label>Price (₱)<input class="form-control" name="price" type="number" min="0.01" max="99999999.99" step="0.01" required placeholder="0.00"></label>
                        <label>Initial stock<input class="form-control" name="stock" type="number" min="0" max="2147483647" step="1" required value="0"></label>
                        <label class="product-description-field">Description<textarea class="form-control" name="productDescription" maxlength="255" rows="3" required placeholder="Describe the flavour and finish..."></textarea></label>
                        <label class="product-image-field">Product image <span class="field-hint">JPG, PNG, or WEBP · up to 3 MB</span><input class="form-control" name="image" id="productImage" type="file" accept="image/jpeg,image/png,image/webp"><img id="productImagePreview" alt="Selected product preview" hidden></label>
                    </div>
                    <div class="product-form-footer"><span>Product details can be updated any time.</span><button class="btn btn-primary" type="submit" id="saveProductBtn"><i class="fa-solid fa-check"></i> Save product</button></div>
                </form>
            </section>

            <div class="products-overview"><div><strong><?= number_format($total_products) ?></strong><span> products in catalogue</span></div><span class="products-page-note">Showing <?= number_format(min($total_products, $start + 1)) ?>–<?= number_format(min($total_products, $start + $limit)) ?> of <?= number_format($total_products) ?></span></div>

            <!-- STOCK MESSAGE -->
            <div id="stockMessage" class="mb-3"></div>

            <div class="card products-card shadow-sm border-0">

                <div class="card-body">

                    <div class="table-responsive custom-scrollbar">

                        <table class="table table-hover align-middle products-table mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                            <th>Current Stock</th>
                            <th>Manage Stock</th>
                                </tr>
                            </thead>

                            <tbody id="productsTable">

                                <?php if (mysqli_num_rows($product_result) > 0): ?>

                                    <?php while ($row = mysqli_fetch_assoc($product_result)): ?>

                                        <?php
                                            $productId = (int)$row['id'];
                                            $currentStock = (int)$row['stock'];
                                        ?>

                                        <tr>

                                            <td class="product-id-cell">
                                                <?php echo $productId; ?>
                                            </td>

                                            <td>
                                                <img
                                                    src="<?php echo htmlspecialchars(!empty($row['img']) ? (preg_match('#^https?://#i', (string)$row['img']) ? $row['img'] : '../assets/product_img/' . rawurlencode(basename($row['img']))) : '../assets/img/varieties.png', ENT_QUOTES, 'UTF-8'); ?>"
                                                    class="product-image"
                                                    alt="<?php echo htmlspecialchars($row['productName'] ?? 'Product image', ENT_QUOTES, 'UTF-8'); ?>"
                                                >
                                            </td>

                                            <td>
                                                <strong>
                                                    <?php echo htmlspecialchars($row['productName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </strong>
                                            </td>

                                            <td class="description-cell">
                                                <?php echo htmlspecialchars($row['productDescription'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <span class="product-price">
                                                    ₱<?php echo number_format((float)$row['price'], 2); ?>
                                                </span>
                                            </td>

                                            <!-- CURRENT STOCK -->
                                            <td>
                                                <span
                                                    class="badge bg-<?php echo ($currentStock <= 5) ? 'danger' : 'success'; ?> stock-display"
                                                    id="stock-<?php echo $productId; ?>"
                                                >
                                                    <?php echo $currentStock; ?>
                                                </span>
                                            </td>

                                            <!-- MANAGE STOCK -->
                                            <td style="min-width: 240px;">

                                                <div class="input-group input-group-sm mb-2">

                                                    <input
                                                        type="number"
                                                        class="form-control stock-input"
                                                        id="add-stock-<?php echo $productId; ?>"
                                                        min="1"
                                                        step="1"
                                                        placeholder="Add qty"
                                                        aria-label="Quantity to add"
                                                    >

                                                    <button
                                                        type="button"
                                                        class="btn btn-success"
                                                        onclick="addStock(<?php echo $productId; ?>)"
                                                    >
                                                        Add
                                                    </button>

                                                </div>

                                                <div class="input-group input-group-sm">

                                                    <input
                                                        type="number"
                                                        class="form-control stock-input"
                                                        id="remove-stock-<?php echo $productId; ?>"
                                                        min="1"
                                                        step="1"
                                                        placeholder="Remove qty"
                                                        aria-label="Quantity to remove"
                                                    >

                                                    <button
                                                        type="button"
                                                        class="btn btn-danger"
                                                        onclick="removeStock(<?php echo $productId; ?>)"
                                                    >
                                                        Remove
                                                    </button>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            No Products Found
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                    <!-- PAGINATION -->
                    <div class="pagination-wrapper">

                        <?php if ($page > 1): ?>

                            <a
                                href="?page=<?php echo $page - 1; ?>"
                                class="btn btn-light border pagination-btn"
                            >
                                Previous
                            </a>

                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                            <a
                                href="?page=<?php echo $i; ?>"
                                class="btn pagination-btn <?php echo ($page == $i) ? 'btn-primary text-white' : 'btn-light border'; ?>"
                            >
                                <?php echo $i; ?>
                            </a>

                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>

                            <a
                                href="?page=<?php echo $page + 1; ?>"
                                class="btn btn-light border pagination-btn"
                            >
                                Next
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

// ==========================================
// SEARCH PRODUCTS
// ==========================================

const searchInput = document.getElementById("searchInput");

searchInput.addEventListener("keyup", function () {

    const value = this.value.toLowerCase();

    const rows = document.querySelectorAll("#productsTable tr");

    rows.forEach(row => {

        row.style.display = row.innerText
            .toLowerCase()
            .includes(value)
            ? ""
            : "none";

    });

});

const productPanel = document.getElementById("productCreatePanel");
const productForm = document.getElementById("productCreateForm");
const productToggle = document.getElementById("toggleProductForm");
const productImageInput = document.getElementById("productImage");
const productImagePreview = document.getElementById("productImagePreview");

productToggle.addEventListener("click", () => {
    productPanel.hidden = !productPanel.hidden;
    productToggle.setAttribute("aria-expanded", String(!productPanel.hidden));
    if (!productPanel.hidden) productForm.elements.productName.focus();
});
document.getElementById("closeProductForm").addEventListener("click", () => {
    productPanel.hidden = true;
    productToggle.setAttribute("aria-expanded", "false");
});

productImageInput.addEventListener("change", () => {
    const file = productImageInput.files[0];
    if (!file) {
        productImagePreview.hidden = true;
        return;
    }
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 3 * 1024 * 1024) {
        showStockMessage("danger", "Choose a JPG, PNG, or WEBP image up to 3 MB.");
        productImageInput.value = "";
        productImagePreview.hidden = true;
        return;
    }
    productImagePreview.src = URL.createObjectURL(file);
    productImagePreview.hidden = false;
});

productForm.addEventListener("submit", async event => {
    event.preventDefault();
    const button = document.getElementById("saveProductBtn");
    button.disabled = true;
    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    try {
        const formData = new FormData(productForm);
        const imageFile = formData.get("image");
        const imageData = imageFile && imageFile.size
            ? await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(new Error("Could not read the selected image."));
                reader.readAsDataURL(imageFile);
            })
            : null;
        const response = await fetch("/api/admin/products", {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json", Accept: "application/json" },
            body: JSON.stringify({
                productName: formData.get("productName"),
                productDescription: formData.get("productDescription"),
                price: formData.get("price"),
                stock: formData.get("stock"),
                imageData
            })
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || "Unable to add product.");
        window.location.href = "adminProduct.php?added=1";
    } catch (error) {
        showStockMessage("danger", error.message);
        button.disabled = false;
        button.innerHTML = '<i class="fa-solid fa-check"></i> Save product';
    }
});

if (new URLSearchParams(window.location.search).get("added") === "1") {
    showStockMessage("success", "Product added to your catalogue successfully.");
    history.replaceState({}, "", window.location.pathname);
}


// ==========================================
// SHOW STOCK MESSAGE
// ==========================================

function showStockMessage(type, message) {

    const container = document.getElementById("stockMessage");

    const alert = document.createElement("div");

    alert.className = `alert alert-${type}`;

    alert.textContent = message;

    container.replaceChildren(alert);

}


// ==========================================
// UPDATE STOCK DISPLAY
// ==========================================

function updateStockDisplay(productId, newStock) {

    const stockDisplay = document.getElementById(`stock-${productId}`);

    stockDisplay.textContent = newStock;

    stockDisplay.classList.remove(
        "bg-danger",
        "bg-success"
    );

    stockDisplay.classList.add(
        newStock <= 5 ? "bg-danger" : "bg-success"
    );

}


// ==========================================
// ADD STOCK
// ==========================================

async function addStock(productId) {

    const input = document.getElementById(`add-stock-${productId}`);

    const button = input.nextElementSibling;

    const quantity = Number(input.value);

    if (!Number.isSafeInteger(quantity) || quantity < 1) {

        showStockMessage(
            "danger",
            "Please enter a valid quantity greater than zero."
        );

        input.focus();
        return;
    }

    if (!confirm(`Are you sure you want to add ${quantity} units to this product?`)) {
        return;
    }

    button.disabled = true;
    button.textContent = "Adding...";

    try {

        const response = await fetch(
            `/api/inventory/${productId}/add-stock`,
            {
                method: "POST",
                credentials: "include",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    quantity: quantity
                })
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Unable to add stock.");
        }

        const newStock = Number(data.inventory.stock);

        updateStockDisplay(productId, newStock);

        input.value = "";

        showStockMessage(
            "success",
            `${data.message} New stock: ${newStock}`
        );

    } catch (error) {

        console.error("Add stock error:", error);

        showStockMessage("danger", error.message);

    } finally {

        button.disabled = false;
        button.textContent = "Add";

    }

}


// ==========================================
// REMOVE STOCK
// ==========================================

async function removeStock(productId) {

    const input = document.getElementById(`remove-stock-${productId}`);

    const button = input.nextElementSibling;

    const stockDisplay = document.getElementById(`stock-${productId}`);

    const currentStock = Number(stockDisplay.textContent);

    const quantity = Number(input.value);

    if (!Number.isSafeInteger(quantity) || quantity < 1) {

        showStockMessage(
            "danger",
            "Please enter a valid removal quantity greater than zero."
        );

        input.focus();
        return;
    }

    if (quantity > currentStock) {

        showStockMessage(
            "danger",
            `You cannot remove ${quantity} units. Only ${currentStock} units are available.`
        );

        input.focus();
        return;
    }

    if (!confirm(`Are you sure you want to remove ${quantity} units from this product?`)) {
        return;
    }

    button.disabled = true;
    button.textContent = "Removing...";

    try {

        const response = await fetch(
            `/api/inventory/${productId}/remove-stock`,
            {
                method: "POST",
                credentials: "include",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    quantity: quantity
                })
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Unable to remove stock.");
        }

        const newStock = Number(data.inventory.stock);

        updateStockDisplay(productId, newStock);

        input.value = "";

        showStockMessage(
            "success",
            `${data.message} Remaining stock: ${newStock}`
        );

    } catch (error) {

        console.error("Remove stock error:", error);

        showStockMessage("danger", error.message);

    } finally {

        button.disabled = false;
        button.textContent = "Remove";

    }

}

</script>

</body>
</html>
