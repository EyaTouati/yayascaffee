<?php
// api/customize.php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$coffee_type = trim($_POST['coffee_type'] ?? '');
$size        = trim($_POST['size']        ?? '');
$milk_type   = trim($_POST['milk_type']   ?? '');
$extras      = trim($_POST['extras']      ?? '[]');
$base_price  = floatval($_POST['base_price']  ?? 0);
$total_price = floatval($_POST['total_price'] ?? 0);

// Validations
if (empty($coffee_type) || empty($size) || empty($milk_type)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

if (!in_array($size, ['S', 'M', 'L'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid size.']);
    exit;
}

if ($total_price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid price.']);
    exit;
}

// Insérer dans la DB
try {
    $stmt = $pdo->prepare("
        INSERT INTO custom_orders
            (user_id, coffee_type, size, milk_type, extras, base_price, total_price, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $coffee_type,
        $size,
        $milk_type,
        $extras,
        $base_price,
        $total_price
    ]);

    echo json_encode([
        'success'  => true,
        'message'  => 'Custom order placed!',
        'order_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    error_log('Custom order error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>