const User = require("./User");
const Cart = require("./cart");

class Customer extends User {
  constructor(db) {
    super(db);

    // Customer has a Cart
    this.cart = new Cart(db);
  }

  // ==========================================
  // ADD PRODUCT TO CART
  // ==========================================

  async addToCart(userId, productUUID, quantity) {
    const parsedUserId = Number(userId);
    const parsedQuantity = Number(quantity);
    const cleanUUID = String(productUUID || "").trim();

    if (!Number.isInteger(parsedUserId) || parsedUserId < 1) {
      throw new Error("Invalid customer ID.");
    }

    if (!cleanUUID) {
      throw new Error("Product UUID is required.");
    }

    if (!Number.isInteger(parsedQuantity) || parsedQuantity < 1) {
      throw new Error("Invalid product quantity.");
    }

    return await this.cart.addProduct(parsedUserId, cleanUUID, parsedQuantity);
  }

  // ==========================================
  // VIEW CUSTOMER PROFILE
  // ==========================================

  async manageProfile(userId) {
    const [rows] = await this.db.execute(
      `SELECT id, uuid, firstName, lastName, middleName,
              emailAddress, contactNumber, username,
              street, barangay, city, role
       FROM users
       WHERE id = ?
         AND role IN ('user', 'customer')
       LIMIT 1`,
      [userId],
    );

    return rows[0] || null;
  }

  // ==========================================
  // UPDATE CUSTOMER PROFILE
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

    // Contact and address are allowed to be empty
    // until profile completion.
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
}

module.exports = Customer;