const fs = require("fs");
const path = require("path");
const crypto = require("crypto");
const bcrypt = require("bcrypt");

class AdminCustomerController {
  constructor(db) {
    this.db = db;
  }

  // ==========================================
  // VALIDATE ADMIN SESSION
  // ==========================================

  checkAdmin(req, res) {
    if (
      !req.session ||
      !req.session.authUser ||
      String(
        req.session.userRole || req.session.authUser.role || "",
      ).toLowerCase() !== "admin"
    ) {
      res.status(403).json({
        success: false,
        message: "Administrator access required.",
      });
      return false;
    }

    return true;
  }

  // ==========================================
  // GET CUSTOMER PROFILE AND STATISTICS
  // ==========================================

  getCustomer = async (req, res) => {
    if (!this.checkAdmin(req, res)) return;

    try {
      const customerId = Number(req.params.id);

      if (!Number.isSafeInteger(customerId) || customerId < 1) {
        return res.status(400).json({
          success: false,
          message: "Invalid customer ID.",
        });
      }

      const [customers] = await this.db.execute(
        `SELECT
           id,
           uuid,
           firstName,
           middleName,
           lastName,
           emailAddress,
           username,
           contactNumber,
           street,
           barangay,
           city,
           profile,
           role,
           accountStatus,
           dateCreated
         FROM users
         WHERE id = ?
           AND role IN ('customer', 'user')
         LIMIT 1`,
        [customerId],
      );

      if (!customers.length) {
        return res.status(404).json({
          success: false,
          message: "Customer not found.",
        });
      }

      const customer = customers[0];

      const [statistics] = await this.db.execute(
        `SELECT
           COUNT(*) AS totalOrders,
           COALESCE(SUM(
             CASE WHEN LOWER(status) = 'completed'
             THEN totalPrice ELSE 0 END
           ), 0) AS totalSpent,
           SUM(
             CASE WHEN LOWER(status) = 'completed'
             THEN 1 ELSE 0 END
           ) AS completedOrders
         FROM orders
         WHERE user_id = ?`,
        [customerId],
      );

      const [orders] = await this.db.execute(
        `SELECT
           uuid,
           COnumber,
           totalPrice,
           paymentMethod,
           status,
           DateCreated
         FROM orders
         WHERE user_id = ?
         ORDER BY DateCreated DESC`,
        [customerId],
      );

      return res.status(200).json({
        success: true,
        customer: {
          ...customer,
          profileImageUrl: customer.profile
            ? `/SoftEcoms/public/assets/profile_img/${encodeURIComponent(customer.profile)}`
            : null,
        },
        statistics: statistics[0],
        orders,
      });
    } catch (error) {
      console.error("Admin get customer error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to retrieve customer information.",
      });
    }
  };

  // ==========================================
  // UPDATE CUSTOMER INFORMATION
  // ==========================================

  updateCustomer = async (req, res) => {
    if (!this.checkAdmin(req, res)) return;

    let savedImagePath = null;

    try {
      const customerId = Number(req.params.id);

      if (!Number.isSafeInteger(customerId) || customerId < 1) {
        return res.status(400).json({
          success: false,
          message: "Invalid customer ID.",
        });
      }

      const firstName = String(req.body.firstName || "").trim();
      const middleName = String(req.body.middleName || "").trim();
      const lastName = String(req.body.lastName || "").trim();
      const emailAddress = String(req.body.emailAddress || "")
        .trim()
        .toLowerCase();
      const contactNumber = String(req.body.contactNumber || "").trim();
      const street = String(req.body.street || "").trim();
      const barangay = String(req.body.barangay || "").trim();
      const city = String(req.body.city || "").trim();

      if (!firstName || !lastName || !emailAddress) {
        return res.status(400).json({
          success: false,
          message: "First name, last name, and email are required.",
        });
      }

      if (
        firstName.length > 100 ||
        middleName.length > 100 ||
        lastName.length > 100 ||
        emailAddress.length > 255 ||
        contactNumber.length > 30 ||
        street.length > 255 ||
        barangay.length > 100 ||
        city.length > 100
      ) {
        return res.status(400).json({
          success: false,
          message: "One or more fields exceed the allowed length.",
        });
      }

      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!emailPattern.test(emailAddress)) {
        return res.status(400).json({
          success: false,
          message: "Please enter a valid email address.",
        });
      }

      const [customers] = await this.db.execute(
        `SELECT id, profile
         FROM users
         WHERE id = ?
           AND role IN ('customer', 'user')
         LIMIT 1`,
        [customerId],
      );

      if (!customers.length) {
        return res.status(404).json({
          success: false,
          message: "Customer not found.",
        });
      }

      const [existingEmail] = await this.db.execute(
        `SELECT id
         FROM users
         WHERE emailAddress = ?
           AND id <> ?
         LIMIT 1`,
        [emailAddress, customerId],
      );

      if (existingEmail.length) {
        return res.status(409).json({
          success: false,
          message: "This email address is already used by another account.",
        });
      }

      let profile = customers[0].profile || null;

      // ==========================================
      // OPTIONAL PROFILE IMAGE
      // ==========================================

      if (req.body.profileImage) {
        const match =
          /^data:image\/(jpeg|png|webp);base64,([A-Za-z0-9+/=]+)$/.exec(
            String(req.body.profileImage),
          );

        if (!match) {
          return res.status(400).json({
            success: false,
            message: "Invalid image format. Use JPG, PNG, or WEBP.",
          });
        }

        const extension = match[1] === "jpeg" ? "jpg" : match[1];
        const imageBuffer = Buffer.from(match[2], "base64");

        if (!imageBuffer.length || imageBuffer.length > 5 * 1024 * 1024) {
          return res.status(400).json({
            success: false,
            message: "The profile picture must be 5 MB or smaller.",
          });
        }

        const isJpeg =
          extension === "jpg" &&
          imageBuffer[0] === 0xff &&
          imageBuffer[1] === 0xd8 &&
          imageBuffer[2] === 0xff;

        const isPng =
          extension === "png" &&
          imageBuffer
            .subarray(0, 8)
            .equals(Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]));

        const isWebp =
          extension === "webp" &&
          imageBuffer.toString("ascii", 0, 4) === "RIFF" &&
          imageBuffer.toString("ascii", 8, 12) === "WEBP";

        if (!isJpeg && !isPng && !isWebp) {
          return res.status(400).json({
            success: false,
            message: "The uploaded file is not a valid image.",
          });
        }

        const uploadDirectory = path.join(
          __dirname,
          "../../public/assets/profile_img",
        );

        await fs.promises.mkdir(uploadDirectory, { recursive: true });

        const filename = `profile_${customerId}_${crypto.randomBytes(8).toString("hex")}.${extension}`;

        savedImagePath = path.join(uploadDirectory, filename);

        await fs.promises.writeFile(savedImagePath, imageBuffer);

        profile = filename;
      }

      await this.db.execute(
        `UPDATE users
         SET firstName = ?,
             middleName = ?,
             lastName = ?,
             emailAddress = ?,
             contactNumber = ?,
             street = ?,
             barangay = ?,
             city = ?,
             profile = ?
         WHERE id = ?
           AND role IN ('customer', 'user')`,
        [
          firstName,
          middleName || null,
          lastName,
          emailAddress,
          contactNumber || null,
          street || null,
          barangay || null,
          city || null,
          profile,
          customerId,
        ],
      );

      // Remove old image only after a successful database update.
      const oldProfile = customers[0].profile;

      if (
        savedImagePath &&
        oldProfile &&
        path.basename(oldProfile) === oldProfile
      ) {
        const oldImagePath = path.join(
          __dirname,
          "../../public/assets/profile_img",
          oldProfile,
        );

        if (oldImagePath !== savedImagePath) {
          await fs.promises.unlink(oldImagePath).catch(() => {});
        }
      }

      return res.status(200).json({
        success: true,
        message: "Customer information updated successfully.",
        profile,
      });
    } catch (error) {
      if (savedImagePath) {
        await fs.promises.unlink(savedImagePath).catch(() => {});
      }

      console.error("Admin update customer error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to update customer information.",
      });
    }
  };

  // ==========================================
  // ADMIN RESET CUSTOMER PASSWORD
  // ==========================================

  resetPassword = async (req, res) => {
    if (!this.checkAdmin(req, res)) return;

    try {
      const customerId = Number(req.params.id);
      const newPassword = String(req.body.newPassword || "");

      if (!Number.isSafeInteger(customerId) || customerId < 1) {
        return res.status(400).json({
          success: false,
          message: "Invalid customer ID.",
        });
      }

      if (newPassword.length < 8 || newPassword.length > 72) {
        return res.status(400).json({
          success: false,
          message: "Password must contain between 8 and 72 characters.",
        });
      }

      const [customers] = await this.db.execute(
        `SELECT id
         FROM users
         WHERE id = ?
           AND role IN ('customer', 'user')
         LIMIT 1`,
        [customerId],
      );

      if (!customers.length) {
        return res.status(404).json({
          success: false,
          message: "Customer not found.",
        });
      }

      const hashedPassword = await bcrypt.hash(newPassword, 12);

      await this.db.execute(
        `UPDATE users
         SET password = ?
         WHERE id = ?
           AND role IN ('customer', 'user')`,
        [hashedPassword, customerId],
      );

      return res.status(200).json({
        success: true,
        message: "Customer password reset successfully.",
      });
    } catch (error) {
      console.error("Admin reset password error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to reset customer password.",
      });
    }
  };

  // ==========================================
  // ACTIVATE / DEACTIVATE CUSTOMER
  // ==========================================

  updateStatus = async (req, res) => {
    if (!this.checkAdmin(req, res)) return;

    try {
      const customerId = Number(req.params.id);
      const accountStatus = String(req.body.accountStatus || "")
        .trim()
        .toLowerCase();

      if (!Number.isSafeInteger(customerId) || customerId < 1) {
        return res.status(400).json({
          success: false,
          message: "Invalid customer ID.",
        });
      }

      if (!["active", "inactive"].includes(accountStatus)) {
        return res.status(400).json({
          success: false,
          message: "Account status must be active or inactive.",
        });
      }

      const [result] = await this.db.execute(
        `UPDATE users
         SET accountStatus = ?
         WHERE id = ?
           AND role IN ('customer', 'user')`,
        [accountStatus, customerId],
      );

      if (!result.affectedRows) {
        return res.status(404).json({
          success: false,
          message: "Customer not found.",
        });
      }

      return res.status(200).json({
        success: true,
        message: `Customer account ${accountStatus === "active" ? "activated" : "deactivated"} successfully.`,
        accountStatus,
      });
    } catch (error) {
      console.error("Admin update customer status error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to update customer account status.",
      });
    }
  };
}

module.exports = AdminCustomerController;
