<?php 
include 'includes/header.php'; 
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch All Orders
$stmt = $pdo->query("
    SELECT o.id, c.full_name, o.total_amount, s.status, s.shipping_address 
    FROM orders o 
    JOIN customers c ON o.customer_id = c.id 
    LEFT JOIN shipping s ON o.id = s.order_id 
    ORDER BY o.id DESC
");
$orders = $stmt->fetchAll();

// Fetch Banners
$banners = $pdo->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll();

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Fetch Products for Management
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();

// Fetch Customers
$customers = $pdo->query("SELECT * FROM customers ORDER BY id DESC")->fetchAll();

// Fetch Bot Responses
$bot_responses = $pdo->query("SELECT * FROM bot_responses ORDER BY id DESC")->fetchAll();

// Fetch Testimonials
try {
    $testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $testimonials = [];
}

// Fetch FAQs
try {
    $faqs = $pdo->query("SELECT * FROM faqs ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $faqs = [];
}
?>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2>Admin Dashboard</h2>
        <a href="admin_settings.php" class="btn-buy" style="width:auto; background:#555;">⚙️ Settings</a>
    </div>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <a onclick="showSection('orders', this)" class="admin-nav-item active">📦 Orders</a>
            <a onclick="showSection('products', this)" class="admin-nav-item">👕 Inventory / Products</a>
            <a onclick="showSection('categories', this)" class="admin-nav-item">🏷️ Categories</a>
            <a onclick="showSection('customers', this)" class="admin-nav-item">👥 Customers</a>
            <a onclick="showSection('banners', this)" class="admin-nav-item">🖼️ Banners (Ads)</a>
            <a onclick="showSection('chatbot', this)" class="admin-nav-item">🤖 Chatbot</a>
            <a onclick="showSection('testimonials', this)" class="admin-nav-item">💬 Testimonials</a>
            <a onclick="showSection('faqs', this)" class="admin-nav-item">❓ FAQs</a>
        </div>

        <!-- Main Content Area -->
        <div class="admin-content">
            
            <!-- SECTION: ORDERS -->
            <div id="orders" class="admin-section active">
            <h3>Recent Orders</h3>
            <table>
                <tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Action</th></tr>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td><?php echo htmlspecialchars($o['full_name']); ?></td>
                    <td>₦<?php echo number_format($o['total_amount'], 2); ?></td>
                    <td><span class="badge" style="background:<?php echo $o['status']=='Delivered'?'green':'orange'; ?>"><?php echo $o['status']; ?></span></td>
                    <td>
                        <select onchange="updateStatus(<?php echo $o['id']; ?>, this.value)">
                            <option value="">Update...</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>

            <!-- SECTION: PRODUCTS -->
            <div id="products" class="admin-section">
                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:2rem;">
            <div class="form-container" style="margin:0; max-width:100%; height:fit-content;">
            <h3 id="productFormTitle">Add / Edit Product</h3>
            <form id="productForm">
                <input type="hidden" name="id" id="prodId">
                <input type="hidden" name="action" id="formAction" value="add">
                
                <div class="form-group">
                    <label>Name</label><input type="text" name="name" id="prodName" required>
                </div>
                <div class="form-group">
                    <label>Price</label><input type="number" step="0.01" name="price" id="prodPrice" required>
                </div>
                <div class="form-group">
                    <label>Old Price (Optional - for discounts)</label><input type="number" step="0.01" name="old_price" id="prodOldPrice" placeholder="e.g. 2000">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="prodCategory" style="width:100%; padding:8px;">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image URL</label><input type="text" name="image_url" id="prodImg" placeholder="http://..." required>
                </div>
                <div class="form-group">
                    <label>Stock</label><input type="number" name="stock" id="prodStock" value="100">
                </div>
                <div class="form-group">
                    <label>Description</label><input type="text" name="description" id="prodDesc" required>
                </div>
                <button type="submit" class="btn-buy" id="saveBtn">Add Product</button>
                <button type="button" onclick="resetForm()" style="margin-top:10px; background:#666; color:white; border:none; padding:5px 10px; cursor:pointer; display:none;" id="cancelBtn">Cancel Edit</button>
            </form>
            </div>

            <div>
                <h3>Product List</h3>
                <table>
                    <tr><th>Image</th><th>Name</th><th>Price</th><th>Stock</th><th>Action</th></tr>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><img src="<?php echo $p['image_url']; ?>" style="height:40px;"></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td>₦<?php echo number_format($p['price'], 2); ?></td>
                        <td><?php echo $p['stock_quantity']; ?></td>
                        <td>
                            <button onclick='editProduct(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>)' style="background:blue; color:white; border:none; padding:5px; cursor:pointer;">Edit</button>
                            <button onclick="deleteProduct(<?php echo $p['id']; ?>)" style="background:red; color:white; border:none; padding:5px; cursor:pointer;">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
                </div>
            </div>

            <!-- SECTION: BANNERS -->
            <div id="banners" class="admin-section">
            <h3>Homepage Banners</h3>
            <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                <div class="form-container" style="margin:0; flex:1; min-width:300px;">
                    <h4>Add New Banner</h4>
                    <form id="addBannerForm">
                        <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
                        <div class="form-group"><label>Subtitle</label><input type="text" name="subtitle" required></div>
                        <div class="form-group"><label>Image URL</label><input type="text" name="image_url" required></div>
                        <button type="submit" class="btn-buy">Publish Banner</button>
                    </form>
                </div>
                <div style="flex:2; min-width:300px;">
                    <h4>Active Banners</h4>
                    <table>
                        <tr><th>Image</th><th>Title</th><th>Action</th></tr>
                        <?php foreach ($banners as $b): ?>
                        <tr>
                            <td><img src="<?php echo $b['image_url']; ?>" style="height:50px;"></td>
                            <td><?php echo htmlspecialchars($b['title']); ?></td>
                            <td><button onclick="deleteBanner(<?php echo $b['id']; ?>)" style="background:red; color:white; border:none; padding:5px;">Delete</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            </div>

            <!-- SECTION: CATEGORIES -->
            <div id="categories" class="admin-section">
            <h3>Manage Categories</h3>
            <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                <div class="form-container" style="margin:0; flex:1; min-width:300px;">
                    <h4>Add Category</h4>
                    <form id="addCategoryForm">
                        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
                        <button type="submit" class="btn-buy">Add Category</button>
                    </form>
                </div>
                <div style="flex:2; min-width:300px;">
                    <h4>Existing Categories</h4>
                    <table>
                        <tr><th>ID</th><th>Name</th><th>Action</th></tr>
                        <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><?php echo htmlspecialchars($c['name']); ?></td>
                            <td><button onclick="deleteCategory(<?php echo $c['id']; ?>)" style="background:red; color:white; border:none; padding:5px;">Delete</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            </div>

            <!-- SECTION: CUSTOMERS -->
            <div id="customers" class="admin-section">
            <h3>Manage Customers</h3>
            <table>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Address</th><th>Action</th></tr>
                <?php foreach ($customers as $cust): ?>
                <tr>
                    <td><?php echo $cust['id']; ?></td>
                    <td><?php echo htmlspecialchars($cust['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($cust['email']); ?></td>
                    <td><?php echo htmlspecialchars($cust['address'] ?? 'N/A'); ?></td>
                    <td>
                        <button onclick="deleteCustomer(<?php echo $cust['id']; ?>)" style="background:red; color:white; border:none; padding:5px; cursor:pointer;">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>

            <!-- SECTION: CHATBOT -->
            <div id="chatbot" class="admin-section">

            <h3>Manage Chatbot Responses</h3>
            <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                <div class="form-container" style="margin:0; flex:1; min-width:300px;">
                    <h4>Add Response</h4>
                    <form id="addBotResponseForm">
                        <div class="form-group"><label>Keyword</label><input type="text" name="keyword" required placeholder="e.g. shipping"></div>
                        <div class="form-group"><label>Response</label><input type="text" name="response" required placeholder="e.g. We ship in 3 days"></div>
                        <button type="submit" class="btn-buy">Add Response</button>
                    </form>
                </div>
                <div style="flex:2; min-width:300px; max-height: 400px; overflow-y: auto;">
                    <h4>Existing Responses</h4>
                    <table>
                        <tr><th>Keyword</th><th>Response</th><th>Action</th></tr>
                        <?php foreach ($bot_responses as $br): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($br['keyword']); ?></td>
                            <td><?php echo htmlspecialchars($br['response']); ?></td>
                            <td><button onclick="deleteBotResponse(<?php echo $br['id']; ?>)" style="background:red; color:white; border:none; padding:5px;">Delete</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            </div>

            <!-- SECTION: TESTIMONIALS -->
            <div id="testimonials" class="admin-section">
            <h3>Manage Testimonials</h3>
            <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                <div class="form-container" style="margin:0; flex:1; min-width:300px;">
                    <h4>Add Testimonial</h4>
                    <form id="addTestimonialForm">
                        <div class="form-group"><label>Name</label><input type="text" name="name" required placeholder="e.g. John Doe"></div>
                        <div class="form-group"><label>Role/Dept</label><input type="text" name="role" required placeholder="e.g. CS Dept"></div>
                        <div class="form-group"><label>Content</label><input type="text" name="content" required></div>
                        <button type="submit" class="btn-buy">Add Testimonial</button>
                    </form>
                </div>
                <div style="flex:2; min-width:300px;">
                    <h4>Existing Testimonials</h4>
                    <table>
                        <tr><th>Name</th><th>Content</th><th>Action</th></tr>
                        <?php foreach ($testimonials as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['name']); ?> <small>(<?php echo htmlspecialchars($t['role']); ?>)</small></td>
                            <td><?php echo htmlspecialchars($t['content']); ?></td>
                            <td><button onclick="deleteTestimonial(<?php echo $t['id']; ?>)" style="background:red; color:white; border:none; padding:5px;">Delete</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            </div>

            <!-- SECTION: FAQs -->
            <div id="faqs" class="admin-section">
            <h3>Manage FAQs</h3>
            <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                <div class="form-container" style="margin:0; flex:1; min-width:300px;">
                    <h4>Add FAQ</h4>
                    <form id="addFaqForm">
                        <div class="form-group"><label>Question</label><input type="text" name="question" required placeholder="e.g. How to return?"></div>
                        <div class="form-group"><label>Answer</label><textarea name="answer" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" rows="4" required></textarea></div>
                        <button type="submit" class="btn-buy">Add FAQ</button>
                    </form>
                </div>
                <div style="flex:2; min-width:300px;">
                    <h4>Existing FAQs</h4>
                    <table>
                        <tr><th>Question</th><th>Answer</th><th>Action</th></tr>
                        <?php foreach ($faqs as $f): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($f['question']); ?></td>
                            <td><?php echo htmlspecialchars($f['answer']); ?></td>
                            <td><button onclick="deleteFaq(<?php echo $f['id']; ?>)" style="background:red; color:white; border:none; padding:5px;">Delete</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            </div>

        </div>
    </div>
</div>

<script>
function showSection(sectionId, element) {
    // Hide all sections
    document.querySelectorAll('.admin-section').forEach(el => el.classList.remove('active'));
    // Show selected section
    document.getElementById(sectionId).classList.add('active');
    
    // Update sidebar active state
    document.querySelectorAll('.admin-nav-item').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
}

function updateStatus(orderId, status) {
    if(!status) return;
    fetch('api/api.php?action=admin_update_order', {
        method: 'POST',
        body: JSON.stringify({ order_id: orderId, status: status })
    }).then(res => res.json()).then(d => {
        if(d.success) location.reload();
    });
}

document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    const action = document.getElementById('formAction').value;
    
    const apiAction = action === 'update' ? 'admin_update_product' : 'admin_add_product';
    
    fetch(`api/api.php?action=${apiAction}`, { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) { alert('Success'); location.reload(); } });
});

function editProduct(p) {
    document.getElementById('productFormTitle').innerText = 'Edit Product: ' + p.name;
    document.getElementById('formAction').value = 'update';
    document.getElementById('prodId').value = p.id;
    document.getElementById('prodName').value = p.name;
    document.getElementById('prodPrice').value = p.price;
    document.getElementById('prodOldPrice').value = p.old_price;
    document.getElementById('prodCategory').value = p.category;
    document.getElementById('prodImg').value = p.image_url;
    document.getElementById('prodStock').value = p.stock_quantity;
    document.getElementById('prodDesc').value = p.description;
    document.getElementById('saveBtn').innerText = 'Update Product';
    document.getElementById('cancelBtn').style.display = 'inline-block';
    
    // Switch to products tab if not active
    showSection('products', document.querySelectorAll('.admin-nav-item')[1]);
    document.getElementById('productFormTitle').scrollIntoView({behavior: 'smooth'});
}

function resetForm() {
    document.getElementById('productForm').reset();
    document.getElementById('productFormTitle').innerText = 'Add New Product';
    document.getElementById('formAction').value = 'add';
    document.getElementById('saveBtn').innerText = 'Add Product';
    document.getElementById('cancelBtn').style.display = 'none';
}

function deleteProduct(id) {
    if(!confirm('Delete this product?')) return;
    fetch('api/api.php?action=admin_delete_product', { method: 'POST', body: JSON.stringify({id: id}) })
    .then(res => res.json()).then(d => { if(d.success) location.reload(); });
}

document.getElementById('addBannerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api/api.php?action=admin_add_banner', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) { location.reload(); } });
});

