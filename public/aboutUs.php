
<?php
session_start();

$pageClass = "about-page";

include('includes/header.php');
include('includes/topbar.php');
?>



<main class="about">

  <!-- PANEL -->
  <section class="about-section">
    <div class="about-panel">

      <p class="about-tagline">Forever delivery joy from our home to yours.</p>

      <!-- MISSION + STORY | TEAM PHOTO -->
      <div class="about-intro">
        <div class="about-text">
          <section>
            <h2>Our Mission</h2>
            <p>
              At <strong>Sweetclaire</strong>, we believe that great pastries should bring a moment
              of pure joy to your day. Our mission is to craft unforgettable, artisanal éclairs
              that combine classic French baking techniques with bold, modern flavors.
            </p>
          </section>

          <section>
            <h2>Our Story</h2>
            <p>
              Sweetclaire began with a simple obsession: perfecting the éclair. We noticed that
              while many bakeries offered standard pastries, few were dedicated to mastering the
              delicate balance of crisp choux pastry, silky pastry cream, and rich glaze.
            </p>
            <p>
              Determined to elevate this timeless treat, we started experimenting with small-batch
              recipes, testing countless flour blends, fillings, and finishes until every bite
              delivered the ideal crunch and melt-in-your-mouth center. Today, Sweetclaire is proud
              to share our signature éclairs with pastry lovers who appreciate craftsmanship and quality.
            </p>
          </section>
        </div>

        <div class="about-photo">
          <img src="/SoftEcoms/public/assets/img/SOFTECOMS TEAM.jpg" alt="The Sweetclaire team">
        </div>
      </div>

      <!-- WHAT WE BAKE -->
      <div class="about-bake">
        <h2>What We Bake</h2>
        <img class="about-mascot" src="/SoftEcoms/public/assets/img/baking.jpg" alt="Baking process">
        <p class="about-bake-intro">
          While our ovens turn out a variety of fresh treats, éclairs are the heart of everything
          we do. Every pastry is baked fresh daily and built around three core promises:
        </p>

        <div class="about-promises">
          <div class="promise">
            <div class="promise-img">
                <img class="promise-img" src="/SoftEcoms/public/assets/img/fresh_ing.jpg" alt="Fresh ingredients">
            </div>
            <h3>Fresh, real ingredients</h3>
            <p>We use real butter, farm-fresh eggs, pure vanilla, and premium chocolate—never artificial shortcuts.</p>
          </div>
          <div class="promise">
            <div class="promise-img">
                <img class="promise-img" src="/SoftEcoms/public/assets/img/precision.jpg" alt="Handcrafted precision">
            </div>
            <h3>Handcrafted precision</h3>
            <p>Every choux shell is piped, baked, filled, and glazed by hand to ensure the perfect texture and finish.</p>
          </div>
          <div class="promise">
            <div class="promise-img">
                <img class="promise-img" src="/SoftEcoms/public/assets/img/creative.jpg" alt="Creative flavors">
            </div>
            <h3>Creative flavors</h3>
            <p>From timeless Dark Chocolate and Madagascar Vanilla to seasonal fruit and nut creations, we offer flavors for every craving.</p>
          </div>
        </div>
      </div>

    </div>
  </section>

</main>