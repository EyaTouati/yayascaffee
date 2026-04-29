<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// ── Fetch all menu items grouped by category ──
$coffees = $pdo->query("SELECT * FROM menu_items WHERE category = 'coffee' ORDER BY is_best_seller DESC, name ASC")->fetchAll();
$foods   = $pdo->query("SELECT * FROM menu_items WHERE category = 'food'   ORDER BY is_best_seller DESC, name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu — Yaya's Caffee</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Poppins',sans-serif; background:#f5eee6; color:#2c1810; overflow-x:hidden; }

    /* ── NAVBAR ── */
    .navbar { position:fixed; top:0; left:0; right:0; z-index:1000; padding-bottom:45px; }
    .nav-inner { background:#9ba36a; display:flex; justify-content:space-between; align-items:center; padding:0 60px; height:75px; }
    .logo { font-family:'Playfair Display',serif; font-size:1.5rem; font-weight:700; color:#2c1810; text-decoration:none; }
    .nav-links { display:flex; gap:40px; list-style:none; }
    .nav-links a { color:#2c1810; text-decoration:none; font-weight:600; font-size:0.9rem; transition:all 0.3s; position:relative; }
    .nav-links a::after { content:''; position:absolute; bottom:-4px; left:0; width:0; height:2px; background:#2c1810; transition:width 0.3s; }
    .nav-links a:hover::after { width:100%; }
    .nav-right { display:flex; align-items:center; gap:25px; }
    .cart-icon { position:relative; font-size:1.3rem; color:#2c1810; text-decoration:none; transition:transform 0.3s; }
    .cart-icon:hover { transform:scale(1.1); }
    .cart-count { position:absolute; top:-8px; right:-10px; background:#d4a574; color:white; font-size:0.7rem; font-weight:bold; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
    .btn-signin { background:#2c1810; color:white; padding:10px 25px; border-radius:8px; font-weight:600; font-size:0.9rem; text-decoration:none; transition:all 0.3s; }
    .btn-signin:hover { background:#1a0f0a; }
    .navbar-wave { display:block; width:100%; height:45px; margin-top:-2px; }

    /* ── HERO ── */
    .menu-hero { text-align:center; padding:160px 20px 50px; background:linear-gradient(135deg,#2c1810 0%,#4a2c1a 100%); color:white; }
    .menu-hero h1 { font-family:'Playfair Display',serif; font-size:clamp(2.5rem,5vw,4rem); letter-spacing:4px; }
    .menu-hero h1 em { color:#9ba36a; font-style:italic; }
    .menu-subtitle { margin-top:15px; font-size:1.1rem; color:rgba(255,255,255,0.7); }

    /* ── TABS ── */
    .tabs-wrapper { background:white; position:sticky; top:75px; z-index:50; box-shadow:0 2px 15px rgba(0,0,0,0.08); }
    .tabs-container { display:flex; justify-content:center; max-width:500px; margin:0 auto; }
    .tab { flex:1; padding:18px 30px; border:none; background:none; font-family:'Poppins',sans-serif; font-size:0.95rem; font-weight:600; color:#888; cursor:pointer; border-bottom:3px solid transparent; transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:8px; }
    .tab:hover { color:#9ba36a; }
    .tab.active { color:#9ba36a; border-bottom-color:#9ba36a; }
    .tab .count { background:#f0ebe4; color:#888; padding:2px 8px; border-radius:10px; font-size:0.75rem; }
    .tab.active .count { background:#9ba36a; color:white; }

    /* ── SEARCH ── */
    .search-bar { padding:25px 8%; background:#f9f7f4; display:flex; justify-content:center; }
    .search-input { width:100%; max-width:500px; padding:13px 20px 13px 48px; border:1.5px solid #e0d5c7; border-radius:30px; font-family:'Poppins',sans-serif; font-size:0.95rem; outline:none; background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") no-repeat 18px center; transition:all 0.3s; }
    .search-input:focus { border-color:#9ba36a; box-shadow:0 0 0 3px rgba(155,163,106,0.15); }

    /* ── MENU SECTION ── */
    .menu-section { padding:50px 8% 100px; }
    .tab-content { display:none; }
    .tab-content.active { display:block; }

    /* ── GRID ── */
    .menu-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:30px; max-width:1200px; margin:0 auto; }

    /* ── CARD ── */
    .menu-card { background:white; border-radius:20px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.07); transition:all 0.4s; display:flex; flex-direction:column; }
    .menu-card:hover { transform:translateY(-8px); box-shadow:0 15px 40px rgba(0,0,0,0.13); }
    .card-image { position:relative; height:220px; overflow:hidden; }
    .card-image img { width:100%; height:100%; object-fit:cover; transition:transform 0.5s; }
    .menu-card:hover .card-image img { transform:scale(1.08); }
    .no-image { width:100%; height:100%; background:linear-gradient(135deg,#f0ebe4,#e8e0d5); display:flex; align-items:center; justify-content:center; font-size:3rem; color:#ccc; }
    .badge { position:absolute; top:14px; right:14px; padding:5px 13px; border-radius:20px; font-size:0.72rem; font-weight:700; }
    .badge.best-seller { background:#9ba36a; color:white; }
    .card-category-tag { position:absolute; bottom:14px; left:14px; background:rgba(44,24,16,0.75); color:white; padding:4px 12px; border-radius:15px; font-size:0.72rem; font-weight:600; }
    .card-body { padding:22px; flex:1; display:flex; flex-direction:column; }
    .card-body h3 { font-family:'Playfair Display',serif; font-size:1.35rem; color:#2c1810; margin-bottom:8px; }
    .card-body p { font-size:0.88rem; color:#6b5b52; line-height:1.6; margin-bottom:14px; flex:1; }
    .nutrition { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
    .pill { background:#f5f0eb; color:#6b5b52; padding:4px 12px; border-radius:12px; font-size:0.75rem; font-weight:600; }
    .pill.protein { background:#e8f5e9; color:#2e7d32; }
    .card-footer { display:flex; justify-content:space-between; align-items:center; padding-top:16px; border-top:1px solid #f0ebe4; }
    .price { font-family:'Playfair Display',serif; font-size:1.7rem; font-weight:700; color:#9ba36a; }
    .card-actions { display:flex; gap:8px; }
    .btn-cart { padding:10px 18px; background:#9ba36a; color:white; border:none; border-radius:9px; font-size:0.82rem; font-weight:600; cursor:pointer; transition:all 0.3s; display:flex; align-items:center; gap:6px; font-family:'Poppins',sans-serif; }
    .btn-cart:hover { background:#8a945f; transform:translateY(-2px); }
    .btn-cart.added { background:#2e7d32; }
    .empty-state { text-align:center; padding:80px 20px; color:#aaa; grid-column:1/-1; }
    .empty-state i { font-size:3rem; margin-bottom:15px; display:block; }

    /* ── TOAST ── */
    .toast { position:fixed; bottom:30px; right:30px; background:#2c1810; color:white; padding:14px 22px; border-radius:12px; font-size:0.88rem; font-weight:500; box-shadow:0 8px 25px rgba(0,0,0,0.2); display:flex; align-items:center; gap:10px; transform:translateY(100px); opacity:0; transition:all 0.4s cubic-bezier(0.175,0.885,0.32,1.275); z-index:9999; }
    .toast.show { transform:translateY(0); opacity:1; }
    .toast i { color:#9ba36a; }

    /* ── FOOTER ── */
    .footer { background:#2c1810; color:#e6d5c3; padding:80px 8% 30px; }
    .footer-container { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:50px; max-width:1200px; margin:0 auto 50px; }
    .footer-logo { font-family:'Playfair Display',serif; font-size:1.8rem; color:#9ba36a; margin-bottom:15px; }
    .footer-col h4 { color:#9ba36a; margin-bottom:15px; }
    .footer-col p { font-size:0.9rem; line-height:1.8; color:#b8a691; }
    .footer-col ul { list-style:none; }
    .footer-col ul li { margin-bottom:10px; }
    .footer-col ul li a { color:#b8a691; text-decoration:none; font-size:0.9rem; transition:color 0.3s; }
    .footer-col ul li a:hover { color:#9ba36a; }
    .social-icons { display:flex; gap:15px; margin-top:12px; }
    .social-icons a { width:38px; height:38px; background:rgba(155,163,106,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#9ba36a; transition:all 0.3s; }
    .social-icons a:hover { background:#9ba36a; color:white; transform:translateY(-3px); }
    .footer-bottom { text-align:center; border-top:1px solid rgba(255,255,255,0.1); padding-top:25px; font-size:0.85rem; color:#888; }

    @media(max-width:968px) {
      .nav-inner { padding:0 20px; }
      .nav-links { display:none; }
      .menu-grid { grid-template-columns:1fr; }
      .menu-section { padding:40px 5% 80px; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.html" class="logo">Yaya's Caffee</a>
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
        <span style="color:#2c1810;font-weight:600;font-size:0.9rem;">
           <?= htmlspecialchars($_SESSION['first_name']) ?> 
        </span>
        <?php if(isAdmin()): ?>
          <a href="<?= BASE_URL ?>admin/dashboard.php" class="btn-signin" style="background:#9ba36a;">
            <i class="fas fa-cog"></i> Admin
          </a>
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
<div class="menu-hero">
  <h1>Our <em>Menu</em></h1>
  <p class="menu-subtitle">Premium Coffee & Conscious Eating</p>
</div>

<!-- TABS -->
<div class="tabs-wrapper">
  <div class="tabs-container">
    <button class="tab active" onclick="switchTab('coffee', this)">
      ☕ Coffee <span class="count"><?= count($coffees) ?></span>
    </button>
    <button class="tab" onclick="switchTab('food', this)">
      🥗 Food <span class="count"><?= count($foods) ?></span>
    </button>
  </div>
</div>

<!-- SEARCH -->
<div class="search-bar">
  <input type="text" class="search-input" id="search-input"
    placeholder="Search menu items…" oninput="filterCards(this.value)">
</div>

<!-- MENU -->
<section class="menu-section">

  <!-- COFFEE TAB -->
  <div class="tab-content active" id="tab-coffee">
    <div class="menu-grid">
      <?php if(empty($coffees)): ?>
        <div class="empty-state">
          <i class="fas fa-mug-hot"></i>No coffee items yet.
        </div>
      <?php else: ?>
        <?php foreach($coffees as $item): ?>
        <div class="menu-card" data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>">
          <div class="card-image">
            <?php if($item['image_path']): ?>
              <img src="<?= BASE_URL . htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="no-image">☕</div>
            <?php endif; ?>
            <?php if($item['is_best_seller']): ?><span class="badge best-seller">⭐ Best Seller</span><?php endif; ?>
            <span class="card-category-tag">☕ Coffee</span>
          </div>
          <div class="card-body">
            <h3><?= htmlspecialchars($item['name']) ?></h3>
            <p><?= htmlspecialchars($item['description'] ?: 'A perfect cup crafted with premium beans.') ?></p>
            <?php if($item['calories'] > 0 || $item['protein'] > 0): ?>
            <div class="nutrition">
              <?php if($item['calories'] > 0): ?><span class="pill">🔥 <?= $item['calories'] ?> cal</span><?php endif; ?>
              <?php if($item['protein'] > 0): ?><span class="pill protein">💪 <?= $item['protein'] ?>g protein</span><?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="card-footer">
              <span class="price"><?= number_format($item['price'],2) ?> DT</span>
              <button class="btn-cart" onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'],ENT_QUOTES) ?>', <?= $item['price'] ?>, this)">
                <i class="fas fa-cart-plus"></i> Add
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- FOOD TAB -->
  <div class="tab-content" id="tab-food">
    <div class="menu-grid">
      <?php if(empty($foods)): ?>
        <div class="empty-state">
          <i class="fas fa-utensils"></i>No food items yet.
        </div>
      <?php else: ?>
        <?php foreach($foods as $item): ?>
        <div class="menu-card" data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>">
          <div class="card-image">
            <?php if($item['image_path']): ?>
              <img src="<?= BASE_URL . htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="no-image">🥗</div>
            <?php endif; ?>
            <?php if($item['is_best_seller']): ?><span class="badge best-seller">⭐ Best Seller</span><?php endif; ?>
            <span class="card-category-tag">🥗 Food</span>
          </div>
          <div class="card-body">
            <h3><?= htmlspecialchars($item['name']) ?></h3>
            <p><?= htmlspecialchars($item['description'] ?: 'Fresh and nutritious, made with care.') ?></p>
            <?php if($item['calories'] > 0 || $item['protein'] > 0): ?>
            <div class="nutrition">
              <?php if($item['calories'] > 0): ?><span class="pill">🔥 <?= $item['calories'] ?> cal</span><?php endif; ?>
              <?php if($item['protein'] > 0): ?><span class="pill protein">💪 <?= $item['protein'] ?>g protein</span><?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="card-footer">
              <span class="price"><?= number_format($item['price'],2) ?> DT</span>
              <button class="btn-cart" onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'],ENT_QUOTES) ?>', <?= $item['price'] ?>, this)">
                <i class="fas fa-cart-plus"></i> Add
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</section>

<!-- TOAST -->
<div class="toast" id="toast">
  <i class="fas fa-check-circle"></i>
  <span id="toast-msg"></span>
</div>

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
        <li><a href="index.html">Home</a></li>
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

<script>
// Tab switching 
function switchTab(tab, btn) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  btn.classList.add('active');
  document.getElementById('search-input').value = '';
}

//  Search
function filterCards(query) {
  query = query.toLowerCase();
  document.querySelectorAll('.tab-content.active .menu-card').forEach(card => {
    card.style.display = card.dataset.name.includes(query) ? '' : 'none';
  });
}

//  Cart (localStorage)
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

  btn.classList.add('added');
  btn.innerHTML = '<i class="fas fa-check"></i> Added!';
  setTimeout(() => {
    btn.classList.remove('added');
    btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add';
  }, 1500);

  showToast(name + ' added to cart!');
}

function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2500);
}

updateCartCount();
</script>

</body>
</html>