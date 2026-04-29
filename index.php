<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Best sellers depuis la base
$best_coffees = $pdo->query("SELECT * FROM menu_items WHERE category='coffee' AND is_best_seller=1 ORDER BY name LIMIT 3")->fetchAll();
$best_foods   = $pdo->query("SELECT * FROM menu_items WHERE category='food'   AND is_best_seller=1 ORDER BY name LIMIT 3")->fetchAll();

// Si pas assez de best sellers, compléter avec les autres
if(count($best_coffees) < 3) {
    $ids   = array_column($best_coffees, 'id') ?: [0];
    $ph    = implode(',', array_fill(0, count($ids), '?'));
    $extra = $pdo->prepare("SELECT * FROM menu_items WHERE category='coffee' AND id NOT IN($ph) ORDER BY id LIMIT ?");
    $extra->execute([...$ids, 3 - count($best_coffees)]);
    $best_coffees = array_merge($best_coffees, $extra->fetchAll());
}
if(count($best_foods) < 3) {
    $ids   = array_column($best_foods, 'id') ?: [0];
    $ph    = implode(',', array_fill(0, count($ids), '?'));
    $extra = $pdo->prepare("SELECT * FROM menu_items WHERE category='food' AND id NOT IN($ph) ORDER BY id LIMIT ?");
    $extra->execute([...$ids, 3 - count($best_foods)]);
    $best_foods = array_merge($best_foods, $extra->fetchAll());
}

