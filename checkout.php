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
        <p>Total to Pay: <strong>₦<?php echo number_format($total, 2); ?></strong></p>
        
        <form id="checkoutForm">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            <input type="hidden" id="email" value="<?php echo $_SESSION['user_name'] ?? 'customer@example.com'; // In real app fetch email from DB ?>">
            <div class="form-group">
                <label>Confirm Shipping Address</label>
                <input type="text" name="address" required placeholder="123 Main St...">
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select style="width:100%; padding:8px;" id="paymentMethod"><option value="paystack">Pay with Card (Paystack)</option><option value="cod">Cash on Delivery</option></select>
            </div>
            <button type="submit" class="btn-buy">Place Order</button>
        </form>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    const method = document.getElementById('paymentMethod').value;

    if (method === 'paystack') {
        payWithPaystack(data);
    } else {
        processOrder(data);
    }
});

function payWithPaystack(data) {
    const handler = PaystackPop.setup({
        key: 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', // REPLACE WITH YOUR PUBLIC KEY
        email: 'customer@fedpodam.com', // In production, use actual user email
        amount: data.total * 100, // Amount in kobo
        currency: 'NGN',
        callback: function(response) {
            // Payment complete! Reference: response.reference
            data.payment_ref = response.reference;
            processOrder(data);
        },
        onClose: function() {
            alert('Transaction was not completed, window closed.');
        }
    });
    handler.openIframe();
}

function processOrder(data) {
    fetch('api/api.php?action=place_order', { method: 'POST', body: JSON.stringify(data) })
    .then(res => res.json()).then(d => {
        if(d.success) { alert('Order Placed! ID: ' + d.order_id); window.location.href = 'dashboard.php'; }
        else alert(d.message);
    });
}
</script>
<?php include 'includes/footer.php'; ?>