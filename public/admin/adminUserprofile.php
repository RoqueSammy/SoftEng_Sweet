
<?php
session_start();

$pageClass = "Admin-edit";

include_once __DIR__ . "/includes/header.php";

$customerId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$customerId || $customerId < 1) {
    echo '<div class="container mt-5 alert alert-danger">Invalid customer ID.</div>';
    include_once __DIR__ . "/includes/footer.php";
    exit;
}
?>

<div class="dashboard-layout">
    <?php include_once __DIR__ . "/includes/sidebar.php"; ?>
    <div class="main-content">
        <div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Customer Profile</h2>
            <p class="text-muted mb-0">View and manage customer information.</p>
        </div>

        <a href="adminCustomer.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Customers
        </a>
    </div>

    <div id="pageMessage"></div>

    <!-- CUSTOMER PROFILE -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">

            <div class="row align-items-center">

                <div class="col-md-3 text-center mb-3 mb-md-0">

                    <img
                        id="previewImg"
                        src="/SoftEcoms/public/assets/profile_img/default-avatar.svg"
                        alt="Customer profile"
                        class="rounded-circle border"
                        width="130"
                        height="130"
                        style="object-fit: cover;"
                    >

                    <div class="mt-2">
                        <input
                            type="file"
                            id="profilePictureInput"
                            class="form-control form-control-sm"
                            accept="image/jpeg,image/png,image/webp"
                            disabled
                        >
                        <small class="text-muted">JPG, PNG, WEBP — maximum 3 MB</small>
                    </div>

                </div>

                <div class="col-md-9">

                    <h3 id="customerName" class="fw-bold mb-1">
                        Loading customer...
                    </h3>

                    <p id="customerEmail" class="text-muted mb-2"></p>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary" id="customerRole">Customer</span>
                        <span class="badge bg-success" id="customerStatus">Loading...</span>
                    </div>

                    <p class="text-muted small mt-3 mb-0">
                        Customer ID: <span id="customerIdDisplay"></span>
                    </p>

                </div>

            </div>

        </div>
    </div>

    <!-- CUSTOMER STATISTICS -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Orders</p>
                    <h3 class="fw-bold mb-0" id="totalOrders">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Completed Orders</p>
                    <h3 class="fw-bold mb-0" id="completedOrders">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Spent</p>
                    <h3 class="fw-bold mb-0" id="totalSpent">₱0.00</h3>
                </div>
            </div>
        </div>

    </div>

    <!-- CUSTOMER INFORMATION -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Personal Information</h5>

            <div class="d-flex gap-2">
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    id="cancelEditBtn"
                    onclick="cancelEdit()"
                    style="display:none;"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    id="editBtn"
                    onclick="toggleEdit()"
                >
                    Edit Profile
                </button>
            </div>
        </div>

        <div class="card-body p-4">

            <form id="profileForm">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">First Name</label>
                        <input type="text" id="firstName" class="form-control" maxlength="100" disabled required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" id="middleName" class="form-control" maxlength="100" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <input type="text" id="lastName" class="form-control" maxlength="100" disabled required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" id="emailAddress" class="form-control" maxlength="255" disabled required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" id="username" class="form-control" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" id="contactNumber" class="form-control" maxlength="30" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Street</label>
                        <input type="text" id="street" class="form-control" maxlength="255" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Barangay</label>
                        <input type="text" id="barangay" class="form-control" maxlength="100" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" id="city" class="form-control" maxlength="100" disabled>
                    </div>

                    <div class="col-12">
                        <small class="text-muted">
                            Account created:
                            <span id="dateCreated">Loading...</span>
                        </small>
                    </div>

                </div>

            </form>

        </div>
    </div>

    <!-- PASSWORD MANAGEMENT -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Password Management</h5>
        </div>

        <div class="card-body p-4">

            <p class="text-muted">
                Set a new password for this customer's account.
                The password will be securely hashed before it is saved.
            </p>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">New Password</label>
                    <input
                        type="password"
                        id="newPassword"
                        class="form-control"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                    >
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <button
                        type="button"
                        class="btn btn-warning"
                        onclick="resetCustomerPassword()"
                    >
                        Reset Password
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- ACCOUNT STATUS -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Account Management</h5>
        </div>

        <div class="card-body p-4">

            <p class="text-muted">
                Activate or deactivate this customer's account.
            </p>

            <div class="d-flex align-items-center flex-wrap gap-3">

                <span>
                    Current status:
                    <strong id="accountStatusText">Loading...</strong>
                </span>

                <button
                    type="button"
                    class="btn btn-outline-danger"
                    id="statusBtn"
                    onclick="toggleAccountStatus()"
                >
                    Deactivate Account
                </button>

            </div>

        </div>
    </div>

    <!-- ORDER HISTORY -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Order History</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Order Number</th>
                            <th>Payment Method</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody id="ordersTable">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Loading orders...
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>

