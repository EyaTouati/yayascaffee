<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Yaya's Caffee - Contact</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="contact.css">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
<?php
// Inclure les fichiers nécessaires pour l'authentification
require_once 'includes/db.php';
require_once 'includes/auth.php';
?>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="logo">yaya's caffee</a>

         <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                    <li><a href="dream.html">Our story</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>


                   <div class="nav-right">

            <!-- Panier pro -->
            <a href="cart.php" class="cart-icon">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-count"  id="cart-count" >0</span>
            </a>

            <?php if(isLoggedIn()): ?>
              <span style="color:#2c1810;font-weight:600;font-size:0.9rem;"> <?= htmlspecialchars($_SESSION['first_name']) ?> </span>
              <?php if(isAdmin()): ?>
                <a href="<?= BASE_URL ?>admin/dashboard.php" class="btn-signin" style="background:#9ba36a;"><i class="fas fa-cog"></i> Admin</a>
              <?php endif; ?>
              <a href="<?= BASE_URL ?>api/signout.php" class="btn-signin">Sign Out</a>
            <?php else: ?>
              <a href="signin.html" class="btn-signin">Sign In</a>
            <?php endif; ?>
        </div>
    </div>

    <svg class="navbar-wave" viewBox="0 0 1440 60" preserveAspectRatio="none">
        <path d="M0,0 C120,60 240,60 360,30 S600,0 720,30 S960,60 1080,30 S1320,0 1440,30 L1440,0 L0,0 Z"
              fill="#9ba36a"/>
    </svg>
</nav>

<!-- HERO -->
<section class="contact-hero">
    <h1>Contact Us</h1>
    <p>Let’s share a story over coffee ☕</p>
</section>

<!-- CONTACT SECTION -->
<section class="contact-section">

    <div class="contact-container">

        <!-- LEFT SIDE -->
        <div class="contact-info">
            <h2>Visit Yaya’s Caffee</h2>
            <p>Where every cup tells a story.
            We would love to hear from you.</p>

            <div class="info-item">
                <span>📍</span>
                <p>123 Coffee Street, Your City</p>
            </div>

            <div class="info-item">
                <span>📞</span>
                <p>+216 00 000 000</p>
            </div>

            <div class="info-item">
                <span>✉</span>
                <p>hello@yayascoffee.com</p>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="contact-form">
            <form id="contactForm">
                <div class="input-group">
                    <input type="text" id="name" name="name" required placeholder=" ">
                    <label>Your Name</label>
                </div>

                <div class="input-group">
                    <input type="email" id="email" name="email" required placeholder=" ">
                    <label>Email Address</label>
                </div>

                <div class="input-group">
                    <select id="subject" name="subject" required placeholder=" ">
                        <option value="" disabled selected></option>
                        <option value="General Inquiry">General Inquiry</option>
                        <option value="Order Issue">Order Issue</option>
                        <option value="Feedback">Feedback</option>
                        <option value="Partnership">Partnership</option>
                        <option value="Other">Other</option>
                    </select>
                    <label>Subject</label>
                </div>

                <div class="input-group">
                    <textarea id="message" name="message" required placeholder=" "></textarea>
                    <label>Your Message</label>
                </div>

                <button type="submit" class="btn-send" id="submitBtn">
                    <i class="fas fa-paper-plane"></i>
                    Send Message
                </button>
            </form>

            <div id="messageContainer"></div>
        </div>

    </div>

</section>

<!-- FOOTER -->
<footer class="footer">

    <div class="footer-container">

        <!-- Column 1 -->
        <div class="footer-col">
            <h3 class="footer-logo">Yaya’s Caffee</h3>
            <p>
                A place where coffee becomes a ritual,
                and every moment feels warmer.
            </p>
        </div>

        <!-- Column 2 -->
        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="dream.html">Our Story</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>

        <!-- Column 3 -->
        <div class="footer-col">
            <h4>Contact</h4>
            <p>Email: hello@yaya.com</p>
            <p>Phone: +216 00 000 000</p>
            <p>Tunis, Tunisia</p>
        </div>

        <!-- Column 4 -->
        <div class="footer-col">
            <h4>Follow Us</h4>

            <div class="social-icons">
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>

    </div>

    <div class="footer-bottom">
        <p>© 2026 Yaya’s Caffee. All rights reserved.</p>
    </div>

</footer>

<!-- ========================================
     JAVASCRIPT
======================================= -->
<script>
  // ── Cart count ──
  function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    document.getElementById('cart-count').textContent = count;
  }

  // ── Form submission ──
  document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    const formData = new FormData(this);

    try {
      const response = await fetch('<?= BASE_URL ?>api/contact.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      const messageContainer = document.getElementById('messageContainer');
      messageContainer.innerHTML = '';

      if (result.success) {
        messageContainer.innerHTML = `
          <div class="message success">
            <i class="fas fa-check-circle"></i>
            ${result.message}
          </div>
        `;
        this.reset();
      } else {
        messageContainer.innerHTML = `
          <div class="message error">
            <i class="fas fa-exclamation-circle"></i>
            ${result.message}
          </div>
        `;
      }
    } catch (error) {
      const messageContainer = document.getElementById('messageContainer');
      messageContainer.innerHTML = `
        <div class="message error">
          <i class="fas fa-exclamation-circle"></i>
          An error occurred. Please try again later.
        </div>
      `;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    }
  });

  // ── Initialize ──
  updateCartCount();
  // Fix labels flottants
document.querySelectorAll('.input-group input, .input-group textarea').forEach(field => {
    // Au chargement
    if (field.value.trim() !== '') {
        field.nextElementSibling.classList.add('active');
    }
    
    // Quand on écrit
    field.addEventListener('input', function() {
        if (this.value.trim() !== '') {
            this.nextElementSibling.classList.add('active');
        } else {
            this.nextElementSibling.classList.remove('active');
        }
    });

    // Quand on quitte le champ
    field.addEventListener('blur', function() {
        if (this.value.trim() !== '') {
            this.nextElementSibling.classList.add('active');
        } else {
            this.nextElementSibling.classList.remove('active');
        }
    });
});
</script>

</body>
</html>