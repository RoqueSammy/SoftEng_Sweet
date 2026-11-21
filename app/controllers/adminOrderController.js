class AdminOrderController {
  constructor(db) {
    this.db = db;
  }

  async logAdminActivity(req, actionType, description) {
    try {
      const adminId = Number(
        req.session?.user_id || req.session?.authUser?.user_id || req.session?.authUser?.id,
      );
      await this.db.execute(
        "INSERT INTO admin_changelog (admin_id, action_type, description) VALUES (?, ?, ?)",
        [adminId || null, actionType, description],
      );
    } catch (error) {
      console.error("Order activity log error:", error);
    }
  }

  // ==========================================
  // GET CANCELLATION REQUESTS
  // ==========================================

  getCancellationRequests = async (req, res) => {
    try {
      const [rows] = await this.db.execute(
        `SELECT
            cr.id AS requestId,
            cr.order_id AS orderId,
            cr.customer_id AS customerId,
            cr.reason,
            cr.status AS cancellationStatus,
            cr.requested_at AS requestedAt,
            cr.reviewed_at AS reviewedAt,
            cr.reviewed_by AS reviewedBy,
            o.uuid AS orderUUID,
            o.COnumber,
            o.totalPrice,
            o.paymentMethod,
            o.status AS orderStatus,
            o.DateCreated,
            u.firstName,
            u.lastName,
            u.emailAddress
         FROM cancellation_requests cr
         INNER JOIN orders o ON o.id = cr.order_id
         INNER JOIN users u ON u.id = cr.customer_id
         ORDER BY
            CASE WHEN cr.status = 'pending' THEN 0 ELSE 1 END,
            cr.requested_at DESC`,
      );

      return res.status(200).json({
        success: true,
        requests: rows,
      });
    } catch (error) {
      console.error("Get cancellation requests error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to retrieve cancellation requests.",
      });
    }
  };

  // ==========================================
  // APPROVE OR REJECT CANCELLATION REQUEST
  // ==========================================

  reviewCancellationRequest = async (req, res) => {
    let connection;

    try {
      const requestId = Number(req.params.id);

      const action = String(req.body.action || "")
        .trim()
        .toLowerCase();

      const adminId = Number(
        req.session?.user_id ||
          req.session?.authUser?.user_id ||
          req.session?.authUser?.id,
      );

      if (!Number.isSafeInteger(requestId) || requestId < 1) {
        return res.status(400).json({
          success: false,
          message: "Invalid cancellation request ID.",
        });
      }

      if (!["approve", "reject"].includes(action)) {
        return res.status(400).json({
          success: false,
          message: "Action must be approve or reject.",
        });
      }

      if (!Number.isSafeInteger(adminId) || adminId < 1) {
        return res.status(401).json({
          success: false,
          message: "Unable to identify the logged-in administrator.",
        });
      }

      connection = await this.db.getConnection();

      await connection.beginTransaction();

      // Lock cancellation request.
      const [requestRows] = await connection.execute(
        `SELECT *
         FROM cancellation_requests
         WHERE id = ?
         LIMIT 1
         FOR UPDATE`,
        [requestId],
      );

      if (!requestRows.length) {
        const error = new Error("Cancellation request not found.");
        error.statusCode = 404;
        throw error;
      }

      const request = requestRows[0];

      if (request.status !== "pending") {
        const error = new Error(
          "This cancellation request has already been reviewed.",
        );
        error.statusCode = 409;
        throw error;
      }

      // Lock associated order.
      const [orderRows] = await connection.execute(
        `SELECT id, COnumber, status
         FROM orders
         WHERE id = ?
         LIMIT 1
         FOR UPDATE`,
        [request.order_id],
      );

      if (!orderRows.length) {
        const error = new Error("Associated order not found.");
        error.statusCode = 404;
        throw error;
      }

      const order = orderRows[0];

      if (action === "approve") {
        // Cancellation is only allowed for Pending and Processing.
        if (
          !["pending", "processing"].includes(
            String(order.status).toLowerCase(),
          )
        ) {
          const error = new Error(
            "This order can no longer be cancelled because its status has changed.",
          );
          error.statusCode = 409;
          throw error;
        }

        // Get original ordered quantities.
        const [orderItems] = await connection.execute(
          `SELECT product_id, SUM(quantity) AS totalQuantity
           FROM orderitems
           WHERE order_id = ?
           GROUP BY product_id
           ORDER BY product_id`,
          [order.id],
        );

        if (!orderItems.length) {
          const error = new Error(
            "Cannot approve cancellation because no order items were found.",
          );
          error.statusCode = 409;
          throw error;
        }

        const productIds = orderItems.map((item) => Number(item.product_id));

        if (productIds.some((id) => !Number.isSafeInteger(id) || id < 1)) {
          throw new Error("Invalid product information in order.");
        }

        // Lock affected products.
        const placeholders = productIds.map(() => "?").join(",");

        const [products] = await connection.execute(
          `SELECT id
           FROM product_item
           WHERE id IN (${placeholders})
           ORDER BY id
           FOR UPDATE`,
          productIds,
        );

        if (products.length !== productIds.length) {
          const error = new Error(
            "One or more ordered products no longer exist. Inventory was not restored.",
          );
          error.statusCode = 409;
          throw error;
        }

        // Restore inventory.
        for (const item of orderItems) {
          const quantity = Number(item.totalQuantity);

          if (!Number.isSafeInteger(quantity) || quantity < 1) {
            throw new Error("Invalid order quantity.");
          }

          await connection.execute(
            `UPDATE product_item
             SET stock = stock + ?
             WHERE id = ?`,
            [quantity, item.product_id],
          );
        }

        // Mark order cancelled.
        await connection.execute(
          `UPDATE orders
           SET status = 'cancelled'
           WHERE id = ?`,
          [order.id],
        );

        // Mark cancellation request approved.
        const [updateResult] = await connection.execute(
          `UPDATE cancellation_requests
           SET status = 'approved',
               reviewed_at = NOW(),
               reviewed_by = ?
           WHERE id = ?
             AND status = 'pending'`,
          [adminId, requestId],
        );

        if (updateResult.affectedRows !== 1) {
          const error = new Error(
            "Cancellation request was already processed.",
          );
          error.statusCode = 409;
          throw error;
        }
      } else {
        // Rejection does not change order or inventory.
        const [updateResult] = await connection.execute(
          `UPDATE cancellation_requests
           SET status = 'rejected',
               reviewed_at = NOW(),
               reviewed_by = ?
           WHERE id = ?
             AND status = 'pending'`,
          [adminId, requestId],
        );

        if (updateResult.affectedRows !== 1) {
          const error = new Error(
            "Cancellation request was already processed.",
          );
          error.statusCode = 409;
          throw error;
        }
      }

      await connection.commit();

      await this.logAdminActivity(
        req,
        "ORDER_CANCELLATION",
        `${action === "approve" ? "Approved" : "Rejected"} cancellation request for order ${order.COnumber || order.id} (request ID ${requestId}).`,
      );

      return res.status(200).json({
        success: true,
        message:
          action === "approve"
            ? "Cancellation approved. Order cancelled and inventory restored."
            : "Cancellation request rejected. Order status and inventory remain unchanged.",
        action,
      });
    } catch (error) {
      if (connection) {
        try {
          await connection.rollback();
        } catch (rollbackError) {
          console.error("Cancellation rollback error:", rollbackError);
        }
      }

      console.error("Review cancellation error:", error);

      return res.status(error.statusCode || 500).json({
        success: false,
        message: error.message || "Unable to review cancellation request.",
      });
    } finally {
      if (connection) {
        connection.release();
      }
    }
  };

  // ==========================================
  // UPDATE ORDER STATUS
  // ==========================================

  updateOrderStatus = async (req, res) => {
    let connection;

    try {
      const orderId = Number(req.body.order_id);

      const newStatus = String(req.body.status || "")
        .trim()
        .toLowerCase();

      if (!Number.isSafeInteger(orderId) || orderId < 1) {
        return res.status(400).json({
          success: false,
          message: "Invalid order ID.",
        });
      }

      // Only allow the next step in the order workflow.
      const allowedTransitions = {
        pending: "processing",
        processing: "ready_for_delivery",
        ready_for_delivery: "out_for_delivery",
        out_for_delivery: "completed",
      };

      if (!Object.values(allowedTransitions).includes(newStatus)) {
        return res.status(400).json({
          success: false,
          message: "Invalid order status.",
        });
      }

      connection = await this.db.getConnection();

      await connection.beginTransaction();

      // Lock order.
      const [orders] = await connection.execute(
        `SELECT id, status
         FROM orders
         WHERE id = ?
         LIMIT 1
         FOR UPDATE`,
        [orderId],
      );

      if (!orders.length) {
        const error = new Error("Order not found.");
        error.statusCode = 404;
        throw error;
      }

      const currentStatus = String(orders[0].status).toLowerCase();

      // Check transition sequence.
      if (allowedTransitions[currentStatus] !== newStatus) {
        const error = new Error(
          `Invalid transition from ${currentStatus} to ${newStatus}.`,
        );
        error.statusCode = 409;
        throw error;
      }

      // Prevent moving to Ready for Delivery when
      // a cancellation request is still pending.
      if (newStatus === "ready_for_delivery") {
        const [requests] = await connection.execute(
          `SELECT id
           FROM cancellation_requests
           WHERE order_id = ?
             AND status = 'pending'
           LIMIT 1`,
          [orderId],
        );

        if (requests.length > 0) {
          const error = new Error(
            "This order has a pending cancellation request. Review it before preparing the order for delivery.",
          );
          error.statusCode = 409;
          throw error;
        }
      }

      await connection.execute(
        `UPDATE orders
         SET status = ?
         WHERE id = ?`,
        [newStatus, orderId],
      );

      await connection.commit();

      await this.logAdminActivity(
        req,
        "ORDER_UPDATE",
        `Updated order ID ${orderId} from ${currentStatus.replace(/_/g, " ")} to ${newStatus.replace(/_/g, " ")}.`,
      );

      return res.status(200).json({
        success: true,
        message: `Order status updated to ${newStatus.replace(/_/g, " ")}.`,
        status: newStatus,
      });
    } catch (error) {
      if (connection) {
        try {
          await connection.rollback();
        } catch (rollbackError) {
          console.error("Order status rollback error:", rollbackError);
        }
      }

      console.error("Update order status error:", error);

      return res.status(error.statusCode || 500).json({
        success: false,
        message: error.message || "Unable to update order status.",
      });
    } finally {
      if (connection) {
        connection.release();
      }
    }
  };
}

module.exports = AdminOrderController;
