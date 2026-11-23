
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

            <h2>
                <span>📊</span>
                Admin Panel
            </h2>

            <p>Management Dashboard</p>

        </div>


        <!-- NAVIGATION -->
        <nav class="nav-menu">

            <a href="adminDashboard.php" class="nav-item">
                <span class="nav-icon">📈</span>
                <span>Dashboard</span>
            </a>

            <a href="adminOrders.php" class="nav-item">
                <span class="nav-icon">📦</span>
                <span>Orders</span>
            </a>

            <a href="adminProduct.php" class="nav-item">
                <span class="nav-icon">🏷️</span>
                <span>Products</span>
            </a>

            <a href="adminCustomers.php" class="nav-item">
                <span class="nav-icon">👥</span>
                <span>Customers</span>
            </a>

            <a href="adminOrders.php" class="nav-item">
                <span class="nav-icon">📝</span>
                <span>ChangeLog</span>
            </a>

            <a href="contact_messages.php" class="nav-item">
                <span class="nav-icon">✉️</span>
                <span>Contact Messages</span>
            </a>

        </nav>


        <!-- STATISTICS -->
        <div class="stats-panel">

            <div class="stat-item">

                <div class="stat-label">
                    💰 Total Revenue
                </div>

                <div class="stat-value">
                    ₱<?php echo number_format((float)$sidebar_total_sales, 2); ?>
                </div>

            </div>


            <div class="stat-item">

                <div class="stat-label">
                    📦 Completed Orders
                </div>

                <div class="stat-value">
                    <?php echo number_format((int)$sidebar_total_orders); ?>
                    <small>orders</small>
                </div>

            </div>

        </div>


        <!-- LOGOUT -->
        <div class="logout-section">

            <form action="/api/logout" method="POST">

                <button type="submit" class="logout-btn">
                    Logout
                </button>

            </form>

        </div>

    </div>

</aside>
