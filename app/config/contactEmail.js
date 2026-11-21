const nodemailer = require("nodemailer");

// Accept either environment variable name
const smtpPassword = process.env.SMTP_PASSWORD || process.env.SMTP_APP_PASSWORD;

const emailConfigured = Boolean(
  process.env.SMTP_HOST &&
  process.env.SMTP_USER &&
  smtpPassword &&
  process.env.CONTACT_ADMIN_EMAIL,
);

console.log("=== EMAIL CONFIGURATION CHECK ===");
console.log("SMTP HOST:", Boolean(process.env.SMTP_HOST));
console.log("SMTP USER:", Boolean(process.env.SMTP_USER));
console.log("SMTP PASSWORD:", Boolean(smtpPassword));
console.log("ADMIN EMAIL:", Boolean(process.env.CONTACT_ADMIN_EMAIL));
console.log("EMAIL CONFIGURED:", emailConfigured);
console.log("=================================");

const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST,
  port: Number(process.env.SMTP_PORT || 587),
  secure: Number(process.env.SMTP_PORT) === 465,
  auth: {
    user: process.env.SMTP_USER,
    pass: smtpPassword,
  },
});

async function sendNewContactNotification(contact) {
  if (!emailConfigured) {
    console.warn("Contact email is not configured. Notification skipped.");
    return;
  }

  await transporter.sendMail({
    from: `"SweetClaire" <${process.env.SMTP_USER}>`,
    to: process.env.CONTACT_ADMIN_EMAIL,
    replyTo: contact.email,
    subject: `New customer inquiry: ${contact.subject}`,
    text: `
A new customer inquiry has been received.

Customer: ${contact.firstName} ${contact.lastName}
Email: ${contact.email}
Subject: ${contact.subject}

Message:
${contact.message}
        `,
  });

  console.log("New contact notification email sent successfully!");
}

async function sendContactReply(contact, reply) {
  if (!emailConfigured) {
    throw new Error("Email service is not configured.");
  }

  await transporter.sendMail({
    from: `"SweetClaire Support" <${process.env.SMTP_USER}>`,
    to: contact.email,
    replyTo: process.env.CONTACT_ADMIN_EMAIL,
    subject: `Re: ${contact.subject}`,
    text: `
Hello ${contact.firstName},

Thank you for contacting SweetClaire.

Our response:

${reply}

Your original message:
${contact.message}

SweetClaire Customer Support
        `,
  });

  console.log("Customer reply email sent successfully!");
}

module.exports = {
  sendNewContactNotification,
  sendContactReply,
};