<!-- SAVE CONFIRMATION MODAL -->
<div class="modal fade" id="saveModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Save Customer Changes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to save these customer information changes?
            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="confirmSaveBtn"
                    onclick="saveCustomerProfile()"
                >
                    Confirm Save
                </button>

            </div>

        </div>

    </div>

</div>

<script>
const API_URL = "/api";
const CUSTOMER_ID = <?= json_encode($customerId) ?>;

let customerData = null;
let originalProfile = {};
let originalProfileImage = "";
let selectedProfileImage = null;
let isEditing = false;

// Bootstrap JS is optional. The page can still work without the modal plugin.
const saveModal = window.bootstrap && typeof window.bootstrap.Modal === "function"
    ? new window.bootstrap.Modal(document.getElementById("saveModal"))
    : null;

// ==========================================
// API REQUEST
// ==========================================

async function apiRequest(endpoint, options = {}) {

    const response = await fetch(API_URL + endpoint, {
        ...options,
        credentials: "include",
        headers: {
            Accept: "application/json",
            ...(options.headers || {})
        },
        cache: "no-store"
    });

    const text = await response.text();

    let data;

    try {
        data = JSON.parse(text);
    } catch {
        throw new Error("The API returned an invalid response. Check the Node.js server.");
    }

    if (response.status === 401 || response.status === 403) {
        throw new Error(data.message || "Administrator access is required. Please log in again.");
    }

    if (!response.ok || data.success === false) {
        throw new Error(data.message || "The request failed.");
    }

    return data;
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
// DISPLAY MESSAGES
// ==========================================

function showMessage(message, type = "success") {

    const container = document.getElementById("pageMessage");

    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${escapeHTML(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

}

// ==========================================
// LOAD CUSTOMER PROFILE
// ==========================================

async function loadCustomerProfile() {

    try {

        const data = await apiRequest(
            `/admin/customers/${CUSTOMER_ID}`
        );

        customerData = data.customer;

        const customer = data.customer;
        const statistics = data.statistics || {};

        document.getElementById("customerIdDisplay").textContent =
            customer.id;

        document.getElementById("customerName").textContent =
            `${customer.firstName || ""} ${customer.lastName || ""}`.trim();

        document.getElementById("customerEmail").textContent =
            customer.emailAddress || "";

        document.getElementById("customerRole").textContent =
            customer.role || "Customer";

        document.getElementById("customerStatus").textContent =
            customer.accountStatus || "Unknown";

        document.getElementById("accountStatusText").textContent =
            customer.accountStatus || "Unknown";

        document.getElementById("firstName").value =
            customer.firstName || "";

        document.getElementById("middleName").value =
            customer.middleName || "";

        document.getElementById("lastName").value =
            customer.lastName || "";

        document.getElementById("emailAddress").value =
            customer.emailAddress || "";

        document.getElementById("username").value =
            customer.username || "";

        document.getElementById("contactNumber").value =
            customer.contactNumber || "";

        document.getElementById("street").value =
            customer.street || "";

        document.getElementById("barangay").value =
            customer.barangay || "";

        document.getElementById("city").value =
            customer.city || "";

        document.getElementById("dateCreated").textContent =
            customer.dateCreated
                ? new Date(customer.dateCreated).toLocaleString()
                : "N/A";

        originalProfileImage = customer.profileImageUrl
            ? customer.profileImageUrl
            : "/SoftEcoms/public/assets/profile_img/default-avatar.svg";

        document.getElementById("previewImg").src =
            originalProfileImage;

        document.getElementById("totalOrders").textContent =
            Number(statistics.totalOrders || 0);

        document.getElementById("completedOrders").textContent =
            Number(statistics.completedOrders || 0);

        document.getElementById("totalSpent").textContent =
            "₱" + Number(statistics.totalSpent || 0).toLocaleString(
                "en-PH",
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );

        updateStatusButton(customer.accountStatus);

        saveOriginalProfile();

        renderOrders(data.orders || []);

    } catch (error) {

        console.error("Customer profile loading error:", error);

        showMessage(error.message, "danger");

    }

}

// ==========================================
// SAVE ORIGINAL VALUES
// ==========================================

function saveOriginalProfile() {

    originalProfile = {
        firstName: document.getElementById("firstName").value,
        middleName: document.getElementById("middleName").value,
        lastName: document.getElementById("lastName").value,
        emailAddress: document.getElementById("emailAddress").value,
        contactNumber: document.getElementById("contactNumber").value,
        street: document.getElementById("street").value,
        barangay: document.getElementById("barangay").value,
        city: document.getElementById("city").value
    };

}

// ==========================================
// ENABLE / DISABLE EDIT MODE
// ==========================================

function toggleEdit() {

    if (!isEditing) {

        saveOriginalProfile();

        [
            "firstName",
            "middleName",
            "lastName",
            "emailAddress",
            "contactNumber",
            "street",
            "barangay",
            "city"
        ].forEach(id => {
            document.getElementById(id).disabled = false;
        });

        document.getElementById("profilePictureInput").disabled = false;

        document.getElementById("editBtn").textContent = "Save Profile";

        document.getElementById("cancelEditBtn").style.display = "inline-block";

        isEditing = true;

    } else {

        if (!document.getElementById("profileForm").reportValidity()) {
            return;
        }

        if (saveModal) {
            saveModal.show();
        } else if (window.confirm("Are you sure you want to save these customer information changes?")) {
            // Fallback when Bootstrap JavaScript is not loaded.
            saveCustomerProfile();
        }

    }

}

// ==========================================
// CANCEL EDIT
// ==========================================

function cancelEdit() {

    Object.keys(originalProfile).forEach(id => {
        document.getElementById(id).value = originalProfile[id];
    });

    selectedProfileImage = null;

    document.getElementById("profilePictureInput").value = "";

    document.getElementById("previewImg").src =
        originalProfileImage;

    setViewMode();

}

// ==========================================
// RETURN TO VIEW MODE
// ==========================================

function setViewMode() {

    [
        "firstName",
        "middleName",
        "lastName",
        "emailAddress",
        "contactNumber",
        "street",
        "barangay",
        "city"
    ].forEach(id => {
        document.getElementById(id).disabled = true;
    });

    document.getElementById("profilePictureInput").disabled = true;

    document.getElementById("editBtn").textContent = "Edit Profile";

    document.getElementById("cancelEditBtn").style.display = "none";

    isEditing = false;

}

// ==========================================
// PROFILE IMAGE PREVIEW
// ==========================================

document.getElementById("profilePictureInput").addEventListener(
    "change",
    function(event) {

        const file = event.target.files[0];

        if (!file) {
            selectedProfileImage = null;
            return;
        }

        const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];

        if (!allowedTypes.includes(file.type)) {

            showMessage("Only JPG, PNG, and WEBP images are allowed.", "danger");

            event.target.value = "";
            selectedProfileImage = null;

            return;
        }

        if (file.size > 3 * 1024 * 1024) {

            showMessage("The profile picture must be 3 MB or smaller.", "danger");

            event.target.value = "";
            selectedProfileImage = null;

            return;
        }

        selectedProfileImage = file;

        const reader = new FileReader();

        reader.onload = function(e) {
            document.getElementById("previewImg").src = e.target.result;
        };

        reader.readAsDataURL(file);

    }
);

// ==========================================
// SAVE CUSTOMER PROFILE
// ==========================================

async function saveCustomerProfile() {

    const button = document.getElementById("confirmSaveBtn");

    button.disabled = true;
    button.textContent = "Saving...";

    try {

        const payload = {
            firstName: document.getElementById("firstName").value.trim(),
            middleName: document.getElementById("middleName").value.trim(),
            lastName: document.getElementById("lastName").value.trim(),
            emailAddress: document.getElementById("emailAddress").value.trim(),
            contactNumber: document.getElementById("contactNumber").value.trim(),
            street: document.getElementById("street").value.trim(),
            barangay: document.getElementById("barangay").value.trim(),
            city: document.getElementById("city").value.trim()
        };

        if (selectedProfileImage) {

            payload.profileImage = await new Promise((resolve, reject) => {

                const reader = new FileReader();

                reader.onload = () => resolve(reader.result);

                reader.onerror = () => reject(
                    new Error("Could not read the selected image.")
                );

                reader.readAsDataURL(selectedProfileImage);

            });

        }

        await apiRequest(
            `/admin/customers/${CUSTOMER_ID}`,
            {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            }
        );

        if (saveModal) {
            saveModal.hide();
        }

        selectedProfileImage = null;

        setViewMode();

        await loadCustomerProfile();

        showMessage("Customer information updated successfully.");

    } catch (error) {

        console.error("Save customer error:", error);

        showMessage(error.message, "danger");

    } finally {

        button.disabled = false;
        button.textContent = "Confirm Save";

    }

}

// ==========================================
// RESET CUSTOMER PASSWORD
// ==========================================

async function resetCustomerPassword() {

    const passwordInput = document.getElementById("newPassword");

    const newPassword = passwordInput.value;

    if (newPassword.length < 8 || newPassword.length > 72) {

        showMessage(
            "Password must contain between 8 and 72 characters.",
            "danger"
        );

        return;
    }

    if (!confirm("Are you sure you want to reset this customer's password?")) {
        return;
    }

    try {

        await apiRequest(
            `/admin/customers/${CUSTOMER_ID}/password`,
            {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    newPassword
                })
            }
        );

        passwordInput.value = "";

        showMessage("Customer password reset successfully.");

    } catch (error) {

        console.error("Password reset error:", error);

        showMessage(error.message, "danger");

    }

}

