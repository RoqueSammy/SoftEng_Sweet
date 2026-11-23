
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// DETECT CURRENT PAGE
// ==========================================

$scriptPage = strtolower(
    basename($_SERVER['SCRIPT_NAME'] ?? '')
);

$urlPage = strtolower(
    basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH))
);

$current_page = !empty($scriptPage) ? $scriptPage : $urlPage;

$isHomePage = ($current_page === 'index.php');

$isMenuPage = in_array(
    $current_page,
    ['productpage.php', 'product.php'],
    true
);

$isContactPage = ($current_page === 'contactus.php');

$isOrdersPage = in_array(
    $current_page,
    ['order.php', 'orderhistory.php'],
    true
);

$isAboutPage = ($current_page === 'aboutus.php');

$isCartPage = ($current_page === 'cart.php');
?>

<nav class="navbar navbar-expand-lg">
    <div class="container">

        <!-- ==========================================
             LOGO
        ========================================== -->

        <a class="navbar-brand" href="/SoftEcoms/public/index.php">

            <img
                src="/SoftEcoms/public/assets/img/svg/sweet-claire-logo.svg"
                alt="Sweetclaire"
                class="logo-full d-none d-lg-block"
            >

            <img
                src="/SoftEcoms/public/assets/img/svg/logo.svg"
                alt="Sweetclaire"
                class="logo-small d-block d-lg-none"
            >

        </a>

        <?php if (!in_array($current_page, [
            'login.php',
            'register.php',
            'verifyforgetpassword.php',
            'forgetpassword.php'
        ], true)) : ?>

            <!-- ==========================================
                 MOBILE NAVBAR TOGGLE
            ========================================== -->

            <button
                class="navbar-toggler"
                type="button"
                data-bs-target="#navbarScroll"
                aria-controls="navbarScroll"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarScroll">

                <!-- ==========================================
                     NAVIGATION LINKS
                ========================================== -->

                <ul class="navbar-nav my-2 my-lg-0">

                    <li class="nav-item">
                        <a
                            class="nav-link <?= $isHomePage ? 'active' : '' ?>"
                            href="/SoftEcoms/public/index.php"
                            <?= $isHomePage ? 'aria-current="page"' : '' ?>
                        >
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link <?= $isMenuPage ? 'active' : '' ?>"
                            href="/SoftEcoms/public/productpage.php"
                            <?= $isMenuPage ? 'aria-current="page"' : '' ?>
                        >
                            Menu
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link <?= $isContactPage ? 'active' : '' ?>"
                            href="/SoftEcoms/public/contactUs.php"
                            <?= $isContactPage ? 'aria-current="page"' : '' ?>
                        >
                            Contact
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link <?= $isOrdersPage ? 'active' : '' ?>"
                            href="/SoftEcoms/public/order.php"
                            <?= $isOrdersPage ? 'aria-current="page"' : '' ?>
                        >
                            Orders
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link <?= $isAboutPage ? 'active' : '' ?>"
                            href="/SoftEcoms/public/aboutUs.php"
                            <?= $isAboutPage ? 'aria-current="page"' : '' ?>
                        >
                            About
                        </a>
                    </li>

                </ul>

            </div>

            <!-- ==========================================
                 NAVBAR ACTIONS
            ========================================== -->

            <div class="navbar-actions">

                <!-- ACCOUNT -->
                <div id="accountNav">
                    <a
                        class="account-login"
                        href="/SoftEcoms/public/login.php"
                    >
                        Login
                    </a>
                </div>

                <!-- ======================================
                     NOTIFICATION ICON
                ====================================== -->

                <div class="notification-wrapper">

                    <button
                        type="button"
                        class="nav-icon notification-icon"
                        id="notificationIcon"
                        aria-label="Notifications"
                        aria-expanded="false"
                        title="Notifications"
                    >

                        
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="m3 7 9 6 9-6"/>
                        </svg>


                        <span
                            id="notificationCount"
                            class="notification-count"
                            style="display: none;"
                        >
                            0
                        </span>

                    </button>

                    <!-- NOTIFICATION DROPDOWN -->
                    <div
                        class="notification-dropdown"
                        id="notificationDropdown"
                    >

                        <div class="notification-header">

                            <h5>Notifications</h5>

                            <button
                                type="button"
                                id="markAllRead"
                                class="mark-all-read"
                            >
                                Mark all as read
                            </button>

                        </div>

                        <div
                            id="notificationList"
                            class="notification-list"
                        >
                            <p class="notification-empty">
                                No notifications yet.
                            </p>
                        </div>

                    </div>

                </div>

                <!-- ======================================
                     CART ICON
                ====================================== -->

                <a
                    href="/SoftEcoms/public/cart.php"
                    class="nav-icon <?= $isCartPage ? 'active' : '' ?>"
                    id="cartIcon"
                    aria-label="Shopping cart"
                    title="Shopping Cart"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="22"
                        height="22"
                        fill="currentColor"
                        class="bi bi-cart"
                        viewBox="0 0 16 16"
                    >
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7z"/>
                        <path d="M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                    </svg>

                    <span
                        id="cartCount"
                        class="cart-count"
                        style="display: none;"
                    >0</span>

                </a>

            </div>

        <?php endif; ?>

    </div>
