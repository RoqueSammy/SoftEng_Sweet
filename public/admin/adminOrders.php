
<?php

session_start();

$pageClass = "orders-page";

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
// ORDER STATUS CONFIGURATION
// ==========================================

$statusLabels = [
    'pending' => 'Pending',
    'processing' => 'Processing',
    'ready_for_delivery' => 'Ready for Delivery',
    'out_for_delivery' => 'Out for Delivery',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];

$statusColors = [
    'pending' => 'warning',
    'processing' => 'info',
    'ready_for_delivery' => 'primary',
    'out_for_delivery' => 'primary',
    'completed' => 'success',
    'cancelled' => 'danger'
];

$nextStatuses = [
    'pending' => 'processing',
    'processing' => 'ready_for_delivery',
    'ready_for_delivery' => 'out_for_delivery',
    'out_for_delivery' => 'completed'
];


// ==========================================
// TOTAL ORDERS
// ==========================================

$total_query = "
    SELECT COUNT(*) AS total
    FROM orders
";

$total_result = mysqli_query($conn, $total_query);

if (!$total_result) {
    die("Unable to retrieve orders: " . mysqli_error($conn));
}

$total_row = mysqli_fetch_assoc($total_result);

$total_orders = (int)($total_row['total'] ?? 0);

$total_pages = max(1, (int)ceil($total_orders / $limit));

$pipeline_query = "
    SELECT
        SUM(status = 'pending') AS pending,
        SUM(status = 'processing') AS processing,
        SUM(status = 'ready_for_delivery') AS ready_for_delivery,
        SUM(status = 'out_for_delivery') AS out_for_delivery,
        SUM(status = 'completed') AS completed,
        SUM(status = 'cancelled') AS cancelled
    FROM orders
";
$pipeline_result = mysqli_query($conn, $pipeline_query);
$pipeline = $pipeline_result ? mysqli_fetch_assoc($pipeline_result) : [];


// ==========================================
// GET ORDERS
// ==========================================

$order_query = "
    SELECT
        orders.id,
        orders.COnumber,
        orders.totalPrice,
        orders.paymentMethod,
        orders.status,
        orders.DateCreated,
        users.firstName,
        users.lastName,
        orders.address
    FROM orders
    JOIN users
        ON users.id = orders.user_id
    ORDER BY orders.DateCreated DESC
    LIMIT $start, $limit
";

$order_result = mysqli_query($conn, $order_query);

if (!$order_result) {
    die("Unable to retrieve order records: " . mysqli_error($conn));
}

?>

