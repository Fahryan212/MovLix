<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    // Ambil user berdasarkan username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verifikasi password
    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user'] = $user;
        header("Location: ../index.php");
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MovLix</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body class="login-body">
    <div class="login-box">
        <h2>Log In</h2>

        <!-- Tampilkan error jika ada -->
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- Tombol kembali ke halaman utama -->
        <a href="../index.php" class="back-button">&lt; Back</a>

        <!-- Form Login -->
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <p>Don't have an account? <a href="register.php">Sign up here</a></p>

            <button type="submit">Log In</button>
        </form>
    </div>
</body>
</html>