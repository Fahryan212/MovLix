<?php
require_once '../config/db.php';
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = 'You need to login to submit a review';
    header("Location: ../view/login.php");
    exit;
}

// Validasi input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    // Validasi dasar
    if ($movie_id <= 0 || $user_id <= 0 || $rating <= 0 || $rating > 5 || empty($comment)) {
        $_SESSION['error'] = 'Invalid review data. Please fill all fields correctly.';
        header("Location: ../view/movie_detail.php?id=" . $movie_id);
        exit;
    }

    try {
        // Cek apakah user sudah pernah mereview film ini
        $check_stmt = $pdo->prepare("SELECT * FROM reviews WHERE ID_Movie = ? AND ID_User = ?");
        $check_stmt->execute([$movie_id, $user_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $_SESSION['error'] = 'You have already reviewed this movie.';
            header("Location: ../view/movie_detail.php?id=" . $movie_id);
            exit;
        }

        // Insert review baru
        $insert_stmt = $pdo->prepare("INSERT INTO reviews (ID_User, ID_Movie, Rating, Comment) VALUES (?, ?, ?, ?)");
        $insert_stmt->execute([$user_id, $movie_id, $rating, $comment]);

        $_SESSION['success'] = 'Thank you for your review!';
        header("Location: ../view/movie_detail.php?id=" . $movie_id);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error submitting review: ' . $e->getMessage();
        header("Location: ../view/movie_detail.php?id=" . $movie_id);
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}