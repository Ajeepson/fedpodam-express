<?php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// 0. Fetch Homepage Banners
if ($action === 'get_banners') {
    $stmt = $pdo->query("SELECT * FROM banners ORDER BY id DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// 0.1 Fetch Categories
if ($action === 'get_categories') {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// 0.2 Fetch Testimonials
if ($action === 'get_testimonials') {
    $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// 0.3 Fetch Site Settings
if ($action === 'get_settings') {
    $stmt = $pdo->query("SELECT * FROM site_settings");
    echo json_encode($stmt->fetchAll(PDO::FETCH_KEY_PAIR));
    exit;
}

// 0.4 Fetch FAQs
if ($action === 'get_faqs') {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY id ASC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// 1. Fetch Products
if ($action === 'get_products') {
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 8;
    $offset = ($page - 1) * $limit;
    
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

    $sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
    
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
    $paymentRef = $input['payment_ref'] ?? 'COD'; // Paystack Reference

    // Create Order
    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, total_amount, status) VALUES (?, ?, 'processing')");
    $stmt->execute([$userId, $total]);
    $orderId = $pdo->lastInsertId();

    // Create Shipping
    $stmt = $pdo->prepare("INSERT INTO shipping (order_id, shipping_address, tracking_number, status) VALUES (?, ?, ?, 'Processing')");
    $stmt->execute([$orderId, $input['address'], $paymentRef]);

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
    $oldPrice = !empty($input['old_price']) ? $input['old_price'] : null;
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, old_price, category, image_url, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$input['name'], $input['description'], $input['price'], $oldPrice, $input['category'], $input['image_url'], $input['stock']]);
    echo json_encode(['success' => true]);
    exit;
}

// 3.1.5 Admin: Update Product
if ($action === 'admin_update_product') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $oldPrice = !empty($input['old_price']) ? $input['old_price'] : null;
    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, old_price=?, category=?, image_url=?, stock_quantity=? WHERE id=?");
    $stmt->execute([$input['name'], $input['description'], $input['price'], $oldPrice, $input['category'], $input['image_url'], $input['stock'], $input['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// 3.1.6 Admin: Delete Product
if ($action === 'admin_delete_product') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$input['id']]);
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

// 3.5 Admin: Add Category
if ($action === 'admin_add_category') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$input['name']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Category likely exists']);
    }
    exit;
}

// 3.6 Admin: Delete Category
if ($action === 'admin_delete_category') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$input['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// 1.9 Get Product Reviews
if ($action === 'get_reviews') {
    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("
        SELECT r.*, c.full_name 
        FROM reviews r 
        JOIN customers c ON r.customer_id = c.id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// 2.0 Add Review
if ($action === 'add_review') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login to review']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $pid = $input['product_id'];
    $uid = $_SESSION['user_id'];
    $rating = (int)$input['rating'];
    $comment = htmlspecialchars($input['comment']);

    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$pid, $uid, $rating, $comment]);

    // Update Product Aggregates
    $stmt = $pdo->prepare("
        UPDATE products p 
        SET 
            review_count = (SELECT COUNT(*) FROM reviews WHERE product_id = p.id),
            average_rating = (SELECT AVG(rating) FROM reviews WHERE product_id = p.id)
        WHERE p.id = ?
    ");
    $stmt->execute([$pid]);

    echo json_encode(['success' => true]);
    exit;
}

// 3.7 Admin: Delete Customer
if ($action === 'admin_delete_customer') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$input['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// 3.8 Admin: Add Bot Response
if ($action === 'admin_add_bot_response') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO bot_responses (keyword, response) VALUES (?, ?)");
    $stmt->execute([$input['keyword'], $input['response']]);
    echo json_encode(['success' => true]);
    exit;
}

// 3.9 Admin: Delete Bot Response
if ($action === 'admin_delete_bot_response') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM bot_responses WHERE id = ?");
    $stmt->execute([$input['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// 3.10 Admin: Add Testimonial
if ($action === 'admin_add_testimonial') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO testimonials (name, role, content) VALUES (?, ?, ?)");
    $stmt->execute([$input['name'], $input['role'], $input['content']]);
    echo json_encode(['success' => true]);
    exit;
}

// 3.11 Admin: Delete Testimonial
if ($action === 'admin_delete_testimonial') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$input['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// 4.0 Admin: Update Settings
if ($action === 'admin_update_settings') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    foreach ($input as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    echo json_encode(['success' => true]);
    exit;
}

// 4.1 Admin: Create New Admin
if ($action === 'admin_create_admin') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $passHash = password_hash($input['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$input['username'], $passHash]);
    echo json_encode(['success' => true]);
    exit;
}

// 4.2 Admin: Add FAQ
if ($action === 'admin_add_faq') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
    $stmt->execute([$input['question'], $input['answer']]);
    echo json_encode(['success' => true]);
    exit;
}

// 4.3 Admin: Delete FAQ
if ($action === 'admin_delete_faq') {
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false]); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$input['id']]);
    echo json_encode(['success' => true]);
    exit;
}
?>
