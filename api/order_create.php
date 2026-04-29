<?php
// api/order_create.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Accepter les non-connectés aussi (emails fourni)
$data = json_decode(file_get_contents('php://input'), true);

$items = $data['items'] ?? [];
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$delivery_address = trim($data['delivery_address'] ?? '');

// Validation
if (empty($items)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

// Validation du domaine : vérifier les enregistrements MX
$domain = substr(strrchr($email, "@"), 1);
if (!checkdnsrr($domain, "MX")) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Le domaine de l\'email n\'est pas valide ou n\'accepte pas les emails.']);
    exit;
}

if (empty($phone) || empty($delivery_address)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Phone and address required']);
    exit;
}

// Validate phone number (Tunisian format)
if (!preg_match('/^(\+216|216)?[0-9]{8}$/', str_replace(' ', '', $phone))) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit;
}

try {
    // Vérifier / créer utilisateur si pas connecté
    $user_id = null;
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
    } else {
        // Chercher utilisateur par email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Créer un utilisateur guest
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Guest', 'User', $email, password_hash('guest123', PASSWORD_BCRYPT), 'user']);
            $user_id = $pdo->lastInsertId();
        } else {
            $user_id = $user['id'];
        }
    }
    
    // Calculer le total
    $total = 0;
    $items_data = [];

    foreach ($items as $item) {
        $item_id = $item['id'] ?? '';
        $qty = intval($item['quantity'] ?? 1);
        $price = floatval($item['price'] ?? 0);

        if ($qty <= 0 || $price <= 0) continue;

        // Gérer les articles personnalisés (custom_)
        if (strpos($item_id, 'custom_') === 0) {
            // Article personnalisé - utiliser directement les données
            $subtotal = $price * $qty;
            $total += $subtotal;

            $items_data[] = [
                'id' => $item_id,
                'name' => $item['name'] ?? 'Custom Coffee',
                'quantity' => $qty,
                'price' => $price,
                'type' => 'custom',
                'customization' => $item['customization'] ?? null,
                'details' => $item['details'] ?? null
            ];
        } else {
            // Article normal du menu
            $item_id = intval($item_id);
            if ($item_id <= 0) continue;

            // Récupérer le prix depuis menu_items
            $stmt = $pdo->prepare("SELECT id, name, price FROM menu_items WHERE id = ?");
            $stmt->execute([$item_id]);
            $menu_item = $stmt->fetch();

            if (!$menu_item) continue;

            $price = floatval($menu_item['price']);
            $subtotal = $price * $qty;
            $total += $subtotal;

            $items_data[] = [
                'id' => $item_id,
                'name' => $menu_item['name'],
                'quantity' => $qty,
                'price' => $price,
                'type' => 'menu',
                'customization' => $item['customization'] ?? null
            ];
        }
    }
    
    if ($total <= 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid cart items']);
        exit;
    }
    
    // Créer la commande
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, email, phone, delivery_address, total, status, items_json)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $email,
        $phone,
        $delivery_address,
        $total,
        'pending',
        json_encode($items_data)
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $order_id,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
