<?php 
include 'includes/header.php'; 
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT o.id, o.total_amount, o.created_at, s.status, s.tracking_number 
    FROM orders o 
    LEFT JOIN shipping s ON o.id = s.order_id 
    WHERE o.customer_id = ? ORDER BY o.id DESC
");
$stmt->execute([$uid]);
$orders = $stmt->fetchAll();
?>

<div class="container">
    <h2>Welcome, <?php echo $_SESSION['user_name']; ?></h2>
    <h3>Your Orders</h3>
    
    <?php if (empty($orders)): ?>
        <p>No orders yet.</p>
    <?php else: ?>
        <table>
            <tr><th>Order ID</th><th>Date</th><th>Total</th><th>Status</th><th>Track</th></tr>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?php echo $o['id']; ?></td>
                <td><?php echo $o['created_at']; ?></td>
                <td>₦<?php echo number_format($o['total_amount'], 2); ?></td>
                <td><?php echo $o['status'] ?? 'Pending'; ?></td>
                <td><?php echo $o['tracking_number'] ?? 'N/A'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>