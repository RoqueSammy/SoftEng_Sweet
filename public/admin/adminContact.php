
<?php
session_start();

$pageClass = "Admin-contact";
include_once __DIR__ . "/includes/header.php";
?>

<style>
/* Contact page details; shared seller-centre layout comes from adminTheme.css. */
.contact-stat-card { min-height: 105px; }
.contact-table th {
    white-space: nowrap;
}

.contact-table td {
    vertical-align: middle;
}

.contact-message-content {
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

</style>

<div class="dashboard-layout contact-layout">

    <?php include_once __DIR__ . "/includes/sidebar.php"; ?>

    <div class="main-content">

        <div class="container-fluid py-4">

            <!-- PAGE HEADER -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div class="admin-page-heading">
                    <span class="admin-eyebrow">CUSTOMER SUPPORT</span>
                    <h2 class="fw-bold mb-1">Contact messages</h2>
                    <p class="text-muted mb-0">
                        View and manage customer inquiries.
                    </p>
                </div>

                <button type="button" class="btn btn-outline-primary" id="refreshBtn">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh messages
                </button>

            </div>

            <div id="pageMessage"></div>

            <!-- STATISTICS -->
            <div class="row g-3 mb-4">

                <div class="col-6 col-xl-3">
                    <div class="card contact-stat-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="admin-stat-icon"><i class="fa-regular fa-envelope"></i></div>
                            <p class="text-muted mb-1">Total messages</p>
                            <h3 class="fw-bold mb-0" id="totalCount">0</h3>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-xl-3">
                    <div class="card contact-stat-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="admin-stat-icon is-warning"><i class="fa-regular fa-bell"></i></div>
                            <p class="text-muted mb-1">Unread</p>
                            <h3 class="fw-bold mb-0 text-warning" id="unreadCount">0</h3>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-xl-3">
                    <div class="card contact-stat-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="admin-stat-icon is-info"><i class="fa-regular fa-envelope-open"></i></div>
                            <p class="text-muted mb-1">Read</p>
                            <h3 class="fw-bold mb-0 text-primary" id="readCount">0</h3>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-xl-3">
                    <div class="card contact-stat-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="admin-stat-icon is-success"><i class="fa-solid fa-reply"></i></div>
                            <p class="text-muted mb-1">Replied / handled</p>
                            <h3 class="fw-bold mb-0 text-success" id="repliedCount">0</h3>
                        </div>
                    </div>
                </div>

            </div>

            <!-- CONTACT MESSAGES TABLE -->
            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <!-- SEARCH AND FILTER -->
                    <div class="row g-3 mb-3">

                        <div class="col-lg-8">
                            <input
                                type="search"
                                id="searchInput"
                                class="form-control"
                                placeholder="Search customer, email, or subject..."
                            >
                        </div>

                        <div class="col-lg-4">
                            <select id="statusFilter" class="form-select">
                                <option value="all">All statuses</option>
                                <option value="unread">Unread</option>
                                <option value="read">Read</option>
                                <option value="replied">Replied</option>
                            </select>
                        </div>

                    </div>

                    <!-- TABLE -->
                    <div class="table-responsive">

                        <table class="table table-hover align-middle contact-table">

                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody id="messagesTable">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Loading messages...
                                    </td>
                                </tr>
                            </tbody>

                        </table>

                    </div>

                    <p class="small text-muted mb-0" id="resultCount"></p>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- MESSAGE DETAILS MODAL -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Customer Message</h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>
            </div>

            <div class="modal-body">

                <div class="row g-2 mb-3">

                    <div class="col-md-6">
                        <strong>Customer:</strong>
                        <span id="detailName"></span>
                    </div>

                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <span id="detailEmail"></span>
                    </div>

                    <div class="col-md-6">
                        <strong>Subject:</strong>
                        <span id="detailSubject"></span>
                    </div>

                    <div class="col-md-6">
                        <strong>Date:</strong>
                        <span id="detailDate"></span>
                    </div>

                </div>

                <hr>

                <div class="fw-semibold mb-2">Message</div>

                <div
                    id="detailMessage"
                    class="p-3 rounded bg-light contact-message-content">
                </div>

                <div class="mt-3">
                    <label for="replyMessage" class="form-label fw-semibold">Reply to customer</label>
                    <textarea id="replyMessage" class="form-control" rows="5" maxlength="5000" placeholder="Write your response..."></textarea>
                    <div class="form-text">This reply will be emailed to the customer.</div>
                </div>

                <div class="mt-3">

                    <label for="detailStatus" class="form-label fw-semibold">
                        Message Status
                    </label>

                    <select id="detailStatus" class="form-select">
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied / Handled</option>
                    </select>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">
                    Close
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="sendReplyBtn">
                    <i class="fa-solid fa-paper-plane me-1"></i> Send reply
                </button>

                <button
                    type="button"
                    class="btn btn-outline-primary"
                    id="saveStatusBtn">
                    Save Status
                </button>

            </div>

        </div>

    </div>

</div>

<script>
(() => {
    "use strict";

    const API_URL = "/api";

    const tableBody = document.getElementById("messagesTable");
    const searchInput = document.getElementById("searchInput");
    const statusFilter = document.getElementById("statusFilter");
    const modalElement = document.getElementById("messageModal");

    let allMessages = [];
    let selectedMessageId = null;
    let messageModal = null;

    if (window.bootstrap && modalElement) {
        messageModal = new bootstrap.Modal(modalElement);
    }

    // Prevent HTML injection
    function escapeHTML(value) {
        return String(value ?? "").replace(/[&<>"']/g, char => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        })[char]);
    }

    // Display notifications
    function showMessage(message, type = "success") {
        const container = document.getElementById("pageMessage");

        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${escapeHTML(message)}
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
                </button>
            </div>
        `;

        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    }

    // API REQUEST
    async function apiRequest(endpoint, options = {}) {

        const response = await fetch(API_URL + endpoint, {
            ...options,

            credentials: "include",

            headers: {
                Accept: "application/json",

                ...(options.body
                    ? { "Content-Type": "application/json" }
                    : {}),

                ...(options.headers || {})
            },

            cache: "no-store"
        });

        const responseText = await response.text();

        let data;

        try {
            data = responseText ? JSON.parse(responseText) : {};
        } catch {
            throw new Error(
                "The server returned an invalid response. Check the Node.js terminal."
            );
        }

        if (!response.ok || data.success === false) {
            throw new Error(
                data.message || `Request failed (${response.status}).`
            );
        }

        return data;
    }

    // FORMAT DATE
    function formatDate(value) {

        if (!value) return "N/A";

        const date = new Date(value);

        return Number.isNaN(date.getTime())
            ? "N/A"
            : date.toLocaleString();
    }

    // STATUS BADGE
    function statusBadge(status) {

        const normalized = String(status || "unread").toLowerCase();

        const classes = {
            unread: "warning text-dark",
            read: "primary",
            replied: "success"
        };

        const labels = {
            unread: "Unread",
            read: "Read",
            replied: "Replied"
        };

        return `
            <span class="badge bg-${classes[normalized] || "secondary"}">
                ${labels[normalized] || escapeHTML(status)}
            </span>
        `;
    }

    // UPDATE STATISTICS
    function updateStatistics() {

        document.getElementById("totalCount").textContent =
            allMessages.length;

        document.getElementById("unreadCount").textContent =
            allMessages.filter(item => item.status === "unread").length;

        document.getElementById("readCount").textContent =
            allMessages.filter(item => item.status === "read").length;

        document.getElementById("repliedCount").textContent =
            allMessages.filter(item => item.status === "replied").length;
    }

    // RENDER MESSAGES
    function renderMessages() {

        const term = searchInput.value.trim().toLowerCase();
        const selectedStatus = statusFilter.value;

        const filtered = allMessages.filter(item => {

            const searchable = [
                item.firstName,
                item.lastName,
                item.email,
                item.subject,
                item.message
            ].join(" ").toLowerCase();

            const matchesSearch = searchable.includes(term);

            const matchesStatus =
                selectedStatus === "all" ||
                item.status === selectedStatus;

            return matchesSearch && matchesStatus;
        });

        if (!filtered.length) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No contact messages found.
                    </td>
                </tr>
            `;

        } else {

            tableBody.innerHTML = filtered.map(item => {

                const fullName =
                    `${item.firstName || ""} ${item.lastName || ""}`.trim()
                    || "Unknown customer";

                return `
                    <tr>

                        <td class="fw-semibold">
                            ${escapeHTML(fullName)}
                        </td>

                        <td>
                            ${escapeHTML(item.email)}
                        </td>

                        <td>
                            ${escapeHTML(item.subject)}
                        </td>

                        <td>
                            ${escapeHTML(formatDate(item.created_at))}
                        </td>

                        <td>
                            ${statusBadge(item.status)}
                        </td>

                        <td class="text-end">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-view-id="${Number(item.id)}">
                                View
                            </button>

                        </td>

                    </tr>
                `;

            }).join("");
        }

        document.getElementById("resultCount").textContent =
            `Showing ${filtered.length} of ${allMessages.length} messages`;
    }

    // LOAD ALL MESSAGES
    async function loadMessages() {

        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    Loading messages...
                </td>
            </tr>
        `;

        try {

            const data = await apiRequest("/contact/messages");

            allMessages = Array.isArray(data.messages)
                ? data.messages
                : [];

            updateStatistics();
            renderMessages();

        } catch (error) {

            console.error("Load contact messages error:", error);

            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger py-4">
                        ${escapeHTML(error.message)}
                    </td>
                </tr>
            `;

            showMessage(error.message, "danger");
        }
    }

    // VIEW MESSAGE DETAILS
    async function openMessage(id) {

        try {

            const data = await apiRequest(`/contact/messages/${id}`);
            const item = data.message;

            selectedMessageId = Number(item.id);

            document.getElementById("detailName").textContent =
                `${item.firstName || ""} ${item.lastName || ""}`.trim();

            document.getElementById("detailEmail").textContent =
                item.email || "";

            document.getElementById("detailSubject").textContent =
                item.subject || "";

            document.getElementById("detailDate").textContent =
                formatDate(item.created_at);

            document.getElementById("detailMessage").textContent =
                item.message || "";

            document.getElementById("detailStatus").value =
                item.status || "unread";

            document.getElementById("replyMessage").value = "";

            if (messageModal) {

                messageModal.show();

            } else {

                modalElement.style.display = "block";
                modalElement.classList.add("show");
                modalElement.removeAttribute("aria-hidden");
            }

        } catch (error) {

            console.error("Open contact message error:", error);

            showMessage(error.message, "danger");
        }
    }

    // UPDATE MESSAGE STATUS
    async function saveMessageStatus() {

        if (!selectedMessageId) return;

        const button = document.getElementById("saveStatusBtn");

        const status =
            document.getElementById("detailStatus").value;

        button.disabled = true;
        button.textContent = "Saving...";

        try {

            await apiRequest(
                `/contact/messages/${selectedMessageId}/status`,
                {
                    method: "PATCH",
                    body: JSON.stringify({ status })
                }
            );

            if (messageModal) {

                messageModal.hide();

            } else {

                modalElement.style.display = "none";
                modalElement.classList.remove("show");
                modalElement.setAttribute("aria-hidden", "true");
            }

            showMessage("Message status updated successfully.");

            await loadMessages();

        } catch (error) {

            console.error("Update message status error:", error);

            showMessage(error.message, "danger");

        } finally {

            button.disabled = false;
            button.textContent = "Save Status";
        }
    }

    async function sendReply() {
        if (!selectedMessageId) return;
        const button = document.getElementById("sendReplyBtn");
        const reply = document.getElementById("replyMessage").value.trim();
        if (!reply) {
            showMessage("Write a reply before sending.", "warning");
            return;
        }
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
        try {
            const data = await apiRequest(`/contact/messages/${selectedMessageId}/reply`, {
                method: "POST",
                body: JSON.stringify({ reply })
            });
            if (messageModal) messageModal.hide();
            else {
                modalElement.style.display = "none";
                modalElement.classList.remove("show");
                modalElement.setAttribute("aria-hidden", "true");
            }
            showMessage(data.message || "Reply sent successfully.");
            await loadMessages();
        } catch (error) {
            console.error("Send customer reply error:", error);
            showMessage(error.message, "danger");
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send reply';
        }
    }

    // EVENT LISTENERS
    tableBody.addEventListener("click", event => {

        const button = event.target.closest("[data-view-id]");

        if (button) {
            openMessage(button.dataset.viewId);
        }
    });

    searchInput.addEventListener("input", renderMessages);

    statusFilter.addEventListener("change", renderMessages);

    document.getElementById("refreshBtn")
        .addEventListener("click", loadMessages);

    document.getElementById("saveStatusBtn")
        .addEventListener("click", saveMessageStatus);

    document.getElementById("sendReplyBtn")
        .addEventListener("click", sendReply);

    // INITIAL LOAD
    loadMessages();

})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
