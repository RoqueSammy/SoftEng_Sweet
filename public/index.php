
<?php
session_start();

$pageClass = "index-page";

include_once __DIR__ . "/../app/config/config.php";
include('includes/header.php');
include('includes/topbar.php');

$sweetQuery = "
    SELECT id, productName, productDescription, price, img
    FROM product_item
    ORDER BY id DESC
    LIMIT 8
";

$sweetResult = mysqli_query($conn, $sweetQuery);

if (!$sweetResult) {
    error_log("Sweet Tooths query failed: " . mysqli_error($conn));
    $sweetProducts = [];
} else {
    $sweetProducts = mysqli_fetch_all($sweetResult, MYSQLI_ASSOC);
    mysqli_free_result($sweetResult);
}


$bestQuery = "
    SELECT
        p.id,
        p.uuid,
        p.productName,
        p.productDescription,
        p.price,
        p.img,
        COALESCE((SELECT SUM(oi.quantity) FROM orderitems oi INNER JOIN orders o ON o.id = oi.order_id WHERE oi.product_id = p.id AND o.status = 'completed'), 0) AS units_sold
    FROM product_item p
    ORDER BY units_sold DESC, p.id DESC
    LIMIT 1
";

$bestResult = mysqli_query($conn, $bestQuery);

if (!$bestResult) {
    error_log("Best Sellers query failed: " . mysqli_error($conn));
    $bestProducts = [];
} else {
    $bestProducts = mysqli_fetch_all($bestResult, MYSQLI_ASSOC);
    mysqli_free_result($bestResult);
}


function sweetProductImage($imageName)
{
    $imageName = trim((string) $imageName);
    if (preg_match('#^https?://#i', $imageName)) {
        return $imageName;
    }

    $imageName = basename((string) $imageName);

    $imageDirectory = __DIR__ . "/assets/product_img/";
    $imageUrlDirectory = "/SoftEcoms/public/assets/product_img/";

    if ($imageName !== "" && is_file($imageDirectory . $imageName)) {
        return $imageUrlDirectory . rawurlencode($imageName);
    }

    return $imageUrlDirectory . "placeholder.jpg";
}
?>


<section class="intro">
    <div class="container hero-box">
        <div class="row">
            <div class="col-lg-7 pt-2 text-center">
                <h1>Welcome to Eclair Shop!</h1>
                <a href="#sweet-tooths" class="btn1">Explore Products</a>
            </div>
        </div>
    </div>
</section>


<section class="new">
    <div class="container py-5">
        <div class="carousel">
            <div class="track">

                <?php for ($copy = 0; $copy < 4; $copy++): ?>
                    <div class="carousel-group" <?php echo $copy > 0 ? 'aria-hidden="true"' : ''; ?>>

                        <div class="carousel-item-custom">
                            <img src="/SoftEcoms/public/assets/img/varieties.png"
                                 class="img-fluid mb-2"
                                 alt="Varieties">
                            <h6 class="vary_custom_sell">Varieties</h6>
                        </div>

                        <div class="carousel-item-custom">
                            <img src="/SoftEcoms/public/assets/img/custom_made.png"
                                 class="img-fluid mb-2"
                                 alt="Custom Made">
                            <h6 class="vary_custom_sell">Custom Made</h6>
                        </div>

                        <div class="carousel-item-custom">
                            <img src="/SoftEcoms/public/assets/img/Best_Seller.png"
                                 class="img-fluid mb-2"
                                 alt="Best Sellers">
                            <h6 class="vary_custom_sell">Best Sellers</h6>
                        </div>

                    </div>
                <?php endfor; ?>

            </div>
        </div>
    </div>
</section>


