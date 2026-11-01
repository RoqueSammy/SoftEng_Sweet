<?php
session_start();

<<<<<<< HEAD
$pageClass = "reset-page";

include_once __DIR__ . "/../app/config/config.php";

include __DIR__ . "/admin/includes/header.php";
include __DIR__ . "/admin/includes/topbar.php";
?>

<section class="resetOverlay">
    <div class="container py-5">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-6 col-md-8 col-sm-10">

                <div class="reset-card bg-white p-4 p-md-5 rounded-4 shadow text-center">

                    <!-- Lock Illustration -->
                    <img
                        src="/SoftEcoms/public/assets/img/svg/lock.svg"
                        alt="Lock illustration"
                        class="reset-lock mb-3"
                    >

                    <!-- Heading -->
                    <h3 class="reset-title mb-2 fw-bold">
                        Forgot your password?
                    </h3>

                    <!-- Description -->
                    <p class="reset-description mb-4">
                        Enter the email address associated with your Sweet! account.
                        If your account is active, we will send you a password-reset link.
                    </p>

                    <!-- Forgot Password Form -->
                    <form id="forgetpasswordForm" autocomplete="on">

                        <div class="text-start mb-3">
                            <label for="email" class="form-label fw-semibold">
                                Email Address
                            </label>

                            <input
                                type="email"
                                class="form-control reset-input"
                                placeholder="Enter your email address"
                                name="email"
                                id="email"
                                maxlength="254"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <button
                            type="submit"
                            class="btn reset-btn w-100 mb-3 rounded-pill py-2 fw-bold"
                            id="loginButton"
                        >
                            SEND RESET LINK
                        </button>

                    </form>

                    <!-- Back to Login -->
                    <p class="mb-0 small">
                        Remember your password?
                        <a
                            href="/SoftEcoms/public/login.php"
                            class="reset-link text-decoration-none fw-semibold"
                        >
                            Back to Login
                        </a>
                    </p>

                </div>

=======
$pageClass = "index-page";

include('includes/header.php');
include('includes/topbar.php');
?>

<section class="resetOverlay d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-8 m-auto">

    <form id="forgetpasswordForm" autocomplete="on" class="reset-card bg-white p-5 rounded-4 shadow text-center">

                    <h3 class="reset-title mb-2 fw-bold">Forgot your password?</h3>

                    <img src="/SoftEcoms/public/assets/img/svg/lock.svg"
                        alt="Lock illustration"
                        class="reset-lock mb-3">

                    <p class="small mb-3">
                        Enter your email address and we'll send you an email to verify to reset your password!
                    </p>

                    <input
                        type="email"
                        class="form-control reset-input mb-3"
                        placeholder="Enter Email"
                        name="email"
                        id="email"
                        autocomplete="username"
                        required>

                    <button
                        type="submit"
                        class="btn reset-btn w-100 mb-3 rounded-pill py-2 fw-bold text-uppercase"
                        id="loginButton">
                        Reset Password
                    </button>

                    <p class="mb-0 small">
                        <a href="login" class="reset-link text-decoration-none">Back to Login</a>
                    </p>
                </form>

>>>>>>> cg
            </div>
        </div>
    </div>
</section>

<<<<<<< HEAD
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("forgetpasswordForm");
    const emailInput = document.getElementById("email");
    const button = document.getElementById("loginButton");

    form.addEventListener("submit", async (event) => {

        event.preventDefault();

        const email = emailInput.value.trim();

        if (!email) {
            await Swal.fire({
                title: "Email required",
                text: "Please enter your email address.",
                icon: "warning",
                confirmButtonColor: "#8b4513"
=======
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("forgetpasswordForm");
            const emailInput = document.getElementById("email");
            const button = document.getElementById("loginButton");

            form.addEventListener("submit", async (event) => {
                event.preventDefault();

                const email = emailInput.value.trim();

                if (!email) {
                    await Swal.fire({
                        title: "Email required",
                        text: "Please enter your email address.",
                        icon: "warning"
                    });
                    return;
                }

                const originalText = button.textContent;
                button.disabled = true;
                button.textContent = "Sending...";

                try {
                    const response = await fetch(
                        "http://localhost:3000/forgot-password",
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({ email })
                        }
                    );

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            data.message || "Unable to process your request."
                        );
                    }

                    await Swal.fire({
                        title: "Request received",
                        text: data.message,
                        icon: "info",
                        confirmButtonColor: "#8b4513"
                    });
                } catch (error) {
                    console.error("Forgot-password error:", error);

                    await Swal.fire({
                        title: "Request failed",
                        text: "Unable to contact the authentication server. Make sure Node.js is running on port 3000.",
                        icon: "error",
                        confirmButtonColor: "#8b4513"
                    });
                } finally {
                    button.disabled = false;
                    button.textContent = originalText;
                }
>>>>>>> cg
            });
            return;
        }

        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = "Sending...";

        try {

            const response = await fetch(
                "http://localhost:3000/forgot-password",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ email })
                }
            );

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message || "Unable to process your request."
                );
            }

            await Swal.fire({
                title: "Request received!",
                text: data.message,
                icon: "success",
                confirmButtonColor: "#8b4513"
            });

        } catch (error) {

            console.error("Forgot-password error:", error);

            await Swal.fire({
                title: "Request failed",
                text: "Unable to contact the authentication server. Make sure Node.js is running on port 3000.",
                icon: "error",
                confirmButtonColor: "#8b4513"
            });

        } finally {

            button.disabled = false;
            button.textContent = originalText;

        }

    });

});
</script>

