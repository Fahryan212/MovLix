<?php
require_once '../config/db.php';
session_start();

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

// Ambil ID film dari URL
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($movie_id > 0) {
    try {
        // Ambil data poster untuk dihapus
        $stmt = $pdo->prepare("SELECT Poster_url FROM movies WHERE ID_Movies = ?");
        $stmt->execute([$movie_id]);
        $movie = $stmt->fetch();
        
        if ($movie) {
            // Hapus poster dari server jika bukan default
            if (strpos($movie['Poster_url'], 'default_poster.jpg') === false) {
                $filePath = str_replace('http://' . $_SERVER['HTTP_HOST'] . '/movlix/public/uploads/posters/', '../public/uploads/posters/', $movie['Poster_url']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Hapus film dari database
            $stmt = $pdo->prepare("DELETE FROM movies WHERE ID_Movies = ?");
            $stmt->execute([$movie_id]);
            
            $_SESSION['success'] = 'Movie deleted successfully!';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting movie: ' . $e->getMessage();
    }
}

header("Location: ../index.php");
exit;