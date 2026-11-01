const bcrypt = require("bcrypt");
const crypto = require("crypto");

const User = require("../models/User");
const {
  sendVerificationEmail,
  sendPasswordResetEmail,
} = require("../config/email");

class Authentication {
  constructor(db) {
    this.db = db;
    this.userModel = new User(db);
  }

  register = async (req, res) => {
    try {
      const {
        firstName,
        lastName,
        middleName,
        email,
        password,
        confirmPassword,
      } = req.body || {};

      const cleanFirstName = String(firstName || "").trim();
      const cleanLastName = String(lastName || "").trim();
      const cleanMiddleName = String(middleName || "").trim();
      const cleanEmail = String(email || "")
        .trim()
        .toLowerCase();

      if (
        !cleanFirstName ||
        !cleanLastName ||
        !cleanEmail ||
        typeof password !== "string" ||
        !password ||
        typeof confirmPassword !== "string" ||
        !confirmPassword
      ) {
        return res.status(400).json({
          message: "Please fill in all required fields.",
        });
      }

      if (
        cleanFirstName.length > 100 ||
        cleanLastName.length > 100 ||
        cleanMiddleName.length > 100
      ) {
        return res.status(400).json({
          message: "Names must not exceed 100 characters.",
        });
      }

      if (
        cleanEmail.length > 254 ||
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cleanEmail)
      ) {
        return res.status(400).json({
          message: "Please enter a valid email address.",
        });
      }

      if (password !== confirmPassword) {
        return res.status(400).json({
          message: "Passwords do not match.",
        });
      }

      if (password.length < 8) {
        return res.status(400).json({
          message: "Password must contain at least 8 characters.",
        });
      }

      const existingEmail = await this.userModel.findByEmail(cleanEmail);

      if (existingEmail) {
        return res.status(409).json({
          message: "This email address is already registered.",
        });
      }

      const emailPrefix =
        cleanEmail
          .split("@")[0]
          .replace(/[^a-z0-9_]/g, "")
          .slice(0, 20) || "customer";

      let username;
      let usernameExists = true;
      let attempts = 0;

      while (usernameExists) {
        attempts++;

        if (attempts > 10) {
          throw new Error("Could not generate a unique username.");
        }

        username = `${emailPrefix}_${crypto.randomBytes(4).toString("hex")}`;

        const existingUser = await this.userModel.findByEmailOrUsername(
          cleanEmail,
          username,
        );

        usernameExists = Boolean(existingUser);
      }

      const hashedPassword = await bcrypt.hash(password, 12);
      const uuid = crypto.randomUUID();

      const userId = await this.userModel.create({
        uuid,
        firstName: cleanFirstName,
        lastName: cleanLastName,
        middleName: cleanMiddleName || null,
        email: cleanEmail,
        contact: null,
        username,
        hashedPassword,
        street: null,
        barangay: null,
        city: null,
        role: "customer",
      });

      const rawToken = crypto.randomBytes(32).toString("hex");

      const tokenHash = crypto
        .createHash("sha256")
        .update(rawToken)
        .digest("hex");

      const expiresAt = new Date(Date.now() + 30 * 60 * 1000);

      const tokenSaved = await this.userModel.saveVerificationToken(
        userId,
        tokenHash,
        expiresAt,
      );

      if (!tokenSaved) {
        return res.status(500).json({
          message:
            "Your account was created, but verification setup failed. Please contact support.",
        });
      }

      const appUrl = (process.env.APP_URL || "http://localhost:3000").replace(
        /\/+$/,
        "",
      );

      const verificationUrl = `${appUrl}/verify-email?token=${rawToken}`;

      try {
        await sendVerificationEmail(cleanEmail, verificationUrl);
      } catch (emailError) {
        console.error("Verification email failed:", emailError.message);

        return res.status(502).json({
          message:
            "Your account was created, but the verification email could not be sent. Please contact support.",
        });
      }

      return res.status(201).json({
        message:
          "Registration successful! Check your email and click the verification link to activate your account.",
        redirect: "/SoftEcoms/public/login.php",
      });
    } catch (error) {
      console.error("Registration error:", error);

      if (error.code === "ER_DUP_ENTRY") {
        return res.status(409).json({
          message: "This email address or username is already registered.",
        });
      }

      return res.status(500).json({
        message: "Registration failed due to a server error.",
      });
    }
  };

  login = async (req, res) => {
    try {
      const email = String(req.body?.email || "")
        .trim()
        .toLowerCase();

      const password = req.body?.password;

      if (!email || !password) {
        return res.status(400).json({
          message: "Please enter your email and password.",
        });
      }

      const user = await this.userModel.findByEmail(email);

      if (!user) {
        return res.status(401).json({
          message: "Invalid email or password.",
        });
      }

      let passwordMatches = false;

      if (
        typeof user.password === "string" &&
        /^\$2[aby]\$/.test(user.password)
      ) {
        const compatibleHash = user.password.replace(/^\$2y\$/, "$2b$");

        passwordMatches = await bcrypt.compare(password, compatibleHash);
      } else if (typeof user.password === "string") {
        passwordMatches = password === user.password;

        if (passwordMatches) {
          const upgradedHash = await bcrypt.hash(password, 12);

          await this.userModel.updatePasswordIfMatches(
            user.id,
            user.password,
            upgradedHash,
          );
        }
      }

      if (!passwordMatches) {
        return res.status(401).json({
          message: "Invalid email or password.",
        });
      }

      if (user.accountStatus !== "active") {
        if (user.accountStatus === "unverified") {
          return res.status(403).json({
            message: "Please verify your email before logging in.",
          });
        }

        return res.status(403).json({
          message: "Your account is not active. Please contact support.",
        });
      }

      const allowedRoles = ["admin", "user", "customer"];

      if (!allowedRoles.includes(user.role)) {
        return res.status(403).json({
          message: "Your account role is not authorized.",
        });
      }

      const fullName = [user.firstName, user.middleName, user.lastName]
        .filter(Boolean)
        .join(" ");

      const profileComplete = Boolean(
        String(user.contactNumber || user.contact || "").trim() &&
        String(user.street || "").trim() &&
        String(user.barangay || "").trim() &&
        String(user.city || "").trim(),
      );

      req.session.regenerate((sessionError) => {
        if (sessionError) {
          console.error("Session regeneration error:", sessionError);

          return res.status(500).json({
            message: "Could not create your login session.",
          });
        }

        req.session.user_id = user.id;
        req.session.uuid = user.uuid;
        req.session.userRole = user.role;

        req.session.authUser = {
          user_id: user.id,
          uuid: user.uuid,
          fullName,
          firstName: user.firstName,
          lastName: user.lastName,
          middleName: user.middleName || null,
          username: user.username,
          email: user.emailAddress || user.email || null,
          profileComplete,
        };

        req.session.save((saveError) => {
          if (saveError) {
            console.error("Session save error:", saveError);

            return res.status(500).json({
              message: "Could not save your login session.",
            });
          }

          const redirect =
            user.role === "admin"
              ? "/SoftEcoms/public/admin/index.php"
              : "/SoftEcoms/public/index.php";

          return res.status(200).json({
            message: "Login successful!",
            redirect,
            user: req.session.authUser,
            role: user.role,
            profileComplete,
            needsProfileCompletion: user.role !== "admin" && !profileComplete,
          });
        });
      });
    } catch (error) {
      console.error("Login error:", error);

      return res.status(500).json({
        message: "Login failed due to a server error.",
      });
    }
  };

  forgotPassword = async (req, res) => {
    const genericMessage =
      "If an active account exists for that email, password-reset instructions will be sent shortly.";

    try {
      const email = String(req.body?.email || "")
        .trim()
        .toLowerCase();

      if (
        !email ||
        email.length > 254 ||
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
      ) {
        return res.status(200).json({ message: genericMessage });
      }

      const user = await this.userModel.findByEmail(email);

      if (!user || user.accountStatus !== "active") {
        return res.status(200).json({ message: genericMessage });
      }

      const rawToken = crypto.randomBytes(32).toString("hex");
      const tokenHash = crypto
        .createHash("sha256")
        .update(rawToken)
        .digest("hex");
      const expiresAt = new Date(Date.now() + 30 * 60 * 1000);

      await this.userModel.savePasswordResetToken(
        user.id,
        tokenHash,
        expiresAt,
      );

      try {
        await sendPasswordResetEmail(email, rawToken);
      } catch (emailError) {
        await this.userModel.clearPasswordResetToken(user.id);
        console.error("Password reset email failed:", emailError.message);
      }

      return res.status(200).json({ message: genericMessage });
    } catch (error) {
      console.error("Forgot password error:", error);

      return res.status(200).json({ message: genericMessage });
    }
  };

  resetPassword = async (req, res) => {
    try {
      const rawToken = String(req.body?.token || "");
      const password = req.body?.password;
      const confirmPassword = req.body?.confirmPassword;

      if (!/^[a-f0-9]{64}$/i.test(rawToken)) {
        return res.status(400).json({
          message:
            "This password-reset link is invalid or has expired. Please request a new one.",
        });
      }

      if (
        typeof password !== "string" ||
        typeof confirmPassword !== "string" ||
        password.length < 8 ||
        password.length > 72
      ) {
        return res.status(400).json({
          message: "Password must be between 8 and 72 characters.",
        });
      }

      if (password !== confirmPassword) {
        return res.status(400).json({ message: "Passwords do not match." });
      }

      const tokenHash = crypto
        .createHash("sha256")
        .update(rawToken)
        .digest("hex");
      const hashedPassword = await bcrypt.hash(password, 12);
      const changed = await this.userModel.resetPasswordWithToken(
        tokenHash,
        hashedPassword,
      );

      if (!changed) {
        return res.status(400).json({
          message:
            "This password-reset link is invalid or has expired. Please request a new one.",
        });
      }

      return res.status(200).json({
        message:
          "Your password has been reset successfully. You can now log in.",
        redirect: "/SoftEcoms/public/login.php",
      });
    } catch (error) {
      console.error("Reset password error:", error);
      return res.status(500).json({
        message: "Could not reset your password. Please try again.",
      });
    }
  };

  verifyEmail = async (req, res) => {
    try {
      const rawToken = String(req.query?.token || "");

      if (!/^[a-f0-9]{64}$/i.test(rawToken)) {
        return res.status(400).send(`
          <h1>Invalid verification link</h1>
          <p>Please check the link in your email.</p>
          <a href="/SoftEcoms/public/login.php">Go to Login</a>
        `);
      }

      const tokenHash = crypto
        .createHash("sha256")
        .update(rawToken)
        .digest("hex");

      const user = await this.userModel.findByVerificationToken(tokenHash);

      if (!user) {
        return res.status(400).send(`
          <h1>Verification link invalid or expired</h1>
          <p>Please request a new verification link or contact support.</p>
          <a href="/SoftEcoms/public/login.php">Go to Login</a>
        `);
      }

      const verified = await this.userModel.verifyEmail(user.id);

      if (!verified) {
        return res.status(400).send(`
          <h1>Verification failed</h1>
          <p>The link may have expired. Please contact support.</p>
          <a href="/SoftEcoms/public/login.php">Go to Login</a>
        `);
      }

      return res.status(200).send(`
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <title>Email Verified - Sweet!</title>
        </head>
        <body style="font-family:Arial,sans-serif;text-align:center;padding:40px;color:#333">
          <h1 style="color:#8b4513">Email verified successfully!</h1>
          <p>Your Sweet! account is now active.</p>
          <p>You can now log in to your account.</p>
          <a href="http://localhost/SoftEcoms/public/login.php"
             style="display:inline-block;background:#8b4513;color:white;padding:12px 22px;text-decoration:none;border-radius:6px">
            Go to Login
          </a>
        </body>
        </html>
      `);
    } catch (error) {
      console.error("Email verification error:", error);

      return res
        .status(500)
        .send(
          "An error occurred while verifying your email. Please try again later.",
        );
    }
  };

  me = async (req, res) => {
    try {
      if (!req.session || !req.session.authUser) {
        return res.status(401).json({
          message: "You are not logged in.",
        });
      }

      const userId =
        req.session.user_id ||
        req.session.authUser.user_id ||
        req.session.authUser.id;

      if (!userId) {
        return res.status(401).json({
          message: "User session is missing an ID.",
        });
      }

      const [rows] = await this.db.execute(
        `SELECT
            id,
            uuid,
            firstName,
            lastName,
            middleName,
            emailAddress,
            username,
            contactNumber,
            street,
            barangay,
            city,
            role,
            accountStatus
         FROM users
         WHERE id = ?
         LIMIT 1`,
        [userId],
      );

      if (rows.length === 0) {
        return res.status(404).json({
          message: "User account not found.",
        });
      }

      const user = rows[0];

      const profileComplete = Boolean(
        String(user.contactNumber || "").trim() &&
        String(user.street || "").trim() &&
        String(user.barangay || "").trim() &&
        String(user.city || "").trim(),
      );

      req.session.authUser = {
        ...req.session.authUser,
        user_id: user.id,
        uuid: user.uuid,
        firstName: user.firstName,
        lastName: user.lastName,
        middleName: user.middleName,
        emailAddress: user.emailAddress,
        username: user.username,
        contactNumber: user.contactNumber,
        street: user.street,
        barangay: user.barangay,
        city: user.city,
        role: user.role,
        accountStatus: user.accountStatus,
        profileComplete,
      };

      req.session.userRole = user.role;

      return res.status(200).json({
        user: req.session.authUser,
        role: user.role,
        profileComplete,
        needsProfileCompletion: user.role === "customer" && !profileComplete,
      });
    } catch (error) {
      console.error("Session lookup error:", error);

      return res.status(500).json({
        message: "Could not retrieve your profile.",
        error:
          process.env.NODE_ENV === "development" ? error.message : undefined,
      });
    }
  };

  completeProfile = async (req, res) => {
    try {
      if (!req.session || !req.session.authUser) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      const userId =
        req.session.user_id ||
        req.session.authUser.user_id ||
        req.session.authUser.id;

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "User session is missing an ID.",
        });
      }

      const contactNumber = String(req.body?.contactNumber || "").trim();

      const street = String(req.body?.street || "").trim();
      const barangay = String(req.body?.barangay || "").trim();
      const city = String(req.body?.city || "").trim();

      if (!contactNumber || !street || !barangay || !city) {
        return res.status(400).json({
          success: false,
          message: "Please complete all four profile fields.",
        });
      }

      if (
        contactNumber.length > 255 ||
        street.length > 255 ||
        barangay.length > 255 ||
        city.length > 255
      ) {
        return res.status(400).json({
          success: false,
          message: "One or more fields are too long.",
        });
      }

      // Use contactNumber, matching your database schema.
      const [result] = await this.db.execute(
        `UPDATE users
             SET contactNumber = ?,
                 street = ?,
                 barangay = ?,
                 city = ?
             WHERE id = ?
               AND role = 'customer'`,
        [contactNumber, street, barangay, city, userId],
      );

      // Confirm the account exists, even if no values changed.
      const [rows] = await this.db.execute(
        `SELECT id, uuid, contactNumber, street, barangay, city, role
             FROM users
             WHERE id = ?
             LIMIT 1`,
        [userId],
      );

      if (rows.length === 0 || rows[0].role !== "customer") {
        return res.status(404).json({
          success: false,
          message: "Customer account not found.",
        });
      }

      const user = rows[0];

      req.session.authUser = {
        ...req.session.authUser,
        user_id: user.id,
        uuid: user.uuid,
        contactNumber: user.contactNumber,
        street: user.street,
        barangay: user.barangay,
        city: user.city,
        profileComplete: Boolean(
          String(user.contactNumber || "").trim() &&
          String(user.street || "").trim() &&
          String(user.barangay || "").trim() &&
          String(user.city || "").trim(),
        ),
      };

      req.session.userRole = "customer";

      req.session.save((error) => {
        if (error) {
          console.error("Profile session save error:", error);

          return res.status(500).json({
            success: false,
            message: "Profile saved, but the session could not be updated.",
          });
        }

        return res.status(200).json({
          success: true,
          message: "Profile completed successfully.",
          profileComplete: true,
          needsProfileCompletion: false,
          user: req.session.authUser,
        });
      });
    } catch (error) {
      console.error("Complete profile error:", error);

      return res.status(500).json({
        success: false,
        message: "Could not save your profile details.",
      });
    }
  };

  logout = (req, res) => {
    if (!req.session) {
      res.clearCookie("connect.sid");

      return res.status(200).json({
        message: "Logged out successfully.",
      });
    }

    req.session.destroy((error) => {
      if (error) {
        console.error("Logout error:", error);

        return res.status(500).json({
          message: "Could not log out.",
        });
      }

      res.clearCookie("connect.sid", {
        httpOnly: true,
        sameSite: "lax",
        secure: false,
      });

      return res.status(200).json({
        message: "Logged out successfully.",
      });
    });
  };
}

module.exports = Authentication;
