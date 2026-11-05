const crypto = require("crypto");

class CheckoutController {
  constructor(db) {
    this.db = db;
  }

  // ==========================================
  // CUSTOMER CHECKOUT
  // ==========================================

  checkout = async (req, res) => {
    let connection;

    try {
      // STEP 1: Verify customer authentication
      if (!req.session?.authUser || !req.session.user_id) {
        return res.status(401).json({
          success: false,
          message: "Please log in before checking out.",
        });
      }

      const userId = Number(req.session.user_id);

      if (!Number.isInteger(userId) || userId < 1) {
        return res.status(401).json({
          success: false,
          message: "Invalid customer session.",
        });
      }

      if (!["customer", "user"].includes(req.session.userRole)) {
        return res.status(403).json({
          success: false,
          message: "Only customers can place orders.",
        });
      }

      // STEP 2: Validate checkout information
      const address = String(req.body.address || "").trim();
      const contactNumber = String(req.body.phone || "").trim();
      const paymentMethod = String(req.body.payment_method || "").trim();

      if (!address) {
        return res.status(400).json({
          success: false,
          message: "Delivery address is required.",
        });
      }

      if (!contactNumber) {
        return res.status(400).json({
          success: false,
          message: "Contact number is required.",
        });
      }

      if (contactNumber.length > 20) {
        return res.status(400).json({
          success: false,
          message: "Contact number is too long.",
        });
      }

      if (!["Cash", "GCash"].includes(paymentMethod)) {
        return res.status(400).json({
          success: false,
          message: "Invalid payment method.",
        });
      }

      // STEP 3: Start database transaction
      connection = await this.db.getConnection();

      await connection.beginTransaction();

      // Verify customer exists
      const [customers] = await connection.execute(
        `SELECT id
         FROM users
         WHERE id = ?
           AND role IN ('user', 'customer')
         LIMIT 1`,
        [userId],
      );

      if (customers.length === 0) {
        throw new Error("Customer account not found.");
      }

      // STEP 4: Retrieve customer's cart
      const [cartItems] = await connection.execute(
        `SELECT
           cart.product_id,
           cart.quantity,
           product_item.productName,
           product_item.price
         FROM cart
         INNER JOIN product_item
           ON cart.product_id = product_item.id
         WHERE cart.user_id = ?
         FOR UPDATE`,
        [userId],
      );

      if (cartItems.length === 0) {
        throw new Error("Your cart is empty.");
      }

      // STEP 5: Calculate subtotal and VAT
      // Use integer cents to avoid floating-point money errors.
      let subtotalCents = 0;

      for (const item of cartItems) {
        const priceCents = Math.round(Number(item.price) * 100);

        const quantity = Number(item.quantity);

        if (
          !Number.isSafeInteger(priceCents) ||
          priceCents < 0 ||
          !Number.isInteger(quantity) ||
          quantity < 1
        ) {
          throw new Error("Invalid cart item information.");
        }

        subtotalCents += priceCents * quantity;
      }

      if (!Number.isSafeInteger(subtotalCents)) {
        throw new Error("Order total is too large.");
      }

      const taxCents = Math.round(subtotalCents * 0.12);
      const totalCents = subtotalCents + taxCents;

      const subtotal = (subtotalCents / 100).toFixed(2);
      const tax = (taxCents / 100).toFixed(2);
      const totalPrice = (totalCents / 100).toFixed(2);

      // STEP 6: Generate order identifiers
      const orderUUID = crypto.randomUUID();

      // CO + 9 digits = 11 characters
      const orderNumber = "CO" + crypto.randomInt(100000000, 1000000000);

      // STEP 7: Create order
      const [orderResult] = await connection.execute(
        `INSERT INTO orders (
           uuid,
           COnumber,
           user_id,
           totalPrice,
           address,
           contactNumber,
           paymentMethod,
           status,
           subtotal,
           tax
         )
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          orderUUID,
          orderNumber,
          userId,
          totalPrice,
          address,
          contactNumber,
          paymentMethod,
          "pending",
          subtotal,
          tax,
        ],
      );

      const orderId = orderResult.insertId;

      // STEP 8: Insert order items
      for (const item of cartItems) {
        await connection.execute(
          `INSERT INTO orderitems (
             order_id,
             product_id,
             quantity,
             price
           )
           VALUES (?, ?, ?, ?)`,
          [
            orderId,
            item.product_id,
            item.quantity,
            Number(item.price).toFixed(2),
          ],
        );
      }

      // STEP 9: Clear customer's cart
      await connection.execute(
        `DELETE FROM cart
         WHERE user_id = ?`,
        [userId],
      );

      // STEP 10: Commit transaction
      await connection.commit();

      return res.status(201).json({
        success: true,
        message: "Your order has been placed successfully!",
        order: {
          id: orderId,
          uuid: orderUUID,
          orderNumber,
          subtotal,
          tax,
          totalPrice,
          paymentMethod,
          status: "pending",
        },
      });
    } catch (error) {
      if (connection) {
        await connection.rollback();
      }

      console.error("Checkout error:", error);

      return res.status(400).json({
        success: false,
        message: error.message || "Unable to process your order.",
      });
    } finally {
      if (connection) {
        connection.release();
      }
    }
  };
}

module.exports = CheckoutController;
