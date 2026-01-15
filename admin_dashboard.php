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
?>

<div class="container">
    <div class="admin-header">
        <h2>Admin Dashboard</h2>
        <p>Manage Orders & Inventory</p>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:2rem;">
        
        <!-- Order Management -->
        <div>
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

        <!-- Add Product -->
        <div class="form-container" style="margin:0; max-width:100%; position:sticky; top:20px; height:fit-content;">
            <h3 id="productFormTitle">Add New Product</h3>
            <form id="productForm">
                <input type="hidden" name="id" id="prodId">
                <input type="hidden" name="action" id="formAction" value="add">
                
                <div class="form-group">
                    <label>Name</label><input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Price</label><input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" style="width:100%; padding:8px;">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image URL</label><input type="text" name="image_url" placeholder="http://..." required>
                </div>
                <div class="form-group">
                    <label>Stock</label><input type="number" name="stock" value="100">
                </div>
                <div class="form-group">
                    <label>Description</label><input type="text" name="description" required>
                </div>
                <button type="submit" class="btn-buy" id="saveBtn">Add Product</button>
                <button type="button" onclick="resetForm()" style="margin-top:10px; background:#666; color:white; border:none; padding:5px 10px; cursor:pointer; display:none;" id="cancelBtn">Cancel Edit</button>
            </form>
        </div>

        <!-- Manage Banners -->
        <div style="grid-column: 1 / -1; margin-top: 2rem;">
            <h3>Homepage Banners (Ads)</h3>
            <div style="display:flex; gap:2rem;">
                <div class="form-container" style="margin:0; flex:1;">
                    <h4>Add New Banner</h4>
                    <form id="addBannerForm">
                        <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
                        <div class="form-group"><label>Subtitle</label><input type="text" name="subtitle" required></div>
                        <div class="form-group"><label>Image URL</label><input type="text" name="image_url" required></div>
                        <button type="submit" class="btn-buy">Publish Banner</button>
                    </form>
                </div>
                <div style="flex:2;">
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

        <!-- Manage Categories -->
        <div style="grid-column: 1 / -1; margin-top: 2rem;">
            <h3>Manage Categories</h3>
            <div style="display:flex; gap:2rem;">
                <div class="form-container" style="margin:0; flex:1;">
                    <h4>Add Category</h4>
                    <form id="addCategoryForm">
                        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
                        <button type="submit" class="btn-buy">Add Category</button>
                    </form>
                </div>
                <div style="flex:2;">
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

        <!-- Product List (For Editing) -->
        <div style="grid-column: 1 / -1; margin-top: 2rem;">
            <h3>Manage Inventory</h3>
            <table>
                <tr><th>ID</th><th>Image</th><th>Name</th><th>Price</th><th>Stock</th><th>Action</th></tr>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><img src="<?php echo $p['image_url']; ?>" style="height:40px;"></td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td>₦<?php echo number_format($p['price'], 2); ?></td>
                    <td><?php echo $p['stock_quantity']; ?></td>
                    <td>
                        <button onclick='editProduct(<?php echo json_encode($p); ?>)' style="background:blue; color:white; border:none; padding:5px; cursor:pointer;">Edit</button>
                        <button onclick="deleteProduct(<?php echo $p['id']; ?>)" style="background:red; color:white; border:none; padding:5px; cursor:pointer;">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

<script>
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
    document.getElementById('prodImg').value = p.image_url;
    document.getElementById('prodStock').value = p.stock_quantity;
    document.getElementById('prodDesc').value = p.description;
    document.getElementById('saveBtn').innerText = 'Update Product';
    document.getElementById('cancelBtn').style.display = 'inline-block';
    
    // Scroll to form
    document.querySelector('.form-container').scrollIntoView({behavior: 'smooth'});
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
</script>

<?php include 'includes/footer.php'; ?>
