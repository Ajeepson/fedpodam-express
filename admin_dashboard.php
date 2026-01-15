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
                    <td>$<?php echo $o['total_amount']; ?></td>
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
        <div class="form-container" style="margin:0; max-width:100%;">
            <h3>Add New Product</h3>
            <form id="addProductForm">
                <div class="form-group">
                    <label>Name</label><input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Price</label><input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" style="width:100%; padding:8px;">
                        <option>Apparel</option><option>Electronics</option><option>Home</option>
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
                <button type="submit" class="btn-buy">Add Product</button>
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
    </div>
</div>

<script>
function updateStatus(orderId, status) {
    if(!status) return;
    fetch('api.php?action=admin_update_order', {
        method: 'POST',
        body: JSON.stringify({ order_id: orderId, status: status })
    }).then(res => res.json()).then(d => {
        if(d.success) location.reload();
    });
}

document.getElementById('addProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api.php?action=admin_add_product', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) { alert('Product Added'); location.reload(); } });
});

document.getElementById('addBannerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api.php?action=admin_add_banner', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) { location.reload(); } });
});

function deleteBanner(id) {
    if(!confirm('Delete this banner?')) return;
    fetch('api.php?action=admin_delete_banner', { method: 'POST', body: JSON.stringify({id: id}) })
    .then(res => res.json()).then(d => { if(d.success) location.reload(); });
}
</script>

<?php include 'includes/footer.php'; ?>
