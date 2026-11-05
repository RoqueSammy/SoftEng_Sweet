require("dotenv").config();

const express = require("express");
const mysql = require("mysql2/promise");
const session = require("express-session");
const cors = require("cors");

const Authentication = require("./app/controllers/Authentication");
const CustomerController = require("./app/controllers/customerController");
const CheckoutController = require("./app/controllers/checkoutController");

const app = express();
const PORT = process.env.PORT || 3000;

// Check required environment variables
if (!process.env.SESSION_SECRET) {
  throw new Error("SESSION_SECRET is missing from .env");
}

if (!process.env.DB_NAME) {
  throw new Error("DB_NAME is missing from .env");
}

// --------------------------------------------------
// MIDDLEWARE
// --------------------------------------------------

app.use(
  cors({
    origin: process.env.FRONTEND_ORIGIN || "http://localhost",
    credentials: true,
  }),
);

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Express session
app.use(
  session({
    name: "connect.sid",
    secret: process.env.SESSION_SECRET,
    resave: false,
    saveUninitialized: false,
    cookie: {
      httpOnly: true,
      sameSite: "lax",
      secure: false, // Local HTTP development only
      maxAge: 60 * 60 * 1000,
    },
  }),
);

// --------------------------------------------------
// START SERVER AND CONNECT DATABASE
// --------------------------------------------------

async function startServer() {
  try {
    const db = mysql.createPool({
      host: process.env.DB_HOST || "localhost",
      user: process.env.DB_USER || "root",
      password: process.env.DB_PASSWORD || "",
      database: process.env.DB_NAME,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
    });

    // Test database connection
    await db.query("SELECT 1");

    console.log("MySQL connected successfully!");

    // --------------------------------------------------
    // INITIALIZE CONTROLLERS
    // --------------------------------------------------

    const auth = new Authentication(db);
    const customerController = new CustomerController(db);
    const checkoutController = new CheckoutController(db);

    // --------------------------------------------------
    // GENERAL ROUTES
    // --------------------------------------------------

    app.get("/", (req, res) => {
      res.send("Sweet! Node.js server is running.");
    });

    // --------------------------------------------------
    // AUTHENTICATION ROUTES
    // --------------------------------------------------

    app.post("/register", auth.register);
    app.post("/login", auth.login);
    app.post("/logout", auth.logout);

    app.get("/me", auth.me);
    app.get("/verify-email", auth.verifyEmail);

    app.post("/forgot-password", auth.forgotPassword);
    app.post("/reset-password", auth.resetPassword);
    app.post("/complete-profile", auth.completeProfile);

    // --------------------------------------------------
    // CUSTOMER CART ROUTES
    // --------------------------------------------------

    app.post("/cart/add", customerController.addToCart);

    app.get("/cart", customerController.getCart);

    app.get("/cart/count", customerController.getCartCount);

    app.post("/cart/update", customerController.updateQuantity);

    app.post("/cart/remove", customerController.removeProduct);

    app.delete("/cart/clear", customerController.clearCart);

    // --------------------------------------------------
    // CHECKOUT ROUTE
    // --------------------------------------------------

    app.post("/checkout", checkoutController.checkout);

    // --------------------------------------------------
    // 404 HANDLER
    // --------------------------------------------------

    app.use((req, res) => {
      res.status(404).json({
        success: false,
        message: "API endpoint not found.",
      });
    });

    // --------------------------------------------------
    // START LISTENING
    // --------------------------------------------------

    app.listen(PORT, () => {
      console.log(`Sweet! server running at http://localhost:${PORT}`);
    });
  } catch (error) {
    console.error("Server startup failed:", error);
    process.exit(1);
  }
}

startServer();
