<?php 
session_start();

$pageClass = "dashboard-page";

include('../admin/includes/header.php');

include_once("../../app/config/config.php");

// WEEKLY SALES
$weekly_query = "
SELECT 
    DATE_FORMAT(DateCreated, '%W (%b %d)') as date_label,
    COALESCE(SUM(totalPrice), 0) as total
FROM orders
WHERE DATE(DateCreated) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
AND status = 'completed'
GROUP BY DATE(DateCreated)
ORDER BY DATE(DateCreated) ASC
";

$weekly_result = mysqli_query($conn, $weekly_query);

$week_labels = [];
$week_data = [];

while($row = mysqli_fetch_assoc($weekly_result)){

    $week_labels[] = $row['date_label'];

    $week_data[] = (float)$row['total'];

}


// MONTHLY SALES
$monthly_query = "
SELECT 
    DATE_FORMAT(DateCreated, '%M %Y') as month,
    COALESCE(SUM(totalPrice), 0) as total
FROM orders
WHERE DATE(DateCreated) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
AND status = 'completed'
GROUP BY YEAR(DateCreated), MONTH(DateCreated)
ORDER BY MIN(DateCreated) ASC
";

$monthly_result = mysqli_query($conn, $monthly_query);

$month_labels = [];
$month_data = [];

while($row = mysqli_fetch_assoc($monthly_result)){

    $month_labels[] = $row['month'];

    $month_data[] = (float)$row['total'];

}


// DAILY SALES SUMMARY
$daily_summary_query = "
SELECT
    COUNT(*) as total_orders,
    SUM(status = 'completed') as completed_orders,
    SUM(status IN ('pending', 'processing', 'ready_for_delivery', 'out_for_delivery')) as open_orders,
    COALESCE(SUM(CASE WHEN status = 'completed' THEN totalPrice ELSE 0 END), 0) as total_sales,
    COALESCE(AVG(CASE WHEN status = 'completed' THEN totalPrice END), 0) as average_order
FROM orders
WHERE DATE(DateCreated) = CURDATE()
";

$daily_summary_result = mysqli_query(
    $conn,
    $daily_summary_query
);

$daily_summary = mysqli_fetch_assoc(
    $daily_summary_result
);

$total_orders = $daily_summary['total_orders'] ?? 0;

$total_sales = $daily_summary['total_sales'] ?? 0;

$average_order = $daily_summary['average_order'] ?? 0;
$completed_today = (int)($daily_summary['completed_orders'] ?? 0);
$open_today = (int)($daily_summary['open_orders'] ?? 0);

$low_stock_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM product_item WHERE stock <= 5");
$low_stock_count = $low_stock_result ? (int)(mysqli_fetch_assoc($low_stock_result)['total'] ?? 0) : 0;

