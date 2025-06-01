<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVLIX - Movie Collection</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="home-body">
    <header>
        <div class="logo">MOVLIX</div>
        <div class="top-right">
            <?php if (isset($_SESSION['user'])): ?>
                <span class="welcome-msg">Welcome, <?= htmlspecialchars($_SESSION['user']['Username']) ?></span>
                <a href="view/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="view/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="view/register.php" class="register-btn">Register</a>
            <?php endif; ?>
        </div>
    </header>