const Contact = require("../models/Contact");
const { sendContactReply } = require("../config/contactEmail");

class ContactController {
  constructor(db) {
    this.contactModel = new Contact(db);
    this.db = db;
  }

  // Customer submits a contact message
  createMessage = async (req, res) => {
    try {
      if (!req.session || !req.session.authUser) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      const userId = req.session.user_id || req.session.authUser.user_id;

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Invalid customer session.",
        });
      }

      const subject = String(req.body?.subject || "").trim();
      const message = String(req.body?.message || "").trim();

      if (!subject || !message) {
        return res.status(400).json({
          success: false,
          message: "Subject and message are required.",
        });
      }

      if (subject.length > 255 || message.length > 5000) {
        return res.status(400).json({
          success: false,
          message: "Your message exceeds the allowed length.",
        });
      }

      const [users] = await this.db.execute(
        `SELECT firstName, lastName, emailAddress
         FROM users
         WHERE id = ?
         AND role IN ('customer', 'user')
         LIMIT 1`,
        [userId],
      );

      if (!users.length) {
        return res.status(404).json({
          success: false,
          message: "Customer account not found.",
        });
      }

      const user = users[0];

      const messageId = await this.contactModel.createMessage({
        userId,
        firstName: user.firstName,
        lastName: user.lastName,
        email: user.emailAddress,
        subject,
        message,
      });

      return res.status(201).json({
        success: true,
        message: "Your message has been submitted successfully!",
        messageId,
      });
    } catch (error) {
      console.error("Contact submission error:", error);

      return res.status(500).json({
        success: false,
        message: "Failed to submit your message.",
      });
    }
  };

  // Admin retrieves all contact messages
  getAllMessages = async (req, res) => {
    try {
      const role = req.session?.userRole || req.session?.authUser?.role;

      if (!req.session?.authUser || String(role).toLowerCase() !== "admin") {
        return res.status(403).json({
          success: false,
          message: "Admin access required.",
        });
      }

      const messages = await this.contactModel.getAllMessages();

      return res.status(200).json({
        success: true,
        messages,
      });
    } catch (error) {
      console.error("Get contact messages error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to retrieve contact messages.",
      });
    }
  };

  // Admin retrieves one contact message
  getMessageById = async (req, res) => {
    try {
      const role = req.session?.userRole || req.session?.authUser?.role;

      if (!req.session?.authUser || String(role).toLowerCase() !== "admin") {
        return res.status(403).json({
          success: false,
          message: "Admin access required.",
        });
      }

      const id = Number(req.params.id);

      if (!Number.isInteger(id) || id <= 0) {
        return res.status(400).json({
          success: false,
          message: "Invalid message ID.",
        });
      }

      const message = await this.contactModel.getMessageById(id);

      if (!message) {
        return res.status(404).json({
          success: false,
          message: "Contact message not found.",
        });
      }

      return res.status(200).json({
        success: true,
        message,
      });
    } catch (error) {
      console.error("Get contact message error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to retrieve contact message.",
      });
    }
  };

  // Admin updates message status
  updateMessageStatus = async (req, res) => {
    try {
      const role = req.session?.userRole || req.session?.authUser?.role;

      if (!req.session?.authUser || String(role).toLowerCase() !== "admin") {
        return res.status(403).json({
          success: false,
          message: "Admin access required.",
        });
      }

      const id = Number(req.params.id);
      const status = String(req.body?.status || "").toLowerCase();

      if (!Number.isInteger(id) || id <= 0) {
        return res.status(400).json({
          success: false,
          message: "Invalid message ID.",
        });
      }

      const allowedStatuses = ["unread", "read", "replied"];

      if (!allowedStatuses.includes(status)) {
        return res.status(400).json({
          success: false,
          message: "Invalid message status.",
        });
      }

      const updated = await this.contactModel.updateStatus(id, status);

      if (!updated) {
        return res.status(404).json({
          success: false,
          message: "Contact message not found.",
        });
      }

      return res.status(200).json({
        success: true,
        message: "Contact message status updated successfully.",
      });
    } catch (error) {
      console.error("Update contact message status error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to update contact message status.",
      });
    }
  };

  // Admin replies to a customer by email and marks the inquiry as replied.
  replyToMessage = async (req, res) => {
    try {
      const role = req.session?.userRole || req.session?.authUser?.role;
      if (!req.session?.authUser || String(role).toLowerCase() !== "admin") {
        return res.status(403).json({ success: false, message: "Admin access required." });
      }

      const id = Number(req.params.id);
      const reply = String(req.body?.reply || "").trim();
      if (!Number.isSafeInteger(id) || id < 1) {
        return res.status(400).json({ success: false, message: "Invalid message ID." });
      }
      if (!reply || reply.length > 5000) {
        return res.status(400).json({ success: false, message: "Enter a reply of 1 to 5,000 characters." });
      }

      const contact = await this.contactModel.getMessageById(id);
      if (!contact) {
        return res.status(404).json({ success: false, message: "Contact message not found." });
      }

      await sendContactReply(contact, reply);
      await this.contactModel.updateStatus(id, "replied");
      return res.status(200).json({ success: true, message: `Reply sent to ${contact.email}.` });
    } catch (error) {
      console.error("Send contact reply error:", error);
      const notConfigured = error.message === "Email service is not configured.";
      return res.status(notConfigured ? 503 : 500).json({
        success: false,
        message: notConfigured
          ? "Email is not configured on the server. Add SMTP settings before replying."
          : "Unable to send the reply. Check the email settings and try again.",
      });
    }
  };
}

module.exports = ContactController;
