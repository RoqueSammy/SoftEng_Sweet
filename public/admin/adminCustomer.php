
<?php

session_start();

$pageClass = "customers-page";

include_once("../../app/config/config.php");

// ==========================================
// HELPER
// ==========================================

function e($value)
{
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

// ==========================================
// PAGINATION
// ==========================================

$limit = 20;

$page = max(1, (int)($_GET['page'] ?? 1));

// ==========================================
// SEARCH AND FILTERS
// ==========================================

$search = trim($_GET['search'] ?? '');

$status = strtolower(trim($_GET['status'] ?? 'all'));

$allowedStatuses = ['all', 'active', 'inactive'];

if (!in_array($status, $allowedStatuses, true)) {
    $status = 'all';
}

// ==========================================
// SORTING
// ==========================================

$sort = $_GET['sort'] ?? 'newest';

$sortOptions = [
    'newest' => 'u.id DESC',
    'oldest' => 'u.id ASC',
    'name_asc' => 'u.firstName ASC, u.lastName ASC',
    'name_desc' => 'u.firstName DESC, u.lastName DESC'
];

$orderBy = $sortOptions[$sort] ?? $sortOptions['newest'];

// ==========================================
// CUSTOMER FILTER CONDITIONS
// ==========================================

$where = ["u.role = 'customer'"];

$params = [];

if ($status === 'active') {

    $where[] = "LOWER(COALESCE(u.accountStatus, '')) = 'active'";

} elseif ($status === 'inactive') {

    $where[] = "LOWER(COALESCE(u.accountStatus, '')) <> 'active'";

}

// SEARCH
if ($search !== '') {

    $where[] = "(
        CONCAT_WS(' ', u.firstName, u.middleName, u.lastName) LIKE ?
        OR u.emailAddress LIKE ?
        OR u.username LIKE ?
        OR u.contactNumber LIKE ?
        OR CAST(u.id AS CHAR) LIKE ?
    )";

    $searchValue = "%{$search}%";

    $params = array_merge(
        $params,
        [
            $searchValue,
            $searchValue,
            $searchValue,
            $searchValue,
            $searchValue
        ]
    );
}

$whereSQL = implode(' AND ', $where);

// ==========================================
// DASHBOARD STATISTICS
// ==========================================

$statsQuery = "
    SELECT
        COUNT(*) AS total_customers,

        SUM(
            CASE
                WHEN LOWER(COALESCE(accountStatus, '')) = 'active'
                THEN 1
                ELSE 0
            END
        ) AS active_customers,

        SUM(
            CASE
                WHEN LOWER(COALESCE(accountStatus, '')) <> 'active'
                THEN 1
                ELSE 0
            END
        ) AS inactive_customers

    FROM users

    WHERE role = 'customer'
";

$statsResult = mysqli_query($conn, $statsQuery);

if (!$statsResult) {
    die("Unable to retrieve customer statistics.");
}

$stats = mysqli_fetch_assoc($statsResult);

$totalCustomers = (int)($stats['total_customers'] ?? 0);
$activeCustomers = (int)($stats['active_customers'] ?? 0);
$inactiveCustomers = (int)($stats['inactive_customers'] ?? 0);

// TOTAL ORDERS
$orderStatsQuery = "
    SELECT COUNT(*) AS total_orders
    FROM orders
";

$orderStatsResult = mysqli_query($conn, $orderStatsQuery);

if (!$orderStatsResult) {
    die("Unable to retrieve order statistics.");
}

$orderStats = mysqli_fetch_assoc($orderStatsResult);

$totalOrders = (int)($orderStats['total_orders'] ?? 0);

// ==========================================
// TOTAL FILTERED CUSTOMERS
// ==========================================

$countQuery = "
    SELECT COUNT(*) AS total

    FROM users u

    WHERE $whereSQL
";

$countStmt = mysqli_prepare($conn, $countQuery);

if (!$countStmt) {
    die("Unable to prepare customer count query.");
}

if (!empty($params)) {
    $countStmt->execute($params);
} else {
    $countStmt->execute();
}

$countResult = $countStmt->get_result();

$countRow = $countResult->fetch_assoc();

$filteredCustomers = (int)($countRow['total'] ?? 0);

$countStmt->close();

