<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart — Yaya's Caffee</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fdfaf7; color: #2c1810; }
        
        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
            background: #9ba36a;
            padding: 0 40px;
            height: 75px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #2c1810;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 40px;
            list-style: none;
        }
        
        .nav-links a {
            color: #2c1810;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: opacity 0.3s;
        }
        
        .nav-links a:hover { opacity: 0.8; }
        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }
        
        .cart-icon {
            position: relative;
            font-size: 1.3rem;
            color: #2c1810;
            text-decoration: none;
            transition: transform 0.3s;
        }
        
        .cart-icon:hover { transform: scale(1.1); }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #d4a574;
            color: #2c1810;
            font-size: 0.7rem;
            font-weight: bold;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-signin {
            color: #2c1810;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .btn-signin:hover { opacity: 0.85; }
        
        /* Main */
        .container { max-width: 1000px; margin: 120px auto 40px; padding: 0 20px; }
        
        .cart-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .cart-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: #2c1810;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #f0ebe4;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .item-info { flex: 1; }
        .item-name { font-weight: 600; color: #2c1810; margin-bottom: 4px; }
        .item-price { color: #9ba36a; font-weight: 700; }
        .item-custom { font-size: 0.85rem; color: #999; margin-top: 4px; }
        
        .item-qty {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 20px;
        }
        
        .qty-btn {
            background: #f5f0eb;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .qty-btn:hover { background: #9ba36a; color: white; }
        
        .item-remove {
            background: #ffebee;
            border: none;
            color: #c62828;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .item-remove:hover { background: #f8d7da; }
        
        .empty-msg {
            text-align: center;
            color: #999;
            padding: 40px;
            font-size: 1.1rem;
        }
        
        .summary {
            background: #f5f0eb;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }
        
        .summary-row.total {
            border-top: 2px solid #ddd;
            padding-top: 12px;
            margin-top: 12px;
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c1810;
        }
        
        .checkout-form {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .checkout-form h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2c1810;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e8e0d5;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #9ba36a;
            background: white;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .checkout-btns {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn-checkout {
            flex: 1;
            padding: 14px 25px;
            background: #9ba36a;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-checkout:hover { background: #858b5a; }
        .btn-checkout:disabled { background: #ccc; cursor: not-allowed; }
        
        .btn-continue {
            flex: 1;
            padding: 14px 25px;
            background: #f5f0eb;
            color: #2c1810;
            border: 2px solid #e8e0d5;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-continue:hover { background: #e8dfd5; }
        
        .message {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .message.error { background: #ffebee; color: #c62828; }
        .message.success { background: #e8f5e9; color: #2e7d32; }
        
        /* Footer */
        .footer {
            background: #2c1810;
            color: #e6d5c3;
            padding: 80px 8% 30px;
            margin-top: 60px;
        }
        
        .footer-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 50px;
        }
        
        .footer-col { flex: 1; min-width: 200px; }
        .footer-logo { font-family: 'Playfair Display', serif; font-size: 1.8rem; margin-bottom: 20px; }
        .footer-col h4 { margin-bottom: 20px; font-size: 1rem; color: #b08968; }
        .footer-col p { font-size: 0.9rem; line-height: 1.8; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a { text-decoration: none; color: #e6d5c3; transition: 0.3s; }
        .footer-col ul li a:hover { color: #b08968; }
        
        .social-icons { display: flex; gap: 20px; margin-top: 15px; }
        .social-icons a { color: #e6d5c3; font-size: 1.2rem; transition: 0.3s; }
        .social-icons a:hover { color: #b08968; transform: translateY(-3px); }
        
        .footer-bottom { margin-top: 60px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; font-size: 0.85rem; }
        
        @media (max-width: 768px) {
            .cart-item { flex-direction: column; align-items: flex-start; gap: 15px; }
            .item-qty { margin-left: 0; }
            .checkout-btns { flex-direction: column; }
            .navbar { padding: 0 20px; flex-direction: column; height: auto; }
            .nav-links { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="logo">yaya's caffee</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="customize.html">Customize</a></li>
            <li><a href="dream.html">Our story</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <div class="nav-right">
            <a href="cart.php" class="cart-icon">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-count">0</span>
            </a>
            <?php if(isLoggedIn()): ?>
               <span style="color:#2c1810;font-weight:600;font-size:0.9rem;"> <?= htmlspecialchars($_SESSION['first_name']) ?> </span>
                <a href="api/signout.php" class="btn-signin">Sign Out</a>
            <?php else: ?>
                <a href="signin.html" class="btn-signin">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container">
        <!-- CART ITEMS -->
        <div class="cart-section">
            <h2>Your Cart</h2>
            <div id="cart-items"></div>
        </div>
        
        <!-- SUMMARY -->
        <div class="summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="subtotal">0.00 DT</span>
            </div>
            <div class="summary-row">
                <span>Tax (10%)</span>
                <span id="tax">0.00 DT</span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span id="total">0.00 DT</span>
            </div>
        </div>
        
        <!-- CHECKOUT FORM -->
        <div class="checkout-form">
            <h2>Order Validation</h2>
            <div id="message"></div>
            
            <form id="checkout-form">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="email" value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['email']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="tel" name="phone" id="phone" required placeholder="ex: +216 20 123 456">
                </div>
                
                <div class="form-group">
                    <label>Delivery Address *</label>
                    <textarea name="delivery_address" id="delivery_address" required></textarea>
                </div>
                
                <div class="checkout-btns">
                    <button type="button" class="btn-continue" onclick="window.location.href='menu.php'">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </button>
                    <button type="submit" class="btn-checkout" id="submit-btn">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Footer -->
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
                <p>Email: hello@yaya.com</p>
                <p>Phone: +216 00 000 000</p>
                <p>Tunis, Tunisia</p>
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
            <p>© 2026 Yaya's Caffee. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        // Charger le panier depuis localStorage
        function loadCart() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const container = document.getElementById('cart-items');
            
            if (cart.length === 0) {
                container.innerHTML = '<div class="empty-msg"><i class="fas fa-shopping-cart"></i><p style="margin-top: 10px;">Your cart is empty</p></div>';
                document.querySelector('.checkout-form').style.display = 'none';
                return;
            }
            
            document.querySelector('.checkout-form').style.display = 'block';
            container.innerHTML = '';
            
            cart.forEach((item, idx) => {
                const custom = item.customization ? `<div class="item-custom">${item.customization}</div>` : '';
                const el = document.createElement('div');
                el.className = 'cart-item';
                el.innerHTML = `
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-price">${item.price.toFixed(2)} DT each</div>
                        ${custom}
                    </div>
                    <div class="item-qty">
                        <button class="qty-btn" onclick="updateQty(${idx}, -1)">−</button>
                        <span style="min-width: 30px; text-align: center;">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQty(${idx}, 1)">+</button>
                    </div>
                    <div style="min-width: 80px; text-align: right; font-weight: 700; color: #2c1810;">
                        ${(item.price * item.quantity).toFixed(2)} DT
                    </div>
                    <button class="item-remove" onclick="removeItem(${idx})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                container.appendChild(el);
            });
            
            updateSummary();
        }
        
        // Mettre à jour quantité
        function updateQty(idx, delta) {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart[idx].quantity += delta;
            
            if (cart[idx].quantity <= 0) {
                cart.splice(idx, 1);
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }
        
        // Supprimer item
        function removeItem(idx) {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart.splice(idx, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }
        
        // Calculer le total
        function updateSummary() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.1;
            const total = subtotal + tax;
            
            document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' DT';
            document.getElementById('tax').textContent = tax.toFixed(2) + ' DT';
            document.getElementById('total').textContent = total.toFixed(2) + ' DT';
            
            // Mettre à jour compteur panier
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.querySelectorAll('.cart-count').forEach(el => el.textContent = count);
        }
        
        // Soumettre commande
        document.addEventListener('DOMContentLoaded', function() {
            loadCart();
            
            const form = document.getElementById('checkout-form');
            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const cart = JSON.parse(localStorage.getItem('cart')) || [];
                    if (cart.length === 0) {
                        showMessage('Cart is empty', 'error');
                        return;
                    }
                    
                    const email = document.getElementById('email').value.trim();
                    const phone = document.getElementById('phone').value.trim();
                    const delivery_address = document.getElementById('delivery_address').value.trim();
                    
                    if (!email || !phone || !delivery_address) {
                        showMessage('Please fill all fields', 'error');
                        return;
                    }
                    
                    // Validate phone number (Tunisian format)
                    const phoneRegex = /^(\+216|216)?[0-9]{8}$/;
                    if (!phoneRegex.test(phone.replace(/\s+/g, ''))) {
                        showMessage('Please enter a valid Tunisian phone number (e.g., +216 20 123 456)', 'error');
                        return;
                    }
                    
                    const btn = document.getElementById('submit-btn');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    
                    try {
                        const res = await fetch('api/order_create.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                items: cart,
                                email,
                                phone,
                                delivery_address
                            })
                        });
                        
                        const data = await res.json();
                        
                        if (data.success) {
                            showMessage('✅ Order placed successfully! Order #' + data.order_id, 'success');
                            localStorage.removeItem('cart');
                            setTimeout(() => {
                                window.location.href = 'index.php';
                            }, 2000);
                        } else {
                            showMessage(data.message || 'Order failed', 'error');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-check"></i> Place Order';
                        }
                    } catch (err) {
                        console.error(err);
                        showMessage('Server error. Please try again.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check"></i> Place Order';
                    }
                });
            }
        });
        
        // Message handler
        function showMessage(msg, type) {
            const el = document.getElementById('message');
            if (el) {
                el.className = 'message ' + type;
                el.textContent = msg;
            }
        }
    </script>
</body>
</html>