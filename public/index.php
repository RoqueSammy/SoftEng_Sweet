
<?php
session_start();

$pageClass = "index-page";

include_once __DIR__ . "/../app/config/config.php";
include('includes/header.php');
include('includes/topbar.php');

/* Sweet Tooths products */
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

/* Best sellers
   NOTE: This currently displays the four latest products.
   Add a sales-based ORDER BY later if you track actual sales.
*/
$bestQuery = "
    SELECT id, productName, price, img
    FROM product_item
    ORDER BY id DESC
    LIMIT 4
";

$bestResult = mysqli_query($conn, $bestQuery);

if (!$bestResult) {
    error_log("Best Sellers query failed: " . mysqli_error($conn));
    $bestProducts = [];
} else {
    $bestProducts = mysqli_fetch_all($bestResult, MYSQLI_ASSOC);
    mysqli_free_result($bestResult);
}

/* Safely generate product image URLs */
function sweetProductImage($imageName)
{
    $imageName = basename((string) $imageName);

    $imageDirectory = __DIR__ . "/assets/product_img/";
    $imageUrlDirectory = "/SoftEcoms/public/assets/product_img/";

    if ($imageName !== "" && is_file($imageDirectory . $imageName)) {
        return $imageUrlDirectory . rawurlencode($imageName);
    }

    return $imageUrlDirectory . "placeholder.jpg";
}
?>

<!-- HERO SECTION -->
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

<!-- FEATURES / CONTINUOUS ANIMATION -->
<section class="new">
    <div class="container py-5">
        <div class="carousel">
            <div class="track">

                <?php for ($copy = 0; $copy < 2; $copy++): ?>
                    <div class="carousel-group" <?php echo $copy === 1 ? 'aria-hidden="true"' : ''; ?>>

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

<!-- SWEET TOOTHS PRODUCT SLIDER -->
<section class="product" id="sweet-tooths">
    <div class="container py-5">

        <div class="section-heading text-center py-4">
            <h1 class="our_products">Sweet Tooths</h1>
            <h6 class="section-subtitle">
                Our Sweetest delivered at your Doorstep.
            </h6>
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
                                            loading="lazy">
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
                                            $<?php echo number_format($productPrice, 2); ?>
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

            <div class="slider-indicator" id="productSliderStatus" aria-live="polite">
                Showing products
            </div>

        <?php else: ?>

            <p class="text-center text-muted py-4">
                No products available at the moment.
            </p>

        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="/SoftEcoms/public/products.php" class="btn2">
                Click For More
            </a>
        </div>

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
            <h1 class="our_products">Sweetness Deserved</h1>
            <h6 class="section-subtitle">
                Experience the Sweetness with our Best Sellers at an Affordable Price!
            </h6>
        </div>

        <?php if (!empty($bestProducts)): ?>

            <div class="row g-4 justify-content-center">

                <?php foreach ($bestProducts as $row): ?>
                    <?php
                    $displayImg = sweetProductImage($row["img"] ?? "");
                    $productId = (int) $row["id"];
                    $productName = (string) ($row["productName"] ?? "");
                    $productPrice = (float) ($row["price"] ?? 0);
                    ?>

                    <div class="col-6 col-md-4 col-lg-3">

                        <a href="/SoftEcoms/public/productpage.php?id=<?php echo $productId; ?>"
                           class="product-card-link">

                            <div class="best-card card border-0 h-100 rounded-4 overflow-hidden shadow-sm">

                                <div class="best-img-wrapper">
                                    <img
                                        src="<?php echo htmlspecialchars($displayImg, ENT_QUOTES, "UTF-8"); ?>"
                                        class="best-img"
                                        alt="<?php echo htmlspecialchars($productName, ENT_QUOTES, "UTF-8"); ?>"
                                        loading="lazy">
                                </div>

                                <div class="card-body text-center">
                                    <h6 class="best-title">
                                        <?php echo htmlspecialchars($productName, ENT_QUOTES, "UTF-8"); ?>
                                    </h6>

                                    <p class="best-price">
                                        $<?php echo number_format($productPrice, 2); ?>
                                    </p>
                                </div>

                            </div>

                        </a>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <p class="text-center text-muted py-4">
                No products available at the moment.
            </p>

        <?php endif; ?>

    </div>
</section>

<!-- CALL TO ACTION -->
<section class="e_clair py-5">
    <div class="container text-white py-5">
        <div class="row py-5">
            <div class="col-lg-12 m-auto text-center">
                <h1 class="font-weight-bold py-3">Invite All!</h1>
                <h6 class="cta-subtitle">
                    Enjoy the heartfelt moments with your Loved Ones at our Shop!
                </h6>
                <a href="/SoftEcoms/public/products.php" class="btn2 mt-3">
                    See More
                </a>
            </div>
        </div>
    </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
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

    const slides = Array.from(slider.querySelectorAll(".product-slide"));

    if (slides.length === 0) return;

    let activeIndex = 0;
    let resizeTimer;

    function updateSlider() {
        const wrapperWidth = wrapper.clientWidth;
        const activeSlide = slides[activeIndex];

        if (!activeSlide || wrapperWidth === 0) return;

        // Add side space so the first and last cards can be centered.
        const sidePadding = Math.max(
            0,
            (wrapperWidth - activeSlide.offsetWidth) / 2
        );

        slider.style.paddingLeft = sidePadding + "px";
        slider.style.paddingRight = sidePadding + "px";

        // Center the active card in the visible slider area.
        const slideCenter =
            activeSlide.offsetLeft + activeSlide.offsetWidth / 2;

        const offset = wrapperWidth / 2 - slideCenter;

        slider.style.transform = `translateX(${offset}px)`;

        // Highlight the active card and its neighbors.
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

        prevButton.disabled = activeIndex === 0;
        nextButton.disabled = activeIndex === slides.length - 1;

        if (status) {
            status.textContent =
                `Showing product ${activeIndex + 1} of ${slides.length}`;
        }
    }

    prevButton.addEventListener("click", function () {
        if (activeIndex > 0) {
            activeIndex--;
            updateSlider();
        }
    });

    nextButton.addEventListener("click", function () {
        if (activeIndex < slides.length - 1) {
            activeIndex++;
            updateSlider();
        }
    });

    // Recalculate the card positions when the screen size changes.
    window.addEventListener("resize", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(updateSlider, 100);
    });

    // Wait for images to load before calculating card dimensions.
    window.addEventListener("load", updateSlider);

    updateSlider();
});
</script>



<script>
document.addEventListener("DOMContentLoaded", async function () {
    try {
        const response = await fetch("http://localhost:3000/me", {
            method: "GET",
            credentials: "include",
            headers: {
                "Accept": "application/json"
            }
        });

        // Guests do not need to complete a profile.
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

        // Only customer accounts need delivery details.
        if (role !== "customer") {
            return;
        }

        // Use the server result when available.
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

    // Allow the customer to complete the profile later.
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
            "http://localhost:3000/complete-profile",
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

        // Refresh the homepage after successfully saving.
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