<div class="dashboard-layout">

    <!-- SIDEBAR -->
    <?php include('../admin/includes/sidebar.php'); ?>


    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="container-fluid py-4">

            <!-- HEADER -->
            <div class="orders-header mb-4">

                <div>
                    <span class="admin-eyebrow">FULFILLMENT</span>
                    <h2>Orders</h2>

                    <p>
                        Manage customer orders and update their delivery status.
                    </p>
                </div>

                <!-- SEARCH -->
                <label class="orders-search"><i class="fa-solid fa-magnifying-glass"></i><input
                    type="text"
                    id="searchInput"
                    class="form-control search-input"
                    placeholder="Search orders or customers..."
                    aria-label="Search orders or customers"
                ></label>

            </div>

            <div class="orders-pipeline" aria-label="Order fulfillment stages">
                <?php foreach ([
                    'pending' => ['New orders', 'fa-inbox'],
                    'processing' => ['Preparing', 'fa-kitchen-set'],
                    'ready_for_delivery' => ['Ready', 'fa-box'],
                    'out_for_delivery' => ['On the way', 'fa-truck'],
                    'completed' => ['Delivered', 'fa-circle-check'],
                ] as $stage => [$label, $icon]): ?>
                    <div class="pipeline-step <?= (int)($pipeline[$stage] ?? 0) > 0 ? 'has-orders' : '' ?>">
                        <span class="pipeline-icon"><i class="fa-solid <?= $icon ?>"></i></span>
                        <span class="pipeline-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                        <strong><?= number_format((int)($pipeline[$stage] ?? 0)) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>


            <!-- ==========================================
                 CANCELLATION REQUESTS
            ========================================== -->

            <div class="card orders-cancellations-card shadow-sm border-0 mb-4">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <div>
                        <span class="admin-eyebrow">CUSTOMER REQUESTS</span>
                        <h5 class="mb-1">Cancellation requests</h5>

                        <small class="text-muted">
                            Review customer requests to cancel their orders.
                        </small>
                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        id="refreshCancellationRequests"
                    >
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
                    </button>

                </div>


                <div class="card-body">

                    <div id="cancellationMessage"></div>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Reason</th>
                                    <th>Total</th>
                                    <th>Requested At</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>


                            <tbody id="cancellationRequestsTable">

                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Loading cancellation requests...
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            <!-- ==========================================
                 ORDERS CARD
            ========================================== -->

            <div class="orders-toolbar-row">
                <div><h3>All orders</h3><p><?= number_format($total_orders) ?> orders · Update each order as it moves through fulfillment.</p></div>
                <div class="order-filter-chips" role="group" aria-label="Filter orders by status">
                    <button type="button" class="order-filter active" data-filter="all">All <span><?= number_format($total_orders) ?></span></button>
                    <?php foreach (['pending' => 'New', 'processing' => 'Preparing', 'ready_for_delivery' => 'Ready', 'out_for_delivery' => 'On the way', 'completed' => 'Delivered', 'cancelled' => 'Cancelled'] as $filterStatus => $filterLabel): ?>
                        <button type="button" class="order-filter" data-filter="<?= htmlspecialchars($filterStatus, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($filterLabel, ENT_QUOTES, 'UTF-8') ?> <span><?= number_format((int)($pipeline[$filterStatus] ?? 0)) ?></span></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card orders-card shadow-sm border-0">

                <div class="card-body">

                    <div class="table-responsive custom-scrollbar">

                        <table class="table table-hover align-middle orders-table mb-0">

                            <thead class="table-light">

                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Address</th>
                                    <th>Payment</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>

                            </thead>


                            <tbody id="ordersTable">

                            <?php if (mysqli_num_rows($order_result) > 0): ?>

                                <?php while ($row = mysqli_fetch_assoc($order_result)): ?>

                                    <?php

                                    $status = strtolower(trim($row['status'] ?? 'pending'));

                                    $badge = $statusColors[$status] ?? 'secondary';

                                    $statusLabel = $statusLabels[$status] ?? ucfirst($status);

                                    // Only show the current status and its next valid status.
                                    $availableStatuses = [$status];

                                    if (isset($nextStatuses[$status])) {
                                        $availableStatuses[] = $nextStatuses[$status];
                                    }

                                    ?>

                                    <tr data-order-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">

                                        <!-- ORDER NUMBER -->
                                        <td>
                                            <strong>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row['COnumber'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>
                                            </strong>
                                        </td>


                                        <!-- CUSTOMER -->
                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                trim(
                                                    ($row['firstName'] ?? '') . ' ' .
                                                    ($row['lastName'] ?? '')
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </td>


                                        <!-- ADDRESS -->
                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                trim($row['address'] ?? ''),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?: 'N/A';
                                            ?>
                                        </td>


                                        <!-- PAYMENT -->
                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $row['paymentMethod'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </td>


                                        <!-- TOTAL -->
                                        <td>
                                            ₱<?php
                                            echo number_format(
                                                (float)$row['totalPrice'],
                                                2
                                            );
                                            ?>
                                        </td>


                                        <!-- ORDER STATUS -->
                                        <td>

                                            <select
                                                class="form-select form-select-sm status-select bg-<?php echo htmlspecialchars($badge, ENT_QUOTES, 'UTF-8'); ?> text-white border-0"
                                                data-order-id="<?php echo (int)$row['id']; ?>"
                                                data-previous-status="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>"
                                                onchange="updateOrderStatus(this)"
                                                <?php echo in_array($status, ['completed', 'cancelled'], true) ? 'disabled' : ''; ?>
                                            >

                                                <?php foreach ($availableStatuses as $availableStatus): ?>

                                                    <option
                                                        value="<?php echo htmlspecialchars($availableStatus, ENT_QUOTES, 'UTF-8'); ?>"
                                                        <?php echo ($status === $availableStatus) ? 'selected' : ''; ?>
                                                    >
                                                        <?php
                                                        echo htmlspecialchars(
                                                            $statusLabels[$availableStatus] ?? ucfirst($availableStatus),
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>
                                                    </option>

                                                <?php endforeach; ?>

                                            </select>

                                        </td>


                                        <!-- DATE -->
                                        <td>
                                            <?php
                                            echo !empty($row['DateCreated'])
                                                ? date(
                                                    "M d, Y",
                                                    strtotime($row['DateCreated'])
                                                )
                                                : 'N/A';
                                            ?>
                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>
                                    <td
                                        colspan="7"
                                        class="text-center py-5 text-muted"
                                    >
                                        No Orders Found
                                    </td>
                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>


                    <!-- PAGINATION -->
                    <div class="pagination-wrapper mt-4">

                        <?php if ($page > 1): ?>

                            <a
                                href="?page=<?php echo $page - 1; ?>"
                                class="btn btn-light border"
                            >
                                Previous
                            </a>

                        <?php endif; ?>


                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                            <a
                                href="?page=<?php echo $i; ?>"
                                class="btn <?php echo ($page === $i)
                                    ? 'btn-primary'
                                    : 'btn-light border'; ?>"
                            >
                                <?php echo $i; ?>
                            </a>

                        <?php endfor; ?>


                        <?php if ($page < $total_pages): ?>

                            <a
                                href="?page=<?php echo $page + 1; ?>"
                                class="btn btn-light border"
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


<!-- ==========================================
     JAVASCRIPT
========================================== -->

<script>


// ==========================================
// SEARCH ORDERS
// ==========================================

const searchInput = document.getElementById("searchInput");
const orderFilters = document.querySelectorAll(".order-filter");
let activeOrderFilter = "all";

function filterOrders() {

    const value = searchInput.value.trim().toLowerCase();

    const rows = document.querySelectorAll("#ordersTable tr");

    rows.forEach(row => {

        const matchesSearch = row.innerText.toLowerCase().includes(value);
        const matchesStatus = activeOrderFilter === "all" || row.dataset.orderStatus === activeOrderFilter;
        row.style.display = matchesSearch && matchesStatus ? "" : "none";

    });

}

searchInput.addEventListener("input", filterOrders);
orderFilters.forEach(button => button.addEventListener("click", () => {
    activeOrderFilter = button.dataset.filter;
    orderFilters.forEach(item => {
        const active = item === button;
        item.classList.toggle("active", active);
        item.setAttribute("aria-pressed", String(active));
    });
    filterOrders();
}));


// ==========================================
// UPDATE ORDER STATUS
// ==========================================

async function updateOrderStatus(select) {

    const orderId = select.dataset.orderId;

    const newStatus = select.value;

    const previousStatus = select.dataset.previousStatus;

    const statusLabels = {
        pending: "Pending",
        processing: "Processing",
        ready_for_delivery: "Ready for Delivery",
        out_for_delivery: "Out for Delivery",
        completed: "Completed"
    };

    const confirmation = confirm(
        `Change order status from "${statusLabels[previousStatus] || previousStatus}" to "${statusLabels[newStatus] || newStatus}"?`
    );

    if (!confirmation) {
        select.value = previousStatus;
        return;
    }

    select.disabled = true;

    try {

        const response = await fetch(
            "/api/admin/orders/update-status",
            {
                method: "POST",

                credentials: "include",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    order_id: orderId,
                    status: newStatus
                })
            }
        );


        const data = await response.json();


        if (!response.ok || !data.success) {

            throw new Error(
                data.message || "Unable to update order status."
            );

        }


        alert(
            data.message || "Order status updated successfully!"
        );

        // Reload so the dropdown reflects the next valid transition.
        window.location.reload();


    } catch (error) {

        console.error("Order status update error:", error);

        select.value = previousStatus;

        alert(
            error.message ||
            "Something went wrong while updating the order."
        );

        select.disabled = false;

    }

}


// ==========================================
// HTML ESCAPING
// ==========================================

function escapeHTML(value) {

    return String(value ?? "").replace(/[&<>"']/g, character => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    })[character]);

}


// ==========================================
// LOAD CANCELLATION REQUESTS
// ==========================================

async function loadCancellationRequests() {

    const table = document.getElementById("cancellationRequestsTable");

    const message = document.getElementById("cancellationMessage");

    table.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                Loading cancellation requests...
            </td>
        </tr>
    `;

    message.innerHTML = "";

    try {

        const response = await fetch(
            "/api/admin/orders/cancellation-requests",
            {
                method: "GET",
                credentials: "include"
            }
        );


        const data = await response.json();


        if (!response.ok || !data.success) {

            throw new Error(
                data.message || "Unable to load cancellation requests."
            );

        }


        const requests = data.requests || [];


        if (requests.length === 0) {

            table.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No cancellation requests found.
                    </td>
                </tr>
            `;

            return;

        }


        table.innerHTML = requests.map(request => {

            const status = String(
                request.cancellationStatus || "pending"
            ).toLowerCase();


            let badge = "warning";

            if (status === "approved") {
                badge = "success";
            } else if (status === "rejected") {
                badge = "danger";
            }


            let actions = "";

            if (status === "pending") {

                actions = `
                    <button
                        type="button"
                        class="btn btn-success btn-sm mb-1"
                        onclick="reviewCancellationRequest(${Number(request.requestId)}, 'approve')"
                    >
                        Approve
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger btn-sm mb-1"
                        onclick="reviewCancellationRequest(${Number(request.requestId)}, 'reject')"
                    >
                        Reject
                    </button>
                `;

            } else {

                actions = `
                    <span class="text-muted">
                        Reviewed
                    </span>
                `;

            }


            return `
                <tr>

                    <td>
                        <strong>
                            ${escapeHTML(request.COnumber)}
                        </strong>
                    </td>

                    <td>
                        ${escapeHTML(request.firstName)}
                        ${escapeHTML(request.lastName)}
                    </td>

                    <td>
                        ${escapeHTML(request.reason)}
                    </td>

                    <td>
                        ₱${Number(request.totalPrice || 0).toFixed(2)}
                    </td>

                    <td>
                        ${escapeHTML(request.requestedAt)}
                    </td>

                    <td>
                        <span class="badge bg-${badge}">
                            ${escapeHTML(status)}
                        </span>
                    </td>

                    <td>
                        ${actions}
                    </td>

                </tr>
            `;

        }).join("");


    } catch (error) {

        console.error("Cancellation requests error:", error);

        table.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger py-4">
                    ${escapeHTML(error.message)}
                </td>
            </tr>
        `;

    }

}


// ==========================================
// APPROVE OR REJECT CANCELLATION
// ==========================================

async function reviewCancellationRequest(requestId, action) {

    const confirmation = action === "approve"

        ? "Approve this cancellation request? The order will be cancelled and its stock restored."

        : "Reject this cancellation request? The order and stock will remain unchanged.";


    if (!confirm(confirmation)) {
        return;
    }


    try {

        const response = await fetch(
            `/api/admin/orders/cancellation-requests/${requestId}/review`,
            {
                method: "POST",

                credentials: "include",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    action: action
                })
            }
        );


        const data = await response.json();


        if (!response.ok || !data.success) {

            throw new Error(
                data.message || "Unable to review cancellation request."
            );

        }


        alert(
            action === "approve"
                ? "Cancellation approved successfully!"
                : "Cancellation rejected successfully!"
        );


        window.location.reload();


    } catch (error) {

        console.error("Cancellation review error:", error);

        alert(
            error.message ||
            "Something went wrong while reviewing the request."
        );

    }

}


// ==========================================
// INITIALIZE
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    loadCancellationRequests();

    document
        .getElementById("refreshCancellationRequests")
        .addEventListener("click", loadCancellationRequests);

});

</script>


</body>
</html>
