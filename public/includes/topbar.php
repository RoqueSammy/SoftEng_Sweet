
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

<<<<<<< HEAD
// Use the actual PHP script filename, with URL fallback.
$current_page = !empty($scriptPage) ? $scriptPage : $urlPage;

$isHomePage = ($current_page === 'index.php');
$isMenuPage = in_array($current_page, ['productpage.php', 'product.php'], true);
$isContactPage = ($current_page === 'contactus.php');
$isOrdersPage = ($current_page === 'order.php');
$isAboutPage = ($current_page === 'aboutus.php');
$isCartPage = ($current_page === 'cart.php');

$cartCount = 0;
=======
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
>>>>>>> second
?>

<nav class="navbar navbar-expand-lg">
    <div class="container">

        <!-- ==========================================
             LOGO
        ========================================== -->

        <a class="navbar-brand" href="/SoftEcoms/public/index.php">

            <!-- Full wordmark -->
            <img
                src="/SoftEcoms/public/assets/img/svg/sweet-claire-logo.svg"
                alt="Sweetclaire"
                class="logo-full d-none d-lg-block"
            >

            <!-- Small logo -->
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
<<<<<<< HEAD
=======

            <!-- ==========================================
                 MOBILE NAVBAR TOGGLE
            ========================================== -->
>>>>>>> second

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

<<<<<<< HEAD
=======
                <ul class="navbar-nav my-2 my-lg-0">

>>>>>>> second
                    <!-- HOME -->
                    <li class="nav-item">
                        <a
                            class="nav-link <?= $isHomePage ? 'active' : '' ?>"
                            href="/SoftEcoms/public/index.php"
                            <?= $isHomePage ? 'aria-current="page"' : '' ?>
                        >
                            Home
                        </a>
                    </li>

                    <!-- MENU -->
                    <li class="nav-item">
                        <a
                            class="nav-link <?= $isMenuPage ? 'active' : '' ?>"
                            href="/SoftEcoms/public/productpage.php"
                            <?= $isMenuPage ? 'aria-current="page"' : '' ?>
                        >
                            Menu
                        </a>
                    </li>

<<<<<<< HEAD
                    <!-- CONTACT US -->
=======
                    <!-- CONTACT -->
>>>>>>> second
                    <li class="nav-item">
                        <a
                            class="nav-link <?= $isContactPage ? 'active' : '' ?>"
                            href="/SoftEcoms/public/contactUs.php"
                            <?= $isContactPage ? 'aria-current="page"' : '' ?>
                        >
                            Contact
                        </a>
                    </li>

                    <!-- ORDERS -->
                    <li class="nav-item">
                        <a
                            class="nav-link <?= $isOrdersPage ? 'active' : '' ?>"
                            href="/SoftEcoms/public/order.php"
                            <?= $isOrdersPage ? 'aria-current="page"' : '' ?>
                        >
                            Orders
                        </a>
                    </li>

                    <!-- ABOUT -->
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

<<<<<<< HEAD
                    <!-- ACCOUNT DROPDOWN -->

                    <div id="accountNav" style="visibility: hidden;">

                        <a
                            class="nav-link"
                            href="/SoftEcoms/public/login.php"
                            style="
                                background-color: black;
                                color: #F1D0C9;
                                padding: 8px 20px;
                                display: inline-block;
                            "
                        >
                            Login
                        </a>

                    </div>


                    <!-- SHOPPING CART -->

                    <a
=======
            <div class="navbar-actions">
                <div id="accountNav">
                    <a class="account-login" href="/SoftEcoms/public/login.php">Login</a>
                </div>

                <a
>>>>>>> second
                        href="/SoftEcoms/public/cart.php"
                        class="nav-icon <?= $isCartPage ? 'active' : '' ?>"
                        id="cartIcon"
                        aria-label="Shopping cart"
<<<<<<< HEAD
=======
                        title="Shopping Cart"
>>>>>>> second
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            fill="currentColor"
                            class="bi bi-cart"
                            viewBox="0 0 16 16"
                        >
<<<<<<< HEAD

=======
>>>>>>> second
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7z" />

                            <path d="M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4" />

                        </svg>

                        <span
                            id="cartCount"
                            class="cart-count"
                            style="display: none;"
                        >0</span>

<<<<<<< HEAD
                    </a>

                </div>

=======
                </a>
>>>>>>> second
            </div>

        <?php endif; ?>

    </div>
</nav>

<<<<<<< HEAD
=======
<!-- ==========================================
     NAVBAR JAVASCRIPT
========================================== -->
>>>>>>> second

