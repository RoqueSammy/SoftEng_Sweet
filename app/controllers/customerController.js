const Customer = require("../models/Customer");

class CustomerController {
  constructor(db) {
    this.customer = new Customer(db);
  }

  // ==========================================
  // GET AUTHENTICATED USER ID
  // ==========================================

  getUserId(req) {
    return (
      req.session?.authUser?.user_id ||
      req.session?.authUser?.id ||
      req.session?.user_id ||
      null
    );
  }

  // ==========================================
  // CHECK CUSTOMER ROLE
  // ==========================================

  isCustomer(req) {
    const role = req.session?.authUser?.role || req.session?.userRole;

    return role === "customer" || role === "user";
  }

  // ==========================================
  // ADD PRODUCT TO CART
  // ==========================================

  addToCart = async (req, res) => {
    try {
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Please log in to add products to your cart.",
          redirect: "/SoftEcoms/public/login.php",
        });
      }

      if (!this.isCustomer(req)) {
        return res.status(403).json({
          success: false,
          message: "Only customers can add products to their cart.",
        });
      }

      const { product_uuid, quantity } = req.body;

      if (!product_uuid || !String(product_uuid).trim()) {
        return res.status(400).json({
          success: false,
          message: "Product UUID is required.",
        });
      }

      const parsedQuantity = Number(quantity);

      if (!Number.isSafeInteger(parsedQuantity) || parsedQuantity < 1) {
        return res.status(400).json({
          success: false,
          message: "Please provide a valid quantity.",
        });
      }

      await this.customer.addToCart(userId, product_uuid, parsedQuantity);

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
  // GET CART
  // ==========================================

  getCart = async (req, res) => {
    try {
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

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
  // GET CART COUNT
  // ==========================================

  getCartCount = async (req, res) => {
    try {
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          cartCount: 0,
          message: "Please log in first.",
        });
      }

      const cartCount = await this.customer.cart.getCartCount(userId);

      return res.status(200).json({
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
  // UPDATE CART QUANTITY
  // ==========================================

  updateQuantity = async (req, res) => {
    try {
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      const { cartID, quantity } = req.body;

      const parsedCartID = Number(cartID);
      const parsedQuantity = Number(quantity);

      if (
        !Number.isSafeInteger(parsedCartID) ||
        parsedCartID < 1 ||
        !Number.isSafeInteger(parsedQuantity) ||
        parsedQuantity < 1
      ) {
        return res.status(400).json({
          success: false,
          message: "Invalid cart item or quantity.",
        });
      }

      const updated = await this.customer.cart.updateQuantity(
        userId,
        parsedCartID,
        parsedQuantity,
      );

      if (!updated) {
        return res.status(404).json({
          success: false,
          message: "Cart item not found.",
        });
      }

      return res.status(200).json({
        success: true,
        message: "Cart quantity updated.",
      });
    } catch (error) {
      console.error("Update quantity error:", error);

      return res.status(400).json({
        success: false,
        message: error.message || "Unable to update cart quantity.",
      });
    }
  };

  // ==========================================
  // REMOVE PRODUCT FROM CART
  // ==========================================

  removeProduct = async (req, res) => {
    try {
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      const parsedCartID = Number(req.body.cartID);

      if (!Number.isSafeInteger(parsedCartID) || parsedCartID < 1) {
        return res.status(400).json({
          success: false,
          message: "Invalid cart item ID.",
        });
      }

      const removed = await this.customer.cart.removeProduct(
        userId,
        parsedCartID,
      );

      if (!removed) {
        return res.status(404).json({
          success: false,
          message: "Cart item not found.",
        });
      }

      const cartCount = await this.customer.cart.getCartCount(userId);

      return res.status(200).json({
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
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      await this.customer.cart.clearCart(userId);

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

  // ==========================================
  // GET CART SUMMARY
  // WITH STOCK VALIDATION
  // ==========================================

  getCartSummary = async (req, res) => {
    try {
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Please log in first.",
        });
      }

      const { cartIDs } = req.body;

      if (!Array.isArray(cartIDs) || cartIDs.length === 0) {
        return res.status(400).json({
          success: false,
          message: "Please select at least one product.",
        });
      }

      const selectedIDs = cartIDs.map(Number);

      if (selectedIDs.some((id) => !Number.isSafeInteger(id) || id < 1)) {
        return res.status(400).json({
          success: false,
          message: "Invalid selected cart items.",
        });
      }

      const cart = await this.customer.cart.getCart(userId);

      const selectedItems = cart.filter((item) =>
        selectedIDs.includes(Number(item.cartID)),
      );

      if (selectedItems.length !== new Set(selectedIDs).size) {
        return res.status(400).json({
          success: false,
          message: "One or more selected cart items could not be found.",
        });
      }

      const unavailableItems = selectedItems.filter((item) => {
        const stock = Number(item.stock);
        const quantity = Number(item.quantity);

        return (
          !Number.isFinite(stock) ||
          stock < quantity ||
          stock <= 0 ||
          item.stockStatus === "out_of_stock" ||
          item.stockStatus === "insufficient_stock"
        );
      });

      if (unavailableItems.length > 0) {
        return res.status(409).json({
          success: false,
          stockError: true,
          message:
            "Some selected products are no longer available in the requested quantity. Please update your cart.",
          unavailableItems: unavailableItems.map((item) => ({
            cartID: item.cartID,
            productName: item.productName,
            quantity: Number(item.quantity),
            stock: Number(item.stock),
            stockStatus: item.stockStatus,
          })),
        });
      }

      const subtotal = await this.customer.cart.subtotal(userId, selectedIDs);

      const tax = await this.customer.cart.tax(userId, selectedIDs);

      const total = await this.customer.cart.Total(userId, selectedIDs);

      return res.status(200).json({
        success: true,
        subtotal,
        tax,
        total,
      });
    } catch (error) {
      console.error("Cart summary error:", error);

      return res.status(400).json({
        success: false,
        message: error.message || "Unable to calculate cart summary.",
      });
    }
  };

  // ==========================================
  // GET CUSTOMER ORDER HISTORY
  // ==========================================

  getOrderHistory = async (req, res) => {
    try {
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Please log in to view your orders.",
        });
      }

      if (!this.isCustomer(req)) {
        return res.status(403).json({
          success: false,
          message: "Only customers can view their orders.",
        });
      }

      const orders = await this.customer.getOrderHistory(userId);

      return res.status(200).json({
        success: true,
        orders,
      });
    } catch (error) {
      console.error("Get order history error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to retrieve order history.",
      });
    }
  };

  // ==========================================
  // REQUEST ORDER CANCELLATION
  // ==========================================

  requestCancellation = async (req, res) => {
    try {
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Please log in to request order cancellation.",
        });
      }

      if (!this.isCustomer(req)) {
        return res.status(403).json({
          success: false,
          message: "Only customers can request order cancellation.",
        });
      }

      const orderUUID = String(req.body?.orderUUID || "").trim();
      const reason = String(req.body?.reason || "").trim();

      if (!orderUUID) {
        return res.status(400).json({
          success: false,
          message: "Order UUID is required.",
        });
      }

      if (!reason) {
        return res.status(400).json({
          success: false,
          message: "Please select or provide a cancellation reason.",
        });
      }

      if (reason.length > 255) {
        return res.status(400).json({
          success: false,
          message: "Cancellation reason must not exceed 255 characters.",
        });
      }

      const result = await this.customer.requestCancellation(
        userId,
        orderUUID,
        reason,
      );

      return res.status(201).json({
        success: true,
        message: result.message,
        cancellation: result,
      });
    } catch (error) {
      console.error("Request cancellation error:", error);

      const message = error.message || "Unable to submit cancellation request.";

      let statusCode = 400;

      if (message === "Order not found.") {
        statusCode = 404;
      } else if (
        message.includes("already") ||
        message.includes("can no longer")
      ) {
        statusCode = 409;
      }

      return res.status(statusCode).json({
        success: false,
        message,
      });
    }
  };

  // ==========================================
  // GET SPECIFIC CUSTOMER RECEIPT
  // ==========================================

  getOrderReceipt = async (req, res) => {
    try {
      const userId = this.getUserId(req);

      if (!userId) {
        return res.status(401).json({
          success: false,
          message: "Please log in to view your receipt.",
        });
      }

      if (!this.isCustomer(req)) {
        return res.status(403).json({
          success: false,
          message: "Only customers can view receipts.",
        });
      }

      const { uuid } = req.params;

      if (!uuid || !uuid.trim()) {
        return res.status(400).json({
          success: false,
          message: "Order UUID is required.",
        });
      }

      const receipt = await this.customer.getOrderReceipt(userId, uuid);

      if (!receipt) {
        return res.status(404).json({
          success: false,
          message: "Receipt not found.",
        });
      }

      if (receipt.items.length === 0) {
        return res.status(404).json({
          success: false,
          message: "No products were found for this order.",
        });
      }

      return res.status(200).json({
        success: true,
        receipt,
      });
    } catch (error) {
      console.error("Get order receipt error:", error);

      return res.status(500).json({
        success: false,
        message: "Unable to retrieve receipt.",
      });
    }
  };
}

module.exports = CustomerController;
