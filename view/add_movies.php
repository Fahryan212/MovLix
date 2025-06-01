<?php
require_once '../config/db.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['title']));
    $genre_id = intval($_POST['genre_id']);
    $release_year = intval($_POST['release_year']);
    $description = htmlspecialchars(trim($_POST['description']));
    $poster_url = '';

    // Handle file upload
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['poster']['tmp_name'];
        $fileName = $_FILES['poster']['name'];
        $fileSize = $_FILES['poster']['size'];
        $fileType = $_FILES['poster']['type'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        // Validate file
        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
            $newFileName = md5(time() . $fileName) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
            $uploadDir = '../public/uploads/posters/';
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $poster_url = 'http://' . $_SERVER['HTTP_HOST'] . '/movlix/public/uploads/posters/' . $newFileName;
            } else {
                $error = 'Error uploading the file.';
            }
        } else {
            $error = 'Invalid file type or size.';
        }
    } else {
        $error = 'Please select a poster image.';
    }

    // Validate other inputs
    if (empty($error)) {
        if (empty($title) || empty($genre_id) || empty($release_year) || empty($poster_url)) {
            $error = 'Please fill all required fields!';
        } elseif ($release_year < 1900 || $release_year > date('Y') + 5) {
            $error = 'Invalid release year!';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO movies (Title, Genre_id, Release_year, Poster_url, Description) 
                                        VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $genre_id, $release_year, $poster_url, $description]);
                
                $success = 'Movie added successfully!';
                $_POST = array(); // Reset form after success
            } catch (PDOException $e) {
                $error = 'Error adding movie: ' . $e->getMessage();
            }
        }
    }
}

// Fetch genres for dropdown
$genres = $pdo->query("SELECT * FROM genres ORDER BY Name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Movie - MOVLIX Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #222;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #444;
        }
        
        .admin-title {
            color: #d32f2f;
            margin: 0;
        }
        
        .back-btn {
            color: #fff;
            text-decoration: none;
            background: #444;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #555;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #ddd;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            background: #333;
            border: 1px solid #444;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #d32f2f;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn-submit {
            background: #d32f2f;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            width: 100%;
        }
        
        .btn-submit:hover {
            background: #b71c1c;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #2e7d32;
            color: white;
        }
        
        .alert-error {
            background: #c62828;
            color: white;
        }
        
        .preview-container {
            margin-top: 10px;
            text-align: center;
        }
        
        .poster-preview {
            max-width: 200px;
            max-height: 300px;
            border-radius: 5px;
            border: 2px solid #444;
            display: none;
            margin: 0 auto;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title"><i class="fas fa-plus-circle"></i> Add New Movie</h1>
            <a href="../index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="movieForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title" class="form-label">Title *</label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="genre_id" class="form-label">Genre *</label>
                <select id="genre_id" name="genre_id" class="form-control" required>
                    <option value="">-- Select Genre --</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?= $genre['ID_Genre'] ?>" 
                            <?= ($_POST['genre_id'] ?? '') == $genre['ID_Genre'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($genre['Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="release_year" class="form-label">Release Year *</label>
                <input type="number" id="release_year" name="release_year" class="form-control" 
                       min="1900" max="<?= date('Y') + 5 ?>" 
                       value="<?= htmlspecialchars($_POST['release_year'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="poster" class="form-label">Poster Image *</label>
                <input type="file" id="poster" name="poster" class="form-control" accept="image/*" required>
                <small class="form-text">Max size: 2MB (Format: JPG, PNG, JPEG)</small>
                <div class="preview-container">
                    <img id="posterPreview" class="poster-preview" src="" alt="Poster Preview">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control"><?= 
                    htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Add Movie
            </button>
        </form>
    </div>

    <script>
        // Preview image when selected
        document.getElementById('poster').addEventListener('change', function(e) {
            const preview = document.getElementById('posterPreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>