// ==========================================
// ACCOUNT STATUS BUTTON
// ==========================================

function updateStatusButton(status) {

    const button = document.getElementById("statusBtn");

    const normalizedStatus = String(status || "").toLowerCase();

    if (normalizedStatus === "active") {

        button.textContent = "Deactivate Account";
        button.className = "btn btn-outline-danger";

    } else {

        button.textContent = "Activate Account";
        button.className = "btn btn-outline-success";

    }

}

// ==========================================
// ACTIVATE / DEACTIVATE ACCOUNT
// ==========================================

async function toggleAccountStatus() {

    const currentStatus = String(
        customerData.accountStatus || ""
    ).toLowerCase();

    const newStatus = currentStatus === "active"
        ? "inactive"
        : "active";

    const action = newStatus === "active"
        ? "activate"
        : "deactivate";

    if (!confirm(`Are you sure you want to ${action} this customer account?`)) {
        return;
    }

    try {

        await apiRequest(
            `/admin/customers/${CUSTOMER_ID}/status`,
            {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    accountStatus: newStatus
                })
            }
        );

        await loadCustomerProfile();

        showMessage(`Customer account ${action}d successfully.`);

    } catch (error) {

        console.error("Account status error:", error);

        showMessage(error.message, "danger");

    }

}

// ==========================================
// ORDER HISTORY
// ==========================================

