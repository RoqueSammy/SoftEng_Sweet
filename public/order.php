
<?php
session_start();

$pageClass = "cus-order-page";

include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/topbar.php';
?>

<main class="container my-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>My Orders</h2>
            <p class="text-muted">Track your orders and view their delivery progress.</p>
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm" id="refreshOrders">
            Refresh Orders
        </button>
    </div>

    <div class="mb-3">
        <input
            type="text"
            id="searchOrder"
            class="form-control"
            placeholder="Search order number or payment method..."
        >
    </div>

    <div id="orderMessage" class="mb-3"></div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Status / Delivery Progress</th>
                    <th>Date Ordered</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody id="ordersTable">
                <tr>
                    <td colspan="6" class="text-center">
                        Loading your orders...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>



</main>

<!-- CANCELLATION REQUEST MODAL -->
<div
    class="modal fade"
    id="cancelOrderModal"
    tabindex="-1"
    aria-labelledby="cancelOrderModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">
                    Request Order Cancellation
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <div class="modal-body">

                <p class="text-muted">
                    Please provide a reason for cancelling this order.
                    Your request will be reviewed by the administrator.
                </p>

                <div class="mb-3">
                    <label for="cancelReason" class="form-label">
                        Reason for cancellation
                    </label>

                    <textarea
                        id="cancelReason"
                        class="form-control"
                        rows="4"
                        maxlength="255"
                        placeholder="Enter your reason..."
                        required
                    ></textarea>

                    <small class="text-muted">
                        Maximum of 255 characters.
                    </small>
                </div>

                <div id="cancelModalMessage"></div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                    id="closeCancelModal"
                >
                    Close
                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    id="submitCancelRequest"
                >
                    Submit Request
                </button>

            </div>

        </div>
    </div>
</div>



<style>
.delivery-progress {
    min-width: 290px;
    max-width: 390px;
    padding: 8px 0;
}

.delivery-progress-track {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    position: relative;
    gap: 2px;
}

.delivery-progress-track::before {
    content: "";
    position: absolute;
    height: 3px;
    background: #dee2e6;
    top: 11px;
    left: 9%;
    right: 9%;
    z-index: 0;
}

.delivery-progress-line {
    position: absolute;
    height: 3px;
    background: #198754;
    top: 11px;
    left: 9%;
    z-index: 1;
    transition: width .2s ease;
}

.delivery-step {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    flex: 1;
    font-size: 10px;
    color: #6c757d;
}

.delivery-step-circle {
    width: 23px;
    height: 23px;
    border-radius: 50%;
    background: #dee2e6;
    border: 2px solid #dee2e6;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    color: #495057;
}

.delivery-step.completed .delivery-step-circle,
.delivery-step.active .delivery-step-circle {
    background: #198754;
    border-color: #198754;
    color: white;
}

.delivery-step.active,
.delivery-step.completed {
    color: #198754;
}

.delivery-step.active {
    font-weight: 700;
}

.delivery-cancelled {
    padding: 9px 12px;
    border-radius: 6px;
    background: #f8d7da;
    color: #842029;
    font-weight: 600;
    display: inline-block;
}

.delivery-progress-note {
    font-size: 11px;
    color: #6c757d;
    margin-top: 7px;
}

@media (max-width: 768px) {
    .delivery-progress {
        min-width: 270px;
    }

    .delivery-step {
        font-size: 9px;
    }
}
</style>