$fulfillment_result = mysqli_query($conn, "
    SELECT
        SUM(status = 'pending') AS pending,
        SUM(status = 'processing') AS processing,
        SUM(status = 'ready_for_delivery') AS ready_for_delivery,
        SUM(status = 'out_for_delivery') AS out_for_delivery,
        SUM(status = 'completed') AS completed
    FROM orders
");
$fulfillment = $fulfillment_result ? mysqli_fetch_assoc($fulfillment_result) : [];


// LATEST ORDERS
$recent_query = "
SELECT 
    orders.id,
    orders.COnumber,
    orders.totalPrice,
    orders.DateCreated,
    orders.status,
    orders.paymentMethod,
    orders.address,
    users.firstName,
    users.lastName
FROM orders
JOIN users 
ON users.id = orders.user_id
ORDER BY orders.DateCreated DESC
LIMIT 8
";

$recent_result = mysqli_query($conn, $recent_query);

?>

<div class="dashboard-layout">

    <?php include('../admin/includes/sidebar.php'); ?>

    <div class="main-content">

        <div class="container-fluid py-4">

            <header class="dashboard-header dashboard-page-heading">
                <div>
                    <span class="admin-eyebrow">SELLER CENTRE</span>
                    <h2>Business overview</h2>
                    <p>Today’s performance and order activity at a glance.</p>
                </div>
                <div class="dashboard-header-actions">
                    <span class="dashboard-date"><i class="fa-regular fa-calendar"></i> <?= date('l, F j, Y'); ?></span>
                    <a href="adminOrders.php" class="btn btn-primary"><i class="fa-solid fa-box me-2"></i>Manage orders</a>
                </div>
            </header>

            <section class="dashboard-kpis" aria-label="Business metrics">
                <article class="dashboard-kpi">
                    <span class="dashboard-kpi-icon"><i class="fa-solid fa-peso-sign"></i></span>
                    <span class="dashboard-kpi-label">Sales today</span>
                    <strong>₱<?= number_format((float)$total_sales, 2); ?></strong>
                    <small><?= number_format($completed_today); ?> completed orders</small>
                </article>
                <article class="dashboard-kpi">
                    <span class="dashboard-kpi-icon is-blue"><i class="fa-solid fa-receipt"></i></span>
                    <span class="dashboard-kpi-label">Orders today</span>
                    <strong><?= number_format((int)$total_orders); ?></strong>
                    <small>Average completed order ₱<?= number_format((float)$average_order, 2); ?></small>
                </article>
                <article class="dashboard-kpi">
                    <span class="dashboard-kpi-icon is-amber"><i class="fa-solid fa-truck-fast"></i></span>
                    <span class="dashboard-kpi-label">Open orders today</span>
                    <strong><?= number_format($open_today); ?></strong>
                    <small>Orders placed today still in progress</small>
                </article>
                <article class="dashboard-kpi">
                    <span class="dashboard-kpi-icon is-green"><i class="fa-solid fa-box-open"></i></span>
                    <span class="dashboard-kpi-label">Low stock</span>
                    <strong><?= number_format($low_stock_count); ?></strong>
                    <small>Products with 5 units or fewer</small>
                </article>
            </section>

            <section class="dashboard-fulfillment mb-4" aria-label="Order fulfillment overview">
                <div class="dashboard-fulfillment-heading">
                    <div><h3>Order pipeline</h3><p>Track orders through preparation and delivery.</p></div>
                    <a href="adminOrders.php" class="dashboard-text-link">View all orders <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="dashboard-fulfillment-stages">
                    <?php foreach ([
                        'pending' => ['New', 'fa-inbox'],
                        'processing' => ['Preparing', 'fa-kitchen-set'],
                        'ready_for_delivery' => ['Ready', 'fa-box'],
                        'out_for_delivery' => ['On the way', 'fa-truck'],
                        'completed' => ['Delivered', 'fa-circle-check'],
                    ] as $stage => [$label, $icon]): ?>
                        <div class="dashboard-stage"><span><i class="fa-solid <?= $icon ?>"></i></span><strong><?= number_format((int)($fulfillment[$stage] ?? 0)) ?></strong><small><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></small></div>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="dashboard-chart-grid mb-4">
                <section class="card dashboard-card">
                    <div class="card-header"><div><h3>Weekly sales</h3><p>Completed order revenue over the last 7 days.</p></div></div>
                    <div class="card-body"><div class="chart-wrapper"><canvas id="weeklyChart"></canvas></div></div>
                </section>
                <section class="card dashboard-card">
                    <div class="card-header"><div><h3>Monthly sales</h3><p>Completed order revenue over the last 6 months.</p></div></div>
                    <div class="card-body"><div class="chart-wrapper"><canvas id="monthlyChart"></canvas></div></div>
                </section>
            </div>

            <section class="card dashboard-card dashboard-recent-orders">
                <div class="card-header dashboard-section-header">
                    <div><h3>Latest orders</h3><p>New and recently updated customer orders.</p></div>
                    <a href="adminOrders.php" class="dashboard-text-link">View all <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive custom-scrollbar">
                        <table class="table table-hover align-middle mb-0 recent-orders-table">
                            <thead><tr><th>Order</th><th>Customer</th><th>Delivery address</th><th>Status</th><th>Total</th><th>Ordered</th></tr></thead>
                            <tbody>
                            <?php if($recent_result && mysqli_num_rows($recent_result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($recent_result)): ?>
                                    <?php
                                    $orderStatus = strtolower((string)($row['status'] ?? 'pending'));
                                    $statusClasses = [
                                        'pending' => 'status-pending',
                                        'processing' => 'status-processing',
                                        'ready_for_delivery' => 'status-ready',
                                        'out_for_delivery' => 'status-delivery',
                                        'completed' => 'status-completed',
                                        'cancelled' => 'status-cancelled',
                                    ];
                                    ?>
                                    <tr>
                                        <td class="dashboard-order-number">#<?= htmlspecialchars($row['COnumber'] ?? (string)$row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="dashboard-customer-name"><?= htmlspecialchars(trim(($row['firstName'] ?? '').' '.($row['lastName'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="dashboard-order-address" title="<?= htmlspecialchars($row['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($row['address'] ?? 'Address not provided', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="dashboard-order-status <?= $statusClasses[$orderStatus] ?? ''; ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $orderStatus)), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="dashboard-order-total">₱<?= number_format((float)$row['totalPrice'], 2); ?></td>
                                        <td class="dashboard-order-date"><?= date('M j, Y · g:i A', strtotime($row['DateCreated'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No orders to show yet.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const commonOptions = {

    responsive: true,

    maintainAspectRatio: false,

    plugins: {

        legend: {

            display: false

        },

        tooltip: {
            callbacks: {
                label: context => " ₱" + Number(context.raw || 0).toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            }
        }

    },

    scales: {
        x: { grid: { display: false }, ticks: { color: "#8b7770", maxRotation: 0 } },
        y: { beginAtZero: true, grid: { color: "#f3e8e4" }, ticks: { color: "#8b7770", callback: value => "₱" + Number(value).toLocaleString("en-PH") } }

    }

};

new Chart(document.getElementById('weeklyChart'), {

    type: 'line',

    data: {

        labels: <?php echo json_encode($week_labels); ?>,

        datasets: [{

            label: 'Weekly Sales',

            data: <?php echo json_encode($week_data); ?>,

            borderColor: '#d85c5c',

            backgroundColor: 'rgba(216,92,92,0.12)',

            pointBackgroundColor: '#d85c5c',

            pointBorderColor: '#ffffff',

            pointBorderWidth: 2,

            fill: true,

            tension: 0.4

        }]

    },

    options: commonOptions

});

new Chart(document.getElementById('monthlyChart'), {

    type: 'bar',

    data: {

        labels: <?php echo json_encode($month_labels); ?>,

        datasets: [{

            label: 'Monthly Sales',

            data: <?php echo json_encode($month_data); ?>,

            backgroundColor: '#d85c5c'

        }]

    },

    options: commonOptions

});

</script>

</body>
</html>

