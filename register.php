<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h2>Create Account</h2>
        <form id="registerForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" required>
            </div>
            <button type="submit" class="btn-buy">Register</button>
        </form>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('api/api.php?action=register', {
        method: 'POST',
        body: JSON.stringify(data)
    }).then(res => res.json()).then(data => {
        if(data.success) window.location.href = 'login.php';
        else alert(data.message);
    });
});
</script>
<?php include 'includes/footer.php'; ?>