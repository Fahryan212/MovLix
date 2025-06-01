<?php
require_once '../config/db.php';
session_start();

// Ambil ID film dari URL
$movie_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Ambil data film
$stmt = $pdo->prepare("
    SELECT movies.*, genres.Name AS genre_name, AVG(reviews.Rating) AS avg_rating
    FROM movies 
    LEFT JOIN genres ON movies.Genre_id = genres.ID_Genre
    LEFT JOIN reviews ON movies.ID_Movies = reviews.ID_Movie
    WHERE movies.ID_Movies = ?
");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch();

if (!$movie) {
    header("Location: ../index.php");
    exit;
}

// Ambil data review
$reviews = $pdo->prepare("
    SELECT reviews.*, users.Username 
    FROM reviews 
    JOIN users ON reviews.ID_User = users.ID_User
    WHERE reviews.ID_Movie = ?
    ORDER BY reviews.Created_at DESC
");
$reviews->execute([$movie_id]);
$reviews = $reviews->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($movie['Title']) ?> - MOVLIX</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="home-body">

    <!-- Header -->
    <header>
        <div class="logo">MOVLIX</div>
        <div class="top-right">
            <?php if (isset($_SESSION['user'])): ?>
                <span class="welcome-msg">Welcome, <?= htmlspecialchars($_SESSION['user']['Username']) ?></span>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="register.php" class="register-btn">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="movie-detail-container">
        <div class="movie-detail">

            <!-- Tombol Back -->
            <a href="../index.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Movies
            </a>

            <!-- Poster -->
            <div class="movie-poster-container">
                <img src="<?= htmlspecialchars($movie['Poster_url']) ?>" 
                     alt="<?= htmlspecialchars($movie['Title']) ?>" 
                     class="movie-poster"
                     onerror="this.src='../public/images/default_poster.jpg'">
            </div>

            <!-- Informasi Film -->
            <div class="movie-info">
                <h1><?= htmlspecialchars($movie['Title']) ?></h1>

                <div class="movie-meta">
                    <span><?= htmlspecialchars($movie['Release_year']) ?></span>
                    <span><?= htmlspecialchars($movie['genre_name']) ?></span>
                </div>

                <div class="movie-rating">
                    <?= number_format($movie['avg_rating'] ?? 0, 1) ?>
                    <i class="fas fa-star"></i>
                </div>

                <div class="movie-description">
                    <?= nl2br(htmlspecialchars($movie['Description'])) ?>
                </div>

                <?php if (isset($_SESSION['user']) && $_SESSION['user']['Role'] === 'Admin'): ?>
                    <div class="admin-actions">
                        <a href="edit_movie.php?id=<?= $movie['ID_Movies'] ?>" class="btn-edit">
                            <i class="fas fa-edit"></i> Edit Movie
                        </a>
                        <a href="delete_movie.php?id=<?= $movie['ID_Movies'] ?>" class="btn-delete"
                           onclick="return confirm('Are you sure you want to delete this movie?')">
                            <i class="fas fa-trash"></i> Delete Movie
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ulasan -->
            <div class="reviews-section">
                <h2>Reviews</h2>

                <?php if (empty($reviews)): ?>
                    <p>No reviews yet.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <span class="review-author"><?= htmlspecialchars($review['Username']) ?></span>
                                <span class="review-rating">
                                    <?= str_repeat('★', $review['Rating']) . str_repeat('☆', 5 - $review['Rating']) ?>
                                </span>
                                <?php if (isset($_SESSION['user']) && $_SESSION['user']['Role'] === 'Admin'): ?>
                                    <a href="edit_review.php?id=<?= $review['ID_Review'] ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="review-comment">
                                <?= nl2br(htmlspecialchars($review['Comment'])) ?>
                            </div>
                            <div class="review-date">
                                <?= date('F j, Y', strtotime($review['Created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Tambah Review -->
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="add-review">
                        <h3>Add Your Review</h3>
                        <form method="POST" action="../actions/add_review.php">
                            <input type="hidden" name="movie_id" value="<?= $movie['ID_Movies'] ?>">
                            <input type="hidden" name="user_id" value="<?= $_SESSION['user']['ID_User'] ?? '' ?>">

                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <div class="star-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'required' : '' ?> />
                                        <label for="star<?= $i ?>" title="<?= $i ?> stars">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea name="comment" id="comment" rows="4" required
                                          placeholder="Share your thoughts about this movie..."></textarea>
                            </div>

                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i> Submit Review
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Rating Tetap -->
    <div class="fixed-rating">
        <span class="fixed-rating-value"><?= number_format($movie['avg_rating'] ?? 0, 1) ?></span>
        <i class="fas fa-star"></i>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <p>&copy; <?= date('Y') ?> MOVLIX. All rights reserved.</p>
    </footer>

    <!-- Script: Fixed Rating Animation -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fixedRating = document.querySelector('.fixed-rating');
            let lastScrollPosition = 0;
            let isHidden = false;

            window.addEventListener('scroll', function () {
                const currentScrollPosition = window.pageYOffset;
                const scrollingDown = currentScrollPosition > lastScrollPosition;

                if (scrollingDown && currentScrollPosition > 100 && !isHidden) {
                    fixedRating.style.transform = 'translateY(-100px)';
                    fixedRating.style.opacity = '0';
                    isHidden = true;
                } else if (!scrollingDown && isHidden) {
                    fixedRating.style.transform = 'translateY(0)';
                    fixedRating.style.opacity = '1';
                    isHidden = false;
                }

                if (currentScrollPosition < 100) {
                    fixedRating.style.opacity = '0.1';
                } else if (!isHidden) {
                    fixedRating.style.opacity = '1';
                }

                lastScrollPosition = currentScrollPosition;
            });

            fixedRating.addEventListener('mouseenter', function () {
                this.style.transform = 'scale(1.1)';
                this.style.backgroundColor = 'rgba(0, 0, 0, 0.9)';
            });

            fixedRating.addEventListener('mouseleave', function () {
                this.style.transform = 'scale(1)';
                this.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            });
        });
    </script>
</body>

</html>
