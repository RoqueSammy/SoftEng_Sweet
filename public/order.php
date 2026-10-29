<?php
session_start();
$pageClass = "order-page";
include('includes/header.php');
include('includes/topbar.php');
include_once("../app/config/config.php");

/* Same helper as index.php: safe image URL + placeholder fallback */
if (!function_exists('sweetProductImage')) {
    function sweetProductImage($imageName)
    {
        $imageName = basename((string) $imageName);
        $dir = __DIR__ . "/assets/product_img/";
        $url = "/SoftEcoms/public/assets/product_img/";

        if ($imageName !== "" && is_file($dir . $imageName)) {
            return $url . rawurlencode($imageName);
        }
        return $url . "placeholder.jpg";
    }
}

/* Sorting: ORDER BY can't use ? placeholders, so only whitelisted values get in */
$sortOptions = [
    'new'        => 'id DESC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
];
$sortLabels = [
    'new'        => 'New',
    'price_asc'  => 'Price ascending',
    'price_desc' => 'Price descending',
];

$sort = $_GET['sort'] ?? 'new';
if (!isset($sortOptions[$sort])) {
    $sort = 'new';
}
$q = trim($_GET['q'] ?? '');

$sql = "SELECT id, productName, price, img
        FROM product_item
        WHERE productName LIKE ?
        ORDER BY " . $sortOptions[$sort] . "
        LIMIT 20";

$stmt = mysqli_prepare($conn, $sql);
$like = "%" . $q . "%";
mysqli_stmt_bind_param($stmt, "s", $like);
mysqli_stmt_execute($stmt);
$products = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<div class="order-banner">
  <h1>Our Pastries</h1>
</div>

<section class="order-listing">
  <div class="order-panel">

    <div class="order-toolbar">
      <form method="get">
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        <input type="search" name="q" class="form-control rounded-pill"
               placeholder="Search" value="<?= htmlspecialchars($q) ?>">
      </form>

      <div class="sort-chips">
        <?php foreach ($sortLabels as $key => $label): ?>
          <a class="sort-chip <?= $sort === $key ? 'active' : '' ?>"
             href="?sort=<?= $key ?>&q=<?= urlencode($q) ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (empty($products)): ?>
      <p class="text-center text-muted py-4">No pastries found.</p>
    <?php else: ?>
      <div class="order-grid">
        <?php foreach ($products as $p): ?>
          <a class="order-card" href="productpage.php?id=<?= (int) $p['id'] ?>">
            <img src="<?= htmlspecialchars(sweetProductImage($p['img'])) ?>"
                 alt="<?= htmlspecialchars($p['productName']) ?>"
                 decoding="async">
            <h6><?= htmlspecialchars($p['productName']) ?></h6>
            <p class="price">$<?= number_format((float) $p['price'], 2) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>