<?php 
include 'includes/header.php'; 
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="container">
    <div class="admin-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2>Admin Settings</h2>
            <p>Manage Global Configurations</p>
        </div>
        <a href="admin_dashboard.php" class="btn-buy" style="width:auto; background:#555;">Back to Dashboard</a>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem;">
        <!-- General Settings -->
        <div class="form-container" style="margin:0; max-width:100%;">
            <h3>General Information</h3>
            <form id="settingsForm">
                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="text" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="contact_address" value="<?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn-buy">Save Settings</button>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="form-container" style="margin:0; max-width:100%;">
            <h3>Create New Admin</h3>
            <p style="font-size:0.9rem; color:#666; margin-bottom:1rem;">Add a new administrator account.</p>
            <form id="newAdminForm">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-buy" style="background:#333;">Create Admin</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api/api.php?action=admin_update_settings', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) alert('Settings Saved!'); });
});

document.getElementById('newAdminForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api/api.php?action=admin_create_admin', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => { if(d.success) { alert('Admin Created!'); this.reset(); } });
});
</script>

<?php include 'includes/footer.php'; ?>