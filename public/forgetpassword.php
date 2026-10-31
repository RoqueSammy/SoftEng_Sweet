<?php
session_start();

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
                        <a href="register" class="reset-link text-decoration-none">Back to Login</a>
                    </p>
                </form>

            </div>
        </div>
    </div>
</section>

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
            });
        });
    </script>

