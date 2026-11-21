require("dotenv").config();

const express = require("express");
const mysql = require("mysql2/promise");
const session = require("express-session");
const cors = require("cors");
const path = require("path");
const crypto = require("crypto");
const { storePublicImage, removePublicImage } = require("./app/config/mediaStorage");

// ==========================================
// CONTROLLER AND MODEL IMPORTS
// ==========================================

const Authentication = require("./app/controllers/Authentication");
const CustomerController = require("./app/controllers/customerController");
const CheckoutController = require("./app/controllers/checkoutController");
const ContactController = require("./app/controllers/contactController");
const AdminOrderController = require("./app/controllers/adminOrderController");
const AdminCustomerController = require("./app/controllers/adminCustomerController");

const Product = require("./app/models/product");
const requireAdmin = require("./app/middleware/adminAuth");

// ==========================================
// APP CONFIGURATION
// ==========================================

const app = express();
const PORT = process.env.NODE_API_PORT || 3000;

if (!process.env.SESSION_SECRET) {
  throw new Error("SESSION_SECRET is missing from .env");
}

if (!process.env.DB_NAME) {
  throw new Error("DB_NAME is missing from .env");
}

// ==========================================
// MIDDLEWARE
// ==========================================

app.use(
  cors({
    origin: process.env.FRONTEND_ORIGIN || "http://localhost",
    credentials: true,
  }),
);

app.use(express.json({ limit: "7mb" }));

app.use(
  express.urlencoded({
    extended: true,
    limit: "7mb",
  }),
);

// ==========================================
// SESSION CONFIGURATION
// ==========================================

app.set("trust proxy", 1);

class MySQLSessionStore extends session.Store {
  constructor(pool) {
    super();
    this.pool = pool;
  }

  get(sessionId, callback) {
    this.pool
      .execute("SELECT session_data FROM app_sessions WHERE session_id = ? AND expires_at > ?", [sessionId, Date.now()])
      .then(([rows]) => callback(null, rows.length ? JSON.parse(rows[0].session_data) : null))
      .catch(callback);
  }

  set(sessionId, sessionData, callback = () => {}) {
    const expiry = sessionData.cookie?.expires
      ? new Date(sessionData.cookie.expires).getTime()
      : Date.now() + Number(sessionData.cookie?.maxAge || 60 * 60 * 1000);
    this.pool
      .execute(
        `INSERT INTO app_sessions (session_id, session_data, expires_at)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE session_data = VALUES(session_data), expires_at = VALUES(expires_at)`,
        [sessionId, JSON.stringify(sessionData), expiry],
      )
      .then(() => callback(null))
      .catch(callback);
  }

  touch(sessionId, sessionData, callback = () => {}) {
    const expiry = sessionData.cookie?.expires
      ? new Date(sessionData.cookie.expires).getTime()
      : Date.now() + Number(sessionData.cookie?.maxAge || 60 * 60 * 1000);
    this.pool
      .execute("UPDATE app_sessions SET expires_at = ? WHERE session_id = ?", [expiry, sessionId])
      .then(() => callback(null))
      .catch(callback);
  }

  destroy(sessionId, callback = () => {}) {
    this.pool
      .execute("DELETE FROM app_sessions WHERE session_id = ?", [sessionId])
      .then(() => callback(null))
      .catch(callback);
  }
}

// ==========================================
// START SERVER
// ==========================================

