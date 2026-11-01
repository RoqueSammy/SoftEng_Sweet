<?php
session_start();

include_once __DIR__ . "/../app/config/config.php";

include __DIR__ . "/admin/includes/header.php";
include __DIR__ . "/admin/includes/topbar.php";

?>

    <!-- Shared Reset Password CSS -->
    <link rel="stylesheet" href="/SoftEcoms/public/assets/css/forgetpass.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="reset-page">

<section class="resetOverlay">
    <main class="reset-card text-center">

        <h3 class="reset-title mb-2">
            Reset Your Password
        </h3>

        <p class="reset-description mb-4">
            Choose a new password for your Sweet! account.
        </p>

        <form id="resetPasswordForm">

            <div class="text-start">
                <label for="password" class="reset-label">
                    New Password
                </label>

                <input
                    type="password"
                    class="reset-input"
                    id="password"
                    name="password"
                    placeholder="Enter your new password"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    required
                >

                <label for="confirmPassword" class="reset-label">
                    Confirm New Password
                </label>

                <input
                    type="password"
                    class="reset-input"
                    id="confirmPassword"
                    name="confirmPassword"
                    placeholder="Confirm your new password"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button
                type="submit"
                class="reset-btn"
                id="resetPasswordButton"
            >
                RESET PASSWORD
            </button>

        </form>

        <p class="mt-4 mb-0 small">
            Remember your password?
            <a
                href="/SoftEcoms/public/login.php"
                class="reset-link"
            >
                Back to Login
            </a>
        </p>

    </main>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("resetPasswordForm");
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirmPassword");
    const button = document.getElementById("resetPasswordButton");

    const token = new URLSearchParams(
        window.location.search
    ).get("token") || "";

    // Validate reset token
    if (!/^[a-f0-9]{64}$/i.test(token)) {

        form.querySelectorAll("input, button").forEach((element) => {
            element.disabled = true;
        });

        Swal.fire({
            title: "Invalid Reset Link",
            text: "This link is invalid or missing. Please request a new password-reset email.",
            icon: "error",
            confirmButtonColor: "#8b4513"
        });

        return;
    }

    form.addEventListener("submit", async (event) => {

        event.preventDefault();

        const password = passwordInput.value;
        const confirmPassword = confirmInput.value;

        if (password.length < 8 || password.length > 72) {
            await Swal.fire({
                title: "Invalid Password",
                text: "Your password must be between 8 and 72 characters.",
                icon: "warning",
                confirmButtonColor: "#8b4513"
            });
            return;
        }

        if (password !== confirmPassword) {
            await Swal.fire({
                title: "Passwords Do Not Match",
                text: "Please make sure both password fields match.",
                icon: "warning",
                confirmButtonColor: "#8b4513"
            });
            return;
        }

        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = "Resetting...";

        try {

            const response = await fetch(
                "http://localhost:3000/reset-password",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        token,
                        password,
                        confirmPassword
                    })
                }
            );

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message || "Unable to reset your password."
                );
            }

            await Swal.fire({
                title: "Password Reset Successful",
                text: data.message,
                icon: "success",
                confirmButtonColor: "#8b4513"
            });

            window.location.href =
                data.redirect || "/SoftEcoms/public/login.php";

        } catch (error) {

            console.error("Password-reset error:", error);

            await Swal.fire({
                title: "Reset Failed",
                text: error.message || "Unable to reset your password. Please try again.",
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

</body>
</html>
