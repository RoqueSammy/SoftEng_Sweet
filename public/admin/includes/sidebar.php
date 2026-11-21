
<?php

// Load database configuration
require_once __DIR__ . '/../../../app/config/config.php';

// TOTAL REVENUE
$sidebar_total_sales = 0;

$sidebar_total_sales_query = "
    SELECT COALESCE(SUM(totalPrice), 0) AS total
    FROM orders
    WHERE status = 'completed'
";

$sidebar_total_sales_result = mysqli_query(
    $conn,
    $sidebar_total_sales_query
);

if ($sidebar_total_sales_result) {
    $sales_row = mysqli_fetch_assoc($sidebar_total_sales_result);
    $sidebar_total_sales = $sales_row['total'] ?? 0;
}


// TOTAL COMPLETED ORDERS
$sidebar_total_orders = 0;

$sidebar_total_orders_query = "
    SELECT COUNT(*) AS count
    FROM orders
    WHERE status = 'completed'
";

$sidebar_total_orders_result = mysqli_query(
    $conn,
    $sidebar_total_orders_query
);

if ($sidebar_total_orders_result) {
    $orders_row = mysqli_fetch_assoc($sidebar_total_orders_result);
    $sidebar_total_orders = $orders_row['count'] ?? 0;
}

?>

<!-- ADMIN SIDEBAR -->
<aside class="admin-sidebar-wrapper">

    <div class="side-panel">

        <!-- HEADER -->
        <div class="panel-header">

            <a class="admin-brand" href="adminDashboard.php"><span class="admin-brand-mark">S</span><span><strong>Sweetclaire</strong><small>Seller Centre</small></span></a>

            <p>Management Dashboard</p>

        </div>


        <!-- NAVIGATION -->
        <nav class="nav-menu">

            <a href="adminDashboard.php" class="nav-item <?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'adminDashboard.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span>
                <span>Dashboard</span>
            </a>

            <a href="adminOrders.php" class="nav-item <?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'adminOrders.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-solid fa-box"></i></span>
                <span>Orders</span>
            </a>

            <a href="adminProduct.php" class="nav-item <?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'adminProduct.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-solid fa-tag"></i></span>
                <span>Products</span>
            </a>

            <a href="adminCustomer.php" class="nav-item <?= in_array(basename($_SERVER['SCRIPT_NAME'] ?? ''), ['adminCustomer.php','adminUserprofile.php'], true) ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
                <span>Customers</span>
            </a>

            <a href="adminChangelog.php" class="nav-item <?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'adminChangelog.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                <span>ChangeLog</span>
            </a>

            <a href="adminContact.php" class="nav-item <?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'adminContact.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-solid fa-envelope"></i></span>
                <span>Contact Messages</span>
            </a>

        </nav>


        <!-- STATISTICS -->
        <div class="stats-panel">

            <div class="stat-item">

                <div class="stat-label">
                    Total Revenue
                </div>

                <div class="stat-value">
                    ₱<?php echo number_format((float)$sidebar_total_sales, 2); ?>
                </div>

            </div>


            <div class="stat-item">

                <div class="stat-label">
                    Completed Orders
                </div>

                <div class="stat-value">
                    <?php echo number_format((int)$sidebar_total_orders); ?>
                    <small>orders</small>
                </div>

            </div>

        </div>

<!-- LOGOUT -->
<div class="logout-section">

    <form id="logoutForm">

        <button type="submit" class="logout-btn">
            Logout
        </button>

    </form>

</div>

<script>
document.getElementById("logoutForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    try {
        const response = await fetch("/api/logout", {
            method: "POST",
            credentials: "include"
        });

        if (response.ok) {
            // Redirect after successfully logging out
            window.location.href = "/SoftEcoms/public/login.php";
        } else {
            alert("Logout failed. Please try again.");
        }

    } catch (error) {
        console.error("Logout error:", error);
        alert("Unable to connect to the server.");
    }
});
</script>
</aside>