async function startServer() {
  let db;

  try {
    // ==========================================
    // DATABASE CONNECTION
    // ==========================================

    db = mysql.createPool({
      host: process.env.DB_HOST || "localhost",
      user: process.env.DB_USER || "root",
      password: process.env.DB_PASSWORD || "",
      database: process.env.DB_NAME,
      port: Number(process.env.DB_PORT || 3306),
      ssl: process.env.DB_SSL === "true" ? { rejectUnauthorized: true } : undefined,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
    });

    await db.query("SELECT 1");

    // Keep inventory support present for databases created from older SQL dumps.
    const [stockColumn] = await db.query("SHOW COLUMNS FROM product_item LIKE 'stock'");
    if (stockColumn.length === 0) {
      await db.query("ALTER TABLE product_item ADD COLUMN stock INT NOT NULL DEFAULT 0");
    }

    const [contactStatusColumn] = await db.query("SHOW COLUMNS FROM contact_messages LIKE 'status'");
    if (contactStatusColumn.length === 0) {
      await db.query("ALTER TABLE contact_messages ADD COLUMN status ENUM('unread', 'read', 'replied') NOT NULL DEFAULT 'unread'");
    }

    await db.query("ALTER TABLE users ALTER COLUMN profile SET DEFAULT 'default-avatar.svg'");
    await db.execute(
      "UPDATE users SET profile = ? WHERE profile IS NULL OR TRIM(profile) = ''",
      ["default-avatar.svg"],
    );

    await db.query(`
      CREATE TABLE IF NOT EXISTS app_sessions (
        session_id VARCHAR(128) NOT NULL PRIMARY KEY,
        session_data MEDIUMTEXT NOT NULL,
        expires_at BIGINT UNSIGNED NOT NULL,
        INDEX app_sessions_expires_at_idx (expires_at)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    `);
    await db.execute("DELETE FROM app_sessions WHERE expires_at <= ?", [Date.now()]);

    app.use(
      session({
        name: "connect.sid",
        secret: process.env.SESSION_SECRET,
        store: new MySQLSessionStore(db),
        resave: false,
        saveUninitialized: false,
        cookie: {
          httpOnly: true,
          sameSite: "lax",
          secure: process.env.APP_ENV === "production",
          maxAge: 60 * 60 * 1000,
        },
      }),
    );

    console.log("MySQL connected successfully!");

    // ==========================================
    // INITIALIZE CONTROLLERS AND MODELS
    // ==========================================

    const auth = new Authentication(db);

    const customerController = new CustomerController(db);

    const checkoutController = new CheckoutController(db);

    const contactController = new ContactController(db);

    const adminOrderController = new AdminOrderController(db);

    const adminCustomerController = new AdminCustomerController(db);

    const product = new Product(db);

    const logAdminActivity = async (req, actionType, description) => {
      try {
        const adminId = Number(req.session?.user_id || req.session?.authUser?.user_id);
        await db.execute(
          "INSERT INTO admin_changelog (admin_id, action_type, description) VALUES (?, ?, ?)",
          [adminId || null, actionType, description],
        );
      } catch (error) {
        console.error("Admin activity log error:", error);
      }
    };

    // ==========================================
    // GENERAL ROUTES
    // ==========================================

    app.get("/", (req, res) => {
      res.send("Sweet! Node.js server is running.");
    });

    // ==========================================
    // AUTHENTICATION ROUTES
    // ==========================================

    app.post("/register", auth.register);

    app.post("/login", auth.login);

    app.post("/logout", auth.logout);

    app.get("/me", auth.me);

    app.get("/verify-email", auth.verifyEmail);

    app.post("/forgot-password", auth.forgotPassword);

    app.post("/reset-password", auth.resetPassword);

    app.post("/complete-profile", auth.completeProfile);

    // ==========================================
    // CUSTOMER PROFILE ROUTES
    // ==========================================

    app.put("/update-profile", auth.updateProfile);

    // ==========================================
    // PRODUCT ROUTES
    // ==========================================

    app.post("/admin/products", requireAdmin, async (req, res) => {
      let savedImageReference = null;
      try {
        const { productName, productDescription, price, stock, imageData } = req.body || {};
        let imageName = null;

        if (imageData) {
          const match = /^data:(image\/(?:jpeg|png|webp));base64,([A-Za-z0-9+/=]+)$/.exec(String(imageData));
          if (!match) return res.status(400).json({ success: false, message: "Choose a JPG, PNG, or WEBP image." });

          const imageBuffer = Buffer.from(match[2], "base64");
          if (!imageBuffer.length || imageBuffer.length > 3 * 1024 * 1024) {
            return res.status(400).json({ success: false, message: "Product images must be 3 MB or smaller." });
          }

          const isValidImage = match[1] === "image/jpeg"
            ? imageBuffer[0] === 0xff && imageBuffer[1] === 0xd8 && imageBuffer[2] === 0xff
            : match[1] === "image/png"
              ? imageBuffer.subarray(0, 8).equals(Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]))
              : imageBuffer.subarray(0, 4).toString() === "RIFF" && imageBuffer.subarray(8, 12).toString() === "WEBP";
          if (!isValidImage) return res.status(400).json({ success: false, message: "The selected file is not a valid image." });

          const extension = { "image/jpeg": "jpg", "image/png": "png", "image/webp": "webp" }[match[1]];
          const filename = `p${crypto.randomBytes(6).toString("hex")}.${extension}`;
          const imageDirectory = path.join(__dirname, "public", "assets", "product_img");
          const storedImage = await storePublicImage({
            buffer: imageBuffer,
            filename,
            folder: "products",
            localDirectory: imageDirectory,
          });
          imageName = storedImage.reference;
          savedImageReference = storedImage.reference;
        }

        const created = await product.createProduct({ productName, productDescription, price, stock, img: imageName });
        await logAdminActivity(req, "PRODUCT_CREATE", `Added product \"${created.productName}\" (ID ${created.id}).`);
        return res.status(201).json({ success: true, message: "Product added successfully.", product: created });
      } catch (error) {
        if (savedImageReference) {
          await removePublicImage(savedImageReference, path.join(__dirname, "public", "assets", "product_img"));
        }
        console.error("Create product error:", error);
        const status = error.message.startsWith("Product ") || error.message.startsWith("Description ") || error.message.startsWith("Enter ") || error.message.startsWith("Stock ") ? 400 : 500;
        return res.status(status).json({ success: false, message: status === 400 ? error.message : "Unable to add product. Check the product table schema and try again." });
      }
    });

    // Get all products
    app.get("/products", async (req, res) => {
      try {
        const products = await product.getAllProducts();

        return res.status(200).json({
          success: true,
          products,
        });
      } catch (error) {
        console.error("Get products error:", error);

        return res.status(500).json({
          success: false,
          message: "Failed to retrieve products.",
        });
      }
    });

    // Get product by ID
    app.get("/products/:id", async (req, res) => {
      try {
        const foundProduct = await product.getProductById(req.params.id);

        if (!foundProduct) {
          return res.status(404).json({
            success: false,
            message: "Product not found.",
          });
        }

        return res.status(200).json({
          success: true,
          product: foundProduct,
        });
      } catch (error) {
        console.error("Get product error:", error);

        return res.status(400).json({
          success: false,
          message: error.message,
        });
      }
    });

    // ==========================================
    // INVENTORY MANAGEMENT ROUTES
    // ADMIN ONLY
    // ==========================================

    // Add stock to an existing product
    app.post("/inventory/:id/add-stock", requireAdmin, async (req, res) => {
      try {
        const { quantity } = req.body || {};

        if (quantity === undefined) {
          return res.status(400).json({
            success: false,
            message: "Stock quantity is required.",
          });
        }

        const result = await product.addStock(req.params.id, quantity);
        await logAdminActivity(req, "INVENTORY_UPDATE", `Added ${result.addedStock} units to \"${result.productName}\" (ID ${result.productId}); stock is now ${result.stock}.`);

        return res.status(200).json({
          success: true,
          message: "Stock added successfully.",
          inventory: result,
        });
      } catch (error) {
        console.error("Add stock error:", error);

        const status = error.message === "Product not found." ? 404 : 400;

        return res.status(status).json({
          success: false,
          message: error.message,
        });
      }
    });

    // Remove stock from an existing product
    app.post("/inventory/:id/remove-stock", requireAdmin, async (req, res) => {
      try {
        const { quantity } = req.body || {};

        if (quantity === undefined) {
          return res.status(400).json({
            success: false,
            message: "Stock quantity is required.",
          });
        }

        const result = await product.removeStock(req.params.id, quantity);
        await logAdminActivity(req, "INVENTORY_UPDATE", `Removed ${result.removedStock} units from \"${result.productName}\" (ID ${result.productId}); stock is now ${result.stock}.`);

        return res.status(200).json({
          success: true,
          message: "Stock removed successfully.",
          inventory: result,
        });
      } catch (error) {
        console.error("Remove stock error:", error);

        const status = error.message === "Product not found." ? 404 : 400;

        return res.status(status).json({
          success: false,
          message: error.message,
        });
      }
    });

    // Set product stock to an exact quantity
    app.put("/inventory/:id/stock", requireAdmin, async (req, res) => {
      try {
        const { stock } = req.body || {};

        if (stock === undefined) {
          return res.status(400).json({
            success: false,
            message: "Stock quantity is required.",
          });
        }

        const result = await product.updateStock(req.params.id, stock);
        await logAdminActivity(req, "INVENTORY_UPDATE", `Set stock for product ID ${result.productId} to ${result.stock}.`);

        return res.status(200).json({
          success: true,
          message: "Product stock updated successfully.",
          inventory: result,
        });
      } catch (error) {
        console.error("Update stock error:", error);

        const status = error.message === "Product not found." ? 404 : 400;

        return res.status(status).json({
          success: false,
          message: error.message,
        });
      }
    });

    // Get low-stock products
    app.get("/inventory/low-stock", requireAdmin, async (req, res) => {
      try {
        const threshold = req.query.threshold ?? 5;

        const products = await product.getLowStockProducts(threshold);

        return res.status(200).json({
          success: true,
          threshold: Number(threshold),
          products,
        });
      } catch (error) {
        console.error("Low stock error:", error);

        return res.status(400).json({
          success: false,
          message: error.message,
        });
      }
    });

    // Check product stock availability
    app.post("/inventory/check-stock", async (req, res) => {
      try {
        const { productUUID, quantity } = req.body || {};

        const result = await product.checkStockAvailability(
          productUUID,
          quantity,
        );

        return res.status(200).json({
          success: true,
          ...result,
        });
      } catch (error) {
        console.error("Check stock error:", error);

        return res.status(400).json({
          success: false,
          message: error.message,
        });
      }
    });

    // ==========================================
    // CUSTOMER CART ROUTES
    // ==========================================

    app.post("/cart/add", customerController.addToCart);

    app.get("/cart", customerController.getCart);

    app.get("/cart/count", customerController.getCartCount);

    app.post("/cart/update", customerController.updateQuantity);

    app.post("/cart/remove", customerController.removeProduct);

    app.delete("/cart/clear", customerController.clearCart);

    app.post("/cart/summary", customerController.getCartSummary);

    // ==========================================
    // CUSTOMER ORDER ROUTES
    // ==========================================

    app.get("/orders/history", customerController.getOrderHistory);

    app.get("/orders/receipt/:uuid", customerController.getOrderReceipt);

    // ==========================================
    // CUSTOMER CANCELLATION ROUTE
    // ==========================================

    app.post("/orders/cancel-request", customerController.requestCancellation);

    // ==========================================
    // ADMIN CANCELLATION MANAGEMENT
    // ==========================================

    // Get all cancellation requests
    app.get(
      "/admin/orders/cancellation-requests",
      requireAdmin,
      adminOrderController.getCancellationRequests,
    );

    // Approve or reject cancellation request
    app.post(
      "/admin/orders/cancellation-requests/:id/review",
      requireAdmin,
      adminOrderController.reviewCancellationRequest,
    );

    // ==========================================
    // ADMIN ORDER STATUS MANAGEMENT
    // ==========================================

    app.post(
      "/admin/orders/update-status",
      requireAdmin,
      adminOrderController.updateOrderStatus,
    );

    // ==========================================
    // ADMIN CUSTOMER MANAGEMENT ROUTES
    // ==========================================

    // Get customer profile, statistics, and order history
    app.get(
      "/admin/customers/:id",
      requireAdmin,
      adminCustomerController.getCustomer,
    );

    // Update customer information and profile picture
    app.put(
      "/admin/customers/:id",
      requireAdmin,
      adminCustomerController.updateCustomer,
    );

    // Reset customer password
    app.put(
      "/admin/customers/:id/password",
      requireAdmin,
      adminCustomerController.resetPassword,
    );

    // Activate or deactivate customer account
    app.patch(
      "/admin/customers/:id/status",
      requireAdmin,
      adminCustomerController.updateStatus,
    );

    // ==========================================
    // CHECKOUT ROUTE
    // ==========================================

    app.post("/checkout", checkoutController.checkout);

    // ==========================================
    // CONTACT ROUTES
    // ==========================================

    // Customer submits a contact message
    app.post("/contact", contactController.createMessage);

    // Admin retrieves all contact messages
    app.get(
      "/contact/messages",
      requireAdmin,
      contactController.getAllMessages,
    );

    // Admin retrieves one contact message
    app.get(
      "/contact/messages/:id",
      requireAdmin,
      contactController.getMessageById,
    );

    // Admin changes message status: unread, read, or replied
    app.patch(
      "/contact/messages/:id/status",
      requireAdmin,
      contactController.updateMessageStatus,
    );

    // Admin replies to a customer inquiry by email.
    app.post(
      "/contact/messages/:id/reply",
      requireAdmin,
      contactController.replyToMessage,
    );

    // ==========================================
    // 404 HANDLER
    // ==========================================

    app.use((req, res) => {
      return res.status(404).json({
        success: false,
        message: "API endpoint not found.",
      });
    });

    // ==========================================
    // ERROR HANDLER
    // ==========================================

    app.use((error, req, res, next) => {
      console.error("Express error:", error);

      if (res.headersSent) {
        return next(error);
      }

      return res.status(500).json({
        success: false,
        message: "An internal server error occurred.",
      });
    });

    // ==========================================
    // START LISTENING
    // ==========================================

    app.listen(PORT, () => {
      console.log(`Sweet! server running at http://localhost:${PORT}`);
    });
  } catch (error) {
    console.error("Server startup failed:", error);

    if (db) {
      await db.end().catch((dbError) => {
        console.error("Database shutdown error:", dbError);
      });
    }

    process.exit(1);
  }
}

// ==========================================
// RUN SERVER
// ==========================================

startServer();
