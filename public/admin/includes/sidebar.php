
<?php

include_once("../../app/config/config.php");


$sidebar_total_sales_query = "
SELECT SUM(totalPrice) as total
FROM orders
WHERE status = 'completed'
";

$sidebar_total_sales_result = mysqli_query(
    $conn,
    $sidebar_total_sales_query
);

$sidebar_total_sales =
mysqli_fetch_assoc($sidebar_total_sales_result)['total'] ?? 0;



$sidebar_total_orders_query = "
SELECT COUNT(*) as count
FROM orders
WHERE status = 'completed'
";

$sidebar_total_orders_result = mysqli_query(
    $conn,
    $sidebar_total_orders_query
);

$sidebar_total_orders =
mysqli_fetch_assoc($sidebar_total_orders_result)['count'] ?? 0;

?>

<div class="admin-sidebar-wrapper">

    <div class="side-panel">

        <!-- HEADER -->
        <div class="panel-header">

            <h2>
                <span>📊</span> Admin Panel
            </h2>

            <?php if (isset($_SESSION['user_id'])) { ?>

                <p>
                    Hello <?php echo $_SESSION['authUser']['username']; ?>
                </p>

            <?php } ?>

        </div>

        <!-- NAVIGATION -->
        <div class="nav-menu">

            <a href="index.php" class="nav-item">
                <span class="nav-icon">📈</span>
                <span>Dashboard</span>
            </a>

            <a href="orders.php" class="nav-item">
                <span class="nav-icon">📦</span>
                <span>Orders</span>
            </a>

            <a href="adminProduct.php" class="nav-item">
                <span class="nav-icon">🏷️</span>
                <span>Products</span>
            </a>

            <a href="customers.php" class="nav-item">
                <span class="nav-icon">👥</span>
                <span>Customers</span>
            </a>

            <a href="changelog.php" class="nav-item">
                <span class="nav-icon">📝</span>
                <span>ChangeLog</span>
            </a>

        </div>

        <!-- STATS -->
        <div class="stats-panel">

            <div class="stat-item">

                <div class="stat-label">
                    💰 Total Revenue
                </div>

                <div class="stat-value">
                    ₱<?php echo number_format($sidebar_total_sales, 2); ?>
                </div>

            </div>

            <div class="stat-item">

                <div class="stat-label">
                    📦 Completed Orders
                </div>

                <div class="stat-value">
                    <?php echo number_format($sidebar_total_orders); ?>
                    <small>orders</small>
                </div>

            </div>

        </div>

        <div class="logout-section">

            <form action="/E-commerce/app/controllers/adminController.php" method="POST">

                <button type="submit" name="logout" class="btn btn-danger">
                    Logout
                </button>

            </form>

        </div>

    </div>

</div>
