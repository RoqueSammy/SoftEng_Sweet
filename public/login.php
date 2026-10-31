
<?php
session_start();

$pageClass = "index-page";

$loginNotice = "";

if (isset($_GET['notice']) && $_GET['notice'] === 'login_required') {
    $loginNotice = "Please log in to your account before adding products to your cart.";
}

include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/topbar.php');
?>

<section class="loginOverlay">
    <div class="container py-5 ">
        <div class="row d-flex justify-content-center align-items-center ">
            <div class="col-xl-10">
                <div class="card rounded-3 text-black">
                    <div class="row g-0">

                        <div class="col-lg-6">
                            <div class="card-body p-md-5 mx-md-4">

                                <div class="text-center">
                                    <img
                                        src="/SoftEcoms/public/assets/img/svg/logo.svg"
                                        style="width: 100px;"
                                        alt="Sweetclaire">

                                    <h4 class="greet_login mt-1 mb-3 pb-1">
                                        Welcome to SweetClaire!
                                    </h4>
                                </div>

                                <form id="loginForm">
                                    <p>Please login to your account</p>

                                    <div class="mb-3">
                                        <label class="form-label" for="email">
                                            Email
                                        </label>

                                        <input
                                            type="email"
                                            id="email"
                                            class="form-control"
                                            placeholder="Email Address"
                                            autocomplete="email"
                                            required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="password">
                                            Password
                                        </label>

                                        <input
                                            type="password"
                                            id="password"
                                            class="form-control"
                                            placeholder="Password"
                                            autocomplete="current-password"
                                            required>
                                    </div>

                                    <div class="d-grid pt-1 mb-3 pb-1">
                                        <button
                                            id="loginButton"
                                            class="btn login-btn mb-3"
                                            type="submit">
                                            Login
                                        </button>

                                        <a
                                            class="text-muted text-center"
                                            href="/SoftEcoms/public/forgetpassword.php">
                                            Forgot password?
                                        </a>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-center pb-4">
                                        <p class="mb-0 me-2">
                                            Don't have an account?
                                        </p>

                                        <a
                                            href="/SoftEcoms/public/register.php"
                                            class="btn btn-outline-danger">
                                            Create new
                                        </a>
                                    </div>
                                </form>

                            </div>
                        </div>

                        <div class="col-lg-6 d-none d-lg-block">
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

        // Admins go directly to their dashboard.
        if (data.role === "admin") {
            window.location.href =
                data.redirect || "/SoftEcoms/public/admin/index.php";
            return;
        }

        // Do not show the profile completion popup here.
        // The homepage will check the profile after login.
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
