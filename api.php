<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// 0. Fetch Homepage Banners
if ($action === 'get_banners') {
    $stmt = $pdo->query("SELECT * FROM banners ORDER BY id DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// 1. Fetch Products
if ($action === 'get_products') {
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($category && $category !== 'All') {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    echo json_encode($products);
    exit;
}

// 1.1 Get Single Product Details
if ($action === 'get_product') {
    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    echo json_encode($product ?: null);
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

// 3. Admin: Login
if ($action === 'admin_login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$input['username']]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($input['password'], $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
    exit;
}

// 3.1 Admin: Add Product
if ($action === 'admin_add_product') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, image_url, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$input['name'], $input['description'], $input['price'], $input['category'], $input['image_url'], $input['stock']]);
    echo json_encode(['success' => true]);
    exit;
}

// 3.2 Admin: Update Order Status
if ($action === 'admin_update_order') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE shipping SET status = ? WHERE order_id = ?");
    $stmt->execute([$input['status'], $input['order_id']]);
    
    echo json_encode(['success' => true]);
    exit;
}

// 3.3 Admin: Add Banner
if ($action === 'admin_add_banner') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO banners (title, subtitle, image_url) VALUES (?, ?, ?)");
    $stmt->execute([$input['title'], $input['subtitle'], $input['image_url']]);
    echo json_encode(['success' => true]);
    exit;
}

// 3.4 Admin: Delete Banner
if ($action === 'admin_delete_banner') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->execute([$input['id']]);
    echo json_encode(['success' => true]);
    exit;
}
?>
