
const crypto = require("crypto");

class CheckoutController {
    constructor(db) {
        this.db = db;
    }

    checkout = async (req, res) => {
        let connection;

        try {
            // 1. VERIFY CUSTOMER SESSION
            if (!req.session?.authUser || !req.session.user_id) {
                return res.status(401).json({
                    success: false,
                    message: "Please log in before checking out."
                });
            }

            const userId = Number(req.session.user_id);

            if (!Number.isInteger(userId) || userId < 1) {
                return res.status(401).json({
                    success: false,
                    message: "Invalid customer session."
                });
            }

            if (!["customer", "user"].includes(req.session.userRole)) {
                return res.status(403).json({
                    success: false,
                    message: "Only customers can place orders."
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
                    message: "Address and contact number are required."
                });
            }

            if (contactNumber.length > 20) {
                return res.status(400).json({
                    success: false,
                    message: "Contact number is too long."
                });
            }

            if (!["Cash", "GCash"].includes(paymentMethod)) {
                return res.status(400).json({
                    success: false,
                    message: "Invalid payment method."
                });
            }

            if (
                req.body.latitude === undefined ||
                req.body.longitude === undefined ||
                !Number.isFinite(latitude) ||
                !Number.isFinite(longitude) ||
                latitude < -90 || latitude > 90 ||
                longitude < -180 || longitude > 180
            ) {
                return res.status(400).json({
                    success: false,
                    message: "Please select a valid delivery location."
                });
            }

            if (!Array.isArray(selectedCartIDs) || selectedCartIDs.length === 0) {
                return res.status(400).json({
                    success: false,
                    message: "Please select at least one product."
                });
            }

            const cartIDs = selectedCartIDs.map(Number);

            if (cartIDs.some(id => !Number.isSafeInteger(id) || id < 1)) {
                return res.status(400).json({
                    success: false,
                    message: "Invalid selected cart items."
                });
            }

            const uniqueCartIDs = [...new Set(cartIDs)];

            if (uniqueCartIDs.length !== cartIDs.length) {
                return res.status(400).json({
                    success: false,
                    message: "Duplicate cart items submitted."
                });
            }

            // 3. DATABASE TRANSACTION
            connection = await this.db.getConnection();
            await connection.beginTransaction();

            // 4. GET CUSTOMER DETAILS
            const [customers] = await connection.execute(
                `SELECT *
                 FROM users
                 WHERE id = ?
                 AND role IN ('user', 'customer')
                 LIMIT 1`,
                [userId]
            );

            if (!customers.length) {
                throw new Error("Customer account not found.");
            }

            const customer = customers[0];

            // 5. GET ONLY SELECTED PRODUCTS
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
                [userId, ...uniqueCartIDs]
            );

            if (cartItems.length !== uniqueCartIDs.length) {
                throw new Error(
                    "Some selected products no longer exist in your cart."
                );
            }

            // 6. CALCULATE TOTAL
            let subtotalCents = 0;

            for (const item of cartItems) {
                const priceCents = Math.round(Number(item.price) * 100);
                const quantity = Number(item.quantity);

                if (
                    !Number.isSafeInteger(priceCents) ||
                    priceCents < 0 ||
                    !Number.isInteger(quantity) ||
                    quantity < 1
                ) {
                    throw new Error("Invalid cart item information.");
                }

                subtotalCents += priceCents * quantity;
            }

            const taxCents = Math.round(subtotalCents * 0.12);
            const totalCents = subtotalCents + taxCents;

            const subtotal = (subtotalCents / 100).toFixed(2);
            const tax = (taxCents / 100).toFixed(2);
            const totalPrice = (totalCents / 100).toFixed(2);

            // 7. GENERATE ORDER DETAILS
            const orderUUID = crypto.randomUUID();
            const orderNumber = "CO" + crypto.randomInt(100000000, 1000000000);

            // 8. INSERT ORDER
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
                    latitude,
                    longitude
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
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
                    latitude,
                    longitude
                ]
            );

            const orderId = orderResult.insertId;

            // 9. INSERT ORDER ITEMS
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
                        Number(item.price).toFixed(2)
                    ]
                );
            }

            // 10. DELETE ONLY PURCHASED PRODUCTS
            await connection.execute(
                `DELETE FROM cart
                 WHERE user_id = ?
                 AND id IN (${placeholders})`,
                [userId, ...uniqueCartIDs]
            );

            // 11. COMMIT
            await connection.commit();

            // 12. RETURN COMPLETE RECEIPT INFORMATION
            const orderDate = new Date().toLocaleString("en-PH", {
                timeZone: "Asia/Manila"
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
                        customer.fullName ||
                        customer.full_name ||
                        customer.username ||
                        "",

                    email: customer.email || "",

                    phone: contactNumber,
                    address,

                    payment_method: paymentMethod,
                    fulfillment: "delivery",

                    street: address,
                    barangay: "",
                    city: "",

                    subtotal,
                    tax,
                    delivery_fee: "0.00",
                    total: totalPrice,

                    totalPrice,
                    status: "pending",

                    latitude,
                    longitude
                },

                items: cartItems.map(item => ({
                    name: item.productName,
                    quantity: Number(item.quantity),
                    price: Number(item.price),
                    image: item.img || ""
                }))
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

            return res.status(400).json({
                success: false,
                message: error.message || "Unable to process your order."
            });

        } finally {
            if (connection) {
                connection.release();
            }
        }
    };
}

module.exports = CheckoutController;
