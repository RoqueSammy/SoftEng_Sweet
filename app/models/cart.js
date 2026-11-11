
class Cart {
  constructor(db) {
    this.db = db;
  }

  // ==========================================
  // ADD PRODUCT TO CART
  // ==========================================

  async addProduct(userId, productUUID, quantity) {
    const parsedUserId = Number(userId);
    const cleanUUID = String(productUUID || "").trim();
    const parsedQuantity = Number(quantity);

    if (!Number.isInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!cleanUUID) {
      throw new Error("Product UUID is required.");
    }

    if (!Number.isInteger(parsedQuantity) || parsedQuantity < 1) {
      throw new Error("Invalid product quantity.");
    }

    const [customers] = await this.db.execute(
      `SELECT id
       FROM users
       WHERE id = ?
       AND role IN ('user', 'customer')
       LIMIT 1`,
      [parsedUserId]
    );

    if (customers.length === 0) {
      throw new Error("Customer not found.");
    }

    const [products] = await this.db.execute(
      `SELECT id
       FROM product_item
       WHERE uuid = ?
       LIMIT 1`,
      [cleanUUID]
    );

    if (products.length === 0) {
      throw new Error("Product not found.");
    }

    const productID = products[0].id;

    await this.db.execute(
      `INSERT INTO cart (
        user_id,
        product_id,
        quantity
      )
      VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE
        quantity = quantity + VALUES(quantity)`,
      [parsedUserId, productID, parsedQuantity]
    );

    const cartCount = await this.getCartCount(parsedUserId);

    return {
      success: true,
      message: "Product added to cart!",
      productID,
      quantity: parsedQuantity,
      cartCount
    };
  }

  // ==========================================
  // GET CUSTOMER CART
  // ==========================================

  async getCart(userId) {
    const parsedUserId = Number(userId);

    if (!Number.isInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    const [rows] = await this.db.execute(
      `SELECT
        cart.id AS cartID,
        cart.user_id,
        cart.product_id,
        cart.quantity,
        product_item.uuid,
        product_item.productName,
        product_item.productDescription,
        product_item.price,
        product_item.img
      FROM cart
      INNER JOIN product_item
        ON cart.product_id = product_item.id
      WHERE cart.user_id = ?
      ORDER BY cart.id DESC`,
      [parsedUserId]
    );

    return rows;
  }

  // ==========================================
  // UPDATE CART QUANTITY
  // ==========================================

  async updateQuantity(userId, cartID, quantity) {
    const parsedUserId = Number(userId);
    const parsedCartID = Number(cartID);
    const parsedQuantity = Number(quantity);

    if (!Number.isInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!Number.isInteger(parsedCartID) || parsedCartID < 1) {
      throw new Error("Invalid cart item ID.");
    }

    if (!Number.isInteger(parsedQuantity) || parsedQuantity < 1) {
      throw new Error("Invalid product quantity.");
    }

    const [result] = await this.db.execute(
      `UPDATE cart
       SET quantity = ?
       WHERE id = ?
       AND user_id = ?`,
      [parsedQuantity, parsedCartID, parsedUserId]
    );

    return result.affectedRows > 0;
  }

  // ==========================================
  // REMOVE PRODUCT FROM CART
  // ==========================================

  async removeProduct(userId, cartID) {
    const parsedUserId = Number(userId);
    const parsedCartID = Number(cartID);

    if (!Number.isInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!Number.isInteger(parsedCartID) || parsedCartID < 1) {
      throw new Error("Invalid cart item ID.");
    }

    const [result] = await this.db.execute(
      `DELETE FROM cart
       WHERE id = ?
       AND user_id = ?`,
      [parsedCartID, parsedUserId]
    );

    return result.affectedRows > 0;
  }

  // ==========================================
  // CLEAR CUSTOMER CART
  // ==========================================

  async clearCart(userId) {
    const parsedUserId = Number(userId);

    if (!Number.isInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    const [result] = await this.db.execute(
      `DELETE FROM cart
       WHERE user_id = ?`,
      [parsedUserId]
    );

    return result.affectedRows;
  }

  // ==========================================
  // GET CART ITEM COUNT
  // Total quantity of all products
  // ==========================================

  async getCartCount(userId) {
    const parsedUserId = Number(userId);

    if (!Number.isInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    const [rows] = await this.db.execute(
      `SELECT COALESCE(SUM(quantity), 0) AS totalItems
       FROM cart
       WHERE user_id = ?`,
      [parsedUserId]
    );

    return Number(rows[0].totalItems);
  }

  // ==========================================
  // GET CART TOTAL
  // ==========================================

  async getCartTotal(userId) {
    const parsedUserId = Number(userId);

    if (!Number.isInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    const [rows] = await this.db.execute(
      `SELECT
        COALESCE(
          SUM(cart.quantity * product_item.price),
          0
        ) AS totalAmount
       FROM cart
       INNER JOIN product_item
         ON cart.product_id = product_item.id
       WHERE cart.user_id = ?`,
      [parsedUserId]
    );

    return Number(rows[0].totalAmount);
  }
}

module.exports = Cart;