<section class="product" id="sweet-tooths">
    <div class="container py-5">

        <!-- SWEET TOOTHS HEADING + VIEW ALL -->
        <div class="section-heading sweet-heading py-4">

            <div class="sweet-heading-content">
                <h1 class="our_products">Sweet Tooths</h1>
                <h6 class="section-subtitle">
                    Our Sweetest delivered at your Doorstep.
                </h6>
            </div>

            <a href="/SoftEcoms/public/order.php" class="view-all-link">
                View All <span>&gt;</span>
            </a>

        </div>

        <?php if (!empty($sweetProducts)): ?>

            <div class="product-slider-wrapper">

                <button type="button"
                        class="product-slider-btn product-prev"
                        aria-label="Show previous products"
                        disabled>
                    &#10094;
                </button>

                <div class="product-slider" id="productSlider">

                    <?php foreach ($sweetProducts as $row): ?>
                        <?php
                        $displayImg = sweetProductImage($row["img"] ?? "");
                        $productId = (int) $row["id"];
                        $productName = (string) ($row["productName"] ?? "");
                        $productDescription = (string) ($row["productDescription"] ?? "");
                        $productPrice = (float) ($row["price"] ?? 0);
                        ?>

                        <div class="product-slide">

                            <a href="/SoftEcoms/public/productpage.php?id=<?php echo $productId; ?>"
                               class="product-card-link">

                                <div class="card product-card h-100 rounded-4 shadow-sm overflow-hidden border-0">

                                    <div class="card-img-wrapper">
                                        <img
                                            src="<?php echo htmlspecialchars($displayImg, ENT_QUOTES, "UTF-8"); ?>"
                                            class="card-img-top"
                                            alt="<?php echo htmlspecialchars($productName, ENT_QUOTES, "UTF-8"); ?>"
                                            loading="lazy" decoding="async" fetchpriority="low">
                                    </div>

                                    <div class="card-body d-flex flex-column">

                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($productName, ENT_QUOTES, "UTF-8"); ?>
                                        </h5>

                                        <p class="card-text flex-grow-1">
                                            <?php
                                            echo htmlspecialchars(
                                                mb_strimwidth($productDescription, 0, 70, "..."),
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>
                                        </p>

                                        <p class="card-price">
                                            ₱<?php echo number_format($productPrice, 2); ?>
                                        </p>

                                    </div>
                                </div>

                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

                <button type="button"
                        class="product-slider-btn product-next"
                        aria-label="Show next products">
                    &#10095;
                </button>

            </div>

            <div class="slider-indicator"
                 id="productSliderStatus"
                 aria-label="Product slider indicators">
            </div>

        <?php else: ?>

            <p class="text-center text-muted py-4">
                No products available at the moment.
            </p>

        <?php endif; ?>

    </div>
</section>

<!-- COMMUNITIES SECTION -->
<section class="new-section">
    <div class="container py-5">

        <div class="section-heading text-center py-4">
            <h1 class="our_products">Communities</h1>
            <h6 class="section-subtitle">
                We Offer a Wide Range of Eclairs at your Comfort.
            </h6>
        </div>

        <div class="row g-4">

            <div class="col-md-6 col-lg-4">
                <div class="community-card card border-0 h-100 text-center rounded-4 overflow-hidden shadow-sm">
                    <div class="community-img-wrapper">
                        <img src="/SoftEcoms/public/assets/img/for_bd.jpg"
                             class="community-img"
                             alt="Join Our Community"
                             loading="lazy">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Join Our Community</h5>
                        <p class="card-text">
                            Connect with fellow eclair enthusiasts and share your love for our delicious treats.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="community-card card border-0 h-100 text-center rounded-4 overflow-hidden shadow-sm">
                    <div class="community-img-wrapper">
                        <img src="/SoftEcoms/public/assets/img/for_peeps.jpg"
                             class="community-img"
                             alt="Exclusive Offers"
                             loading="lazy">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Exclusive Offers</h5>
                        <p class="card-text">
                            Gain access to exclusive offers, discounts, and promotions on our delectable eclairs.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="community-card card border-0 h-100 text-center rounded-4 overflow-hidden shadow-sm">
                    <div class="community-img-wrapper">
                        <img src="/SoftEcoms/public/assets/img/section_show.jpg"
                             class="community-img"
                             alt="Stay Updated"
                             loading="lazy">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Stay Updated</h5>
                        <p class="card-text">
                            Be the first to know about our latest eclair flavors, upcoming events, and exciting news.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-center mt-4">
            <a href="/SoftEcoms/public/products.php" class="btn2">
                See More
            </a>
        </div>

    </div>
</section>

<!-- BEST SELLERS SECTION -->
<section class="shop">
    <div class="container py-5">

        <div class="section-heading text-center py-4">
            <h1 class="our_products">Best Seller Spotlight</h1>
            <h6 class="section-subtitle">
                Our customer favorite, made fresh for you.
            </h6>
        </div>

        <?php if (!empty($bestProducts)): ?>
            <?php $bestProduct = $bestProducts[0]; $bestImage = sweetProductImage($bestProduct["img"] ?? ""); ?>
            <button type="button" class="best-feature-card" id="bestSellerTrigger"
                    data-product-uuid="<?= htmlspecialchars($bestProduct["uuid"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    data-product-name="<?= htmlspecialchars($bestProduct["productName"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    data-product-description="<?= htmlspecialchars($bestProduct["productDescription"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    data-product-price="<?= htmlspecialchars((string)($bestProduct["price"] ?? "0"), ENT_QUOTES, "UTF-8") ?>"
                    data-product-image="<?= htmlspecialchars($bestImage, ENT_QUOTES, "UTF-8") ?>"
                    aria-label="See details for <?= htmlspecialchars($bestProduct["productName"] ?? "our best seller", ENT_QUOTES, "UTF-8") ?>">
                <span class="best-feature-badge"><i class="fa-solid fa-star"></i> BEST SELLER</span>
                <span class="best-feature-image-wrap">
                    <img src="<?= htmlspecialchars($bestImage, ENT_QUOTES, "UTF-8") ?>"
                         class="best-feature-image"
                         alt="<?= htmlspecialchars($bestProduct["productName"] ?? "Best seller pastry", ENT_QUOTES, "UTF-8") ?>"
                         loading="lazy" decoding="async">
                    <span class="best-feature-open"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </span>
                <span class="best-feature-copy">
                    <span class="best-feature-name"><?= htmlspecialchars($bestProduct["productName"] ?? "SweetClaire favorite", ENT_QUOTES, "UTF-8") ?></span>
                    <span class="best-feature-hint">Tap to see details and order</span>
                </span>
            </button>

        <?php else: ?>

            <p class="text-center text-muted py-4">
                No products available at the moment.
            </p>

        <?php endif; ?>

    </div>
</section>

<div class="modal fade" id="bestSellerModal" tabindex="-1" aria-labelledby="bestSellerModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content quick-view-content">
            <div class="modal-header">
                <div><span class="quick-view-eyebrow">SWEETCLAIRE CUSTOMER FAVORITE</span><h2 class="modal-title" id="bestSellerModalTitle">Product details</h2></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="quick-view-layout">
                    <div class="quick-view-image-wrap"><img id="bestSellerModalImage" src="" alt=""></div>
                    <div class="quick-view-details">
                        <h3 id="bestSellerModalName"></h3>
                        <p class="quick-view-price" id="bestSellerModalPrice"></p>
                        <p class="quick-view-description" id="bestSellerModalDescription"></p>
                        <div id="bestSellerModalMessage" class="quick-view-message" role="status" aria-live="polite"></div>
                        <label class="quick-view-quantity-label" for="bestSellerQuantity">Quantity</label>
                        <div class="quick-view-quantity">
                            <button type="button" id="bestSellerDecrease" aria-label="Decrease quantity">−</button>
                            <input type="number" id="bestSellerQuantity" min="1" step="1" value="1" inputmode="numeric">
                            <button type="button" id="bestSellerIncrease" aria-label="Increase quantity">+</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer quick-view-footer">
                <button type="button" class="btn quick-view-back" data-bs-dismiss="modal">Back</button>
                <button type="button" class="btn quick-view-add" id="bestSellerOrderNow"><i class="fa-solid fa-bag-shopping"></i> Order Now</button>
            </div>
        </div>
    </div>
</div>

<!-- CALL TO ACTION -->
<section class="e_clair py-5">
    <div class="container text-white py-5">
        <div class="row py-5">
            <div class="col-lg-12 m-auto text-center">
                <h1 class="font-weight-bold py-3">Contact Us!</h1>
                <h6 class="cta-subtitle">
                    Enjoy the heartfelt moments with your Loved Ones at our Shop!
                </h6>
                <a href="/SoftEcoms/public/contactUs.php" class="btn2 mt-3">
                    Click Here
                </a>
            </div>
        </div>
    </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const trigger = document.getElementById("bestSellerTrigger");
    const modalElement = document.getElementById("bestSellerModal");
    if (!trigger || !modalElement || !window.bootstrap) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const quantityInput = document.getElementById("bestSellerQuantity");
    const message = document.getElementById("bestSellerModalMessage");
    const orderButton = document.getElementById("bestSellerOrderNow");
    let currentProduct = null;

    trigger.addEventListener("click", () => {
        currentProduct = { ...trigger.dataset };
        document.getElementById("bestSellerModalTitle").textContent = currentProduct.productName;
        document.getElementById("bestSellerModalName").textContent = currentProduct.productName;
        document.getElementById("bestSellerModalDescription").textContent = currentProduct.productDescription || "Freshly prepared by SweetClaire.";
        document.getElementById("bestSellerModalPrice").textContent = "₱" + Number(currentProduct.productPrice).toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const image = document.getElementById("bestSellerModalImage");
        image.src = currentProduct.productImage;
        image.alt = currentProduct.productName;
        quantityInput.value = "1";
        message.textContent = "";
        message.className = "quick-view-message";
        orderButton.disabled = false;
        orderButton.innerHTML = '<i class="fa-solid fa-bag-shopping"></i> Order Now';
        modal.show();
    });

    document.getElementById("bestSellerDecrease").addEventListener("click", () => {
        quantityInput.value = Math.max(1, (parseInt(quantityInput.value, 10) || 1) - 1);
    });
    document.getElementById("bestSellerIncrease").addEventListener("click", () => {
        quantityInput.value = (parseInt(quantityInput.value, 10) || 1) + 1;
    });

    orderButton.addEventListener("click", async () => {
        if (!currentProduct) return;
        const quantity = Number(quantityInput.value);
        if (!Number.isSafeInteger(quantity) || quantity < 1) {
            message.textContent = "Choose a valid quantity.";
            message.className = "quick-view-error";
            return;
        }

        orderButton.disabled = true;
        orderButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Preparing checkout...';
        message.textContent = "";

        try {
            const addResponse = await fetch("/api/cart/add", {
                method: "POST",
                credentials: "include",
                headers: { "Content-Type": "application/json", Accept: "application/json" },
                body: JSON.stringify({ product_uuid: currentProduct.productUuid, quantity })
            });
            const addData = await addResponse.json();
            if (addResponse.status === 401) {
                window.location.href = addData.redirect || "/SoftEcoms/public/login.php";
                return;
            }
            if (!addResponse.ok || !addData.success) {
                throw new Error(addData.message || "Could not add this item to your cart.");
            }

            const cartResponse = await fetch("/api/cart", {
                credentials: "include",
                headers: { Accept: "application/json" }
            });
            const cartData = await cartResponse.json();
            if (!cartResponse.ok || !cartData.success) {
                throw new Error(cartData.message || "Could not open checkout.");
            }
            const cartItem = (cartData.cart || []).find(item =>
                String(item.uuid) === String(currentProduct.productUuid)
            );
            if (!cartItem) throw new Error("The item was added, but checkout could not find it in your cart.");

            const checkoutForm = document.createElement("form");
            checkoutForm.method = "POST";
            checkoutForm.action = "/SoftEcoms/public/checkout.php";
            const selectedItem = document.createElement("input");
            selectedItem.type = "hidden";
            selectedItem.name = "selected_cart_ids[]";
            selectedItem.value = cartItem.cartID;
            checkoutForm.appendChild(selectedItem);
            document.body.appendChild(checkoutForm);
            checkoutForm.submit();
        } catch (error) {
            message.textContent = error.message || "Unable to start checkout.";
            message.className = "quick-view-error";
            orderButton.disabled = false;
            orderButton.innerHTML = '<i class="fa-solid fa-bag-shopping"></i> Try again';
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const slider = document.getElementById("productSlider");
    if (!slider) return;

    const wrapper = slider.closest(".product-slider-wrapper");
    const prevButton = wrapper?.querySelector(".product-prev");
    const nextButton = wrapper?.querySelector(".product-next");
    const status = document.getElementById("productSliderStatus");

    if (!wrapper || !prevButton || !nextButton) {
        console.error("Product slider elements are missing.");
        return;
    }

    const originalSlides = Array.from(slider.querySelectorAll(".product-slide"));
    if (originalSlides.length === 0) return;

    const productCount = originalSlides.length;
    const cloneSlides = () => originalSlides.map((slide) => {
        const clone = slide.cloneNode(true);
        clone.setAttribute("aria-hidden", "true");
        clone.inert = true;
        return clone;
    });
    const leftClones = cloneSlides();
    const rightClones = cloneSlides();
    slider.prepend(...leftClones);
    slider.append(...rightClones);
    const slides = Array.from(slider.querySelectorAll(".product-slide"));

    let activeIndex = productCount;
    let resizeTimer;
    let isMoving = false;

    // Create one clickable indicator dot for each product.
    if (status) {
        status.innerHTML = "";

        originalSlides.forEach(function (slide, index) {
            const dot = document.createElement("button");
            dot.type = "button";
            dot.className = "slider-dot";
            dot.setAttribute("aria-label", "Go to product " + (index + 1));

            dot.addEventListener("click", function () {
                activeIndex = productCount + index;
                updateSlider();
            });

            status.appendChild(dot);
        });
    }

    function updateSlider() {
        const wrapperWidth = wrapper.clientWidth;
        const activeSlide = slides[activeIndex];

        if (!activeSlide || wrapperWidth === 0) return;

        const sidePadding = Math.max(
            0,
            (wrapperWidth - activeSlide.offsetWidth) / 2
        );

        slider.style.paddingLeft = sidePadding + "px";
        slider.style.paddingRight = sidePadding + "px";

        // Center the active card.
        const slideCenter =
            activeSlide.offsetLeft + activeSlide.offsetWidth / 2;
        const offset = wrapperWidth / 2 - slideCenter;

        slider.style.transform = `translateX(${offset}px)`;

        slides.forEach(function (slide, index) {
            slide.classList.toggle("is-active", index === activeIndex);
            slide.classList.toggle(
                "is-near",
                Math.abs(index - activeIndex) === 1
            );

            slide.setAttribute(
                "aria-current",
                index === activeIndex ? "true" : "false"
            );
        });

        prevButton.disabled = false;
        nextButton.disabled = false;

        // Highlight the indicator for the active product.
        if (status) {
            const dots = status.querySelectorAll(".slider-dot");

            const logicalIndex = ((activeIndex - productCount) % productCount + productCount) % productCount;
            dots.forEach(function (dot, index) {
                dot.classList.toggle("active", index === logicalIndex);

                if (index === logicalIndex) {
                    dot.setAttribute("aria-current", "true");
                } else {
                    dot.removeAttribute("aria-current");
                }
            });
        }
    }

    prevButton.addEventListener("click", function () {
        if (isMoving) return;
        isMoving = true;
        activeIndex--;
        updateSlider();
    });

    nextButton.addEventListener("click", function () {
        if (isMoving) return;
        isMoving = true;
        activeIndex++;
        updateSlider();
    });

    slider.addEventListener("transitionend", function (event) {
        // Child slides also animate their scale; only rebase when the track itself finishes.
        if (event.target !== slider || event.propertyName !== "transform") return;
        let needsReposition = false;
        while (activeIndex < productCount) {
            activeIndex += productCount;
            needsReposition = true;
        }
        while (activeIndex >= productCount * 2) {
            activeIndex -= productCount;
            needsReposition = true;
        }
        if (needsReposition) {
            slider.style.transition = "none";
            updateSlider();
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    slider.style.transition = "";
                });
            });
        }
        isMoving = false;
    });

    // Recalculate positions when the screen size changes.
    window.addEventListener("resize", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(updateSlider, 100);
    });

    // Recalculate after images finish loading.
    window.addEventListener("load", updateSlider);

    updateSlider();
});
</script>


