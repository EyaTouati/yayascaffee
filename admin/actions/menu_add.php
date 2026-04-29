<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$name           = trim($_POST['name']          ?? '');
$category       = trim($_POST['category']      ?? '');
$price          = floatval($_POST['price']      ?? 0);
$calories       = intval($_POST['calories']     ?? 0);
$protein        = intval($_POST['protein']      ?? 0);
$description    = trim($_POST['description']    ?? '');
$is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;

// ── Validation ──
$errors = [];
if (strlen($name) < 2)                            $errors[] = 'Name is required (min 2 chars).';
if (!in_array($category, ['coffee','food']))      $errors[] = 'Invalid category.';
if ($price <= 0)                                  $errors[] = 'Price must be greater than 0.';

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => implode(' ', $errors)];
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

// ── Image upload ──
$image_path = null;
if (!empty($_FILES['image']['name'])) {
    $allowed    = ['image/jpeg','image/png','image/webp','image/gif'];
    $max_size   = 2 * 1024 * 1024; // 2MB

    if (!in_array($_FILES['image']['type'], $allowed)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid image format. JPG, PNG, WEBP only.'];
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
        exit;
    }
    if ($_FILES['image']['size'] > $max_size) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Image too large. Max 2MB.'];
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
        exit;
    }

    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/projet web/images/menu/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename   = 'item_' . time() . '_' . uniqid() . '.' . $ext;
    $dest       = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $image_path = 'images/menu/' . $filename;
    }
}

// ── Insert ──
$stmt = $pdo->prepare("
    INSERT INTO menu_items (name, category, price, calories, protein, description, is_best_seller, image_path)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([$name, $category, $price, $calories, $protein, $description, $is_best_seller, $image_path]);

$_SESSION['flash'] = ['type' => 'success', 'msg' => '"'.$name.'" has been added to the menu!'];
header('Location: ' . BASE_URL . 'admin/dashboard.php');
exit;