<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container" style="border-top: 5px solid #333;">
        <h2>Admin Portal</h2>
        <form id="adminLoginForm">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-buy" style="background-color:#333;">Login as Admin</button>
        </form>
    </div>
</div>

<script>
document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('api.php?action=admin_login', {
        method: 'POST',
        body: JSON.stringify(data)
    }).then(res => res.json()).then(data => {
        if(data.success) window.location.href = 'admin_dashboard.php';
        else alert(data.message);
    });
});
</script>

<?php include 'includes/footer.php'; ?>