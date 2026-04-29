<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id   = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// Get image to delete
$stmt = $pdo->prepare("SELECT image_path FROM menu_items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

// Delete image file
if ($item['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'].'/projet web/'.$item['image_path'])) {
    unlink($_SERVER['DOCUMENT_ROOT'].'/projet web/'.$item['image_path']);
}

// Delete from DB
$pdo->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$id]);

echo json_encode(['success' => true]);