<?php 
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Handle genre actions
if (isset($_GET['genre_action'])) {
    $action = $_GET['genre_action'];
    $id = isset($_GET['genre_id']) ? $_GET['genre_id'] : null;
    
    if ($action == 'delete' && $id) {
        // Delete genre
        $stmt = $pdo->prepare("DELETE FROM genres WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Genre deleted successfully";
        header("Location: movies.php");
        exit();
    }
}

// Handle genre form submission
if (isset($_POST['genre_submit'])) {
    $genre_id = isset($_POST['genre_id']) ? $_POST['genre_id'] : null;
    $genre_name = trim($_POST['genre_name']);
    $genre_description = isset($_POST['genre_description']) ? trim($_POST['genre_description']) : '';
    
    try {
        // Check if description column exists
        $columns = $pdo->query("SHOW COLUMNS FROM genres LIKE 'description'")->fetchAll();
        $has_description = count($columns) > 0;
        
        if ($genre_id) {
            // Update existing genre
            if ($has_description) {
                $stmt = $pdo->prepare("UPDATE genres SET name=?, description=? WHERE id=?");
                $stmt->execute([$genre_name, $genre_description, $genre_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE genres SET name=? WHERE id=?");
                $stmt->execute([$genre_name, $genre_id]);
            }
            $_SESSION['message'] = "Genre updated successfully";
        } else {
            // Add new genre
            if ($has_description) {
                $stmt = $pdo->prepare("INSERT INTO genres (name, description) VALUES (?, ?)");
                $stmt->execute([$genre_name, $genre_description]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
                $stmt->execute([$genre_name]);
            }
            $_SESSION['message'] = "Genre added successfully";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error saving genre: " . $e->getMessage();
    }
    
    header("Location: movies.php");
    exit();
}

// Handle movie actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if ($action == 'delete' && $id) {
        // Delete movie
        $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Movie deleted successfully";
        header("Location: movies.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $release_year = $_POST['release_year'];
    $duration = $_POST['duration'];
    $poster_url = $_POST['poster_url'];
    $video_url = $_POST['video_url'];
    $genre_id = $_POST['genre_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    if ($id) {
        // Update existing movie
        $stmt = $pdo->prepare("UPDATE movies SET title=?, description=?, release_year=?, duration=?, poster_url=?, video_url=?, genre_id=?, featured=? WHERE id=?");
        $stmt->execute([$title, $description, $release_year, $duration, $poster_url, $video_url, $genre_id, $featured, $id]);
        $_SESSION['message'] = "Movie updated successfully";
    } else {
        // Add new movie
        $stmt = $pdo->prepare("INSERT INTO movies (title, description, release_year, duration, poster_url, video_url, genre_id, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $release_year, $duration, $poster_url, $video_url, $genre_id, $featured]);
        $_SESSION['message'] = "Movie added successfully";
    }
    
    header("Location: movies.php");
    exit();
}

// Get movie for editing
$movie = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get genre for editing
$edit_genre = null;
if (isset($_GET['genre_action']) && $_GET['genre_action'] == 'edit' && isset($_GET['genre_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM genres WHERE id = ?");
    $stmt->execute([$_GET['genre_id']]);
    $edit_genre = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all genres with error handling
try {
    $genres = $pdo->query("SELECT * FROM genres ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($genres)) {
        $_SESSION['warning'] = "No genres found. Please add genres first.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error loading genres: " . $e->getMessage();
    $genres = [];
}

// Get all movies with error handling
try {
    $movies = $pdo->query("SELECT m.*, g.name as genre FROM movies m LEFT JOIN genres g ON m.genre_id = g.id ORDER BY m.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error loading movies: " . $e->getMessage();
    $movies = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.5);
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.1);
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar bg-dark">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="movies.php">
                                <i class="bi bi-film me-2"></i>Movies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="slides.php">
                                <i class="bi bi-images me-2"></i>Slides
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#genresSection" onclick="document.getElementById('genresSection').scrollIntoView({behavior: 'smooth'});">
                                <i class="bi bi-tags me-2"></i>Genres
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Movies & Genres</h1>
                    <div class="btn-group" role="group">
                        <a href="#movieForm" class="btn btn-outline-primary">Movies</a>
                        <a href="#genresSection" class="btn btn-outline-success">Genres</a>
                    </div>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['warning'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <?= $_SESSION['warning'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['warning']); ?>
                <?php endif; ?>

                <!-- Movie Form -->
                <div class="card mb-4" id="movieForm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= isset($movie) ? 'Edit Movie' : 'Add New Movie' ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if (isset($movie)): ?>
                                <input type="hidden" name="id" value="<?= $movie['id'] ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?= isset($movie) ? $movie['title'] : '' ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?= isset($movie) ? $movie['description'] : '' ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="release_year" class="form-label">Release Year</label>
                                    <input type="number" class="form-control" id="release_year" name="release_year" value="<?= isset($movie) ? $movie['release_year'] : '' ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="duration" class="form-label">Duration (minutes)</label>
                                    <input type="number" class="form-control" id="duration" name="duration" value="<?= isset($movie) ? $movie['duration'] : '' ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="genre_id" class="form-label">Genre</label>
                                    <select class="form-select" id="genre_id" name="genre_id" required>
                                        <option value="">Select Genre</option>
                                    <?php if (!empty($genres)): ?>
                                        <?php foreach ($genres as $genre): ?>
                                            <option value="<?= $genre['id'] ?>" <?= (isset($movie) && $movie['genre_id'] == $genre['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($genre['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">No genres available - Please add genres first</option>
                                    <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="poster_url" class="form-label">Poster URL</label>
                                <input type="url" class="form-control" id="poster_url" name="poster_url" value="<?= isset($movie) ? $movie['poster_url'] : '' ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="video_url" class="form-label">Video URL</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" value="<?= isset($movie) ? $movie['video_url'] : '' ?>" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="featured" name="featured" <?= (isset($movie) && $movie['featured']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="featured">Featured Movie</label>
                            </div>
                            <button type="submit" class="btn btn-primary"><?= isset($movie) ? 'Update Movie' : 'Add Movie' ?></button>
                            <?php if (isset($movie)): ?>
                                <a href="movies.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Genres Management Section -->
                <div class="card mb-4" id="genresSection">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><?= isset($edit_genre) ? 'Edit Genre' : 'Add New Genre' ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if (isset($edit_genre)): ?>
                                <input type="hidden" name="genre_id" value="<?= $edit_genre['id'] ?>">
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="genre_name" class="form-label">Genre Name</label>
                                    <input type="text" class="form-control" id="genre_name" name="genre_name" value="<?= isset($edit_genre) ? htmlspecialchars($edit_genre['name']) : '' ?>" required>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="genre_description" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="genre_description" name="genre_description" value="<?= isset($edit_genre) ? htmlspecialchars($edit_genre['description'] ?? '') : '' ?>" placeholder="Optional">
                                </div>
                            </div>
                            <button type="submit" name="genre_submit" class="btn btn-success"><?= isset($edit_genre) ? 'Update Genre' : 'Add Genre' ?></button>
                            <?php if (isset($edit_genre)): ?>
                                <a href="movies.php#genresSection" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Genres List -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">All Genres</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Genre Name</th>
                                        <th>Description</th>
                                        <th>Movie Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($genres)): ?>
                                        <?php foreach ($genres as $g): ?>
                                            <?php
                                            try {
                                                $count_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM movies WHERE genre_id = ?");
                                                $count_stmt->execute([$g['id']]);
                                                $movie_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                            } catch (PDOException $e) {
                                                $movie_count = 0;
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $g['id'] ?></td>
                                                <td><span class="badge bg-info"><?= htmlspecialchars($g['name']) ?></span></td>
                                                <td><?= htmlspecialchars($g['description'] ?? 'N/A') ?></td>
                                                <td><span class="badge bg-primary"><?= $movie_count ?> movies</span></td>
                                                <td>
                                                    <a href="movies.php?genre_action=edit&genre_id=<?= $g['id'] ?>#genresSection" class="btn btn-sm btn-warning">Edit</a>
                                                    <?php if ($movie_count == 0): ?>
                                                        <a href="movies.php?genre_action=delete&genre_id=<?= $g['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this genre?')">Delete</a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete genre with movies">Delete</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No genres found. Add your first genre above!</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Movies List -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">All Movies</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Poster</th>
                                        <th>Title</th>
                                        <th>Genre</th>
                                        <th>Year</th>
                                        <th>Duration</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($movies)): ?>
                                        <?php foreach ($movies as $m): ?>
                                            <tr>
                                                <td><?= $m['id'] ?></td>
                                                <td><img src="<?= htmlspecialchars($m['poster_url']) ?>" width="50" height="75" style="object-fit: cover;" alt="<?= htmlspecialchars($m['title']) ?>" onerror="this.src='https://via.placeholder.com/50x75?text=No+Image'"></td>
                                                <td><?= htmlspecialchars($m['title']) ?></td>
                                                <td><span class="badge bg-info"><?= htmlspecialchars($m['genre'] ?? 'N/A') ?></span></td>
                                                <td><?= $m['release_year'] ?></td>
                                                <td><?= floor($m['duration']/60) ?>h <?= $m['duration']%60 ?>m</td>
                                                <td><?= $m['featured'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                                                <td>
                                                    <a href="movies.php?action=edit&id=<?= $m['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                    <a href="movies.php?action=delete&id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this movie?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No movies found. Add your first movie above!</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>