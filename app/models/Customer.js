const User = require("./User");
const Cart = require("./cart");

class Customer extends User {
  constructor(db) {
    super(db);
    this.cart = new Cart(db);
  }

  // ==========================================
  // ADD PRODUCT TO CART
  // ==========================================

  async addToCart(userId, productUUID, quantity) {
    const parsedUserId = Number(userId);
    const parsedQuantity = Number(quantity);
    const cleanUUID = String(productUUID || "").trim();

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!cleanUUID) {
      throw new Error("Product UUID is required.");
    }

    if (!Number.isSafeInteger(parsedQuantity) || parsedQuantity < 1) {
      throw new Error("Invalid product quantity.");
    }

    return this.cart.addProduct(parsedUserId, cleanUUID, parsedQuantity);
  }

  // ==========================================
  // MANAGE CUSTOMER PROFILE
  // ==========================================

  async manageProfile(userId) {
    const [rows] = await this.db.execute(
      `SELECT
          id,
          uuid,
          firstName,
          lastName,
          middleName,
          emailAddress,
          contactNumber,
          username,
          street,
          barangay,
          city,
          role
       FROM users
       WHERE id = ?
         AND role IN ('user', 'customer')
       LIMIT 1`,
      [userId],
    );

    return rows[0] || null;
  }

  // ==========================================
  // EDIT CUSTOMER PROFILE
  // ==========================================

  async editProfile(userId, profile) {
    const { firstName, lastName, middleName, contact, street, barangay, city } =
      profile;

    const cleanFirstName = String(firstName || "").trim();
    const cleanLastName = String(lastName || "").trim();
    const cleanContact = String(contact || "").trim();
    const cleanStreet = String(street || "").trim();
    const cleanBarangay = String(barangay || "").trim();
    const cleanCity = String(city || "").trim();

    if (!cleanFirstName || !cleanLastName) {
      throw new Error("First name and last name are required.");
    }

    const [result] = await this.db.execute(
      `UPDATE users
       SET firstName = ?,
           lastName = ?,
           middleName = ?,
           contactNumber = ?,
           street = ?,
           barangay = ?,
           city = ?
       WHERE id = ?
         AND role IN ('user', 'customer')`,
      [
        cleanFirstName,
        cleanLastName,
        String(middleName || "").trim() || null,
        cleanContact || null,
        cleanStreet || null,
        cleanBarangay || null,
        cleanCity || null,
        userId,
      ],
    );

    return result.affectedRows > 0;
  }

  // ==========================================
  // GET CUSTOMER ORDER HISTORY
  // INCLUDING CANCELLATION REQUEST STATUS
  // ==========================================

  async getOrderHistory(userId) {
    const parsedUserId = Number(userId);

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    const [rows] = await this.db.execute(
      `SELECT
          o.uuid,
          o.COnumber,
          o.totalPrice,
          o.paymentMethod,
          o.status,
          o.DateCreated,
          cr.status AS cancellationStatus,
          cr.reason AS cancellationReason,
          cr.requested_at AS cancellationRequestedAt,
          cr.reviewed_at AS cancellationReviewedAt
       FROM orders o
       LEFT JOIN cancellation_requests cr
          ON cr.order_id = o.id
       WHERE o.user_id = ?
       ORDER BY o.DateCreated DESC`,
      [parsedUserId],
    );

    return rows;
  }

  // ==========================================
  // REQUEST ORDER CANCELLATION
  // ==========================================

  async requestCancellation(userId, orderUUID, reason) {
    const parsedUserId = Number(userId);
    const cleanUUID = String(orderUUID || "").trim();
    const cleanReason = String(reason || "").trim();

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!cleanUUID) {
      throw new Error("Order UUID is required.");
    }

    if (!cleanReason) {
      throw new Error("Please provide a cancellation reason.");
    }

    if (cleanReason.length > 255) {
      throw new Error("Cancellation reason must not exceed 255 characters.");
    }

    const connection = await this.db.getConnection();

    try {
      await connection.beginTransaction();

      // Lock the customer's order while processing the request.
      const [orderRows] = await connection.execute(
        `SELECT id, uuid, status
         FROM orders
         WHERE uuid = ?
           AND user_id = ?
         LIMIT 1
         FOR UPDATE`,
        [cleanUUID, parsedUserId],
      );

      if (orderRows.length === 0) {
        throw new Error("Order not found.");
      }

      const order = orderRows[0];
      const orderStatus = String(order.status || "")
        .toLowerCase()
        .trim();

      // Only these order statuses can be cancelled.
      const allowedStatuses = ["pending", "processing"];

      if (!allowedStatuses.includes(orderStatus)) {
        throw new Error(
          "This order can no longer be cancelled because it is already being fulfilled or completed.",
        );
      }

      // Prevent duplicate cancellation requests.
      const [existingRequests] = await connection.execute(
        `SELECT id, status
         FROM cancellation_requests
         WHERE order_id = ?
         LIMIT 1`,
        [order.id],
      );

      if (existingRequests.length > 0) {
        const requestStatus = existingRequests[0].status;

        if (requestStatus === "pending") {
          throw new Error(
            "You already have a pending cancellation request for this order.",
          );
        }

        if (requestStatus === "approved") {
          throw new Error("This order has already been cancelled.");
        }

        if (requestStatus === "rejected") {
          throw new Error(
            "Your cancellation request was already rejected. You cannot submit another request for this order.",
          );
        }

        throw new Error("A cancellation request already exists.");
      }

      // Save the request without changing the order status.
      const [result] = await connection.execute(
        `INSERT INTO cancellation_requests (
            order_id,
            customer_id,
            reason,
            status,
            requested_at
         )
         VALUES (?, ?, ?, 'pending', NOW())`,
        [order.id, parsedUserId, cleanReason],
      );

      await connection.commit();

      return {
        id: result.insertId,
        orderUUID: order.uuid,
        orderStatus: order.status,
        cancellationStatus: "pending",
        reason: cleanReason,
        message: "Cancellation request submitted successfully.",
      };
    } catch (error) {
      await connection.rollback();

      // Handle the unique constraint as a duplicate request.
      if (error.code === "ER_DUP_ENTRY") {
        throw new Error(
          "A cancellation request already exists for this order.",
        );
      }

      throw error;
    } finally {
      connection.release();
    }
  }

  // ==========================================
  // GET SPECIFIC CUSTOMER RECEIPT
  // ==========================================

  async getOrderReceipt(userId, orderUUID) {
    const parsedUserId = Number(userId);
    const cleanUUID = String(orderUUID || "").trim();

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!cleanUUID) {
      throw new Error("Order UUID is required.");
    }

    const [orderRows] = await this.db.execute(
      `SELECT
          o.id,
          o.uuid,
          o.COnumber,
          o.user_id,
          o.totalPrice,
          o.address,
          o.contactNumber,
          o.paymentMethod,
          o.status,
          o.DateCreated,
          o.subtotal,
          o.tax,
          o.delivery_fee,
          o.delivery_distance_km,
          u.firstName,
          u.lastName,
          u.emailAddress,
          u.street,
          u.barangay,
          u.city
       FROM orders o
       INNER JOIN users u
          ON u.id = o.user_id
       WHERE o.uuid = ?
         AND o.user_id = ?
       LIMIT 1`,
      [cleanUUID, parsedUserId],
    );

    if (orderRows.length === 0) {
      return null;
    }

    const order = orderRows[0];

    const [itemRows] = await this.db.execute(
      `SELECT
          oi.product_id,
          oi.quantity,
          oi.price,
          p.productName,
          p.img
       FROM orderitems oi
       LEFT JOIN product_item p
          ON p.id = oi.product_id
       WHERE oi.order_id = ?
       ORDER BY oi.id ASC`,
      [order.id],
    );

    return {
      order: {
        uuid: order.uuid,
        orderNumber: order.COnumber,
        orderDate: order.DateCreated,

        fullName: `${order.firstName} ${order.lastName}`.trim(),

        phone: order.contactNumber || "",
        email: order.emailAddress || "",
        paymentMethod: order.paymentMethod || "N/A",

        address:
          order.address ||
          [order.street, order.barangay, order.city].filter(Boolean).join(", "),

        subtotal: Number(order.subtotal || 0),
        tax: Number(order.tax || 0),
        deliveryFee: Number(order.delivery_fee || 0),
        deliveryDistance: Number(order.delivery_distance_km || 0),

        total: Number(order.totalPrice || 0),
        status: order.status || "pending",
      },

      items: itemRows.map((item) => ({
        productName: item.productName || "Product unavailable",
        quantity: Number(item.quantity || 0),
        price: Number(item.price || 0),
        image: item.img || "",
      })),
    };
  }
}

module.exports = Customer;
