
<?php
session_start();

$pageClass = "register-page";

include_once __DIR__ . "/../app/config/config.php";
include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/topbar.php";
?>

<section class="registerOverlay">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-xl-10 col-lg-11">

                <div class="card register-card">
                    <div class="row g-0">

                        <!-- LEFT SIDE: IMAGE -->
                        <div class="col-lg-5 d-none d-lg-flex register-image-column">
                            <div class="registerSide"></div>
                        </div>

                        <!-- RIGHT SIDE: REGISTRATION FORM -->
                        <div class="col-lg-7 register-form-column">
                            <div class="card-body register-card-body">

                                <div class="text-center register-heading">
                                    <h3 class="register-title">
                                        Create Account
                                    </h3>

                                    <p class="register-subtitle">
                                        Join SweetClaire and enjoy our delicious treats!
                                    </p>
                                </div>

                                <form id="registerForm" autocomplete="on">

                                    <!-- FIRST AND LAST NAME -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="firstName">
                                                First Name <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                id="firstName"
                                                name="firstName"
                                                placeholder="Enter first name"
                                                required
                                                maxlength="100"
                                                autocomplete="given-name">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="lastName">
                                                Last Name <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                id="lastName"
                                                name="lastName"
                                                placeholder="Enter last name"
                                                required
                                                maxlength="100"
                                                autocomplete="family-name">
                                        </div>
                                    </div>

                                    <!-- MIDDLE NAME -->
                                    <div class="mb-3">
                                        <label class="form-label" for="middleName">
                                            Middle Name
                                            <span class="text-muted">(Optional)</span>
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            id="middleName"
                                            name="middleName"
                                            placeholder="Enter middle name"
                                            maxlength="100"
                                            autocomplete="additional-name">
                                    </div>

                                    <!-- EMAIL -->
                                    <div class="mb-3">
                                        <label class="form-label" for="email">
                                            Email Address <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="email"
                                            class="form-control"
                                            id="email"
                                            name="email"
                                            placeholder="Enter your email address"
                                            required
                                            maxlength="254"
                                            autocomplete="email">
                                    </div>

                                    <!-- PASSWORDS -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="password">
                                                Password <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="password"
                                                class="form-control"
                                                id="password"
                                                name="password"
                                                placeholder="Create password"
                                                minlength="8"
                                                required
                                                autocomplete="new-password">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="confirmPassword">
                                                Confirm Password <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="password"
                                                class="form-control"
                                                id="confirmPassword"
                                                name="confirmPassword"
                                                placeholder="Confirm password"
                                                minlength="8"
                                                required
                                                autocomplete="new-password">
                                        </div>
                                    </div>

                                    <!-- INFORMATION -->
                                    <p class="register-note">
                                        You can add your phone number and delivery address
                                        after verifying your email.
                                    </p>

                                    <!-- REGISTER BUTTON -->
                                    <button
                                        type="submit"
                                        id="registerButton"
                                        class="btn btn-register w-100 mt-2">

                                        Register
                                    </button>

                                    <!-- LOGIN LINK -->
                                    <div class="register-login-prompt text-center">
                                        <p class="mb-0">
                                            Already have an account?

                                            <a href="/SoftEcoms/public/login.php">
                                                Login
                                            </a>
                                        </p>
                                    </div>

                                </form>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const registerForm = document.getElementById("registerForm");
const registerButton = document.getElementById("registerButton");

registerForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const data = Object.fromEntries(
        new FormData(registerForm).entries()
    );

    if (data.password !== data.confirmPassword) {
        await Swal.fire({
            icon: "error",
            title: "Password mismatch",
            text: "Your passwords do not match."
        });
        return;
    }

    registerButton.disabled = true;
    registerButton.textContent = "Registering...";

    try {
        const response = await fetch("http://localhost:3000/register", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "include",
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(
                result.message || "Registration failed."
            );
        }

        await Swal.fire({
            icon: "success",
            title: "Check your email!",
            text: result.message,
            confirmButtonText: "Go to Login"
        });

        window.location.href =
            result.redirect || "/SoftEcoms/public/login.php";

    } catch (error) {
        console.error("Registration error:", error);

        await Swal.fire({
            icon: "error",
            title: "Registration failed",
            text: error.message ||
                "Could not connect to the server. Please try again."
        });

    } finally {
        registerButton.disabled = false;
        registerButton.textContent = "Register";
    }
});
</script>

</body>
</html>
