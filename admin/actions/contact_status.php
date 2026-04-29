<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
$action = trim($data['action'] ?? '');

if (!$id || !in_array($action, ['mark_read', 'reply'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Vérifier que le contact existe
$stmt = $pdo->prepare("SELECT id FROM contacts WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Contact not found']);
    exit;
}

try {
    if ($action === 'mark_read') {
        $stmt = $pdo->prepare("UPDATE contacts SET status = 'read' WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Message marked as read';
    } elseif ($action === 'reply') {
        $reply = trim($data['reply'] ?? '');
        if (empty($reply)) {
            throw new Exception('Reply message is required');
        }

        $stmt = $pdo->prepare("
            UPDATE contacts
            SET status = 'replied',
                admin_reply = ?,
                replied_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$reply, $id]);
        $message = 'Reply sent successfully';
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    error_log('Contact status error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>