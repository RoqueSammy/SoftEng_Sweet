const Customer = require("../models/Customer");

class CustomerController {
  constructor(db) {
    this.customer = new Customer(db);
  }

  // ==========================================
  // ADD TO CART
  // ==========================================

  addToCart = async (req, res) => {
    try {
      if (!req.session || !req.session.authUser) {
        return res.status(401).json({
          success: false,
          message: "Please log in to add products to your cart.",
          redirect: "/SoftEcoms/public/login.php",
        });
      }

      const userId = req.session.user_id;
      const { product_uuid, quantity } = req.body;

      if (
        req.session.userRole !== "customer" &&
        req.session.userRole !== "user"
      ) {
        return res.status(403).json({
          success: false,
          message: "Only customers can add products to their cart.",
        });
      }

      await this.customer.addToCart(userId, product_uuid, Number(quantity));

      const cartCount = await this.customer.cart.getCartCount(userId);

      return res.status(200).json({
        success: true,
        message: "Product added to cart!",
        cartCount,
      });
    } catch (error) {
      console.error("Add to cart error:", error);

      return res.status(400).json({
        success: false,
        message: error.message || "Unable to add product to cart.",
      });
    }
  };

  // ==========================================
  // VIEW CART
  // ==========================================

  getCart = async (req, res) => {
    try {
      if (!req.session?.authUser) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      const userId = req.session.user_id;

      const cart = await this.customer.cart.getCart(userId);

      return res.status(200).json({
        success: true,
        cart,
      });
    } catch (error) {
      console.error("Get cart error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to retrieve cart.",
      });
    }
  };

  // ==========================================
  // CART COUNT
  // ==========================================

  getCartCount = async (req, res) => {
    try {
      if (!req.session?.authUser) {
        return res.status(401).json({
          success: false,
          cartCount: 0,
        });
      }

      const cartCount = await this.customer.cart.getCartCount(
        req.session.user_id,
      );

      return res.json({
        success: true,
        cartCount,
      });
    } catch (error) {
      console.error("Cart count error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to retrieve cart count.",
      });
    }
  };

  // ==========================================
  // UPDATE QUANTITY
  // ==========================================

  updateQuantity = async (req, res) => {
    try {
      if (!req.session?.authUser) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      const { cartID, quantity } = req.body;

      const updated = await this.customer.cart.updateQuantity(
        req.session.user_id,
        Number(cartID),
        Number(quantity),
      );

      if (!updated) {
        return res.status(404).json({
          success: false,
          message: "Cart item not found.",
        });
      }

      return res.json({
        success: true,
        message: "Cart quantity updated.",
      });
    } catch (error) {
      console.error("Update quantity error:", error);

      return res.status(400).json({
        success: false,
        message: error.message,
      });
    }
  };

  // ==========================================
  // REMOVE CART ITEM
  // ==========================================

  removeProduct = async (req, res) => {
    try {
      if (!req.session?.authUser) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      const { cartID } = req.body;

      const removed = await this.customer.cart.removeProduct(
        req.session.user_id,
        Number(cartID),
      );

      if (!removed) {
        return res.status(404).json({
          success: false,
          message: "Cart item not found.",
        });
      }

      const cartCount = await this.customer.cart.getCartCount(
        req.session.user_id,
      );

      return res.json({
        success: true,
        message: "Product removed from cart.",
        cartCount,
      });
    } catch (error) {
      console.error("Remove cart error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to remove product.",
      });
    }
  };

  // ==========================================
  // CLEAR CART
  // ==========================================

  clearCart = async (req, res) => {
    try {
      if (!req.session?.authUser) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      await this.customer.cart.clearCart(req.session.user_id);

      return res.status(200).json({
        success: true,
        message: "Cart cleared successfully.",
        cartCount: 0,
      });
    } catch (error) {
      console.error("Clear cart error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to clear cart.",
      });
    }
  };
}

module.exports = CustomerController;