// ==========================================
// PAGINATION CALCULATION
// ==========================================

$totalPages = max(1, (int)ceil($filteredCustomers / $limit));

$page = min($page, $totalPages);

$start = ($page - 1) * $limit;

// ==========================================
// CUSTOMER RECORDS
// ==========================================

$customerQuery = "
    SELECT

        u.id,
        u.uuid,
        u.firstName,
        u.middleName,
        u.lastName,
        u.emailAddress,
        u.username,
        u.contactNumber,
        u.street,
        u.barangay,
        u.city,
        u.profile,
        u.role,
        u.accountStatus,
        u.dateCreated,

        COALESCE(order_stats.total_orders, 0) AS total_orders,

        COALESCE(order_stats.total_spent, 0) AS total_spent

    FROM users u

    LEFT JOIN (

        SELECT

            user_id,

            COUNT(*) AS total_orders,

            SUM(
                CASE
                    WHEN status = 'completed'
                    THEN totalPrice
                    ELSE 0
                END
            ) AS total_spent

        FROM orders

        GROUP BY user_id

    ) AS order_stats

        ON order_stats.user_id = u.id

    WHERE $whereSQL

    ORDER BY $orderBy

    LIMIT $limit OFFSET $start
";

$customerStmt = mysqli_prepare($conn, $customerQuery);

if (!$customerStmt) {
    die("Unable to prepare customer records query.");
}

if (!empty($params)) {
    $customerStmt->execute($params);
} else {
    $customerStmt->execute();
}

$customerResult = $customerStmt->get_result();

// ==========================================
// HEADER
// ==========================================

include('../admin/includes/header.php');

?>

