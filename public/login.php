
<?php
session_start();

$pageClass = "login-page";

$loginNotice = "";

if (isset($_GET['notice']) && $_GET['notice'] === 'login_required') {
    $loginNotice = "Please log in to your account before adding products to your cart.";
}

include_once __DIR__ . "/../app/config/config.php";

include('includes/header.php');
include('includes/topbar.php');
?>

<section class="loginOverlay">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-xl-10 col-lg-11">

                <div class="card login-card">
                    <div class="row g-0">

                        <!-- LEFT SIDE: LOGIN FORM -->
                        <div class="col-lg-6 login-form-column">
                            <div class="card-body login-card-body">

                                <div class="text-center login-heading">
                                    <img
                                        src="/SoftEcoms/public/assets/img/svg/logo.svg"
                                        class="login-logo"
                                        alt="SweetClaire Logo">

                                    <h4 class="greet_login mt-3 mb-2">
                                        Welcome to SweetClaire!
                                    </h4>

                                    <p class="login-subtitle">
                                        Please login to your account
                                    </p>
                                </div>

                                <form id="loginForm">

                                    <div class="mb-3">
                                        <label class="form-label" for="email">
                                            Email Address
                                        </label>

                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            class="form-control"
                                            placeholder="Enter your email address"
                                            autocomplete="email"
                                            maxlength="254"
                                            required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="password">
                                            Password
                                        </label>

                                        <input
                                            type="password"
                                            id="password"
                                            name="password"
                                            class="form-control"
                                            placeholder="Enter your password"
                                            autocomplete="current-password"
                                            required>
                                    </div>

                                    <div class="text-end mb-3">
                                        <a
                                            href="/SoftEcoms/public/forgetpassword.php"
                                            class="forgot-password-link">
                                            Forgot password?
                                        </a>
                                    </div>

                                    <div class="d-grid mb-4">
                                        <button
                                            id="loginButton"
                                            class="btn login-btn"
                                            type="submit">
                                            Login
                                        </button>
                                    </div>

                                    <div class="register-prompt text-center">
                                        <p class="mb-2">
                                            Don't have an account?
                                        </p>

                                        <a
                                            href="/SoftEcoms/public/register.php"
                                            class="btn btn-outline-danger register-btn">
                                            Create new account
                                        </a>
                                    </div>

                                </form>
                            </div>
                        </div>

                        <!-- RIGHT SIDE: IMAGE -->
                        <div class="col-lg-6 d-none d-lg-flex login-image-column">
                            <img
                                src="/SoftEcoms/public/assets/img/Top_Viewclair.jpg"
                                alt="SweetClaire products"
                                class="login-side-img">
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- LOGIN REQUIRED NOTIFICATION -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    <?php if (!empty($loginNotice)): ?>

        Swal.fire({
            icon: "info",
            title: "Login Required",
            text: <?php echo json_encode($loginNotice); ?>,
            confirmButtonText: "Okay",
            confirmButtonColor: "#F56C6C"
        });

    <?php endif; ?>

});
</script>

<!-- LOGIN FUNCTIONALITY -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    <?php if (!empty($loginNotice)): ?>

        Swal.fire({
            icon: "info",
            title: "Login Required",
            text: <?php echo json_encode($loginNotice); ?>,
            confirmButtonText: "Okay",
            confirmButtonColor: "#F56C6C"
        });

    <?php endif; ?>

});
</script>


<script>
const loginForm = document.getElementById("loginForm");
const loginButton = document.getElementById("loginButton");

loginForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    if (!email || !password) {
        await Swal.fire({
            icon: "warning",
            title: "Missing information",
            text: "Please enter your email and password."
        });
        return;
    }

    loginButton.disabled = true;
    loginButton.textContent = "Logging in...";

    try {
        const response = await fetch("http://localhost:3000/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "include",
            body: JSON.stringify({
                email: email,
                password: password
            })
        });

        const data = await response.json();

        if (!response.ok) {
            await Swal.fire({
                icon: "error",
                title: "Login failed",
                text: data.message || "Unable to log in."
            });
            return;
        }

        // Admin redirect
        if (data.role === "admin") {
            window.location.href =
                data.redirect || "/SoftEcoms/public/admin/index.php";
            return;
        }

        // Customer/user login success
        await Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: "Login successful!",
            showConfirmButton: false,
            timer: 1000
        });

        window.location.href =
            data.redirect || "/SoftEcoms/public/index.php";

    } catch (error) {
        console.error("Login error:", error);

        await Swal.fire({
            icon: "error",
            title: "Connection Error",
            text: "Could not connect to the authentication server. Make sure Node.js is running."
        });

    } finally {
        loginButton.disabled = false;
        loginButton.textContent = "Login";
    }
});
</script>

</body>
</html> 