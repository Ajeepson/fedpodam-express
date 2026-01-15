<?php 
include 'includes/header.php'; 
require_once 'config.php';

$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    // Securely fetch only items in cart
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $total += $subtotal;
        $p['qty'] = $qty;
        $cartItems[] = $p;
    }
}
?>

<div class="container">
    <h2>Your Shopping Cart</h2>
    <?php if (empty($cartItems)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
            <?php foreach ($cartItems as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>₦<?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo $item['qty']; ?></td>
                <td>₦<?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h3>Total: ₦<?php echo number_format($total, 2); ?></h3>
        <a href="checkout.php?total=<?php echo $total; ?>" class="btn-buy" style="display:inline-block; text-align:center; text-decoration:none;">Proceed to Checkout</a>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>