
<?php
$pageClass = "checkout-page";

include_once __DIR__ . '/../admin/includes/header.php';
include_once __DIR__ . '/../admin/includes/topbar.php';
?>

<link rel="stylesheet" href="assets/css/checkout.css">

<div class="confirmation-container">

    <div class="checkout-heading">

        <span class="checkout-eyebrow">
            SWEET! • ORDER DETAILS
        </span>

        <h1>Your Sweet Order</h1>

        <p>View your order details and purchased products.</p>

    </div>

    <div class="confirmation-card" id="receiptContainer">

        <div class="text-center py-4" id="receiptLoading">
            Loading receipt...
        </div>

        <div id="receiptContent" hidden>

            <div class="success-icon">
                <i class="bi bi-receipt"></i>
            </div>

            <h1>Order Receipt</h1>

            <p class="confirmation-message">
                Thank you for ordering from SweetClaire!
            </p>

            <div class="order-number">
                <span>Order Number</span>
                <h3 id="orderNumber"></h3>
            </div>

            <div class="order-details">

                <div>
                    <span>Order Date</span>
                    <strong id="orderDate"></strong>
                </div>

                <div>
                    <span>Customer Name</span>
                    <strong id="customerName"></strong>
                </div>

                <div>
                    <span>Phone Number</span>
                    <strong id="customerPhone"></strong>
                </div>

                <div>
                    <span>Email Address</span>
                    <strong id="customerEmail"></strong>
                </div>

                <div>
                    <span>Payment Method</span>
                    <strong id="paymentMethod"></strong>
                </div>

                <div>
                    <span>Order Status</span>
                    <strong id="orderStatus"></strong>
                </div>

                <div>
                    <span>Delivery Address</span>
                    <strong id="deliveryAddress"></strong>
                </div>

                <div>
                    <span>Delivery Distance</span>
                    <strong id="deliveryDistance"></strong>
                </div>

            </div>

            <h3 class="summary-title">Order Summary</h3>

            <div id="orderedItems"></div>

            <div class="summary-row">
                <span>Subtotal</span>
                <strong id="subtotal"></strong>
            </div>

            <div class="summary-row">
                <span>Tax (12%)</span>
                <strong id="tax"></strong>
            </div>

            <div class="summary-row">
                <span>Delivery Fee</span>
                <strong id="deliveryFee"></strong>
            </div>

            <div class="confirmation-total">
                <span>Total Amount</span>
                <h2 id="totalAmount"></h2>
            </div>

            <a href="/SoftEcoms/public/order.php" class="continue-shopping">
                Back to My Orders
            </a>

        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", async () => {

    const API_URL = "http://localhost:3000";

    const loading = document.getElementById("receiptLoading");
    const content = document.getElementById("receiptContent");

    function money(value) {
        return "₱" + Number(value || 0).toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function setText(id, value) {
        document.getElementById(id).textContent =
            value === null || value === undefined || value === ""
                ? "N/A"
                : value;
    }

    function showError(message) {
        loading.textContent = message;
        loading.className = "alert alert-danger";
    }

    function formatDate(value) {
        if (!value) return "N/A";

        const date = new Date(
            String(value).replace(" ", "T")
        );

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleString("en-PH");
    }

    function renderItems(items) {

        const container = document.getElementById("orderedItems");

        container.innerHTML = "";

        items.forEach(item => {

            const row = document.createElement("div");
            row.className = "confirmation-item";

            const info = document.createElement("div");
            info.className = "item-info";

            const name = document.createElement("strong");
            name.textContent = item.productName;

            const quantity = document.createElement("span");
            quantity.textContent = `Quantity: ${item.quantity}`;

            info.appendChild(name);
            info.appendChild(quantity);

            const price = document.createElement("strong");
            price.textContent = money(item.price * item.quantity);

            row.appendChild(info);
            row.appendChild(price);

            container.appendChild(row);
        });
    }

    async function loadReceipt() {

        const params = new URLSearchParams(window.location.search);
        const uuid = params.get("uuid");

        if (!uuid) {
            showError("No order UUID was provided.");
            return;
        }

        try {

            const response = await fetch(
                `${API_URL}/orders/receipt/${encodeURIComponent(uuid)}`,
                {
                    method: "GET",
                    credentials: "include",
                    headers: {
                        "Accept": "application/json"
                    },
                    cache: "no-store"
                }
            );

            const data = await response.json();

            if (response.status === 401) {
                showError("Your session has expired. Please log in again.");
                return;
            }

            if (response.status === 403) {
                showError("You are not authorized to view this receipt.");
                return;
            }

            if (response.status === 404) {
                showError(data.message || "Receipt not found.");
                return;
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || "Unable to load receipt.");
            }

            const order = data.receipt.order;
            const items = data.receipt.items;

            setText("orderNumber", order.orderNumber || order.uuid);
            setText("orderDate", formatDate(order.orderDate));
            setText("customerName", order.fullName);
            setText("customerPhone", order.phone);
            setText("customerEmail", order.email);
            setText("paymentMethod", order.paymentMethod);
            setText("orderStatus", order.status);
            setText("deliveryAddress", order.address);
            setText(
                "deliveryDistance",
                Number(order.deliveryDistance || 0).toFixed(2) + " km"
            );

            setText("subtotal", money(order.subtotal));
            setText("tax", money(order.tax));
            setText("deliveryFee", money(order.deliveryFee));
            setText("totalAmount", money(order.total));

            renderItems(items);

            loading.hidden = true;
            content.hidden = false;

        } catch (error) {

            console.error("Receipt loading error:", error);

            showError(
                "Unable to load receipt. Please check your connection and try again."
            );
        }
    }

    loadReceipt();

});
</script>