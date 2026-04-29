<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

// ── Stats ──
$total_users    = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_orders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue  = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$total_items    = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$total_contacts = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$unread_contacts = $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'unread'")->fetchColumn();

// ── Recent orders pour Overview (last 10) ──
$recent_orders = $pdo->query("
    SELECT o.id, o.total, o.status, o.created_at,
           u.first_name, u.last_name, u.email
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT 10
")->fetchAll();

// ── Toutes les commandes pour panel Orders ──
$all_orders = $pdo->query("
    SELECT o.id, o.total, o.status, o.created_at,
           u.first_name, u.last_name, u.email
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
")->fetchAll();

// ── Recent contacts (last 10) ──
$recent_contacts = $pdo->query("
    SELECT id, name, email, subject, message, status, created_at
    FROM contacts
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll();

// ── Menu items ──
$menu_items = $pdo->query("SELECT * FROM menu_items ORDER BY category, name")->fetchAll();

// ── Flash message ──
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — Yaya's Caffee</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Poppins',sans-serif; background:#f4f0eb; color:#2c1810; display:flex; min-height:100vh; }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 260px;
      background: #2c1810;
      color: #e6d5c3;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0; bottom: 0;
      z-index: 100;
      overflow-y: auto;
    }
    .sidebar-logo {
      font-family:'Playfair Display',serif;
      font-size:1.4rem;
      color:#9ba36a;
      padding: 30px 25px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .sidebar-logo span { display:block; font-size:0.75rem; color:#888; font-family:'Poppins',sans-serif; margin-top:4px; }
    .sidebar-nav { padding: 20px 0; flex:1; }
    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 13px 25px;
      color: #b8a691;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.2s;
      cursor: pointer;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
    }
    .nav-item:hover, .nav-item.active {
      background: rgba(155,163,106,0.15);
      color: #9ba36a;
    }
    .nav-item i { width: 18px; text-align:center; }
    .nav-section {
      font-size: 0.7rem;
      color: #666;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 18px 25px 8px;
    }
    .sidebar-bottom {
      padding: 20px 25px;
      border-top: 1px solid rgba(255,255,255,0.08);
    }
    .admin-info { font-size:0.82rem; color:#888; margin-bottom:12px; }
    .admin-info strong { display:block; color:#e6d5c3; margin-bottom:2px; }
    .btn-logout {
      display:flex; align-items:center; gap:8px;
      background: rgba(255,100,100,0.15);
      color: #ff8080;
      padding: 10px 15px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 600;
      transition: all 0.3s;
      border: none; cursor: pointer; width:100%;
    }
    .btn-logout:hover { background: rgba(255,100,100,0.25); }

    /* ── MAIN ── */
    .main { margin-left:260px; flex:1; padding:35px 40px; }

    /* ── TOP BAR ── */
    .topbar {
      display:flex; justify-content:space-between; align-items:center;
      margin-bottom: 35px;
    }
    .topbar h1 { font-family:'Playfair Display',serif; font-size:1.8rem; }
    .topbar-right { display:flex; align-items:center; gap:15px; }
    .badge-pending {
      background:#ff6b35; color:white;
      padding:6px 14px; border-radius:20px;
      font-size:0.8rem; font-weight:600;
    }

    /* ── FLASH MESSAGE ── */
    .flash {
      padding:14px 20px; border-radius:10px;
      margin-bottom:25px; font-size:0.9rem; font-weight:500;
      display:flex; align-items:center; gap:10px;
    }
    .flash.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    .flash.error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }

    /* ── STATS CARDS ── */
    .stats-grid {
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap:20px;
      margin-bottom:35px;
    }
    .stat-card {
      background:white;
      border-radius:16px;
      padding:25px;
      display:flex;
      align-items:center;
      gap:18px;
      box-shadow:0 2px 12px rgba(0,0,0,0.06);
      transition: transform 0.2s;
    }
    .stat-card:hover { transform:translateY(-3px); }
    .stat-icon {
      width:55px; height:55px;
      border-radius:14px;
      display:flex; align-items:center; justify-content:center;
      font-size:1.4rem;
      flex-shrink:0;
    }
    .stat-icon.green  { background:#e8f5e9; color:#2e7d32; }
    .stat-icon.amber  { background:#fff8e1; color:#f57f17; }
    .stat-icon.blue   { background:#e3f2fd; color:#1565c0; }
    .stat-icon.red    { background:#fce4ec; color:#c62828; }
    .stat-icon.olive  { background:#f1f8e9; color:#558b2f; }
    .stat-icon.purple { background:#f3e5f5; color:#7b1fa2; }
    .stat-info { flex:1; }
    .stat-value { font-size:1.8rem; font-weight:700; color:#2c1810; line-height:1; }
    .stat-label { font-size:0.8rem; color:#888; margin-top:4px; }

    /* ── SECTION TABS ── */
    .section-tabs {
      display:flex; gap:8px; margin-bottom:25px;
      border-bottom:2px solid #e8e0d5;
      padding-bottom:0;
    }
    .stab {
      padding:10px 22px;
      border:none; background:none;
      font-family:'Poppins',sans-serif;
      font-size:0.9rem; font-weight:600;
      color:#888; cursor:pointer;
      border-bottom:3px solid transparent;
      margin-bottom:-2px;
      transition:all 0.2s;
    }
    .stab.active { color:#9ba36a; border-bottom-color:#9ba36a; }
    .stab:hover  { color:#9ba36a; }

    /* ── PANEL ── */
    .panel { display:none; }
    .panel.active { display:block; }

    /* ── TABLE ── */
    .table-wrap {
      background:white;
      border-radius:16px;
      box-shadow:0 2px 12px rgba(0,0,0,0.06);
      overflow:hidden;
    }
    .table-header {
      display:flex; justify-content:space-between; align-items:center;
      padding:20px 25px;
      border-bottom:1px solid #f0ebe4;
    }
    .table-header h3 { font-size:1.05rem; }
    table { width:100%; border-collapse:collapse; }
    th {
      background:#f9f7f4; padding:13px 18px;
      text-align:left; font-size:0.8rem;
      font-weight:600; color:#888;
      text-transform:uppercase; letter-spacing:0.5px;
    }
    td { padding:14px 18px; border-bottom:1px solid #f5f0eb; font-size:0.88rem; vertical-align:middle; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:#fdfaf7; }

    /* status badges */
    .status {
      padding:4px 12px; border-radius:20px;
      font-size:0.75rem; font-weight:600; text-transform:capitalize;
    }
    .status.pending   { background:#fff3cd; color:#856404; }
    .status.confirmed { background:#d1ecf1; color:#0c5460; }
    .status.ready     { background:#d4edda; color:#155724; }
    .status.delivered { background:#e2e3e5; color:#383d41; }
    .status.cancelled { background:#f8d7da; color:#721c24; }

    /* ── ADD MENU FORM ── */
    .form-card {
      background:white;
      border-radius:16px;
      padding:30px;
      box-shadow:0 2px 12px rgba(0,0,0,0.06);
      margin-bottom:30px;
    }
    .form-card h3 { margin-bottom:22px; font-size:1.1rem; }
    .form-grid {
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:18px;
    }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-group label { font-size:0.82rem; font-weight:600; color:#555; }
    .form-group input,
    .form-group select,
    .form-group textarea {
      padding:11px 14px;
      border:1.5px solid #e8e0d5;
      border-radius:9px;
      font-family:'Poppins',sans-serif;
      font-size:0.9rem;
      color:#2c1810;
      outline:none;
      transition:border-color 0.2s;
      background:#fdfaf7;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { border-color:#9ba36a; background:white; }
    .form-group textarea { resize:vertical; min-height:80px; }
    .checkbox-group {
      display:flex; align-items:center; gap:10px;
      padding:11px 14px;
      border:1.5px solid #e8e0d5;
      border-radius:9px;
      background:#fdfaf7;
    }
    .checkbox-group input[type="checkbox"] {
      width:18px; height:18px;
      accent-color:#9ba36a; padding:0; border:none;
    }
    .btn-add {
      background:#9ba36a; color:white;
      padding:12px 28px;
      border:none; border-radius:10px;
      font-family:'Poppins',sans-serif;
      font-weight:600; font-size:0.9rem;
      cursor:pointer; transition:all 0.3s;
      display:inline-flex; align-items:center; gap:8px;
      margin-top:5px;
    }
    .btn-add:hover { background:#8a945f; transform:translateY(-2px); }

    /* ── MENU ITEMS TABLE actions ── */
    .btn-edit {
      background:#fff3cd; color:#856404;
      border:none; padding:6px 14px;
      border-radius:7px; cursor:pointer;
      font-size:0.8rem; font-weight:600;
      transition:all 0.2s;
    }
    .btn-edit:hover { background:#ffc107; color:white; }
    .btn-delete {
      background:#f8d7da; color:#721c24;
      border:none; padding:6px 14px;
      border-radius:7px; cursor:pointer;
      font-size:0.8rem; font-weight:600;
      transition:all 0.2s; margin-left:6px;
    }
    .btn-delete:hover { background:#dc3545; color:white; }
    .best-seller-badge {
      background:#9ba36a; color:white;
      padding:3px 10px; border-radius:12px;
      font-size:0.72rem; font-weight:700;
    }
    .img-thumb {
      width:48px; height:48px;
      border-radius:8px; object-fit:cover;
    }
    .img-placeholder {
      width:48px; height:48px;
      border-radius:8px;
      background:#f0ebe4;
      display:flex; align-items:center; justify-content:center;
      color:#ccc; font-size:1.2rem;
    }

    /* ── ORDER STATUS UPDATE ── */
    .select-status {
      padding:5px 10px;
      border:1.5px solid #e0d5c7;
      border-radius:7px;
      font-size:0.82rem;
      font-family:'Poppins',sans-serif;
      background:#fdfaf7;
      cursor:pointer;
    }
    .btn-update-status {
      background:#9ba36a; color:white;
      border:none; padding:6px 12px;
      border-radius:7px; cursor:pointer;
      font-size:0.8rem; font-weight:600;
      margin-left:6px; transition:all 0.2s;
    }
    .btn-update-status:hover { background:#8a945f; }

    /* ── CONTACT CARDS ── */
    .contact-card {
      background:white;
      border-radius:16px;
      padding:25px;
      margin-bottom:20px;
      box-shadow:0 2px 12px rgba(0,0,0,0.06);
      border-left:4px solid #9ba36a;
    }
    .contact-card[data-status="unread"] { border-left-color:#ff6b35; }
    .contact-card[data-status="read"] { border-left-color:#17a2b8; }
    .contact-card[data-status="replied"] { border-left-color:#28a745; }

    .contact-header {
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      margin-bottom:15px;
    }
    .contact-info h4 {
      margin:0 0 8px 0;
      color:#2c1810;
      font-size:1.1rem;
    }
    .contact-info p {
      margin:3px 0;
      font-size:0.85rem;
      color:#666;
    }
    .contact-meta {
      text-align:right;
    }
    .contact-meta small {
      display:block;
      color:#888;
      font-size:0.75rem;
      margin-top:5px;
    }
    .contact-message {
      background:#f8f5f0;
      padding:15px;
      border-radius:8px;
      margin-bottom:15px;
      color:#555;
      line-height:1.5;
    }
    .contact-actions {
      display:flex;
      gap:10px;
    }
    .btn-reply {
      background:#9ba36a;
      color:white;
      border:none;
      padding:8px 16px;
      border-radius:6px;
      cursor:pointer;
      font-size:0.85rem;
      font-weight:600;
      transition:all 0.2s;
    }
    .btn-reply:hover { background:#8a945f; }
    .btn-mark-read {
      background:#17a2b8;
      color:white;
      border:none;
      padding:8px 16px;
      border-radius:6px;
      cursor:pointer;
      font-size:0.85rem;
      font-weight:600;
      transition:all 0.2s;
    }
    .btn-mark-read:hover { background:#138496; }

    /* ── EDIT MODAL ── */
    .modal-overlay {
      display:none; position:fixed; inset:0;
      background:rgba(0,0,0,0.5);
      z-index:1000; align-items:center; justify-content:center;
    }
    .modal-overlay.open { display:flex; }
    .modal {
      background:white; border-radius:18px;
      padding:35px; width:90%; max-width:600px;
      max-height:90vh; overflow-y:auto;
      box-shadow:0 20px 60px rgba(0,0,0,0.3);
    }
    .modal-header {
      display:flex; justify-content:space-between; align-items:center;
      margin-bottom:25px;
    }
    .modal-header h3 { font-size:1.2rem; }
    .btn-close-modal {
      background:none; border:none;
      font-size:1.4rem; cursor:pointer;
      color:#888; transition:color 0.2s;
    }
    .btn-close-modal:hover { color:#2c1810; }

    /* ── RESPONSIVE ── */
    @media(max-width:1024px) {
      .sidebar { width:220px; }
      .main { margin-left:220px; padding:25px; }
      .form-grid { grid-template-columns:1fr; }
    }
    @media(max-width:768px) {
      .sidebar { display:none; }
      .main { margin-left:0; padding:20px; }
      .stats-grid { grid-template-columns:1fr 1fr; }
    }
  </style>
</head>
<body>

<!-- 
     SIDEBAR
 -->
<aside class="sidebar">
  <div class="sidebar-logo">
    Yaya's Caffee
    <span>Admin Panel</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">Main</div>
    <button class="nav-item active" onclick="showPanel('overview')">
      <i class="fas fa-chart-pie"></i> Overview
    </button>
    <button class="nav-item" onclick="showPanel('menu')">
      <i class="fas fa-utensils"></i> Menu Management
    </button>
    <button class="nav-item" onclick="showPanel('orders')">
      <i class="fas fa-shopping-bag"></i> Orders
      <?php if($pending_orders > 0): ?>
        <span style="background:#ff6b35;color:white;padding:2px 8px;border-radius:10px;font-size:0.7rem;margin-left:auto;"><?= $pending_orders ?></span>
      <?php endif; ?>
    </button>
    <button class="nav-item" onclick="showPanel('users')">
      <i class="fas fa-users"></i> Users
    </button>
    <button class="nav-item" onclick="showPanel('contacts')">
      <i class="fas fa-envelope"></i> Contacts
      <?php if($unread_contacts > 0): ?>
        <span style="background:#ff6b35;color:white;padding:2px 8px;border-radius:10px;font-size:0.7rem;margin-left:auto;"><?= $unread_contacts ?></span>
      <?php endif; ?>
    </button>

    <div class="nav-section">Site</div>
    <a href="<?= BASE_URL ?>index.php" class="nav-item" target="_blank">
      <i class="fas fa-external-link-alt"></i> View Website
    </a>
  </nav>

  <div class="sidebar-bottom">
    <div class="admin-info">
      <strong><?= htmlspecialchars($_SESSION['first_name']) ?></strong>
      <?= htmlspecialchars($_SESSION['email']) ?>
    </div>
    <a href="<?= BASE_URL ?>api/signout.php" class="btn-logout">
      <i class="fas fa-sign-out-alt"></i> Sign Out
    </a>
  </div>
</aside>

<!--  MAIN CONTENT -->
<main class="main">

  <!-- Top Bar -->
  <div class="topbar">
    <div>
      <h1>Dashboard</h1>
      <p style="color:#888;font-size:0.85rem;margin-top:4px;"><?= date('l, d F Y') ?></p>
    </div>
    <div class="topbar-right">
      <?php if($pending_orders > 0): ?>
        <span class="badge-pending"><i class="fas fa-bell"></i> <?= $pending_orders ?> pending orders</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Flash Message -->
  <?php if($flash): ?>
    <div class="flash <?= $flash['type'] ?>">
      <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <!-- ── OVERVIEW PANEL ── -->
  <div class="panel active" id="panel-overview">

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-users"></i></div>
        <div class="stat-info">
          <div class="stat-value"><?= $total_users ?></div>
          <div class="stat-label">Total Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-info">
          <div class="stat-value"><?= $total_orders ?></div>
          <div class="stat-label">Total Orders</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-coins"></i></div>
        <div class="stat-info">
          <div class="stat-value"><?= number_format($total_revenue, 1) ?> DT</div>
          <div class="stat-label">Total Revenue</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon olive"><i class="fas fa-utensils"></i></div>
        <div class="stat-info">
          <div class="stat-value"><?= $total_items ?></div>
          <div class="stat-label">Menu Items</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
          <div class="stat-value"><?= $pending_orders ?></div>
          <div class="stat-label">Pending Orders</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-envelope"></i></div>
        <div class="stat-info">
          <div class="stat-value"><?= $total_contacts ?> (<?= $unread_contacts ?>)</div>
          <div class="stat-label">Contacts (Unread)</div>
        </div>
      </div>
    </div>

    <!-- Recent Orders preview -->
    <div class="table-wrap">
      <div class="table-header">
        <h3><i class="fas fa-clock" style="color:#9ba36a;margin-right:8px;"></i>Recent Orders</h3>
        <button class="btn-add" onclick="showPanel('orders')" style="padding:8px 18px;font-size:0.82rem;">
          View All <i class="fas fa-arrow-right"></i>
        </button>
      </div>
      <table>
        <thead>
          <tr>
            <th>#</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach(array_slice($recent_orders, 0, 5) as $o): ?>
          <tr>
            <td>#<?= $o['id'] ?></td>
            <td><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></td>
            <td><strong><?= number_format($o['total'],2) ?> DT</strong></td>
            <td><span class="status <?= $o['status'] ?>"><?= $o['status'] ?></span></td>
            <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($recent_orders)): ?>
          <tr><td colspan="5" style="text-align:center;color:#aaa;padding:30px;">No orders yet</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── MENU PANEL ── -->
  <div class="panel" id="panel-menu">

    <!-- ADD FORM -->
    <div class="form-card">
      <h3><i class="fas fa-plus-circle" style="color:#9ba36a;margin-right:8px;"></i>Add New Menu Item</h3>
      <form action="<?= BASE_URL ?>admin/actions/menu_add.php" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
          <div class="form-group">
            <label>Item Name *</label>
            <input type="text" name="name" placeholder="e.g. Cappuccino" required maxlength="100">
          </div>
          <div class="form-group">
            <label>Category *</label>
            <select name="category" required>
              <option value="">— Select —</option>
              <option value="coffee">☕ Coffee</option>
              <option value="food">🥗 Food</option>
            </select>
          </div>
          <div class="form-group">
            <label>Price (DT) *</label>
            <input type="number" name="price" step="0.01" min="0" placeholder="0.00" required>
          </div>
          <div class="form-group">
            <label>Calories</label>
            <input type="number" name="calories" min="0" placeholder="0">
          </div>
          <div class="form-group">
            <label>Protein (g)</label>
            <input type="number" name="protein" min="0" placeholder="0">
          </div>
          <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" accept="image/*">
          </div>
          <div class="form-group full">
            <label>Description</label>
            <textarea name="description" placeholder="Describe the item…"></textarea>
          </div>
          <div class="form-group">
            <label>Best Seller ?</label>
            <div class="checkbox-group">
              <input type="checkbox" name="is_best_seller" value="1" id="bs_add">
              <label for="bs_add" style="font-size:0.88rem;color:#555;font-weight:400;">Mark as Best Seller</label>
            </div>
          </div>
        </div>
        <button type="submit" class="btn-add">
          <i class="fas fa-plus"></i> Add Item
        </button>
      </form>
    </div>

    <!-- MENU TABLE -->
    <div class="table-wrap">
      <div class="table-header">
        <h3><i class="fas fa-list" style="color:#9ba36a;margin-right:8px;"></i>All Menu Items (<?= count($menu_items) ?>)</h3>
        <div style="display:flex;gap:10px;">
          <input type="text" id="menu-search" placeholder="🔍 Search…"
            style="padding:8px 14px;border:1.5px solid #e8e0d5;border-radius:8px;font-family:'Poppins',sans-serif;font-size:0.85rem;outline:none;"
            oninput="filterMenu(this.value)">
          <select id="menu-filter" onchange="filterMenu(document.getElementById('menu-search').value)"
            style="padding:8px 12px;border:1.5px solid #e8e0d5;border-radius:8px;font-family:'Poppins',sans-serif;font-size:0.85rem;outline:none;background:#fdfaf7;">
            <option value="">All</option>
            <option value="coffee">Coffee</option>
            <option value="food">Food</option>
          </select>
        </div>
      </div>
      <table id="menu-table">
        <thead>
          <tr>
            <th>Image</th><th>Name</th><th>Category</th>
            <th>Price</th><th>Cal</th><th>Best Seller</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($menu_items as $item): ?>
          <tr data-name="<?= strtolower($item['name']) ?>" data-cat="<?= $item['category'] ?>">
            <td>
              <?php if($item['image_path']): ?>
                <img src="<?= BASE_URL . htmlspecialchars($item['image_path']) ?>" class="img-thumb" alt="">
              <?php else: ?>
                <div class="img-placeholder"><i class="fas fa-image"></i></div>
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
            <td style="text-transform:capitalize;"><?= $item['category'] === 'coffee' ? '☕' : '🥗' ?> <?= $item['category'] ?></td>
            <td><strong><?= number_format($item['price'],2) ?> DT</strong></td>
            <td><?= $item['calories'] ?> cal</td>
            <td>
              <?php if($item['is_best_seller']): ?>
                <span class="best-seller-badge">⭐ Best Seller</span>
              <?php else: ?>
                <span style="color:#ccc;font-size:0.8rem;">—</span>
              <?php endif; ?>
            </td>
            <td>
              <button class="btn-edit" onclick='openEditModal(<?= json_encode($item) ?>)'>
                <i class="fas fa-edit"></i> Edit
              </button>
              <button class="btn-delete" onclick="deleteItem(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>')">
                <i class="fas fa-trash"></i> Delete
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($menu_items)): ?>
          <tr><td colspan="7" style="text-align:center;color:#aaa;padding:30px;">No menu items yet</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── ORDERS PANEL ── -->
  <div class="panel" id="panel-orders">
    <div class="table-wrap">
      <div class="table-header">
        <h3><i class="fas fa-shopping-bag" style="color:#9ba36a;margin-right:8px;"></i>All Orders (<?= count($all_orders) ?>)</h3>
      </div>
      <table>
        <thead>
          <tr>
            <th>#</th><th>Customer</th><th>Email</th>
            <th>Total</th><th>Status</th><th>Date</th><th>Update Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($all_orders as $o): ?>
          <tr>
            <td>#<?= $o['id'] ?></td>
            <td><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></td>
            <td style="font-size:0.82rem;color:#888;"><?= htmlspecialchars($o['email']) ?></td>
            <td><strong><?= number_format($o['total'],2) ?> DT</strong></td>
            <td><span class="status <?= $o['status'] ?>"><?= $o['status'] ?></span></td>
            <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
            <td>
              <form action="<?= BASE_URL ?>admin/actions/order_status.php" method="POST" style="display:inline-flex;align-items:center;">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                <select name="status" class="select-status">
                  <?php foreach(['pending','confirmed','ready','delivered','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-update-status"><i class="fas fa-check"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($recent_orders)): ?>
          <tr><td colspan="7" style="text-align:center;color:#aaa;padding:30px;">No orders yet</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── USERS PANEL ── -->
  <div class="panel" id="panel-users">
    <?php
      $users = $pdo->query("SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    ?>
    <div class="table-wrap">
      <div class="table-header">
        <h3><i class="fas fa-users" style="color:#9ba36a;margin-right:8px;"></i>All Users (<?= count($users) ?>)</h3>
      </div>
      <table>
        <thead>
          <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr>
        </thead>
        <tbody>
          <?php foreach($users as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <span style="padding:3px 10px;border-radius:12px;font-size:0.75rem;font-weight:700;
                background:<?= $u['role']==='admin'?'#fce4ec':'#e8f5e9' ?>;
                color:<?= $u['role']==='admin'?'#c62828':'#2e7d32' ?>;">
                <?= strtoupper($u['role']) ?>
              </span>
            </td>
            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── CONTACTS PANEL ── -->
  <div class="panel" id="panel-contacts">
    <div class="section-tabs">
      <button class="stab active" onclick="showContactTab('all')">All Messages</button>
      <button class="stab" onclick="showContactTab('unread')">Unread (<?= $unread_contacts ?>)</button>
      <button class="stab" onclick="showContactTab('read')">Read</button>
      <button class="stab" onclick="showContactTab('replied')">Replied</button>
    </div>

    <div id="contacts-container">
      <?php foreach($recent_contacts as $contact): ?>
      <div class="contact-card" data-status="<?= $contact['status'] ?>">
        <div class="contact-header">
          <div class="contact-info">
            <h4><?= htmlspecialchars($contact['name']) ?></h4>
            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($contact['email']) ?></p>
            <p><i class="fas fa-tag"></i> <?= htmlspecialchars($contact['subject']) ?></p>
          </div>
          <div class="contact-meta">
            <span class="status <?= $contact['status'] ?>">
              <?= ucfirst($contact['status']) ?>
            </span>
            <small><?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></small>
          </div>
        </div>
        <div class="contact-message">
          <?= nl2br(htmlspecialchars(substr($contact['message'], 0, 200))) ?>
          <?php if(strlen($contact['message']) > 200): ?>...<?php endif; ?>
        </div>
        <div class="contact-actions">
          <button class="btn-reply" onclick="replyToContact(<?= $contact['id'] ?>)">
            <i class="fas fa-reply"></i> Reply
          </button>
          <button class="btn-mark-read" onclick="markAsRead(<?= $contact['id'] ?>)">
            <i class="fas fa-check"></i> Mark as Read
          </button>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($recent_contacts)): ?>
      <div style="text-align:center;padding:60px;color:#aaa;">
        <i class="fas fa-envelope-open" style="font-size:3rem;margin-bottom:20px;"></i>
        <p>No contact messages yet</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

</main>

<!-- ══════════════════════════════
     EDIT MODAL
══════════════════════════════ -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-edit" style="color:#9ba36a;margin-right:8px;"></i>Edit Menu Item</h3>
      <button class="btn-close-modal" onclick="closeEditModal()">×</button>
    </div>
    <form action="<?= BASE_URL ?>admin/actions/menu_edit.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit_id">
      <div class="form-grid">
        <div class="form-group">
          <label>Item Name *</label>
          <input type="text" name="name" id="edit_name" required maxlength="100">
        </div>
        <div class="form-group">
          <label>Category *</label>
          <select name="category" id="edit_category" required>
            <option value="coffee">☕ Coffee</option>
            <option value="food">🥗 Food</option>
          </select>
        </div>
        <div class="form-group">
          <label>Price (DT) *</label>
          <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
        </div>
        <div class="form-group">
          <label>Calories</label>
          <input type="number" name="calories" id="edit_calories" min="0">
        </div>
        <div class="form-group">
          <label>Protein (g)</label>
          <input type="number" name="protein" id="edit_protein" min="0">
        </div>
        <div class="form-group">
          <label>New Image (optional)</label>
          <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group full">
          <label>Description</label>
          <textarea name="description" id="edit_description"></textarea>
        </div>
        <div class="form-group">
          <label>Best Seller ?</label>
          <div class="checkbox-group">
            <input type="checkbox" name="is_best_seller" value="1" id="edit_bs">
            <label for="edit_bs" style="font-size:0.88rem;color:#555;font-weight:400;">Mark as Best Seller</label>
          </div>
        </div>
      </div>
      <div style="display:flex;gap:12px;margin-top:20px;">
        <button type="submit" class="btn-add"><i class="fas fa-save"></i> Save Changes</button>
        <button type="button" onclick="closeEditModal()"
          style="padding:12px 25px;border:1.5px solid #e8e0d5;background:none;border-radius:10px;cursor:pointer;font-family:'Poppins',sans-serif;font-weight:600;color:#888;">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // ── Panel switching ──
  function showPanel(name) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
    event.currentTarget.classList.add('active');
  }

  // ── Menu search/filter ──
  function filterMenu(search) {
    const cat = document.getElementById('menu-filter').value;
    search = search.toLowerCase();
    document.querySelectorAll('#menu-table tbody tr').forEach(row => {
      const name    = row.dataset.name  || '';
      const rowCat  = row.dataset.cat   || '';
      const matchS  = name.includes(search);
      const matchC  = !cat || rowCat === cat;
      row.style.display = (matchS && matchC) ? '' : 'none';
    });
  }

  // ── Edit modal ──
  function openEditModal(item) {
    document.getElementById('edit_id').value          = item.id;
    document.getElementById('edit_name').value        = item.name;
    document.getElementById('edit_category').value    = item.category;
    document.getElementById('edit_price').value       = item.price;
    document.getElementById('edit_calories').value    = item.calories;
    document.getElementById('edit_protein').value     = item.protein;
    document.getElementById('edit_description').value = item.description;
    document.getElementById('edit_bs').checked        = item.is_best_seller == 1;
    document.getElementById('edit-modal').classList.add('open');
  }

  function closeEditModal() {
    document.getElementById('edit-modal').classList.remove('open');
  }

  // Close modal on overlay click
  document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
  });

  // ── Delete item ──
  function deleteItem(id, name) {
    if (!confirm('Delete "' + name + '" from the menu?\nThis action cannot be undone.')) return;
    fetch('<?= BASE_URL ?>admin/actions/menu_delete.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    });
  }

  // ── Auto-hide flash ──
  const flash = document.querySelector('.flash');
  if (flash) setTimeout(() => flash.style.display = 'none', 4000);

  // ── Contact functions ──
  function showContactTab(tab) {
    document.querySelectorAll('.stab').forEach(s => s.classList.remove('active'));
    event.target.classList.add('active');

    const cards = document.querySelectorAll('.contact-card');
    cards.forEach(card => {
      const status = card.dataset.status;
      if (tab === 'all' || status === tab) {
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  }

  function markAsRead(id) {
    if (!confirm('Mark this message as read?')) return;
    fetch('<?= BASE_URL ?>admin/actions/contact_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, action: 'mark_read' })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    });
  }

  function replyToContact(id) {
    const reply = prompt('Enter your reply message:');
    if (!reply || reply.trim() === '') return;

    fetch('<?= BASE_URL ?>admin/actions/contact_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, action: 'reply', reply: reply.trim() })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    });
  }
</script>

</body>
</html>