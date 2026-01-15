<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fedpodam Express | Interactive E-Commerce</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Navigation -->
    <header>
        <h1><a href="index.php" style="color:white; text-decoration:none;">Fedpodam Express</a></h1>
        
        <div class="menu-toggle" onclick="document.querySelector('.nav-links').classList.toggle('active')">
            ☰
        </div>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="cart.php">Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>