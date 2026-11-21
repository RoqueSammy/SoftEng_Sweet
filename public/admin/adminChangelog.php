<?php
session_start();

$pageClass = "changelog-page";

include('../admin/includes/header.php');
include_once('../../app/config/config.php');

$search = trim((string)($_GET['search'] ?? ''));
$safeSearch = mysqli_real_escape_string($conn, $search);
$query = "
    SELECT admin_changelog.*, users.firstName, users.lastName
    FROM admin_changelog
    LEFT JOIN users ON admin_changelog.admin_id = users.id
    WHERE ('$safeSearch' = ''
        OR CONCAT_WS(' ', users.firstName, users.lastName) LIKE '%$safeSearch%'
        OR admin_changelog.action_type LIKE '%$safeSearch%'
        OR admin_changelog.description LIKE '%$safeSearch%')
    ORDER BY admin_changelog.created_at DESC
    LIMIT 250
";
$result = mysqli_query($conn, $query);
$logQueryFailed = $result === false;
$count_query = mysqli_query($conn, "
    SELECT COUNT(*) AS total,
        SUM(action_type IN ('ORDER_UPDATE', 'ORDER_CANCELLATION')) AS order_events,
        SUM(action_type IN ('PRODUCT_CREATE', 'INVENTORY_UPDATE')) AS product_events,
        SUM(action_type IN ('CUSTOMER_UPDATE', 'CUSTOMER_STATUS', 'CUSTOMER_PASSWORD_RESET')) AS customer_events
    FROM admin_changelog
");
$activityCounts = $count_query ? mysqli_fetch_assoc($count_query) : [];
$total_logs = (int)($activityCounts['total'] ?? 0);
$order_logs = (int)($activityCounts['order_events'] ?? 0);
$product_logs = (int)($activityCounts['product_events'] ?? 0);
$customer_logs = (int)($activityCounts['customer_events'] ?? 0);

?>

<div class="dashboard-layout">

    <?php include('../admin/includes/sidebar.php'); ?>

    <div class="main-content">

        <div class="container-fluid py-4">

            <div class="activity-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

                <div>

                    <span class="admin-eyebrow">AUDIT TRAIL</span>
                    <h1 class="fw-bold mb-1">Activity log</h1>

                    <p class="text-muted mb-0">
                        A clear record of order, catalogue, and customer account changes.
                    </p>

                </div>

            </div>

            <div class="row g-3 mb-4 activity-summary">

                <!-- TOTAL LOGS -->
                <div class="col-xl-3 col-sm-6">

                    <div class="card border-0 shadow-sm rounded-4 log-card h-100">

                        <div class="card-body activity-stat">
                            <span class="activity-stat-icon"><i class="fa-solid fa-list-check"></i></span>

                            <h2 class="fw-bold mb-2">
                                <?php echo $total_logs; ?>
                            </h2>

                            <p class="text-muted mb-0">
                                Total activities
                            </p>

                        </div>

                    </div>

                </div>

                <!-- ORDER LOGS -->
                <div class="col-xl-3 col-sm-6">

                    <div class="card border-0 shadow-sm rounded-4 log-card h-100">

                        <div class="card-body activity-stat">
                            <span class="activity-stat-icon"><i class="fa-solid fa-truck-fast"></i></span>

                            <h2 class="fw-bold text-primary mb-2">
                                <?php echo $order_logs; ?>
                            </h2>

                            <p class="text-muted mb-0">
                                Order activity
                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 log-card h-100"><div class="card-body activity-stat">
                        <span class="activity-stat-icon"><i class="fa-solid fa-box-open"></i></span>
                        <h2 class="fw-bold text-primary mb-2"><?= number_format($product_logs); ?></h2>
                        <p class="text-muted mb-0">Product & inventory</p>
                    </div></div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 log-card h-100"><div class="card-body activity-stat">
                        <span class="activity-stat-icon"><i class="fa-solid fa-users"></i></span>
                        <h2 class="fw-bold text-primary mb-2"><?= number_format($customer_logs); ?></h2>
                        <p class="text-muted mb-0">Customer changes</p>
                    </div></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4 activity-search-card">

                <div class="card-body">

                    <form method="GET">

                        <div class="row g-3 align-items-center">

                            <div class="col-md-9">

                                <input
                                    type="text"
                                    name="search"
                                    class="form-control form-control-lg search-input"
                                    placeholder="Search logs..."
                                    value="<?php echo htmlspecialchars($search); ?>"
                                >

                            </div>

                            <div class="col-md-3">

                                <button
                                    type="submit"
                                    class="btn btn-primary w-100"
                                >
                                    Search
                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div><h5 class="mb-1">Recent activity</h5><small class="text-muted">Showing up to 250 latest events</small></div>
                    <span class="badge rounded-pill text-bg-light"><?= number_format($total_logs); ?> total</span>

                </div>

                <div class="table-responsive custom-scrollbar">

                    <table class="table align-middle mb-0 changelog-table">

                        <thead class="table-light">

                            <tr>

                                <th class="px-4 py-3">
                                    Admin
                                </th>

                                <th class="px-4 py-3">
                                    Action Type
                                </th>

                                <th class="px-4 py-3">
                                    Description
                                </th>

                                <th class="px-4 py-3">
                                    Date
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if($result && mysqli_num_rows($result) > 0){ ?>

                                <?php while($log = mysqli_fetch_assoc($result)){ ?>

                                    <tr>

                                        <td class="px-4 py-3 fw-semibold">

                                            <?php 
                                            
                                            echo htmlspecialchars(trim(($log['firstName'] ?? '') . ' ' . ($log['lastName'] ?? '')) ?: ('Admin #' . ($log['admin_id'] ?? '—')), ENT_QUOTES, 'UTF-8');
                                            
                                            ?>

                                        </td>

                                        <td class="px-4 py-3">

                                            <?php

                                            $badge = "secondary";

                                            if(in_array($log['action_type'], ["ORDER_UPDATE", "ORDER_CANCELLATION", "PRODUCT_CREATE", "INVENTORY_UPDATE", "CUSTOMER_UPDATE", "CUSTOMER_STATUS", "CUSTOMER_PASSWORD_RESET"], true)){
                                                $badge = "primary";
                                            }

                                            if(in_array($log['action_type'], ["DELETE", "ORDER_CANCELLATION"], true)){
                                                $badge = "danger";
                                            }

                                            if($log['action_type'] == "LOGIN"){
                                                $badge = "dark";
                                            }

                                            ?>

                                            <span class="badge bg-<?php echo $badge; ?> px-3 py-2">

                                                <?php echo htmlspecialchars(str_replace('_', ' ', $log['action_type'] ?? 'Activity'), ENT_QUOTES, 'UTF-8'); ?>

                                            </span>

                                        </td>

                                        <td class="px-4 py-3">

                                            <?php echo htmlspecialchars($log['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>

                                        </td>

                                        <td class="px-4 py-3 text-muted">

                                            <?php 
                                            
                                            echo date(
                                                'M d, Y h:i A',
                                                strtotime($log['created_at'])
                                            ); 
                                            
                                            ?>

                                        </td>

                                    </tr>

                                <?php } ?>

                            <?php } else { ?>

                                <tr>

                                    <td colspan="4" class="text-center py-5 text-muted">

                                        <?php if ($logQueryFailed): ?>Unable to load activities. Check the database connection and activity log table.<?php elseif ($search !== ''): ?>No activities match “<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>”.<?php else: ?>No activities have been recorded yet. New order, product, inventory, and customer changes will appear here.<?php endif; ?>

                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>
</body>
</html>
