const crypto = require("crypto");

class CheckoutController {
  constructor(db) {
    this.db = db;
  }

  // ==========================================
  // SHOP LOCATION
  // ==========================================

  SHOP_LATITUDE = 8.474078;
  SHOP_LONGITUDE = 124.644512;

  // ==========================================
  // CALCULATE DISTANCE USING HAVERSINE FORMULA
  // ==========================================

  calculateDistance(lat1, lon1, lat2, lon2) {
    const earthRadius = 6371;

    const toRadians = (degrees) => degrees * (Math.PI / 180);

    const dLat = toRadians(lat2 - lat1);
    const dLon = toRadians(lon2 - lon1);

    const a =
      Math.sin(dLat / 2) ** 2 +
      Math.cos(toRadians(lat1)) *
        Math.cos(toRadians(lat2)) *
        Math.sin(dLon / 2) ** 2;

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return earthRadius * c;
  }

  // ==========================================
  // CALCULATE DELIVERY FEE
  // ==========================================

  calculateDeliveryFee(distanceKm) {
    if (distanceKm <= 2) {
      return 15;
    }

    if (distanceKm <= 5) {
      return 30;
    }

    if (distanceKm <= 8) {
      return 75;
    }

    if (distanceKm <= 10) {
      return 90;
    }

    return null;
  }

  // ==========================================
  // CHECKOUT
  // ==========================================

