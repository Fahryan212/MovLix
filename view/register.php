<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = ucfirst(strtolower($_POST['role'])); // Konversi ke format 'Admin'/'User'

    // Validasi role
    if (!in_array($role, ['Admin', 'User'])) {
        $error = "Role tidak valid.";
    } else {
        // Cek apakah username/email sudah ada
        $stmt = $pdo->prepare("SELECT * FROM users WHERE Username = ? OR Email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $error = "Username atau email sudah terdaftar.";
        } else {
            // Simpan user baru
            $stmt = $pdo->prepare("INSERT INTO users (Username, Email, Password, Role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $password, $role])) {
                // Login otomatis
                $last_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("SELECT * FROM users WHERE ID_User = ?");
                $stmt->execute([$last_id]);
                $_SESSION['user'] = $stmt->fetch();

                header("Location: ../index.php");
                exit;
            } else {
                $error = "Registrasi gagal. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - MovLix</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body class="login-body">
    <div class="login-box">
        <h2>Register</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <a href="../index.php" class="back-button">&lt; Back</a>

        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>

            <select name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select><br><br>

            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here!</a></p>
    </div>
</body>
</html>