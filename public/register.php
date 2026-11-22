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
                    <div class="row g-4">

                        <div class="col-lg-5 d-none d-lg-flex register-image-column">
                            <div class="registerSide"></div>
                        </div>

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

                                    <!-- TERMS AND PRIVACY -->
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the
                                            <a href="#" id="termsLink">Terms of Service</a> and
                                            <a href="#" id="privacyLink">Privacy Policy</a>
                                        </label>
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

<!-- TERMS MODAL -->
<dialog id="termsModal" class="legal-modal">
    <div class="legal-modal-header">
        <h4>Terms of Service</h4>
        <button type="button" class="legal-modal-x" data-close aria-label="Close">&times;</button>
    </div>
    <div class="legal-modal-body">
        <p><em>Last updated: November 2026</em></p>
        <p>Welcome to SweetClaire. By creating an account or placing an order, you agree to these Terms of Service. If you do not agree, please do not use our website.</p>

        <h5>1. Accounts</h5>
        <p>You must provide accurate information when registering and verify your email address. You are responsible for keeping your password secure and for all activity under your account.</p>

        <h5>2. Ordering</h5>
        <p>Our online ordering is open 24 hours. Please place your order at least 3 hours before your desired delivery or pickup time. An order is confirmed only after you receive a confirmation email from us.</p>

        <h5>3. Delivery</h5>
        <p>A standard delivery fee applies to every delivery, with an additional charge for distances beyond our base delivery radius. The exact fee is shown at checkout before you confirm your order. Delivery times are estimates and may vary depending on the products ordered, traffic, and weather.</p>

        <h5>4. Tracking Your Order</h5>
        <p>We update the status of your order on your account page and by email. If you do not see our emails, please check your spam or promotions folder.</p>

        <h5>5. Unreceived Orders</h5>
        <p>If your order is marked as delivered but you did not receive it, please contact us right away at hello@sweetclaire.com so we can sort it out together.</p>

        <h5>6. Cancellations</h5>
        <p>Orders may be cancelled at least one (1) day before the scheduled pickup or delivery time. Later cancellations may result in the forfeiture of any down payment.</p>

        <h5>7. Refunds, Returns and Exchanges</h5>
        <p>If you receive a faulty or incorrect product, please contact us within 24 hours of the transaction with your order details so we can verify and assist you. Because our products are fresh food items, we cannot accept returns or exchanges unless the product is faulty or incorrect.</p>

        <h5>8. Discounts</h5>
        <p>We honor the Senior Citizen and PWD discounts required by Philippine law. Discounts apply only to items intended for the exclusive use of the qualified customer and require valid proof at the time of the transaction.</p>

        <h5>9. Changes to These Terms</h5>
        <p>We may update these terms from time to time. Continued use of the website after changes means you accept the updated terms.</p>

        <h5>10. Contact Us</h5>
        <p>For questions about these terms, email hello@sweetclaire.com or call +63 912 345 6789.</p>
    </div>
    <div class="legal-modal-footer">
        <button type="button" class="legal-close" data-close>Close</button>
    </div>
</dialog>

<!-- PRIVACY MODAL -->
<dialog id="privacyModal" class="legal-modal">
    <div class="legal-modal-header">
        <h4>Privacy Policy</h4>
        <button type="button" class="legal-modal-x" data-close aria-label="Close">&times;</button>
    </div>
    <div class="legal-modal-body">
        <p><em>Last updated: November 2026</em></p>
        <p>SweetClaire respects your privacy. This policy explains what personal information we collect, why we collect it, and how we protect it, in line with the Philippine Data Privacy Act of 2012 (Republic Act No. 10173).</p>

        <h5>1. Information We Collect</h5>
        <p>When you register or order, we collect your name, email address, password, contact number, delivery address, and order history.</p>

        <h5>2. How We Use Your Information</h5>
        <p>We use your information to create and verify your account, process and deliver your orders, send order confirmations and status updates, respond to your concerns, and improve our services.</p>

        <h5>3. How We Protect Your Information</h5>
        <p>Your password is stored in encrypted (hashed) form, and access to our database is limited to authorized personnel. No online system is completely secure, but we take reasonable steps to protect your data.</p>

        <h5>4. Sharing of Information</h5>
        <p>We do not sell your personal information. We only share what is needed to complete your order, such as your name, contact number, and address with our delivery riders, or when required by law.</p>

        <h5>5. Cookies and Sessions</h5>
        <p>We use session cookies to keep you logged in and to remember items in your cart. You can clear cookies in your browser settings, but some features may stop working.</p>

        <h5>6. Your Rights</h5>
        <p>You may ask to view, correct, or delete your personal information, or withdraw your consent to its use, by contacting us. We will respond within a reasonable time.</p>

        <h5>7. Data Retention</h5>
        <p>We keep your information only as long as needed to provide our services or as required by law.</p>

        <h5>8. Contact Us</h5>
        <p>For privacy concerns, email hello@sweetclaire.com or call +63 912 345 6789.</p>
    </div>
    <div class="legal-modal-footer">
        <button type="button" class="legal-close" data-close>Close</button>
    </div>
</dialog>

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

// TERMS AND PRIVACY MODALS
function openLegal(id) {
    document.getElementById(id).showModal();
}

document.getElementById("termsLink").addEventListener("click", (e) => {
    e.preventDefault();
    openLegal("termsModal");
});

document.getElementById("privacyLink").addEventListener("click", (e) => {
    e.preventDefault();
    openLegal("privacyModal");
});

document.querySelectorAll(".legal-modal").forEach((modal) => {
    // X button and Close button
    modal.querySelectorAll("[data-close]").forEach((btn) => {
        btn.addEventListener("click", () => modal.close());
    });

    // clicking the dark area outside the box also closes it
    modal.addEventListener("click", (e) => {
        if (e.target === modal) modal.close();
    });
});
</script>

</body>
</html>
