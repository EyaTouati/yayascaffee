<?php
// api/signup.php
require_once '../includes/db.php';

header('Content-Type: application/json');

// Accepte uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Récupère les données JSON envoyées par fetch()
$data = json_decode(file_get_contents('php://input'), true);

$first_name = trim($data['first_name'] ?? '');
$last_name  = trim($data['last_name']  ?? '');
$email      = trim($data['email']      ?? '');
$password   = trim($data['password']   ?? '');

// =============================
// VALIDATION
// =============================
$errors = [];

if (strlen($first_name) < 2 || strlen($first_name) > 50) {
    $errors[] = 'First name must be between 2 and 50 characters.';
} elseif (!preg_match('/^[a-zA-Z\s]+$/', $first_name)) {
    $errors[] = 'First name must contain only letters and spaces.';
}

if (strlen($last_name) < 2 || strlen($last_name) > 50) {
    $errors[] = 'Last name must be between 2 and 50 characters.';
} elseif (!preg_match('/^[a-zA-Z\s]+$/', $last_name)) {
    $errors[] = 'Last name must contain only letters and spaces.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
} else {
    // Validation du domaine : vérifier les enregistrements MX
    $domain = substr(strrchr($email, "@"), 1);
    if (!checkdnsrr($domain, "MX")) {
        $errors[] = 'Le domaine de l\'email n\'est pas valide ou n\'accepte pas les emails.';
    }
}

// Contraintes mot de passe : 8+ chars, 1 majuscule, 1 chiffre
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Password must contain at least one uppercase letter.';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password must contain at least one number.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// =============================
// Vérifie si email existe déjà
// =============================
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);

if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
    exit;
}

// =============================
// Insertion
// =============================
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare(
    'INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$first_name, $last_name, $email, $hash, 'user']);

echo json_encode(['success' => true, 'message' => 'Account created! You can now sign in.']);