<script>
document.addEventListener("DOMContentLoaded", function () {

<<<<<<< HEAD
    const API_URL = "http://localhost:3000";

=======
    const API_URL = "/api";
    const navToggle = document.querySelector(".navbar-toggler");
    const navCollapse = document.getElementById("navbarScroll");

    if (navToggle && navCollapse) {
        navToggle.addEventListener("click", function () {
            const isOpen = navCollapse.classList.toggle("show");
            navToggle.setAttribute("aria-expanded", String(isOpen));
        });

        navCollapse.querySelectorAll(".nav-link").forEach(link => {
            link.addEventListener("click", () => {
                if (window.innerWidth < 992) {
                    navCollapse.classList.remove("show");
                    navToggle.setAttribute("aria-expanded", "false");
                }
            });
        });
    }
>>>>>>> second

    // ==========================================
    // UPDATE CART COUNTER
    // ==========================================

    async function refreshCartCount() {

        const badge = document.getElementById("cartCount");

        if (!badge) {
            return;
        }

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
<<<<<<< HEAD

    }


    // Refresh cart count on every page
    refreshCartCount();


=======
    }

>>>>>>> second
    // ==========================================
    // ACCOUNT NAVIGATION
    // ==========================================

    const accountNav = document.getElementById("accountNav");

    if (!accountNav) {
        return;
    }
<<<<<<< HEAD
=======

    // Start the cart request alongside the account request to avoid a serial delay.
    refreshCartCount();
>>>>>>> second

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

<<<<<<< HEAD
            // Guest user: display Login button.
            if (response.status === 100 || response.status === 401) {
=======
            // Guest user
            if (response.status === 401) {
>>>>>>> second
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

<<<<<<< HEAD

            // ACCOUNT DROPDOWN

            const wrapper = document.createElement("div");
            wrapper.className = "nav-item dropdown";

            const toggle = document.createElement("a");

            toggle.className = "nav-link dropdown-toggle";
            toggle.href = "#";

            toggle.setAttribute("role", "button");
            toggle.setAttribute("data-bs-toggle", "dropdown");
            toggle.setAttribute("aria-expanded", "false");

            toggle.textContent =
                user.username || user.fullName || "My Account";


            const menu = document.createElement("ul");
            menu.className = "dropdown-menu dropdown-menu-end";


            // PROFILE

=======
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
            toggle.setAttribute("aria-label", "Open account menu");

            const avatar = document.createElement("img");
            avatar.className = "account-avatar";
            avatar.alt = "";
            avatar.src = user.profileImageUrl || (user.profile
                ? "/SoftEcoms/public/assets/profile_img/" + encodeURIComponent(user.profile)
                : "/SoftEcoms/public/assets/profile_img/default-avatar.svg");
            avatar.onerror = () => {
                avatar.onerror = null;
                avatar.src = "/SoftEcoms/public/assets/profile_img/default-avatar.svg";
            };
            const displayName = document.createElement("span");
            displayName.className = "account-name";
            displayName.textContent = user.username || user.fullName || user.firstName || "My Account";
            toggle.append(avatar, displayName);

            const menu = document.createElement("ul");
            menu.className = "dropdown-menu dropdown-menu-end account-menu";

            toggle.addEventListener("click", function () {
                const isOpen = menu.classList.toggle("show");
                toggle.setAttribute("aria-expanded", String(isOpen));
            });

            document.addEventListener("click", function (event) {
                if (!wrapper.contains(event.target)) {
                    menu.classList.remove("show");
                    toggle.setAttribute("aria-expanded", "false");
                }
            });

            document.addEventListener("keydown", function (event) {
                if (event.key === "Escape") {
                    menu.classList.remove("show");
                    toggle.setAttribute("aria-expanded", "false");
                    toggle.focus();
                }
            });

            // PROFILE
>>>>>>> second
            const profileItem = document.createElement("li");

            const profileLink = document.createElement("a");

            profileLink.className = "dropdown-item";
<<<<<<< HEAD

=======
>>>>>>> second
            profileLink.href =
                "/SoftEcoms/public/users/profilePage.php";

            profileLink.textContent = "Profile";

            profileItem.appendChild(profileLink);

<<<<<<< HEAD

            // DIVIDER

            const dividerItem = document.createElement("li");

            const divider = document.createElement("hr");

=======
            // DIVIDER
            const dividerItem = document.createElement("li");

            const divider = document.createElement("hr");
>>>>>>> second
            divider.className = "dropdown-divider";

            dividerItem.appendChild(divider);

<<<<<<< HEAD

            // SIGN OUT

=======
            // SIGN OUT
>>>>>>> second
            const logoutItem = document.createElement("li");

            const logoutButton = document.createElement("button");

            logoutButton.type = "button";
<<<<<<< HEAD

            logoutButton.className = "dropdown-item";

            logoutButton.textContent = "Sign Out";


=======
            logoutButton.className = "dropdown-item";
            logoutButton.textContent = "Sign Out";

>>>>>>> second
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

<<<<<<< HEAD

            // APPEND DROPDOWN

            menu.append(
                profileItem,
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

=======
            // APPEND DROPDOWN
            menu.append(
                profileItem,
                dividerItem,
                logoutItem
            );

            const settingsItem = document.createElement("li");
            const settingsLink = document.createElement("a");
            settingsLink.className = "dropdown-item";
            settingsLink.href = "/SoftEcoms/public/users/settings.php";
            settingsLink.textContent = "Settings";
            settingsItem.appendChild(settingsLink);
            menu.insertBefore(settingsItem, dividerItem);

            wrapper.append(toggle, menu);

            accountNav.replaceChildren(wrapper);

            showAccountNav();

        } catch (error) {

            console.error("Account navbar error:", error);

            showAccountNav();

        }
>>>>>>> second
    }

    loadAccountNav();

});
</script>
