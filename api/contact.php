<?php
// api/contact.php
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ── Sanitize and validate input ──
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Basic validation
$errors = [];
if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Name is required and must be at least 2 characters.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email address is required.';
}
if (empty($subject)) {
    $errors[] = 'Please select a subject.';
}
if (empty($message) || strlen($message) < 10) {
    $errors[] = 'Message is required and must be at least 10 characters.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Insert into database ──
try {
    $stmt = $pdo->prepare("
        INSERT INTO contacts (name, email, subject, message, status, created_at)
        VALUES (?, ?, ?, ?, 'unread', NOW())
    ");

    $stmt->execute([$name, $email, $subject, $message]);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.'
    ]);

} catch (PDOException $e) {
    error_log('Contact form error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sorry, there was an error sending your message. Please try again later.']);
}
?>