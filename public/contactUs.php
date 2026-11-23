
<?php
session_start();

$pageClass = "contact-page";

include('./includes/header.php');
include('./includes/topbar.php');
?>

<main class="contact-page">

    <!-- HERO SECTION -->
    <section class="contact-hero">
        <div class="contact-hero-content">
            <span class="contact-eyebrow">WE'D LOVE TO HEAR FROM YOU</span>
            <h1>Let's Talk About Something <span>Sweet.</span></h1>
            <p>
                Have a question, suggestion, or just want to say hello?
                We're always happy to hear from you!
            </p>
        </div>

        <div class="contact-hero-decoration">
            <span class="decoration-circle circle-one"></span>
            <span class="decoration-circle circle-two"></span>
            <span class="decoration-heart">♡</span>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section class="contact-container">

        <!-- LEFT SIDE -->
        <div class="contact-info">

            <span class="section-eyebrow">GET IN TOUCH</span>
            <h2>We're Here for You!</h2>

            <p class="contact-description">
                Whether you have questions about our pastries, need help
                with an order, or want to share your feedback, feel free
                to reach out.
            </p>

            <div class="contact-info-list">

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h3>Email Us</h3>
                        <p>hello@sweetclaire.com</p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div>
                        <h3>Call Us</h3>
                        <p>+63 912 345 6789</p>
                        <p>+63 910 559 6442</p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h3>Visit Us</h3>
                        <p>Cagayan de Oro City, Philippines</p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h3>Business Hours</h3>
                        <p>Monday – Saturday</p>
                        <p>9:00 AM – 6:00 PM</p>
                    </div>
                </div>

            </div>

            <div class="contact-note">
                <span>♡</span>
                <p>
                    Every message matters to us. Thank you for being
                    part of the SweetClaire family!
                </p>
            </div>

        </div>

        <!-- RIGHT SIDE: CONTACT FORM -->
        <div class="contact-form-card">

            <div class="contact-form-heading">
                <span class="section-eyebrow">SEND US A MESSAGE</span>
                <h2>Drop Us a Line</h2>
                <p>Fill out the form below and let us know how we can help.</p>
            </div>

            <!-- LOGIN NOTICE -->
            <div id="contactLoginNotice" class="contact-login-notice" style="display:none;">

                <div class="contact-login-icon">
                    <i class="fas fa-user-lock"></i>
                </div>

                <span class="section-eyebrow">WELCOME TO SWEETCLAIRE</span>

                <h3>One Sweet Step Away!</h3>

                <p>
                    Please log in to your customer account before sending us a message.
                    We'd love to hear from you and help with anything you need!
                </p>

                <a href="/SoftEcoms/public/login.php" class="contact-login-btn">
                    Login to Your Account
                    <i class="fas fa-arrow-right"></i>
                </a>

                <span class="contact-login-note">
                    Your SweetClaire account makes it easier to connect with us.
                </span>

            </div>

            <!-- ACCOUNT INFORMATION -->
            <div id="contactAccountInfo" style="display:none;">

                <div class="form-group">
                    <label for="contactName">Name</label>
                    <input
                        type="text"
                        id="contactName"
                        placeholder="Your account name"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="contactEmail">Email Address</label>
                    <input
                        type="email"
                        id="contactEmail"
                        placeholder="Your account email"
                        readonly
                    >
                </div>

            </div>

            <!-- CONTACT FORM -->
            <form id="contactForm" style="display:none;">

                <div class="form-group">
                    <label for="contactSubject">Subject</label>
                    <input
                        type="text"
                        id="contactSubject"
                        name="subject"
                        placeholder="What is this about?"
                        maxlength="200"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="contactMessage">Your Message</label>
                    <textarea
                        id="contactMessage"
                        name="message"
                        rows="6"
                        placeholder="Write your message here..."
                        maxlength="5000"
                        required
                    ></textarea>
                </div>

                <button
                    type="submit"
                    class="contact-submit-btn"
                    id="contactSubmitBtn"
                >
                    Send Message
                    <i class="fas fa-paper-plane"></i>
                </button>

                <p class="form-disclaimer">
                    We appreciate your time and look forward to hearing from you.
                </p>

                <p id="contactError" style="display:none;" role="alert"></p>

            </form>

            <!-- SUCCESS MESSAGE -->
            <div class="contact-success" id="contactSuccess" style="display:none;">

                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>

                <h3>Thank You!</h3>

                <p>
                    Your message has been submitted successfully.
                    We appreciate you reaching out to SweetClaire!
                </p>

                <button type="button" id="sendAnotherMessage">
                    Send Another Message
                </button>

            </div>

        </div>

    </section>

    <!-- BOTTOM MESSAGE -->
    <section class="contact-bottom">
        <div class="contact-bottom-content">
            <span>MADE WITH LOVE ♡</span>
            <h2>Sweet Moments Start with a Conversation.</h2>
            <p>Thank you for choosing SweetClaire for your sweet cravings.</p>
        </div>
    </section>

</main>

<!-- CONTACT JAVASCRIPT -->
<script src="assets/js/contact.js"></script>

</body>
</html>
