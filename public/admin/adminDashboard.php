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
    COALESCE(SUM(totalPrice), 0) as total_sales,
    COALESCE(AVG(totalPrice), 0) as average_order
FROM orders
WHERE DATE(DateCreated) = DATE(NOW())
AND status = 'completed'
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


// RECENT COMPLETED ORDERS
$recent_query = "
SELECT 
    orders.id,
    orders.totalPrice,
    orders.DateCreated,
    users.firstName,
    users.lastName,
    users.street,
    users.barangay,
    users.city
FROM orders
JOIN users 
ON users.id = orders.user_id
WHERE orders.status = 'completed'
ORDER BY orders.DateCreated DESC
LIMIT 5
";

$recent_result = mysqli_query($conn, $recent_query);

?>

<div class="dashboard-layout">

    <?php include('../admin/includes/sidebar.php'); ?>

    <div class="main-content">

        <div class="container-fluid py-4">

            <div class="dashboard-header mb-4">

                <h2>
                     Dashboard Analytics
                </h2>

                <p>
                    Sales overview and recent completed orders
                </p>

            </div>

            <div class="row g-4 mb-4">

                <div class="col-12 col-xl-4 col-md-6">

                    <div class="card dashboard-card shadow-sm border-0 h-100 daily-summary-card">

                        <div class="card-header bg-white border-0 fw-semibold">

                             Daily Sales Summary

                        </div>

                        <div class="card-body">

                            <div class="summary-item mb-4">

                                <span class="summary-label">
                                    Total Sales Today
                                </span>

                                <h3 class="summary-value text-success">

                                    ₱<?php echo number_format($total_sales, 2); ?>

                                </h3>

                            </div>

                            <div class="summary-item mb-4">

                                <span class="summary-label">
                                    Orders Today
                                </span>

                                <h4 class="fw-bold">

                                    <?php echo $total_orders; ?>

                                </h4>

                            </div>

                            <div class="summary-item mb-4">

                                <span class="summary-label">
                                    Average Order
                                </span>

                                <h5 class="fw-semibold text-primary">

                                    ₱<?php echo number_format($average_order, 2); ?>

                                </h5>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-12 col-xl-4 col-md-6">

                    <div class="card dashboard-card shadow-sm border-0 h-100">

                        <div class="card-header bg-white border-0 fw-semibold">

                             Weekly Sales

                        </div>

                        <div class="card-body">

                            <div class="chart-wrapper">

                                <canvas id="weeklyChart"></canvas>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-12 col-xl-4">

                    <div class="card dashboard-card shadow-sm border-0 h-100">

                        <div class="card-header bg-white border-0 fw-semibold">

                             Monthly Sales

                        </div>

                        <div class="card-body">

                            <div class="chart-wrapper">

                                <canvas id="monthlyChart"></canvas>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="card dashboard-card shadow-sm border-0">

                <div class="card-header bg-white border-0 fw-semibold">

                     Recent Completed Orders

                </div>

                <div class="card-body">

                    <div class="table-responsive custom-scrollbar">

                        <table class="table table-hover align-middle mb-0 recent-orders-table">

                            <thead class="table-light">

                                <tr>

                                    <th>Customer</th>
                                    <th>Address</th>
                                    <th>Location</th>
                                    <th>Total</th>
                                    <th>Date & Time</th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php if(mysqli_num_rows($recent_result) > 0): ?>

                                <?php while($row = mysqli_fetch_assoc($recent_result)): ?>

                                    <tr>

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $row['firstName'].' '.$row['lastName']
                                            );
                                            ?>

                                        </td>

                                        <td>

                                            <?php
                                            echo htmlspecialchars($row['street']);
                                            ?>

                                        </td>

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $row['barangay'].', '.$row['city']
                                            );
                                            ?>

                                        </td>

                                        <td>

                                            ₱<?php
                                            echo number_format($row['totalPrice'], 2);
                                            ?>

                                        </td>

                                        <td>

                                            <?php
                                            echo date(
                                                "F d, Y - h:i A",
                                                strtotime($row['DateCreated'])
                                            );
                                            ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="5" class="text-center py-5 text-muted">

                                        No completed orders found

                                    </td>

                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

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

            display: true

        }

    }

};

new Chart(document.getElementById('weeklyChart'), {

    type: 'line',

    data: {

        labels: <?php echo json_encode($week_labels); ?>,

        datasets: [{

            label: 'Weekly Sales',

            data: <?php echo json_encode($week_data); ?>,

            borderColor: '#3b82f6',

            backgroundColor: 'rgba(59,130,246,0.2)',

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

            backgroundColor: '#10b981'

        }]

    },

    options: commonOptions

});

</script>

</body>
</html>