// Helper: render a seller card
function sellerCard(array $item, string $cat): string {
    $img     = $item['image_path']
               ? '<img src="'.BASE_URL.htmlspecialchars($item['image_path']).'" alt="'.htmlspecialchars($item['name']).'">'
               : '<div style="width:100%;height:100%;background:#f0ebe4;display:flex;align-items:center;justify-content:center;font-size:3rem;">'.($cat==='coffee'?'☕':'🥗').'</div>';
    $badge   = $item['is_best_seller'] ? '<div class="card-badge">Best Seller</div>' : '';
    $cal     = $item['calories'] > 0 ? '<span>'.$item['calories'].' cal</span><span>•</span>' : '';
    $protein = $item['protein']  > 0 ? '<span>'.$item['protein'].'g protein</span>' : '';
    return '
    <div class="seller-card">
      <div class="card-image">'.$img.$badge.'</div>
      <div class="card-content">
        <h3>'.htmlspecialchars($item['name']).'</h3>
        <p>'.htmlspecialchars($item['description'] ?: ($cat==='coffee'?'Premium coffee crafted with care.':'Fresh and nutritious, made with love.')).'</p>
        <div class="card-meta">
          <span class="card-price">'.number_format($item['price'],2).' DT</span>
          <div class="card-nutrition">'.$cal.$protein.'</div>
        </div>
        <div class="card-actions">
          <button class="btn-buy" onclick="addToCart('.(int)$item['id'].', \''.htmlspecialchars($item['name'],ENT_QUOTES).'\', '.(float)$item['price'].', this)">
            <i class="fas fa-shopping-cart"></i> Add to Cart
          </button>
        </div>
      </div>
    </div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Yaya's Caffee - Premium Coffee & Conscious Eating</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar" id="navbar">
    <div class="nav-inner">
      <a href="index.php" class="logo">Yaya's Caffee</a>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="menu.php">Menu</a></li>
        <li><a href="dream.html">Our Story</a></li>
        <li><a href="contact.php">Contact</a></li>
      </ul>
      <div class="nav-right">
        <a href="cart.php" class="cart-icon">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="cart-count" id="cart-count">0</span>
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
    <svg class="navbar-wave" viewBox="0 0 1440 45" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <path d="M0,0 C120,45 240,45 360,22 S600,0 720,22 S960,45 1080,22 S1320,0 1440,22 L1440,0 L0,0 Z" fill="#9ba36a"/>
    </svg>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-content">
      <h1>Premium Coffee.<br><em>Conscious Eating.</em></h1>
      <p class="hero-subtitle">A place where coffee becomes a ritual, and every moment feels warmer.</p>
      <div class="hero-buttons">
        <a href="customize.html" class="btn-primary"><i class="fas fa-coffee"></i> Customize Your Coffee</a>
        <a href="menu.php" class="btn-secondary">Check the Menu <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>
  </section>

  <!-- VALUE PROPS -->
  <section class="value-props">
    <div class="section-header">
      <span class="section-label">Why Choose Us</span>
      <h2>What Makes Us Different</h2>
    </div>
    <div class="props-grid">
      <div class="prop-card"><div class="prop-icon"><i class="fas fa-seedling"></i></div><h3>Premium Coffee Beans</h3><p>Ethically sourced from the world's finest coffee regions. Single-origin beans roasted to perfection.</p></div>
      <div class="prop-card"><div class="prop-icon"><i class="fas fa-leaf"></i></div><h3>Farm-Fresh Ingredients</h3><p>Locally sourced, organic produce delivered daily. High-protein, nutrient-dense meals for wellness.</p></div>
      <div class="prop-card"><div class="prop-icon"><i class="fas fa-palette"></i></div><h3>Coffee Customization</h3><p>Build your perfect cup in 4 easy steps. Choose beans, brew method, add-ons, and size.</p></div>
      <div class="prop-card"><div class="prop-icon"><i class="fas fa-chart-line"></i></div><h3>Nutrition Transparency</h3><p>Complete nutrition dashboard for every item. Calories, macros, allergens — all clearly displayed.</p></div>
    </div>
  </section>

  <!-- BEST SELLERS — dynamic from DB -->
  <section class="best-sellers">
    <div class="section-header">
      <span class="section-label">Popular Choices</span>
      <h2>Our Best Sellers</h2>
    </div>

    <div class="tabs-container">
      <a href="#coffee-tab" class="tab active">☕ Coffee</a>
      <a href="#food-tab"   class="tab">🥗 Food</a>
    </div>

    <!-- Coffee -->
    <div class="tab-content" id="coffee-tab">
      <div class="best-sellers-grid">
        <?php foreach($best_coffees as $item): echo sellerCard($item,'coffee'); endforeach; ?>
        <?php if(empty($best_coffees)): ?>
          <p style="color:#aaa;text-align:center;padding:40px;grid-column:1/-1;">No coffee items yet — add some from the Admin Dashboard!</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Food -->
    <div class="tab-content" id="food-tab" style="display:none;">
      <div class="best-sellers-grid">
        <?php foreach($best_foods as $item): echo sellerCard($item,'food'); endforeach; ?>
        <?php if(empty($best_foods)): ?>
          <p style="color:#aaa;text-align:center;padding:40px;grid-column:1/-1;">No food items yet — add some from the Admin Dashboard!</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- OUR STORY -->
  <section class="our-story">
    <div class="story-container">
      <div class="story-image-wrapper">
        <div class="story-decoration"></div>
        <div class="story-image"><img src="images/yaya.jpeg" alt="Yaya's Coffee Story"></div>
      </div>
      <div class="story-content">
        <span class="story-label">Our Story</span>
        <h2>More Than Just Coffee</h2>
        <p class="story-lead">Yaya's Caffee was born from quiet mornings and warm conversations.</p>
        <p class="story-body">We believe coffee should be a ritual — a pause in your day, a moment to reconnect with yourself and others. Every cup tells a story, from the farmers who grew the beans to the barista who crafted your drink.</p>
        <div class="story-stats">
          <div class="stat-item"><span class="stat-number">5000+</span><span class="stat-label">Happy Customers</span></div>
          <div class="stat-item"><span class="stat-number">100%</span><span class="stat-label">Organic Beans</span></div>
          <div class="stat-item"><span class="stat-number">3</span><span class="stat-label">Years of Excellence</span></div>
        </div>
        <a href="dream.html" class="story-btn">Discover Our Journey <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>
  </section>

  <!-- FINAL CTA -->
  <section class="final-cta">
    <div class="cta-overlay"></div>
    <div class="cta-content">
      <h2>Ready To Experience Yaya's Caffee?</h2>
      <p>Step into a world of warmth, aroma, and slow moments. Your perfect cup is waiting.</p>
      <div class="cta-buttons">
        <a href="menu.php" class="cta-btn primary"><i class="fas fa-utensils"></i> View Menu</a>
        <a href="contact.php" class="cta-btn secondary"><i class="fas fa-map-marker-alt"></i> Visit Us</a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-col">
        <h3 class="footer-logo">Yaya's Caffee</h3>
        <p>A place where coffee becomes a ritual, and every moment feels warmer.</p>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="menu.php">Menu</a></li>
          <li><a href="dream.html">Our Story</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <p><i class="fas fa-envelope"></i> hello@yaya.com</p>
        <p><i class="fas fa-phone"></i> +216 00 000 000</p>
        <p><i class="fas fa-map-marker-alt"></i> Tunis, Tunisia</p>
      </div>
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
      <p>© 2026 Yaya's Caffee. All rights reserved. Made with ❤️ in Tunisia</p>
    </div>
  </footer>

  <!-- TOAST -->
  <div id="toast" style="position:fixed;bottom:30px;right:30px;background:#2c1810;color:white;padding:14px 22px;border-radius:12px;font-size:0.88rem;font-weight:500;box-shadow:0 8px 25px rgba(0,0,0,0.2);display:flex;align-items:center;gap:10px;transform:translateY(100px);opacity:0;transition:all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);z-index:9999;">
    <i class="fas fa-check-circle" style="color:#9ba36a;"></i>
    <span id="toast-msg"></span>
  </div>

  <script>
    // Tabs
    document.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        document.getElementById(this.getAttribute('href').substring(1)).style.display = 'block';
      });
    });

    // Cart
    function getCart()      { return JSON.parse(localStorage.getItem('cart') || '[]'); }
    function saveCart(cart) { localStorage.setItem('cart', JSON.stringify(cart)); updateCartCount(); }
    function updateCartCount() {
      const total = getCart().reduce((s,i) => s + i.quantity, 0);
      document.getElementById('cart-count').textContent = total;
    }
    function addToCart(id, name, price, btn) {
      const cart  = getCart();
      const index = cart.findIndex(i => i.id === id);
      if (index > -1) cart[index].quantity++;
      else cart.push({ id, name, price, quantity: 1 });
      saveCart(cart);
      btn.innerHTML = '<i class="fas fa-check"></i> Added!';
      setTimeout(() => { btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart'; }, 1500);
      const t = document.getElementById('toast');
      document.getElementById('toast-msg').textContent = name + ' added to cart!';
      t.style.transform = 'translateY(0)'; t.style.opacity = '1';
      setTimeout(() => { t.style.transform = 'translateY(100px)'; t.style.opacity = '0'; }, 2500);
    }
    updateCartCount();
  </script>

</body>
</html>