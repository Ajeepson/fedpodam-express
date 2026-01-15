<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// 1. Fetch Products
if ($action === 'get_products') {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
    echo json_encode($products);
    exit;
}

// 1.5 Register User
if ($action === 'register') {
    $input = json_decode(file_get_contents('php://input'), true);
    $passHash = password_hash($input['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO customers (full_name, email, password_hash, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$input['name'], $input['email'], $passHash, $input['address']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Email likely already exists.']);
    }
    exit;
}

// 1.6 Login User
if ($action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch();

    if ($user && password_verify($input['password'], $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
    exit;
}

// 1.7 Add to Cart
if ($action === 'add_to_cart') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'];
    
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }
    
    echo json_encode(['success' => true, 'count' => count($_SESSION['cart'])]);
    exit;
}

// 1.8 Place Order
if ($action === 'place_order') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $total = $input['total'];
    $userId = $_SESSION['user_id'];

    // Create Order
    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, total_amount) VALUES (?, ?)");
    $stmt->execute([$userId, $total]);
    $orderId = $pdo->lastInsertId();

    // Create Shipping
    $stmt = $pdo->prepare("INSERT INTO shipping (order_id, shipping_address, status) VALUES (?, ?, 'Processing')");
    $stmt->execute([$orderId, $input['address']]);

    // Clear Cart
    unset($_SESSION['cart']);

    echo json_encode(['success' => true, 'order_id' => $orderId]);
    exit;
}

// 2. Chatbot Logic
if ($action === 'chat') {
    // Get the raw POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = strtolower(trim($input['message'] ?? ''));

    if (empty($userMessage)) {
        echo json_encode(['reply' => 'I did not catch that.']);
        exit;
    }

    // Simple keyword matching logic
    $stmt = $pdo->prepare("SELECT response FROM bot_responses WHERE ? LIKE CONCAT('%', keyword, '%') LIMIT 1");
    $stmt->execute([$userMessage]);
    $result = $stmt->fetch();

    if ($result) {
        $reply = $result['response'];
    } else {
        // Fallback response
        $reply = "I'm not sure about that. Try asking about 'shipping', 'returns', or 'contact'.";
    }

    echo json_encode(['reply' => $reply]);
    exit;
}
?>
