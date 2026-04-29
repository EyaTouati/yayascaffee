<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
$status   = trim($_POST['status']     ?? '');
$allowed  = ['pending','confirmed','ready','delivered','cancelled'];

if ($order_id <= 0 || !in_array($status, $allowed)) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid order or status.'];
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->execute([$status, $order_id]);

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Order #'.$order_id.' updated to "'.ucfirst($status).'".'];
header('Location: ' . BASE_URL . 'admin/dashboard.php');
exit;