<?php
require_once '../config/db.php';
session_start();

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== 'Admin') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied. Only admins can edit reviews.';
    exit;
}

// Validasi ID review dari query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$reviewId = (int) $_GET['id'];

// Ambil data review berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE ID_Review = :id");
$stmt->execute(['id' => $reviewId]);
$review = $stmt->fetch();

if (!$review) {
    echo 'Review not found.';
    exit;
}

// Tangani submit form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Rating must be between 1 and 5.';
    } elseif (empty($comment)) {
        $error = 'Comment cannot be empty.';
    }

    if (empty($error)) {
        $updateStmt = $pdo->prepare("
            UPDATE reviews 
            SET Rating = :rating, Comment = :comment 
            WHERE ID_Review = :id
        ");
        $updateStmt->execute([
            'rating' => $rating,
            'comment' => $comment,
            'id' => $reviewId
        ]);
        header('Location: movie_detail.php?id=' . $review['ID_Movie']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Review - MOVLIX</title>
    <link rel="stylesheet" href="../public/css/style.css" />
</head>
<body>
    <header>
        <div class="logo">MOVLIX</div>
    </header>

    <main class="form-container" style="max-width: 500px; margin: 2rem auto; background: #222; padding: 20px; border-radius: 10px; color: white;">
        <h2>Edit Review</h2>

        <?php if (!empty($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="rating">Rating (1–5):</label><br />
                <select id="rating" name="rating" required>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($review['Rating'] == $i) ? 'selected' : '' ?>>
                            <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="comment">Comment:</label><br />
                <textarea id="comment" name="comment" rows="5" required><?= htmlspecialchars($review['Comment']) ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Update Review</button>
            <a href="movie_detail.php?id=<?= $review['ID_Movie'] ?>" class="btn-cancel" style="margin-left: 1rem; color: #ccc; text-decoration: underline;">
                Cancel
            </a>
        </form>
    </main>

    <footer class="main-footer">
        <p>&copy; <?= date('Y') ?> MOVLIX. All rights reserved.</p>
    </footer>
</body>
</html>