<div class="dashboard-layout">

    <!-- SIDEBAR -->
    <?php include('../admin/includes/sidebar.php'); ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="container-fluid py-4">

            <!-- PAGE HEADER -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <span class="admin-eyebrow">CUSTOMER RELATIONSHIPS</span>
                    <h2 class="mb-1">Customers</h2>

                    <p class="text-muted mb-0">
                        Monitor customer accounts and purchasing activity.
                    </p>
                </div>

            </div>

            <!-- ==========================================
                 STATISTICS
            ========================================== -->

            <div class="row g-3 mb-4">

                <div class="col-xl-3 col-md-6">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>
                                    <p class="text-muted mb-1">
                                        Total Customers
                                    </p>

                                    <h3 class="fw-bold mb-0">
                                        <?= number_format($totalCustomers); ?>
                                    </h3>
                                </div>

                                <div class="admin-stat-icon"><i class="fa-solid fa-users"></i></div>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-md-6">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>
                                    <p class="text-muted mb-1">
                                        Active Customers
                                    </p>

                                    <h3 class="fw-bold text-success mb-0">
                                        <?= number_format($activeCustomers); ?>
                                    </h3>
                                </div>

                                <div class="admin-stat-icon is-success"><i class="fa-solid fa-user-check"></i></div>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-md-6">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>
                                    <p class="text-muted mb-1">
                                        Inactive Customers
                                    </p>

                                    <h3 class="fw-bold text-secondary mb-0">
                                        <?= number_format($inactiveCustomers); ?>
                                    </h3>
                                </div>

                                <div class="admin-stat-icon is-muted"><i class="fa-solid fa-user-clock"></i></div>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-md-6">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>
                                    <p class="text-muted mb-1">
                                        Total Orders
                                    </p>

                                    <h3 class="fw-bold mb-0">
                                        <?= number_format($totalOrders); ?>
                                    </h3>
                                </div>

                                <div class="admin-stat-icon is-info"><i class="fa-solid fa-box"></i></div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==========================================
                 CUSTOMER DIRECTORY
            ========================================== -->

            <div class="card customers-card shadow-sm border-0">

                <div class="card-header bg-white border-0 pt-4 px-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Customer Directory
                            </h5>

                            <p class="text-muted small mb-0">
                                <?= number_format($filteredCustomers); ?>
                                customer records found
                            </p>

                        </div>

                    </div>

                    <!-- SEARCH AND FILTER -->
                    <form method="GET" class="row g-2 mt-3">

                        <div class="col-lg-5 col-md-12">

                            <input
                                type="search"
                                name="search"
                                class="form-control"
                                placeholder="Search name, email, username, contact..."
                                value="<?= e($search); ?>"
                            >

                        </div>


                        <div class="col-lg-3 col-md-4">

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option value="all"
                                    <?= $status === 'all' ? 'selected' : ''; ?>>
                                    All Accounts
                                </option>

                                <option value="active"
                                    <?= $status === 'active' ? 'selected' : ''; ?>>
                                    Active
                                </option>

                                <option value="inactive"
                                    <?= $status === 'inactive' ? 'selected' : ''; ?>>
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <div class="col-lg-2 col-md-4">

                            <select
                                name="sort"
                                class="form-select"
                            >

                                <option value="newest"
                                    <?= $sort === 'newest' ? 'selected' : ''; ?>>
                                    Newest First
                                </option>

                                <option value="oldest"
                                    <?= $sort === 'oldest' ? 'selected' : ''; ?>>
                                    Oldest First
                                </option>

                                <option value="name_asc"
                                    <?= $sort === 'name_asc' ? 'selected' : ''; ?>>
                                    Name A-Z
                                </option>

                                <option value="name_desc"
                                    <?= $sort === 'name_desc' ? 'selected' : ''; ?>>
                                    Name Z-A
                                </option>

                            </select>

                        </div>


                        <div class="col-lg-2 col-md-4 d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary flex-grow-1"
                            >
                                <i class="bi bi-search"></i>
                                Filter
                            </button>

                            <a
                                href="adminCustomer.php"
                                class="btn btn-light border"
                                title="Reset filters"
                            >
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>

                        </div>

                    </form>

                </div>


                <!-- TABLE -->
                <div class="card-body px-4">

                    <div class="table-responsive custom-scrollbar">

                        <table class="table table-hover align-middle customers-table">

                            <thead class="table-light">

                                <tr>

                                    <th>ID</th>

                                    <th>Customer</th>

                                    <th>Email</th>

                                    <th>Contact</th>

                                    <th>Location</th>

                                    <th>Orders</th>

                                    <th>Total Spent</th>

                                    <th>Status</th>

                                    <th>Registered</th>

                                    <th>Action</th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php if ($customerResult->num_rows > 0): ?>

                                    <?php while ($row = $customerResult->fetch_assoc()): ?>

                                        <?php

                                        $fullName = trim(
                                            $row['firstName'] . ' ' .
                                            $row['middleName'] . ' ' .
                                            $row['lastName']
                                        );

                                        $addressParts = array_filter([
                                            $row['street'],
                                            $row['barangay'],
                                            $row['city']
                                        ]);

                                        $address = !empty($addressParts)
                                            ? implode(', ', $addressParts)
                                            : 'No address provided';

                                        $accountStatus = strtolower(
                                            trim($row['accountStatus'] ?? '')
                                        );

                                        $isActive = $accountStatus === 'active';

                                        ?>

                                        <tr>

                                            <!-- CUSTOMER ID -->
                                            <td>
                                                #<?= (int)$row['id']; ?>
                                            </td>


                                            <!-- CUSTOMER -->
                                            <td>

                                                <div class="fw-semibold">
                                                    <?= e($fullName); ?>
                                                </div>

                                                <small class="text-muted">
                                                    @<?= e($row['username']); ?>
                                                </small>

                                            </td>


                                            <!-- EMAIL -->
                                            <td>
                                                <?= e($row['emailAddress']); ?>
                                            </td>


                                            <!-- CONTACT -->
                                            <td>
                                                <?= e($row['contactNumber'] ?: 'N/A'); ?>
                                            </td>


                                            <!-- ADDRESS -->
                                            <td>

                                                <span
                                                    title="<?= e($address); ?>"
                                                >
                                                    <?= e($address); ?>
                                                </span>

                                            </td>


                                            <!-- ORDERS -->
                                            <td>

                                                <span class="badge bg-light text-dark border">
                                                    <?= number_format((int)$row['total_orders']); ?>
                                                </span>

                                            </td>


                                            <!-- TOTAL SPENT -->
                                            <td class="fw-semibold">

                                                ₱<?= number_format(
                                                    (float)$row['total_spent'],
                                                    2
                                                ); ?>

                                            </td>


                                            <!-- ACCOUNT STATUS -->
                                            <td>

                                                <?php if ($isActive): ?>

                                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">
                                                        Active
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">
                                                        <?= e(ucfirst($accountStatus ?: 'Inactive')); ?>
                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                            <!-- REGISTRATION DATE -->
                                            <td>

                                                <?php

                                                echo !empty($row['dateCreated'])
                                                    ? e(date(
                                                        'M d, Y',
                                                        strtotime($row['dateCreated'])
                                                    ))
                                                    : 'N/A';

                                                ?>

                                            </td>


                                            <!-- ACTION -->
                                            <td>

                                                <a
                                                    href="/SoftEcoms/public/admin/adminUserProfile.php?id=<?= (int)$row['id']; ?>"
                                                    class="btn btn-sm btn-primary rounded-pill px-3"
                                                >
                                                    View
                                                </a>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="10"
                                            class="text-center py-5 text-muted"
                                        >

                                            <div class="fs-2 mb-2">👥</div>

                                            <h6>No Customers Found</h6>

                                            <p class="small mb-0">
                                                Try changing your search or filters.
                                            </p>

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>


                    <!-- ==========================================
                         PAGINATION
                    ========================================== -->

                    <?php if ($filteredCustomers > 0): ?>

                        <?php

                        $queryParams = [
                            'search' => $search,
                            'status' => $status,
                            'sort' => $sort
                        ];

                        $visiblePages = [];

                        for (
                            $i = max(1, $page - 2);
                            $i <= min($totalPages, $page + 2);
                            $i++
                        ) {
                            $visiblePages[] = $i;
                        }

                        ?>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">

                            <small class="text-muted">

                                Showing

                                <?= number_format($start + 1); ?>

                                to

                                <?= number_format(
                                    min($start + $limit, $filteredCustomers)
                                ); ?>

                                of

                                <?= number_format($filteredCustomers); ?>

                                customers

                            </small>


                            <nav aria-label="Customer pagination">

                                <ul class="pagination pagination-sm mb-0">

                                    <!-- PREVIOUS -->
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">

                                        <?php $queryParams['page'] = max(1, $page - 1); ?>

                                        <a
                                            class="page-link"
                                            href="?<?= e(http_build_query($queryParams)); ?>"
                                        >
                                            Previous
                                        </a>

                                    </li>


                                    <!-- FIRST PAGE -->
                                    <?php if ($page > 3): ?>

                                        <?php $queryParams['page'] = 1; ?>

                                        <li class="page-item">
                                            <a
                                                class="page-link"
                                                href="?<?= e(http_build_query($queryParams)); ?>"
                                            >
                                                1
                                            </a>
                                        </li>

                                        <?php if ($page > 4): ?>

                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>

                                        <?php endif; ?>

                                    <?php endif; ?>


                                    <!-- VISIBLE PAGES -->
                                    <?php foreach ($visiblePages as $pageNumber): ?>

                                        <?php $queryParams['page'] = $pageNumber; ?>

                                        <li class="page-item <?= $pageNumber === $page ? 'active' : ''; ?>">

                                            <a
                                                class="page-link"
                                                href="?<?= e(http_build_query($queryParams)); ?>"
                                            >
                                                <?= $pageNumber; ?>
                                            </a>

                                        </li>

                                    <?php endforeach; ?>


                                    <!-- LAST PAGE -->
                                    <?php if ($page < $totalPages - 2): ?>

                                        <?php if ($page < $totalPages - 3): ?>

                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>

                                        <?php endif; ?>

                                        <?php $queryParams['page'] = $totalPages; ?>

                                        <li class="page-item">

                                            <a
                                                class="page-link"
                                                href="?<?= e(http_build_query($queryParams)); ?>"
                                            >
                                                <?= $totalPages; ?>
                                            </a>

                                        </li>

                                    <?php endif; ?>


                                    <!-- NEXT -->
                                    <?php $queryParams['page'] = min($totalPages, $page + 1); ?>

                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : ''; ?>">

                                        <a
                                            class="page-link"
                                            href="?<?= e(http_build_query($queryParams)); ?>"
                                        >
                                            Next
                                        </a>

                                    </li>

                                </ul>

                            </nav>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>

<?php

$customerStmt->close();

?>
