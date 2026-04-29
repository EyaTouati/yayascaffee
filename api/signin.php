<?php
// api/signin.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$email    = trim($data['email']    ?? '');
$password = trim($data['password'] ?? '');

// =============================
// VALIDATION basique
// =============================
if (empty($email) || empty($password)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

// =============================
// Cherche l'utilisateur
// =============================
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Incorrect email or password.']);
    exit;
}

// =============================
// Crée la session
// =============================
session_regenerate_id(true); // sécurité anti-fixation

$_SESSION['user_id']    = $user['id'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['email']      = $user['email'];
$_SESSION['role']       = $user['role'];

// Redirige selon le rôle
$redirect = ($user['role'] === 'admin')
    ? BASE_URL . 'admin/dashboard.php'
    : BASE_URL . 'index.php';

echo json_encode([
    'success'  => true,
    'redirect' => $redirect,
    'role'     => $user['role']
]);