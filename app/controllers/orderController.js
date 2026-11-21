class OrderController {
  constructor(db) {
    this.db = db;
  }

  updateStatus = async (req, res) => {
    try {
      if (!req.session?.user_id || req.session.userRole !== "admin") {
        return res.status(403).json({
          success: false,
          message: "Admin access required.",
        });
      }

      const orderId = Number(req.body.order_id);
      const status = String(req.body.status || "").toLowerCase();

      const allowedStatuses = ["pending", "completed", "cancelled"];

      if (!Number.isSafeInteger(orderId) || orderId < 1) {
        return res.status(400).json({
          success: false,
          message: "Invalid order ID.",
        });
      }

      if (!allowedStatuses.includes(status)) {
        return res.status(400).json({
          success: false,
          message: "Invalid order status.",
        });
      }

      const [result] = await this.db.execute(
        `UPDATE orders
                 SET status = ?
                 WHERE id = ?`,
        [status, orderId],
      );

      if (result.affectedRows === 0) {
        const [rows] = await this.db.execute(
          "SELECT id FROM orders WHERE id = ?",
          [orderId],
        );

        if (!rows.length) {
          return res.status(404).json({
            success: false,
            message: "Order not found.",
          });
        }
      }

      return res.json({
        success: true,
        message: "Order status updated successfully.",
        status,
      });
    } catch (error) {
      console.error("Update order status error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to update order status.",
      });
    }
  };
}

module.exports = OrderController;
