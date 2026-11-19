
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

<body class="products-page">

<div class="dashboard-layout">

    <!-- SIDEBAR -->
    <?php include('../admin/includes/sidebar.php'); ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="container-fluid py-4">

            <!-- HEADER -->
            <div class="products-header">

                <div>
                    <h2>🛍️ Products Management</h2>
                    <p>Manage your pastry products and inventory</p>
                </div>

                <div class="header-actions">
                    <input
                        type="text"
                        id="searchInput"
                        class="form-control search-input"
                        placeholder="Search product..."
                    >
                </div>

            </div>

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

                                            <td>
                                                <?php echo $productId; ?>
                                            </td>

                                            <td>
                                                <img
                                                    src="../assets/product_img/<?php echo rawurlencode(basename($row['img'])); ?>"
                                                    class="product-image"
                                                    alt="<?php echo htmlspecialchars($row['productName'], ENT_QUOTES, 'UTF-8'); ?>"
                                                >
                                            </td>

                                            <td>
                                                <strong>
                                                    <?php echo htmlspecialchars($row['productName'], ENT_QUOTES, 'UTF-8'); ?>
                                                </strong>
                                            </td>

                                            <td class="description-cell">
                                                <?php echo htmlspecialchars($row['productDescription'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <span class="fw-bold text-primary">
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
                                                        placeholder="Quantity to add"
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
                                                        placeholder="Quantity to remove"
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
            `http://localhost:3000/inventory/${productId}/add-stock`,
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
            `http://localhost:3000/inventory/${productId}/remove-stock`,
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
