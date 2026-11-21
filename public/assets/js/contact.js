document.addEventListener("DOMContentLoaded", () => {
  const API_URL = "/api";

  // ==========================================
  // ELEMENTS
  // ==========================================

  const contactForm = document.getElementById("contactForm");
  const contactSuccess = document.getElementById("contactSuccess");
  const sendAnotherMessage = document.getElementById("sendAnotherMessage");

  const loginNotice = document.getElementById("contactLoginNotice");
  const accountInfo = document.getElementById("contactAccountInfo");

  const nameInput = document.getElementById("contactName");
  const emailInput = document.getElementById("contactEmail");

  const subjectInput = document.getElementById("contactSubject");
  const messageInput = document.getElementById("contactMessage");

  const submitButton = document.getElementById("contactSubmitBtn");
  const errorMessage = document.getElementById("contactError");

  // Prevent errors if required HTML elements are missing.
  const requiredElements = [
    contactForm,
    contactSuccess,
    sendAnotherMessage,
    loginNotice,
    accountInfo,
    nameInput,
    emailInput,
    subjectInput,
    messageInput,
    submitButton,
    errorMessage,
  ];

  if (requiredElements.some((element) => !element)) {
    console.error(
      "Contact page: One or more required HTML elements are missing.",
    );
    return;
  }

  // ==========================================
  // HELPER FUNCTIONS
  // ==========================================

  function showError(message) {
    errorMessage.textContent = message;
    errorMessage.style.display = "block";
  }

  function hideError() {
    errorMessage.textContent = "";
    errorMessage.style.display = "none";
  }

  function showLoginNotice() {
    loginNotice.style.display = "block";
    accountInfo.style.display = "none";
    contactForm.style.display = "none";
    contactSuccess.style.display = "none";
  }

  function showContactForm() {
    loginNotice.style.display = "none";
    accountInfo.style.display = "block";
    contactForm.style.display = "block";
    contactSuccess.style.display = "none";
  }

  function setLoading(isLoading) {
    submitButton.disabled = isLoading;

    if (isLoading) {
      submitButton.innerHTML = "Sending...";
    } else {
      submitButton.innerHTML =
        'Send Message <i class="fas fa-paper-plane"></i>';
    }
  }

  async function readResponse(response) {
    const contentType = response.headers.get("content-type") || "";

    if (contentType.includes("application/json")) {
      return await response.json();
    }

    const text = await response.text();

    throw new Error(
      response.ok
        ? "The server returned an unexpected response."
        : `Server error (${response.status}). Please try again.`,
    );
  }

  // ==========================================
  // LOAD LOGGED-IN CUSTOMER
  // ==========================================

  async function loadCustomer() {
    hideError();

    try {
      const response = await fetch(`${API_URL}/me`, {
        method: "GET",
        credentials: "include",
        headers: {
          Accept: "application/json",
        },
      });

      if (response.status === 401) {
        showLoginNotice();
        return;
      }

      const data = await readResponse(response);

      if (!response.ok) {
        throw new Error(data.message || "Unable to retrieve your account.");
      }

      const user = data.user;

      if (!user || !["customer", "user"].includes(data.role)) {
        showLoginNotice();
        return;
      }

      const fullName =
        user.fullName ||
        [user.firstName, user.middleName, user.lastName]
          .filter(Boolean)
          .join(" ");

      const email = user.emailAddress || user.email;

      if (!fullName || !email) {
        showLoginNotice();
        return;
      }

      // Populate account information.
      nameInput.value = fullName;
      emailInput.value = email;

      showContactForm();
    } catch (error) {
      console.error("Unable to load customer:", error);

      showError(
        error.message ||
          "Unable to connect to the server. Please try again later.",
      );

      loginNotice.style.display = "none";
      accountInfo.style.display = "none";
      contactForm.style.display = "none";
    }
  }

  // ==========================================
  // SUBMIT CONTACT MESSAGE
  // ==========================================

  contactForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    hideError();

    if (!contactForm.checkValidity()) {
      contactForm.reportValidity();
      return;
    }

    const subject = subjectInput.value.trim();
    const message = messageInput.value.trim();

    if (!subject || !message) {
      showError("Please complete all required fields.");
      return;
    }

    if (subject.length > 200) {
      showError("Subject must not exceed 200 characters.");
      return;
    }

    if (message.length > 5000) {
      showError("Message must not exceed 5000 characters.");
      return;
    }

    setLoading(true);

    try {
      const response = await fetch(`${API_URL}/contact`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          subject,
          message,
        }),
      });

      const data = await readResponse(response);

      if (response.status === 401) {
        showLoginNotice();
        return;
      }

      if (!response.ok || !data.success) {
        throw new Error(data.message || "Unable to submit your message.");
      }

      // Show success only after successful database insertion.
      contactForm.reset();

      contactForm.style.display = "none";
      accountInfo.style.display = "none";
      loginNotice.style.display = "none";
      contactSuccess.style.display = "block";

      hideError();
    } catch (error) {
      console.error("Contact submission error:", error);

      showError(error.message || "Something went wrong. Please try again.");
    } finally {
      setLoading(false);
    }
  });

  // ==========================================
  // SEND ANOTHER MESSAGE
  // ==========================================

  sendAnotherMessage.addEventListener("click", () => {
    contactForm.reset();
    hideError();

    showContactForm();

    // Restore account details after resetting the form.
    loadCustomer();
  });

  // ==========================================
  // INITIALIZE CONTACT PAGE
  // ==========================================

  loadCustomer();
});