<!-- IMPORTANT: Bootstrap Bundle must load before the order script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const API_URL = "/api";

    const ordersTable = document.getElementById("ordersTable");
    const orderMessage = document.getElementById("orderMessage");
    const searchOrder = document.getElementById("searchOrder");
    const refreshOrders = document.getElementById("refreshOrders");

    const cancelOrderModalElement =
        document.getElementById("cancelOrderModal");

    const cancelReason = document.getElementById("cancelReason");
    const cancelModalMessage = document.getElementById("cancelModalMessage");
    const submitCancelRequest = document.getElementById("submitCancelRequest");

    let cancelModal = null;

    if (
        cancelOrderModalElement &&
        typeof bootstrap !== "undefined" &&
        bootstrap.Modal
    ) {
        cancelModal = bootstrap.Modal.getOrCreateInstance(
            cancelOrderModalElement
        );
    }

    let orders = [];
    let selectedOrderUUID = null;
    let isSubmittingCancellation = false;

    const orderStatuses = [
        "pending",
        "processing",
        "ready_for_delivery",
        "out_for_delivery",
        "completed"
    ];

    const statusLabels = {
        pending: "Pending",
        processing: "Processing",
        ready_for_delivery: "Ready for Delivery",
        out_for_delivery: "Out for Delivery",
        completed: "Completed",
        cancelled: "Cancelled"
    };

    function showMessage(message, type = "danger") {
        orderMessage.textContent = message;
        orderMessage.className = `alert alert-${type} mb-3`;
    }

    function clearMessage() {
        orderMessage.textContent = "";
        orderMessage.className = "";
    }

    function showCancelModalMessage(message, type = "danger") {
        cancelModalMessage.textContent = message;
        cancelModalMessage.className = `alert alert-${type}`;
    }

    function escapeHTML(value) {
        return String(value ?? "").replace(/[&<>"']/g, char => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        })[char]);
    }

    function getStatusClass(status) {
        const cleanStatus = String(status || "").toLowerCase();

        switch (cleanStatus) {
            case "completed":
                return "bg-success";
            case "pending":
                return "bg-warning text-dark";
            case "processing":
                return "bg-info text-dark";
            case "ready_for_delivery":
            case "out_for_delivery":
                return "bg-primary";
            case "cancelled":
                return "bg-danger";
            default:
                return "bg-secondary";
        }
    }

    function getStatusLabel(status) {
        const cleanStatus = String(status || "").toLowerCase();
        return statusLabels[cleanStatus] || cleanStatus;
    }

    function getCancellationStatus(order) {
        return String(order.cancellationStatus || "").toLowerCase();
    }

    function canRequestCancellation(order) {
        const status = String(order.status || "").toLowerCase();
        const cancellationStatus = getCancellationStatus(order);

        return (
            ["pending", "processing"].includes(status) &&
            !["pending", "approved", "rejected"].includes(cancellationStatus)
        );
    }

    function renderDeliveryProgress(status) {
        const cleanStatus = String(status || "").toLowerCase();

        if (cleanStatus === "cancelled") {
            return `<span class="delivery-cancelled">Order Cancelled</span>`;
        }

        const currentIndex = orderStatuses.indexOf(cleanStatus);

        if (currentIndex === -1) {
            return `
                <span class="badge bg-secondary">
                    ${escapeHTML(getStatusLabel(cleanStatus))}
                </span>
            `;
        }

        const progressPercentage = currentIndex === 0
            ? 0
            : (currentIndex / (orderStatuses.length - 1)) * 82;

        const steps = orderStatuses.map((step, index) => {
            let stepClass = "";

            if (index < currentIndex) {
                stepClass = "completed";
            } else if (index === currentIndex) {
                stepClass = "active";
            }

            return `
                <div class="delivery-step ${stepClass}">
                    <div class="delivery-step-circle">
                        ${index < currentIndex ? "✓" : index + 1}
                    </div>
                    <span>${escapeHTML(getStatusLabel(step))}</span>
                </div>
            `;
        }).join("");

        return `
            <div class="delivery-progress">
                <div class="delivery-progress-track">
                    <div
                        class="delivery-progress-line"
                        style="width: ${progressPercentage}%"
                    ></div>
                    ${steps}
                </div>

                <div class="delivery-progress-note">
                    Current status:
                    <strong>${escapeHTML(getStatusLabel(cleanStatus))}</strong>
                </div>
            </div>
        `;
    }

    function renderOrders(orderList) {
        ordersTable.innerHTML = "";

        if (!orderList.length) {
            ordersTable.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No orders found.
                    </td>
                </tr>
            `;
            return;
        }

        orderList.forEach(order => {
            const uuid = escapeHTML(order.uuid);

            const orderNumber = escapeHTML(
                order.COnumber || order.uuid
            );

            const totalPrice = Number(order.totalPrice || 0);
            const paymentMethod = escapeHTML(order.paymentMethod || "N/A");

            const cleanStatus = String(
                order.status || "unknown"
            ).toLowerCase();

            const statusLabel = escapeHTML(getStatusLabel(cleanStatus));

            const dateCreated = order.DateCreated
                ? escapeHTML(new Date(order.DateCreated).toLocaleString())
                : "N/A";

            const statusClass = getStatusClass(cleanStatus);
            const cancellationStatus = getCancellationStatus(order);

            let cancellationHTML = "";

            if (cancellationStatus === "pending") {
                cancellationHTML = `
                    <div class="mt-2">
                        <span class="badge bg-warning text-dark">
                            Cancellation Pending
                        </span>
                    </div>
                `;
            } else if (cancellationStatus === "approved") {
                cancellationHTML = `
                    <div class="mt-2">
                        <span class="badge bg-danger">
                            Cancellation Approved
                        </span>
                    </div>
                `;
            } else if (cancellationStatus === "rejected") {
                cancellationHTML = `
                    <div class="mt-2">
                        <span class="badge bg-secondary">
                            Cancellation Rejected
                        </span>
                    </div>
                `;
            }

            let cancellationReasonHTML = "";

            if (order.cancellationReason) {
                cancellationReasonHTML = `
                    <div class="small text-muted mt-1">
                        Reason: ${escapeHTML(order.cancellationReason)}
                    </div>
                `;
            }

            let cancellationButtonHTML = "";

            if (canRequestCancellation(order)) {
                cancellationButtonHTML = `
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger cancel-order-btn"
                        data-uuid="${uuid}"
                    >
                        Cancel Order
                    </button>
                `;
            }

            const row = document.createElement("tr");

            row.innerHTML = `
                <td><strong>${orderNumber}</strong></td>
                <td>₱${totalPrice.toFixed(2)}</td>
                <td>${paymentMethod}</td>

                <td>
                    <span class="badge ${statusClass}">
                        ${statusLabel}
                    </span>

                    ${renderDeliveryProgress(cleanStatus)}
                    ${cancellationHTML}
                    ${cancellationReasonHTML}
                </td>

                <td>${dateCreated}</td>

                <td>
                    <div class="order-action-stack">
                        <a
                            href="/SoftEcoms/public/users/receipt.php?uuid=${encodeURIComponent(order.uuid)}"
                            class="btn btn-sm btn-primary"
                        >
                            View Receipt
                        </a>

                        ${cancellationButtonHTML}
                    </div>
                </td>
            `;

            ordersTable.appendChild(row);
        });
    }

    async function loadOrderHistory() {
        refreshOrders.disabled = true;
        refreshOrders.textContent = "Refreshing...";

        try {
            const response = await fetch(`${API_URL}/orders/history`, {
                method: "GET",
                credentials: "include",
                headers: {
                    "Accept": "application/json"
                },
                cache: "no-store"
            });

            const data = await response.json();

            if (response.status === 401) {
                showMessage("Your session has expired. Please log in again.");

                ordersTable.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <a href="/SoftEcoms/public/login.php" class="btn btn-primary">
                                Log In
                            </a>
                        </td>
                    </tr>
                `;
                return;
            }

            if (response.status === 403) {
                showMessage(
                    data.message || "You are not authorized to view these orders."
                );
                ordersTable.innerHTML = "";
                return;
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || "Unable to load orders.");
            }

            orders = Array.isArray(data.orders) ? data.orders : [];

            renderOrders(orders);

            if (orders.length === 0) {
                showMessage("You haven't placed any orders yet.", "info");
            } else {
                clearMessage();
            }

        } catch (error) {
            console.error("Order history error:", error);

            showMessage(
                "Unable to load your orders. Please check your connection and try again."
            );

            ordersTable.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        Failed to load orders.
                    </td>
                </tr>
            `;
        } finally {
            refreshOrders.disabled = false;
            refreshOrders.textContent = "Refresh Orders";
        }
    }

    // OPEN CANCELLATION MODAL
    ordersTable.addEventListener("click", event => {
        const button = event.target.closest(".cancel-order-btn");

        if (!button) return;

        if (!cancelModal) {
            showMessage(
                "Bootstrap modal is unavailable. Please check that Bootstrap JavaScript loaded correctly."
            );
            return;
        }

        selectedOrderUUID = button.dataset.uuid;
        cancelReason.value = "";
        cancelModalMessage.textContent = "";
        cancelModalMessage.className = "";

        submitCancelRequest.disabled = false;
        submitCancelRequest.textContent = "Submit Request";

        cancelModal.show();
    });

    // SUBMIT CANCELLATION REQUEST
    submitCancelRequest.addEventListener("click", async () => {
        if (isSubmittingCancellation) return;

        const reason = cancelReason.value.trim();

        if (!selectedOrderUUID) {
            showCancelModalMessage("Please select an order first.");
            return;
        }

        if (!reason) {
            showCancelModalMessage("Please provide a reason for cancellation.");
            cancelReason.focus();
            return;
        }

        if (reason.length > 255) {
            showCancelModalMessage("Your reason must not exceed 255 characters.");
            return;
        }

        isSubmittingCancellation = true;
        submitCancelRequest.disabled = true;
        submitCancelRequest.textContent = "Submitting...";

        try {
            const response = await fetch(`${API_URL}/orders/cancel-request`, {
                method: "POST",
                credentials: "include",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    orderUUID: selectedOrderUUID,
                    reason: reason
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || "Failed to submit cancellation request."
                );
            }

            cancelModal.hide();

            showMessage(
                data.message || "Your cancellation request has been submitted successfully.",
                "success"
            );

            await loadOrderHistory();

        } catch (error) {
            console.error("Cancellation request error:", error);

            showCancelModalMessage(
                error.message || "Unable to submit your cancellation request."
            );
        } finally {
            isSubmittingCancellation = false;
            submitCancelRequest.disabled = false;
            submitCancelRequest.textContent = "Submit Request";
        }
    });

    // RESET MODAL WHEN CLOSED
    if (cancelOrderModalElement) {
        cancelOrderModalElement.addEventListener("hidden.bs.modal", () => {
            selectedOrderUUID = null;
            cancelReason.value = "";
            cancelModalMessage.textContent = "";
            cancelModalMessage.className = "";
            isSubmittingCancellation = false;
            submitCancelRequest.disabled = false;
            submitCancelRequest.textContent = "Submit Request";
        });
    }

    // SEARCH ORDERS
    searchOrder.addEventListener("input", () => {
        const searchValue = searchOrder.value.toLowerCase().trim();

        const filteredOrders = orders.filter(order => {
            const orderNumber = String(
                order.COnumber || order.uuid || ""
            ).toLowerCase();

            const paymentMethod = String(
                order.paymentMethod || ""
            ).toLowerCase();

            const status = String(order.status || "").toLowerCase();

            const cancellationStatus = String(
                order.cancellationStatus || ""
            ).toLowerCase();

            return (
                orderNumber.includes(searchValue) ||
                paymentMethod.includes(searchValue) ||
                status.includes(searchValue) ||
                getStatusLabel(status).toLowerCase().includes(searchValue) ||
                cancellationStatus.includes(searchValue)
            );
        });

        renderOrders(filteredOrders);
    });

    // REFRESH ORDERS
    refreshOrders.addEventListener("click", loadOrderHistory);

    // INITIAL LOAD
    loadOrderHistory();

});
</script>
