<?php 
include 'includes/header.php'; 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$total = $_GET['total'] ?? 0;
?>

<div class="container">
    <div class="form-container">
        <h2>Checkout</h2>
        <p>Total to Pay: <strong>$<?php echo htmlspecialchars($total); ?></strong></p>
        
        <form id="checkoutForm">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            <div class="form-group">
                <label>Confirm Shipping Address</label>
                <input type="text" name="address" required placeholder="123 Main St...">
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select style="width:100%; padding:8px;"><option>Credit Card (Mock)</option><option>Cash on Delivery</option></select>
            </div>
            <button type="submit" class="btn-buy">Place Order</button>
        </form>
    </div>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api.php?action=place_order', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => {
        if(d.success) { alert('Order Placed! ID: ' + d.order_id); window.location.href = 'dashboard.php'; }
        else alert(d.message);
    });
});
</script>
<?php include 'includes/footer.php'; ?>