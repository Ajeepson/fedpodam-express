<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h2>Login</h2>
        <form id="loginForm">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-buy">Login</button>
        </form>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('api.php?action=login', {
        method: 'POST',
        body: JSON.stringify(data)
    }).then(res => res.json()).then(data => {
        if(data.success) window.location.href = 'dashboard.php';
        else alert(data.message);
    });
});
</script>
<?php include 'includes/footer.php'; ?>