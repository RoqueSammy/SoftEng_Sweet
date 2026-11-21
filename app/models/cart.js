const Product = require("./product");

class Cart {
  constructor(db) {
    this.db = db;
    this.product = new Product(db);
    this.VAT_RATE = 0.12;
  }

  // ==========================================
  // ADD PRODUCT TO CART
  // ==========================================

  async addProduct(userId, productUUID, quantity) {
    const parsedUserId = Number(userId);
    const cleanUUID = String(productUUID || "").trim();
    const parsedQuantity = Number(quantity);

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!cleanUUID) {
      throw new Error("Product UUID is required.");
    }

    if (!Number.isSafeInteger(parsedQuantity) || parsedQuantity < 1) {
      throw new Error("Invalid product quantity.");
    }

    const connection = await this.db.getConnection();

    try {
      await connection.beginTransaction();

      const [customers] = await connection.execute(
        `SELECT id
         FROM users
         WHERE id = ?
         AND role IN ('user', 'customer')
         LIMIT 1`,
        [parsedUserId],
      );

      if (customers.length === 0) {
        throw new Error("Customer not found.");
      }

      // Lock product while checking available stock.
      const [products] = await connection.execute(
        `SELECT id, productName, stock
         FROM product_item
         WHERE uuid = ?
         LIMIT 1
         FOR UPDATE`,
        [cleanUUID],
      );

      if (products.length === 0) {
        throw new Error("Product not found.");
      }

      const product = products[0];
      const availableStock = Number(product.stock);

      if (availableStock <= 0) {
        throw new Error(`${product.productName} is currently out of stock.`);
      }

      const [existingItems] = await connection.execute(
        `SELECT quantity
         FROM cart
         WHERE user_id = ?
         AND product_id = ?
         LIMIT 1
         FOR UPDATE`,
        [parsedUserId, product.id],
      );

      const existingQuantity = existingItems.length
        ? Number(existingItems[0].quantity)
        : 0;

      const requestedTotal = existingQuantity + parsedQuantity;

      if (requestedTotal > availableStock) {
        throw new Error(
          `Only ${availableStock} unit(s) of ${product.productName} are available. ` +
            `You already have ${existingQuantity} in your cart.`,
        );
      }

      await connection.execute(
        `INSERT INTO cart (
          user_id,
          product_id,
          quantity
        )
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
          quantity = quantity + VALUES(quantity)`,
        [parsedUserId, product.id, parsedQuantity],
      );

      await connection.commit();

      const cartCount = await this.getCartCount(parsedUserId);

      return {
        success: true,
        message: "Product added to cart!",
        productID: product.id,
        quantity: parsedQuantity,
        cartCount,
      };
    } catch (error) {
      await connection.rollback();
      throw error;
    } finally {
      connection.release();
    }
  }

  // ==========================================
  // GET CUSTOMER CART
  // ==========================================

  async getCart(userId) {
    const parsedUserId = Number(userId);

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
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
        product_item.img,
        product_item.stock,
        CASE
          WHEN product_item.stock <= 0 THEN 'out_of_stock'
          WHEN cart.quantity > product_item.stock THEN 'insufficient_stock'
          ELSE 'available'
        END AS stockStatus
      FROM cart
      INNER JOIN product_item
        ON cart.product_id = product_item.id
      WHERE cart.user_id = ?
      ORDER BY cart.id DESC`,
      [parsedUserId],
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

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!Number.isSafeInteger(parsedCartID) || parsedCartID < 1) {
      throw new Error("Invalid cart item ID.");
    }

    if (!Number.isSafeInteger(parsedQuantity) || parsedQuantity < 1) {
      throw new Error("Invalid product quantity.");
    }

    const connection = await this.db.getConnection();

    try {
      await connection.beginTransaction();

      // Find the customer's cart item.
      const [cartItems] = await connection.execute(
        `SELECT product_id
         FROM cart
         WHERE id = ?
         AND user_id = ?
         LIMIT 1`,
        [parsedCartID, parsedUserId],
      );

      if (cartItems.length === 0) {
        throw new Error("Cart item not found.");
      }

      // Lock the product before checking its stock.
      const [products] = await connection.execute(
        `SELECT productName, stock
         FROM product_item
         WHERE id = ?
         LIMIT 1
         FOR UPDATE`,
        [cartItems[0].product_id],
      );

      if (products.length === 0) {
        throw new Error("Product not found.");
      }

      const product = products[0];
      const availableStock = Number(product.stock);

      if (availableStock <= 0) {
        throw new Error(`${product.productName} is currently out of stock.`);
      }

      if (parsedQuantity > availableStock) {
        throw new Error(
          `Only ${availableStock} unit(s) of ${product.productName} are available.`,
        );
      }

      const [result] = await connection.execute(
        `UPDATE cart
         SET quantity = ?
         WHERE id = ?
         AND user_id = ?`,
        [parsedQuantity, parsedCartID, parsedUserId],
      );

      if (result.affectedRows === 0) {
        throw new Error("Unable to update cart quantity.");
      }

      await connection.commit();

      return true;
    } catch (error) {
      await connection.rollback();
      throw error;
    } finally {
      connection.release();
    }
  }

  // ==========================================
  // REMOVE PRODUCT FROM CART
  // ==========================================

  async removeProduct(userId, cartID) {
    const parsedUserId = Number(userId);
    const parsedCartID = Number(cartID);

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!Number.isSafeInteger(parsedCartID) || parsedCartID < 1) {
      throw new Error("Invalid cart item ID.");
    }

    const [result] = await this.db.execute(
      `DELETE FROM cart
       WHERE id = ?
       AND user_id = ?`,
      [parsedCartID, parsedUserId],
    );

    return result.affectedRows > 0;
  }

  // ==========================================
  // CLEAR CUSTOMER CART
  // ==========================================

  async clearCart(userId) {
    const parsedUserId = Number(userId);

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    const [result] = await this.db.execute(
      `DELETE FROM cart
       WHERE user_id = ?`,
      [parsedUserId],
    );

    return result.affectedRows;
  }

  // ==========================================
  // GET CART ITEM COUNT
  // ==========================================

  async getCartCount(userId) {
    const parsedUserId = Number(userId);

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    const [rows] = await this.db.execute(
      `SELECT COALESCE(SUM(quantity), 0) AS totalItems
       FROM cart
       WHERE user_id = ?`,
      [parsedUserId],
    );

    return Number(rows[0].totalItems);
  }

  // ==========================================
  // SUBTOTAL
  // ==========================================

  async subtotal(userId, cartIDs = null) {
    const parsedUserId = Number(userId);

    if (!Number.isSafeInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (cartIDs !== null && !Array.isArray(cartIDs)) {
      throw new Error("Cart IDs must be an array.");
    }

    if (Array.isArray(cartIDs) && cartIDs.length === 0) {
      return 0;
    }

    let sql = `
      SELECT COALESCE(
        SUM(cart.quantity * product_item.price),
        0
      ) AS subtotal
      FROM cart
      INNER JOIN product_item
        ON cart.product_id = product_item.id
      WHERE cart.user_id = ?
    `;

    const params = [parsedUserId];

    if (Array.isArray(cartIDs)) {
      const parsedIDs = cartIDs.map(Number);

      if (parsedIDs.some((id) => !Number.isSafeInteger(id) || id < 1)) {
        throw new Error("Invalid cart item ID.");
      }

      const placeholders = parsedIDs.map(() => "?").join(", ");

      sql += ` AND cart.id IN (${placeholders})`;
      params.push(...parsedIDs);
    }

    const [rows] = await this.db.execute(sql, params);

    return Number(rows[0].subtotal);
  }

  // ==========================================
  // TAX
  // ==========================================

  async tax(userId, cartIDs = null) {
    const subtotalAmount = await this.subtotal(userId, cartIDs);

    return Math.round(subtotalAmount * this.VAT_RATE * 100) / 100;
  }

  // ==========================================
  // TOTAL
  // ==========================================

  async Total(userId, cartIDs = null) {
    const subtotalAmount = await this.subtotal(userId, cartIDs);

    const taxAmount = Math.round(subtotalAmount * this.VAT_RATE * 100) / 100;

    return Math.round((subtotalAmount + taxAmount) * 100) / 100;
  }

  // ==========================================
  // GET CART TOTAL
  // ==========================================

  async getCartTotal(userId) {
    return await this.subtotal(userId);
  }
}

module.exports = Cart;