<script>
document.addEventListener("DOMContentLoaded", async function () {
    try {
        const response = await fetch("/api/me", {
            method: "GET",
            credentials: "include",
            headers: {
                "Accept": "application/json"
            }
        });

        if (response.status === 401) {
            return;
        }

        if (!response.ok) {
            console.error(
                "Profile check failed:",
                response.status,
                await response.text()
            );
            return;
        }

        const data = await response.json();
        const user = data.user || data.authUser || data;

        const role = data.role || user.role;

        if (role !== "customer") {
            return;
        }

        const needsCompletion =
            typeof data.needsProfileCompletion === "boolean"
                ? data.needsProfileCompletion
                : !(
                    String(user.contactNumber || "").trim() &&
                    String(user.street || "").trim() &&
                    String(user.barangay || "").trim() &&
                    String(user.city || "").trim()
                );

        if (needsCompletion) {
            await showProfileCompletion();
        }

    } catch (error) {
        console.error("Profile check failed:", error);
    }
});


async function showProfileCompletion() {
    const result = await Swal.fire({
        title: "Complete Your Profile",

        html: `
            <p style="font-size:14px;color:#666">
                Add your phone number and delivery address
                to prepare for checkout.
            </p>

            <input
                id="profilePhone"
                class="swal2-input"
                type="tel"
                placeholder="Phone number"
                maxlength="255"
                autocomplete="tel">

            <input
                id="profileStreet"
                class="swal2-input"
                type="text"
                placeholder="House number and street"
                maxlength="255"
                autocomplete="street-address">

            <input
                id="profileBarangay"
                class="swal2-input"
                type="text"
                placeholder="Barangay"
                maxlength="255">

            <input
                id="profileCity"
                class="swal2-input"
                type="text"
                placeholder="City or municipality"
                maxlength="255">
        `,

        confirmButtonText: "Save Details",
        showCancelButton: true,
        cancelButtonText: "Do This Later",
        allowOutsideClick: false,
        focusConfirm: false,

        preConfirm: function () {
            const contactNumber =
                document.getElementById("profilePhone").value.trim();

            const street =
                document.getElementById("profileStreet").value.trim();

            const barangay =
                document.getElementById("profileBarangay").value.trim();

            const city =
                document.getElementById("profileCity").value.trim();

            if (!contactNumber || !street || !barangay || !city) {
                Swal.showValidationMessage(
                    "Please fill in all four fields."
                );
                return false;
            }

            return {
                contactNumber: contactNumber,
                street: street,
                barangay: barangay,
                city: city
            };
        }
    });


    if (!result.isConfirmed) {
        return;
    }

    try {
        Swal.fire({
            title: "Saving your details...",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        const response = await fetch(
            "/api/complete-profile",
            {
                method: "POST",
                credentials: "include",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(result.value)
            }
        );

        const data = await response.json().catch(function () {
            return {};
        });

        if (!response.ok) {
            throw new Error(
                data.message ||
                "Could not save your profile. HTTP " + response.status
            );
        }

        await Swal.fire({
            icon: "success",
            title: "Profile Completed!",
            text: "Your delivery details have been saved.",
            confirmButtonText: "Continue"
        });

        window.location.reload();

    } catch (error) {
        console.error("Profile save error:", error);

        await Swal.fire({
            icon: "error",
            title: "Could Not Save",
            text: error.message || "Please try again."
        });
    }
}
</script>


<?php include __DIR__ . "/admin/includes/footer.php"; ?>