</nav>

<!-- ==========================================
     NAVBAR JAVASCRIPT
========================================== -->

<script>
document.addEventListener("DOMContentLoaded", function () {

    const API_URL = "/api";

    const navToggle = document.querySelector(".navbar-toggler");
    const navCollapse = document.getElementById("navbarScroll");

    // ==========================================
    // MOBILE NAVIGATION
    // ==========================================

    if (navToggle && navCollapse) {

        navToggle.addEventListener("click", function () {

            const isOpen = navCollapse.classList.toggle("show");

            navToggle.setAttribute(
                "aria-expanded",
                String(isOpen)
            );

        });

        navCollapse.querySelectorAll(".nav-link").forEach(link => {

            link.addEventListener("click", () => {

                if (window.innerWidth < 992) {

                    navCollapse.classList.remove("show");

                    navToggle.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                }

            });

        });

    }

    // ==========================================
    // UPDATE CART COUNTER
    // ==========================================

    async function refreshCartCount() {

        const badge = document.getElementById("cartCount");

        if (!badge) return;

        try {

            const response = await fetch(
                API_URL + "/cart/count",
                {
                    method: "GET",
                    credentials: "include",
                    headers: {
                        "Accept": "application/json"
                    },
                    cache: "no-store"
                }
            );

            if (!response.ok) {
                throw new Error("Unable to retrieve cart count.");
            }

            const data = await response.json();

            if (data.success !== true) {
                throw new Error(
                    data.message || "Unable to retrieve cart count."
                );
            }

            const count = Math.max(
                0,
                Number(data.cartCount) || 0
            );

            badge.textContent = count;

            badge.style.display =
                count > 0 ? "flex" : "none";

        } catch (error) {

            console.error("Cart counter error:", error);

        }

    }

    refreshCartCount();

    // ==========================================
    // NOTIFICATION SYSTEM
    // FRONTEND ONLY - LOCAL STORAGE
    // ==========================================

    const notificationIcon = document.getElementById("notificationIcon");
    const notificationDropdown = document.getElementById("notificationDropdown");
    const notificationList = document.getElementById("notificationList");
    const notificationCount = document.getElementById("notificationCount");
    const markAllRead = document.getElementById("markAllRead");

    const NOTIFICATION_KEY = "sweetclaire_admin_inbox";

    let notifications = [];

    const sampleNotifications = [
        {
            id: 1,
            title: "Welcome to SweetClaire!",
            message: "Thank you for joining SweetClaire. We are happy to have you as our customer!",
            date: "November 23, 2026",
            read: false
        },
        {
            id: 2,
            title: "Special Announcement",
            message: "Check out our latest bakery products and special offers!",
            date: "November 22, 2026",
            read: false
        },
        {
            id: 3,
            title: "Store Information",
            message: "Thank you for supporting SweetClaire. Have a wonderful day!",
            date: "November 20, 2026",
            read: true
        }
    ];

    // ESCAPE TEXT
    function escapeNotificationText(value) {

        const element = document.createElement("div");

        element.textContent = String(value ?? "");

        return element.innerHTML;

    }

    // SAVE NOTIFICATIONS
    function saveNotifications() {

        localStorage.setItem(
            NOTIFICATION_KEY,
            JSON.stringify(notifications)
        );

    }

    // UPDATE UNREAD COUNT
    function updateNotificationCount() {

        const unreadCount = notifications.filter(
            notification => !notification.read
        ).length;

        notificationCount.textContent = unreadCount;

        notificationCount.style.display =
            unreadCount > 0 ? "flex" : "none";

    }

    // DISPLAY NOTIFICATIONS
    function renderNotifications() {

        if (!notificationList) return;

        updateNotificationCount();

        if (notifications.length === 0) {

            notificationList.innerHTML = `
                <p class="notification-empty">
                    No notifications yet.
                </p>
            `;

            return;

        }

        notificationList.innerHTML = notifications.map(notification => `

            <div
                class="notification-item ${notification.read ? 'read' : 'unread'}"
                data-id="${escapeNotificationText(notification.id)}"
            >

                <div class="notification-item-content">

                    <div class="notification-item-heading">

                        <strong>
                            ${escapeNotificationText(notification.title)}
                        </strong>

                        ${!notification.read
                            ? '<span class="notification-unread-dot"></span>'
                            : ''
                        }

                    </div>

                    <p>
                        ${escapeNotificationText(notification.message)}
                    </p>

                    <small>
                        ${escapeNotificationText(notification.date)}
                    </small>

                    <button
                        type="button"
                        class="delete-notification"
                        data-delete-id="${escapeNotificationText(notification.id)}"
                    >
                        Delete
                    </button>

                </div>

            </div>

        `).join("");

    }

    // LOAD NOTIFICATIONS
    function loadNotifications() {

        const savedNotifications = localStorage.getItem(
            NOTIFICATION_KEY
        );

        if (savedNotifications) {

            try {

                notifications = JSON.parse(savedNotifications);

                if (!Array.isArray(notifications)) {
                    throw new Error("Invalid notification data.");
                }

            } catch (error) {

                console.error("Notification loading error:", error);

                notifications = [...sampleNotifications];

                saveNotifications();

            }

        } else {

            notifications = [...sampleNotifications];

            saveNotifications();

        }

        renderNotifications();

    }

    // OPEN / CLOSE NOTIFICATION DROPDOWN
    notificationIcon?.addEventListener("click", function (event) {

        event.stopPropagation();

        const isOpen = notificationDropdown.classList.toggle("show");

        notificationIcon.setAttribute(
            "aria-expanded",
            String(isOpen)
        );

    });

    // CLOSE WHEN CLICKING OUTSIDE
    document.addEventListener("click", function (event) {

        if (
            notificationDropdown &&
            notificationIcon &&
            !notificationDropdown.contains(event.target) &&
            !notificationIcon.contains(event.target)
        ) {

            notificationDropdown.classList.remove("show");

            notificationIcon.setAttribute(
                "aria-expanded",
                "false"
            );

        }

    });

    // CLOSE WITH ESCAPE
    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape" && notificationDropdown) {

            notificationDropdown.classList.remove("show");

            notificationIcon?.setAttribute(
                "aria-expanded",
                "false"
            );

        }

    });

    // CLICK NOTIFICATION OR DELETE
    notificationList?.addEventListener("click", function (event) {

        const deleteButton = event.target.closest(
            ".delete-notification"
        );

        if (deleteButton) {

            const id = Number(deleteButton.dataset.deleteId);

            notifications = notifications.filter(
                notification => Number(notification.id) !== id
            );

            saveNotifications();

            renderNotifications();

            return;

        }

        const notificationItem = event.target.closest(
            ".notification-item"
        );

        if (!notificationItem) return;

        const id = Number(notificationItem.dataset.id);

        const selectedNotification = notifications.find(
            notification => Number(notification.id) === id
        );

        if (selectedNotification) {

            selectedNotification.read = true;

            saveNotifications();

            renderNotifications();

        }

    });

    // MARK ALL AS READ
    markAllRead?.addEventListener("click", function () {

        notifications.forEach(notification => {
            notification.read = true;
        });

        saveNotifications();

        renderNotifications();

    });

    // INITIALIZE NOTIFICATIONS
    if (
        notificationIcon &&
        notificationDropdown &&
        notificationList &&
        notificationCount
    ) {

        loadNotifications();

    }

    // ==========================================
    // ACCOUNT NAVIGATION
    // ==========================================

    const accountNav = document.getElementById("accountNav");

    if (!accountNav) {
        return;
    }

    function showAccountNav() {
        accountNav.style.visibility = "visible";
    }

    async function loadAccountNav() {

        try {

            const response = await fetch(
                API_URL + "/me",
                {
                    method: "GET",
                    credentials: "include",
                    cache: "no-store"
                }
            );

            // GUEST USER
            if (response.status === 401) {

                showAccountNav();

                return;

            }

            if (!response.ok) {

                throw new Error(
                    "Unable to retrieve account information."
                );

            }

            const data = await response.json();

            const user = data.user;

            if (!user) {

                showAccountNav();

                return;

            }

            // ==========================================
            // ACCOUNT DROPDOWN
            // ==========================================

            const wrapper = document.createElement("div");

            wrapper.className = "dropdown account-dropdown";

            const toggle = document.createElement("button");

            toggle.type = "button";

            toggle.className = "account-toggle dropdown-toggle";

            toggle.setAttribute("aria-haspopup", "true");

            toggle.setAttribute("aria-expanded", "false");

            toggle.setAttribute(
                "aria-label",
                "Open account menu"
            );

            const avatar = document.createElement("img");

            avatar.className = "account-avatar";

            avatar.alt = "";

            avatar.src = user.profileImageUrl || (
                user.profile
                    ? "/SoftEcoms/public/assets/profile_img/" +
                      encodeURIComponent(user.profile)
                    : "/SoftEcoms/public/assets/profile_img/default-avatar.svg"
            );

            avatar.onerror = () => {

                avatar.onerror = null;

                avatar.src =
                    "/SoftEcoms/public/assets/profile_img/default-avatar.svg";

            };

            const displayName = document.createElement("span");

            displayName.className = "account-name";

            displayName.textContent =
                user.username ||
                user.fullName ||
                user.firstName ||
                "My Account";

            toggle.append(avatar, displayName);

            const menu = document.createElement("ul");

            menu.className =
                "dropdown-menu dropdown-menu-end account-menu";

            toggle.addEventListener("click", function () {

                const isOpen = menu.classList.toggle("show");

                toggle.setAttribute(
                    "aria-expanded",
                    String(isOpen)
                );

            });

            document.addEventListener("click", function (event) {

                if (!wrapper.contains(event.target)) {

                    menu.classList.remove("show");

                    toggle.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                }

            });

            document.addEventListener("keydown", function (event) {

                if (event.key === "Escape") {

                    menu.classList.remove("show");

                    toggle.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                    toggle.focus();

                }

            });

            // PROFILE
            const profileItem = document.createElement("li");

            const profileLink = document.createElement("a");

            profileLink.className = "dropdown-item";

            profileLink.href =
                "/SoftEcoms/public/users/profilePage.php";

            profileLink.textContent = "Profile";

            profileItem.appendChild(profileLink);

            // SETTINGS
            const settingsItem = document.createElement("li");

            const settingsLink = document.createElement("a");

            settingsLink.className = "dropdown-item";

            settingsLink.href =
                "/SoftEcoms/public/users/settings.php";

            settingsLink.textContent = "Settings";

            settingsItem.appendChild(settingsLink);

            // DIVIDER
            const dividerItem = document.createElement("li");

            const divider = document.createElement("hr");

            divider.className = "dropdown-divider";

            dividerItem.appendChild(divider);

            // SIGN OUT
            const logoutItem = document.createElement("li");

            const logoutButton = document.createElement("button");

            logoutButton.type = "button";

            logoutButton.className = "dropdown-item";

            logoutButton.textContent = "Sign Out";

            logoutButton.addEventListener("click", async function () {

                logoutButton.disabled = true;

                try {

                    const logoutResponse = await fetch(
                        API_URL + "/logout",
                        {
                            method: "POST",
                            credentials: "include"
                        }
                    );

                    if (!logoutResponse.ok) {
                        throw new Error("Logout failed.");
                    }

                    window.location.href =
                        "/SoftEcoms/public/login.php";

                } catch (error) {

                    console.error("Sign out error:", error);

                    alert(
                        "Could not sign out. Check whether the Node.js server is running."
                    );

                    logoutButton.disabled = false;

                }

            });

            logoutItem.appendChild(logoutButton);

            // APPEND DROPDOWN
            menu.append(
                profileItem,
                settingsItem,
                dividerItem,
                logoutItem
            );

            wrapper.append(toggle, menu);

            accountNav.replaceChildren(wrapper);

            showAccountNav();

        } catch (error) {

            console.error("Account navbar error:", error);

            showAccountNav();

        }

    }

    loadAccountNav();

});
</script>