function deleteBanner(id) {
    if(!confirm('Delete this banner?')) return;
    fetch('api/api.php?action=admin_delete_banner', { method: 'POST', body: JSON.stringify({id: id}) })
    .then(res => res.json()).then(d => { if(d.success) location.reload(); });
}

document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api/api.php?action=admin_add_category', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) { location.reload(); } else { alert(d.message); } });
});

function deleteCategory(id) {
    if(!confirm('Delete this category?')) return;
    fetch('api/api.php?action=admin_delete_category', { method: 'POST', body: JSON.stringify({id: id}) })
    .then(res => res.json()).then(d => { if(d.success) location.reload(); });
}

function deleteCustomer(id) {
    if(!confirm('Delete this customer? This will also delete their orders.')) return;
    fetch('api/api.php?action=admin_delete_customer', { method: 'POST', body: JSON.stringify({id: id}) })
    .then(res => res.json()).then(d => { if(d.success) location.reload(); });
}

document.getElementById('addBotResponseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api/api.php?action=admin_add_bot_response', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) { location.reload(); } });
});

function deleteBotResponse(id) {
    if(!confirm('Delete this response?')) return;
    fetch('api/api.php?action=admin_delete_bot_response', { method: 'POST', body: JSON.stringify({id: id}) })
    .then(res => res.json()).then(d => { if(d.success) location.reload(); });
}

document.getElementById('addTestimonialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api/api.php?action=admin_add_testimonial', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) { location.reload(); } });
});

function deleteTestimonial(id) {
    if(!confirm('Delete this testimonial?')) return;
    fetch('api/api.php?action=admin_delete_testimonial', { method: 'POST', body: JSON.stringify({id: id}) })
    .then(res => res.json()).then(d => { if(d.success) location.reload(); });
}

document.getElementById('addFaqForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api/api.php?action=admin_add_faq', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) { location.reload(); } });
});

function deleteFaq(id) {
    if(!confirm('Delete this FAQ?')) return;
    fetch('api/api.php?action=admin_delete_faq', { method: 'POST', body: JSON.stringify({id: id}) })
    .then(res => res.json()).then(d => { if(d.success) location.reload(); });
}
</script>

<?php include 'includes/footer.php'; ?>
