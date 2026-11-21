class Product {
  constructor(db) {
    this.db = db;
  }

  // ==========================================
  // GET ALL PRODUCTS
  // ==========================================

  async getAllProducts() {
    const [rows] = await this.db.execute(
      `SELECT id, uuid, productName, productDescription,
              price, img, stock
       FROM product_item
       ORDER BY id DESC`,
    );

    return rows;
  }

  // ==========================================
  // GET PRODUCT BY ID
  // ==========================================

  async getProductById(id) {
    const productId = Number(id);

    if (!Number.isSafeInteger(productId) || productId < 1) {
      throw new Error("Invalid product ID.");
    }

    const [rows] = await this.db.execute(
      `SELECT id, uuid, productName, productDescription,
              price, img, stock
       FROM product_item
       WHERE id = ?
       LIMIT 1`,
      [productId],
    );

    return rows[0] || null;
  }

  // ==========================================
  // GET PRODUCT BY UUID
  // ==========================================

  async getProductByUUID(uuid) {
    const productUUID = String(uuid || "").trim();

    if (!productUUID) {
      throw new Error("Product UUID is required.");
    }

    const [rows] = await this.db.execute(
      `SELECT id, uuid, productName, productDescription,
              price, img, stock
       FROM product_item
       WHERE uuid = ?
       LIMIT 1`,
      [productUUID],
    );

    return rows[0] || null;
  }

  async createProduct({ productName, productDescription, price, img, stock }) {
    const name = String(productName || "").trim();
    const description = String(productDescription || "").trim();
    const amount = Number(price);
    const quantity = Number(stock);

    if (!name || name.length > 255) throw new Error("Product name is required and must be 255 characters or fewer.");
    if (!description || description.length > 255) throw new Error("Description is required and must be 255 characters or fewer.");
    if (!Number.isFinite(amount) || amount <= 0 || amount > 99999999.99) throw new Error("Enter a valid product price.");
    if (!Number.isSafeInteger(quantity) || quantity < 0 || quantity > 2147483647) throw new Error("Stock must be a non-negative whole number.");

    const uuid = require("crypto").randomUUID();
    const [result] = await this.db.execute(
      `INSERT INTO product_item (uuid, productName, productDescription, price, img, stock)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [uuid, name, description, amount.toFixed(2), img || null, quantity],
    );

    return { id: result.insertId, uuid, productName: name, stock: quantity };
  }

  // ==========================================
  // UPDATE PRODUCT STOCK
  // Sets stock to an exact quantity
  // ==========================================

  async updateStock(id, newStock) {
    const productId = Number(id);
    const quantity = Number(newStock);

    if (!Number.isSafeInteger(productId) || productId < 1) {
      throw new Error("Invalid product ID.");
    }

    if (!Number.isSafeInteger(quantity) || quantity < 0) {
      throw new Error("Stock must be a non-negative whole number.");
    }

    const [result] = await this.db.execute(
      `UPDATE product_item
       SET stock = ?
       WHERE id = ?`,
      [quantity, productId],
    );

    if (result.affectedRows === 0) {
      const product = await this.getProductById(productId);

      if (!product) {
        throw new Error("Product not found.");
      }
    }

    return {
      productId,
      stock: quantity,
    };
  }

  // ==========================================
  // ADD STOCK
  // Adds quantity to existing stock
  // ==========================================

  async addStock(id, quantityToAdd) {
    const productId = Number(id);
    const quantity = Number(quantityToAdd);

    if (!Number.isSafeInteger(productId) || productId < 1) {
      throw new Error("Invalid product ID.");
    }

    if (!Number.isSafeInteger(quantity) || quantity < 1) {
      throw new Error("Please enter a valid stock quantity.");
    }

    const connection = await this.db.getConnection();

    try {
      await connection.beginTransaction();

      const [products] = await connection.execute(
        `SELECT id, productName, stock
         FROM product_item
         WHERE id = ?
         FOR UPDATE`,
        [productId],
      );

      if (products.length === 0) {
        throw new Error("Product not found.");
      }

      const currentStock = Number(products[0].stock);
      const newStock = currentStock + quantity;

      if (
        !Number.isSafeInteger(currentStock) ||
        !Number.isSafeInteger(newStock) ||
        newStock > 2147483647
      ) {
        throw new Error("The resulting stock quantity is too large.");
      }

      await connection.execute(
        `UPDATE product_item
         SET stock = ?
         WHERE id = ?`,
        [newStock, productId],
      );

      await connection.commit();

      return {
        productId,
        productName: products[0].productName,
        previousStock: currentStock,
        addedStock: quantity,
        stock: newStock,
      };
    } catch (error) {
      await connection.rollback();
      throw error;
    } finally {
      connection.release();
    }
  }

  async removeStock(id, quantityToRemove) {
    const productId = Number(id);
    const quantity = Number(quantityToRemove);

    if (!Number.isSafeInteger(productId) || productId < 1) {
      throw new Error("Invalid product ID.");
    }

    if (!Number.isSafeInteger(quantity) || quantity < 1) {
      throw new Error("Please enter a valid removal quantity.");
    }

    const connection = await this.db.getConnection();

    try {
      await connection.beginTransaction();

      const [products] = await connection.execute(
        `SELECT id, productName, stock
         FROM product_item
         WHERE id = ?
         FOR UPDATE`,
        [productId],
      );

      if (products.length === 0) {
        throw new Error("Product not found.");
      }

      const currentStock = Number(products[0].stock);

      if (!Number.isSafeInteger(currentStock) || currentStock < 0) {
        throw new Error("Current stock value is invalid.");
      }

      if (quantity > currentStock) {
        throw new Error(
          `Insufficient stock. Only ${currentStock} units are available.`,
        );
      }

      const newStock = currentStock - quantity;

      await connection.execute(
        `UPDATE product_item
         SET stock = ?
         WHERE id = ?`,
        [newStock, productId],
      );

      await connection.commit();

      return {
        productId,
        productName: products[0].productName,
        previousStock: currentStock,
        removedStock: quantity,
        stock: newStock,
      };
    } catch (error) {
      await connection.rollback();
      throw error;
    } finally {
      connection.release();
    }
  }

  async getLowStockProducts(threshold = 5) {
    const limit = Number(threshold);

    if (!Number.isSafeInteger(limit) || limit < 0) {
      throw new Error("Invalid stock threshold.");
    }

    const [rows] = await this.db.execute(
      `SELECT id, uuid, productName, price, img, stock
       FROM product_item
       WHERE stock <= ?
       ORDER BY stock ASC`,
      [limit],
    );

    return rows;
  }

  async checkStockAvailability(productUUID, requestedQuantity) {
    const cleanUUID = String(productUUID || "").trim();
    const quantity = Number(requestedQuantity);

    if (!cleanUUID) {
      throw new Error("Product UUID is required.");
    }

    if (!Number.isSafeInteger(quantity) || quantity < 1) {
      throw new Error("Requested quantity must be at least 1.");
    }

    const [rows] = await this.db.execute(
      `SELECT id, productName, stock
       FROM product_item
       WHERE uuid = ?
       LIMIT 1`,
      [cleanUUID],
    );

    if (rows.length === 0) {
      return {
        available: false,
        message: "Product not found.",
      };
    }

    const product = rows[0];
    const available = Number(product.stock) >= quantity;

    return {
      available,
      productName: product.productName,
      currentStock: Number(product.stock),
      requestedQuantity: quantity,
      message: available ? "Stock is sufficient." : "Insufficient stock.",
    };
  }
}

module.exports = Product;
