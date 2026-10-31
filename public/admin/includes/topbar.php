<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg">
    <div class="container">

        <a class="navbar-brand" href="/SoftEcoms/public/index.php">

            <!-- Full wordmark: hidden on mobile, shown from lg up -->
            <!-- if naay changes regarding ani, consider look sa css/topbar.css -->
            <img src="/SoftEcoms/public/assets/img/svg/sweet-claire-logo.svg"
                alt="Sweetclaire"
                class="logo-full d-none d-lg-block">

            <!-- Small version: shown on mobile, hidden from lg up -->
            <img src="/SoftEcoms/public/assets/img/svg/logo.svg"
                alt="Sweetclaire"
                class="logo-small d-block d-lg-none">

        </a>

        <?php if (!in_array($current_page, ['login.php', 'register.php'], true)) : ?>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarScroll"
                aria-controls="navbarScroll"
                aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarScroll">

                <ul class="navbar-nav ms-lg-auto my-2 my-lg-0">

                    <li class="nav-item">
                        <a class="nav-link active"
                            href="/SoftEcoms/public/index.php">
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="/SoftEcoms/public/productpage.php">
                            Menu
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="/SoftEcoms/public/contactUs.php">
                            Contact Us
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="/SoftEcoms/public/order.php">
                            Order
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="/SoftEcoms/public/aboutUs.php">
                            About
                        </a>
                    </li>

                </ul>

                <div class="d-flex align-items-center gap-3">

                    <!-- Order history -->
                    <!-- <a href="/SoftEcoms/public/users/orderHistory.php"
                       class="nav-icon"
                       aria-label="Order history">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="20"
                             height="20"
                             fill="currentColor"
                             class="bi bi-receipt"
                             viewBox="0 0 16 16">

                            <path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27"/>

                            <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5"/>
                        </svg>
                    </a> -->

                    <!-- Shopping cart -->


                    <!-- Account dropdown: populated by Node.js -->
                    <div id="accountNav">
                        <a class="nav-link"
                            href="/SoftEcoms/public/login.php"
                            style="background-color: black; color: #F1D0C9; padding: 8px 20px;  
                            display: inline-block;  ">
                            Login
                        </a>
                    </div>


                    <a href="/SoftEcoms/public/cart.php"
                            class="nav-icon"
                            id="cartIcon"
                            aria-label="Shopping cart">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            fill="currentColor"
                            class="bi bi-cart"
                            viewBox="0 0 16 16">

                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7z" />

                            <path d="M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4" />
                        </svg>
                    </a>

                </div>
            </div>

        <?php endif; ?>

    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", async function() {
        const accountNav = document.getElementById("accountNav");

        if (!accountNav) return;

        try {
            const response = await fetch("http://localhost:3000/me", {
                method: "GET",
                credentials: "include"
            });

            if (response.status === 401) {
                return; // Keep Sign In visible for guests.
            }

            if (!response.ok) {
                throw new Error("Unable to retrieve account information.");
            }

            const data = await response.json();
            const user = data.user;

            if (!user) return;

            // Create the dropdown without inserting user data as HTML.
            const wrapper = document.createElement("div");
            wrapper.className = "nav-item dropdown";

            const toggle = document.createElement("a");
            toggle.className = "nav-link dropdown-toggle";
            toggle.href = "#";
            toggle.setAttribute("role", "button");
            toggle.setAttribute("data-bs-toggle", "dropdown");
            toggle.setAttribute("aria-expanded", "false");
            toggle.textContent = user.username || user.fullName || "My Account";

            const menu = document.createElement("ul");
            menu.className = "dropdown-menu dropdown-menu-end";

            const profileItem = document.createElement("li");
            const profileLink = document.createElement("a");
            profileLink.className = "dropdown-item";
            profileLink.href = "/SoftEcoms/public/users/profilePage.php";
            profileLink.textContent = "Profile";
            profileItem.appendChild(profileLink);

            const dividerItem = document.createElement("li");
            const divider = document.createElement("hr");
            divider.className = "dropdown-divider";
            dividerItem.appendChild(divider);

            const logoutItem = document.createElement("li");
            const logoutButton = document.createElement("button");
            logoutButton.type = "button";
            logoutButton.className = "dropdown-item";
            logoutButton.textContent = "Sign Out";

            logoutButton.addEventListener("click", async function() {
                logoutButton.disabled = true;

                try {
                    const logoutResponse = await fetch(
                        "http://localhost:3000/logout", {
                            method: "POST",
                            credentials: "include"
                        }
                    );

                    if (!logoutResponse.ok) {
                        throw new Error("Logout failed.");
                    }

                    window.location.href = "/SoftEcoms/public/login.php";

                } catch (error) {
                    console.error("Sign out error:", error);
                    alert("Could not sign out. Check whether the Node.js server is running.");
                    logoutButton.disabled = false;
                }
            });

            logoutItem.appendChild(logoutButton);

            menu.append(profileItem, dividerItem, logoutItem);
            wrapper.append(toggle, menu);
            accountNav.replaceChildren(wrapper);

        } catch (error) {
            console.error("Account navbar error:", error);
            // Leave Sign In visible if the API is unavailable.
        }
    });
</script>