  checkout = async (req, res) => {
    let connection;

    try {
      // 1. VERIFY CUSTOMER SESSION

      const sessionUser = req.session?.authUser;

      const userId = Number(
        req.session?.user_id || sessionUser?.user_id || sessionUser?.id,
      );

      const userRole = req.session?.userRole || sessionUser?.role;

      if (!sessionUser || !Number.isSafeInteger(userId) || userId < 1) {
        return res.status(401).json({
          success: false,
          message: "Please log in before checking out.",
        });
      }

      if (!["customer", "user"].includes(userRole)) {
        return res.status(403).json({
          success: false,
          message: "Only customers can place orders.",
        });
      }

      // 2. VALIDATE CHECKOUT INFORMATION

      const address = String(req.body.address || "").trim();
      const contactNumber = String(req.body.phone || "").trim();
      const paymentMethod = String(req.body.payment_method || "").trim();

      const latitude = Number(req.body.latitude);
      const longitude = Number(req.body.longitude);

      const selectedCartIDs = req.body.selected_cart_ids;

      if (!address || !contactNumber) {
        return res.status(400).json({
          success: false,
          message: "Address and contact number are required.",
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

      if (
        req.body.latitude === undefined ||
        req.body.longitude === undefined ||
        !Number.isFinite(latitude) ||
        !Number.isFinite(longitude) ||
        latitude < -90 ||
        latitude > 90 ||
        longitude < -180 ||
        longitude > 180
      ) {
        return res.status(400).json({
          success: false,
          message: "Please select a valid delivery location.",
        });
      }

      // 3. CALCULATE DELIVERY DISTANCE

      const distanceKm = this.calculateDistance(
        this.SHOP_LATITUDE,
        this.SHOP_LONGITUDE,
        latitude,
        longitude,
      );

      const deliveryFee = this.calculateDeliveryFee(distanceKm);

      if (deliveryFee === null) {
        return res.status(400).json({
          success: false,
          delivery_unavailable: true,
          message:
            `Your delivery location is ${distanceKm.toFixed(2)} km ` +
            "from our shop. We only deliver within 10 km.",
        });
      }

      // 4. VALIDATE SELECTED CART ITEMS

      if (!Array.isArray(selectedCartIDs) || selectedCartIDs.length === 0) {
        return res.status(400).json({
          success: false,
          message: "Please select at least one product.",
        });
      }

      const cartIDs = selectedCartIDs.map(Number);

      if (cartIDs.some((id) => !Number.isSafeInteger(id) || id < 1)) {
        return res.status(400).json({
          success: false,
          message: "Invalid selected cart items.",
        });
      }

      const uniqueCartIDs = [...new Set(cartIDs)];

      if (uniqueCartIDs.length !== cartIDs.length) {
        return res.status(400).json({
          success: false,
          message: "Duplicate cart items submitted.",
        });
      }

      // 5. START DATABASE TRANSACTION

      connection = await this.db.getConnection();

      await connection.beginTransaction();

      // 6. GET CUSTOMER DETAILS

      const [customers] = await connection.execute(
        `SELECT *
         FROM users
         WHERE id = ?
           AND role IN ('user', 'customer')
         LIMIT 1`,
        [userId],
      );

      if (!customers.length) {
        throw new Error("Customer account not found.");
      }

      const customer = customers[0];

      // 7. GET ONLY SELECTED CART ITEMS
      // Lock the selected cart rows during checkout.

      const placeholders = uniqueCartIDs.map(() => "?").join(",");

      const [cartItems] = await connection.execute(
        `SELECT
            cart.id AS cartID,
            cart.product_id,
            cart.quantity,
            product_item.productName,
            product_item.price,
            product_item.img
         FROM cart
         INNER JOIN product_item
            ON cart.product_id = product_item.id
         WHERE cart.user_id = ?
           AND cart.id IN (${placeholders})
         FOR UPDATE`,
        [userId, ...uniqueCartIDs],
      );

      if (cartItems.length !== uniqueCartIDs.length) {
        throw new Error("Some selected products no longer exist in your cart.");
      }

      // 8. LOCK PRODUCT ROWS AND CHECK STOCK

      // Sort product IDs to keep the locking order consistent
      // and reduce the risk of deadlocks.

      const productIDs = [
        ...new Set(cartItems.map((item) => Number(item.product_id))),
      ].sort((a, b) => a - b);

      const productPlaceholders = productIDs.map(() => "?").join(",");

      const [products] = await connection.execute(
        `SELECT id, productName, stock
         FROM product_item
         WHERE id IN (${productPlaceholders})
         ORDER BY id
         FOR UPDATE`,
        productIDs,
      );

      if (products.length !== productIDs.length) {
        throw new Error(
          "One or more selected products are no longer available.",
        );
      }

      const productStockMap = new Map(
        products.map((product) => [Number(product.id), Number(product.stock)]),
      );

      // Check each selected cart item's requested quantity
      // against the currently available stock.

      const unavailableItems = [];

      for (const item of cartItems) {
        const productID = Number(item.product_id);
        const availableStock = productStockMap.get(productID);
        const requestedQuantity = Number(item.quantity);

        if (
          !Number.isSafeInteger(availableStock) ||
          !Number.isSafeInteger(requestedQuantity) ||
          requestedQuantity < 1 ||
          availableStock < requestedQuantity
        ) {
          unavailableItems.push({
            cartID: item.cartID,
            productName: item.productName,
            requestedQuantity,
            availableStock: Number.isFinite(availableStock)
              ? availableStock
              : 0,
          });
        }
      }

      if (unavailableItems.length > 0) {
        const stockError = new Error(
          "Some products have insufficient stock. Please update your cart before checking out.",
        );

        stockError.statusCode = 409;
        stockError.stockError = true;
        stockError.unavailableItems = unavailableItems;

        throw stockError;
      }

      // 9. CALCULATE SUBTOTAL

      let subtotalCents = 0;

      for (const item of cartItems) {
        const priceCents = Math.round(Number(item.price) * 100);
        const quantity = Number(item.quantity);

        if (
          !Number.isSafeInteger(priceCents) ||
          priceCents < 0 ||
          !Number.isSafeInteger(quantity) ||
          quantity < 1
        ) {
          throw new Error("Invalid cart item information.");
        }

        subtotalCents += priceCents * quantity;
      }

      // 10. CALCULATE VAT AND FINAL TOTAL

      const taxCents = Math.round(subtotalCents * 0.12);

      const deliveryFeeCents = deliveryFee * 100;

      const totalCents = subtotalCents + taxCents + deliveryFeeCents;

      const subtotal = (subtotalCents / 100).toFixed(2);
      const tax = (taxCents / 100).toFixed(2);

      const deliveryFeeAmount = (deliveryFeeCents / 100).toFixed(2);

      const deliveryDistance = distanceKm.toFixed(2);

      const totalPrice = (totalCents / 100).toFixed(2);

      // 11. GENERATE ORDER DETAILS

      const orderUUID = crypto.randomUUID();

      const orderNumber = "CO" + crypto.randomInt(100000000, 1000000000);

      // 12. INSERT ORDER

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
            tax,
            delivery_fee,
            delivery_distance_km,
            latitude,
            longitude
         )
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
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
          deliveryFeeAmount,
          deliveryDistance,
          latitude,
          longitude,
        ],
      );

      const orderId = orderResult.insertId;

      // 13. INSERT ORDER ITEMS

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

      // 14. DEDUCT PRODUCT STOCK

      // Product rows are still locked by this transaction.
      // The extra stock condition is a final safeguard.

      for (const item of cartItems) {
        const [stockResult] = await connection.execute(
          `UPDATE product_item
           SET stock = stock - ?
           WHERE id = ?
             AND stock >= ?`,
          [
            Number(item.quantity),
            Number(item.product_id),
            Number(item.quantity),
          ],
        );

        if (stockResult.affectedRows !== 1) {
          const stockError = new Error(
            `Insufficient stock for ${item.productName}. Please update your cart.`,
          );

          stockError.statusCode = 409;
          stockError.stockError = true;

          throw stockError;
        }
      }

      // 15. DELETE ONLY PURCHASED PRODUCTS FROM CART

      await connection.execute(
        `DELETE FROM cart
         WHERE user_id = ?
           AND id IN (${placeholders})`,
        [userId, ...uniqueCartIDs],
      );

      // 16. COMMIT TRANSACTION

      // The order, order items, stock deductions, and cart deletion
      // are committed together. If any step fails, everything rolls back.

      await connection.commit();

      // 17. RETURN RECEIPT INFORMATION

      const orderDate = new Date().toLocaleString("en-PH", {
        timeZone: "Asia/Manila",
      });

      return res.status(201).json({
        success: true,
        message: "Your order has been placed successfully!",

        order: {
          id: orderId,
          uuid: orderUUID,
          orderNumber,
          order_date: orderDate,

          full_name:
            customer.fullName || customer.full_name || customer.username || "",

          email: customer.emailAddress || customer.email || "",

          phone: contactNumber,
          address,

          payment_method: paymentMethod,
          fulfillment: "delivery",

          street: address,
          barangay: "",
          city: "",

          subtotal,
          tax,
          delivery_fee: deliveryFeeAmount,
          delivery_distance_km: deliveryDistance,
          total: totalPrice,
          totalPrice,

          status: "pending",

          latitude,
          longitude,
        },

        items: cartItems.map((item) => ({
          name: item.productName,
          quantity: Number(item.quantity),
          price: Number(item.price),
          image: item.img || "",
        })),
      });
    } catch (error) {
      if (connection) {
        try {
          await connection.rollback();
        } catch (rollbackError) {
          console.error("Rollback error:", rollbackError);
        }
      }

      console.error("Checkout error:", error);

      return res.status(error.statusCode || 400).json({
        success: false,
        message: error.message || "Unable to process your order.",
        ...(error.stockError
          ? {
              stockError: true,
              unavailableItems: error.unavailableItems || [],
            }
          : {}),
      });
    } finally {
      if (connection) {
        connection.release();
      }
    }
  };
}

module.exports = CheckoutController;