function renderOrders(orders) {

    const table = document.getElementById("ordersTable");

    if (!orders.length) {

        table.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    This customer has no orders yet.
                </td>
            </tr>
        `;

        return;
    }

    table.innerHTML = orders.map(order => {

        const orderNumber = escapeHTML(
            order.COnumber || order.uuid || "N/A"
        );

        const paymentMethod = escapeHTML(
            order.paymentMethod || "N/A"
        );

        const status = String(order.status || "Unknown");

        const statusClass = {
            pending: "warning text-dark",
            processing: "info text-dark",
            completed: "success",
            cancelled: "danger"
        }[status.toLowerCase()] || "secondary";

        const date = order.DateCreated
            ? new Date(order.DateCreated).toLocaleString()
            : "N/A";

        return `
            <tr>
                <td class="fw-semibold">${orderNumber}</td>

                <td>${paymentMethod}</td>

                <td>
                    ₱${Number(order.totalPrice || 0).toLocaleString(
                        "en-PH",
                        {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }
                    )}
                </td>

                <td>
                    <span class="badge bg-${statusClass}">
                        ${escapeHTML(status)}
                    </span>
                </td>

                <td>${escapeHTML(date)}</td>
            </tr>
        `;

    }).join("");

}

// ==========================================
// INITIAL LOAD
// ==========================================

document.addEventListener("DOMContentLoaded", () => {
    loadCustomerProfile();
});
</script>

        </div>
    </div>
</div>

<?php
include_once __DIR__ . "/includes/footer.php";
?>
