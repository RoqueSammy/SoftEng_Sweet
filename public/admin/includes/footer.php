


<footer class="site-footer">
  <div class="container">
    <div class="row">


      <div class="col-md-4 mb-4">
        <h5>Éclair Shop</h5>
        <p>Bringing you the finest handcrafted éclairs, made fresh daily with love. Taste the sweetness in every bite.</p>
      </div>

    
      <div class="col-md-4 mb-4">
        <h5>Quick Links</h5>
        <ul class="footer-links">
          <li><a href="/SoftEcoms/public/index.php">Home</a></li>
          <li><a href="/SoftEcoms/public/order.php">Shop</a></li>
          <li><a href="/SoftEcoms/public/aboutUs.php">About Us</a></li>
          <li><a href="/SoftEcoms/public/contactUs.php">Contact</a></li>
        </ul>
      </div>

 
      <div class="col-md-4 mb-4">
        <h5>Get In Touch</h5>
        <p>hello@sweetclaire.com</p>
        <p>+63 912 345 6789</p>
        <div class="footer-social d-flex mt-3">
          <a href="#" aria-label="Facebook">FB</a>
          <a href="#" aria-label="Instagram">IG</a>
          <a href="#" aria-label="Twitter">X</a>
        </div>
      </div>

    </div>

    <hr class="footer-divider">

    <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center">
      <p class="mb-0">&copy; <?php echo date('Y'); ?> Éclair Shop. All rights reserved.</p>
      <p class="mb-0">Made with <span class="footer-heart">&hearts;</span> for éclair lovers</p>
    </div>
  </div>
</footer>

</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<?php 
if (isset($_SESSION['message']) && $_SESSION['code'] != ""){
?>
<script>
  const Toast = Swal.mixin({
    toast: true,
    position: "Top-end",
    showConfirmationButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    }

  });
   Toast.fire({
      icon: "<?php echo $_SESSION['code']; ?>",
      title: "<?php echo $_SESSION['message']; ?>"
    });

    </script>

<?php
  unset($_SESSION['message']);
  unset($_SESSION['code']);
}
?>

<script>
setTimeout(function() {
    const message = document.getElementById('successMessage');
    if(message){
        message.style.opacity = "0";
        message.style.transition = "0.5s";

        setTimeout(() => {
            message.style.display = "none";
        }, 500);
    }
}, 3000);
</script>
