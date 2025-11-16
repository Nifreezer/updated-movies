<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('SITE_NAME') ? SITE_NAME : 'MovieFlix' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            color: #fff;
        }
        .movie-card {
            transition: all 0.4s ease;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .movie-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .movie-card img {
            transition: transform 0.4s ease;
        }
        .movie-card:hover img {
            transform: scale(1.1);
        }
        .carousel-item {
            height: 400px;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .carousel-content {
            width: 80%;
            height: 100%;
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .carousel-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.8));
            z-index: 1;
        }
        .carousel-caption {
            position: relative;
            z-index: 2;
            bottom: 50px !important;
        }
        .carousel-caption h5 {
            font-size: 2rem;
            font-weight: 700;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
        }
        .carousel-caption p {
            font-size: 1rem;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.8);
        }
        .admin-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .user-auth-links {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 10px;
            border-radius: 10px;
        }
        .genre-filter {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .genre-badge {
            padding: 8px 16px;
            margin: 5px;
            border-radius: 25px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .genre-badge:hover, .genre-badge.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .navbar {
            background: rgba(0, 0, 0, 0.5) !important;
            backdrop-filter: blur(10px);
        }
        .card-body {
            background: rgba(0, 0, 0, 0.5);
        }
        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- User Auth Links -->
    <div class="user-auth-links">
        <?php if (isset($_SESSION['user_logged_in'])): ?>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    Welcome, <?= $_SESSION['user_username'] ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary me-2">Login</a>
            <a href="signup.php" class="btn btn-success">Sign Up</a>
        <?php endif; ?>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
            <?php if (defined('SITE_LOGO_URL') && SITE_LOGO_URL): ?>
                <img src="<?= SITE_LOGO_URL ?>" alt="<?= defined('SITE_NAME') ? SITE_NAME : 'Site' ?> Logo" height="32" class="me-2">
            <?php endif; ?>
            <?= defined('SITE_NAME') ? SITE_NAME : 'MovieFlix' ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#movies">Movies</a></li>
                    <li class="nav-item"><a class="nav-link" href="#genres">Genres</a></li>
                </ul>
                <form class="d-flex" method="GET" action="index.php">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search movies..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Slideshow -->
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $stmt = $pdo->query("SELECT * FROM slides WHERE active = TRUE ORDER BY created_at DESC LIMIT 5");
            $first = true;
            while ($slide = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="carousel-item'.($first ? ' active' : '').'">';
                echo '<div class="carousel-content" style="background-image: url('.$slide['image_url'].')">';
                echo '<div class="carousel-caption d-none d-md-block">';
                echo '<h5>'.$slide['title'].'</h5>';
                echo '<p>'.$slide['description'].'</p>';
                echo '<a href="watch.php?slide='.$slide['id'].'" class="btn btn-primary">Watch Now</a>';
                echo '</div></div></div>';
                $first = false;
            }
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- Genre Filter -->
    <div class="container mt-5" id="genres">
        <div class="genre-filter">
            <h3 class="text-center mb-4">Browse by Genre</h3>
            <div class="text-center">
                <a href="index.php" class="genre-badge <?= !isset($_GET['genre']) ? 'active' : '' ?>">All</a>
                <?php
                try {
                    $genres = $pdo->query("SELECT * FROM genres ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($genres as $genre) {
                        $active = (isset($_GET['genre']) && $_GET['genre'] == $genre['id']) ? 'active' : '';
                        echo '<a href="index.php?genre='.$genre['id'].'" class="genre-badge '.$active.'">'.htmlspecialchars($genre['name']).'</a>';
                    }
                } catch (PDOException $e) {
                    echo '<p class="text-danger">Error loading genres</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Featured Movies -->
    <div class="container mt-5" id="movies">
        <h2 class="section-title">Featured Movies</h2>
        <div class="row">
            <?php
            $genre_filter = isset($_GET['genre']) ? intval($_GET['genre']) : null;
            $search_query = isset($_GET['search']) ? trim($_GET['search']) : null;
            
            try {
                if ($search_query) {
                    // When searching, show all matching movies in featured section
                    $stmt = $pdo->prepare("SELECT m.*, g.name as genre FROM movies m LEFT JOIN genres g ON m.genre_id = g.id WHERE m.title LIKE ? ORDER BY created_at DESC LIMIT 8");
                    $stmt->execute(['%'.$search_query.'%']);
                } elseif ($genre_filter) {
                    $stmt = $pdo->prepare("SELECT m.*, g.name as genre FROM movies m LEFT JOIN genres g ON m.genre_id = g.id WHERE featured = TRUE AND m.genre_id = ? ORDER BY created_at DESC LIMIT 8");
                    $stmt->execute([$genre_filter]);
                } else {
                    $stmt = $pdo->query("SELECT m.*, g.name as genre FROM movies m LEFT JOIN genres g ON m.genre_id = g.id WHERE featured = TRUE ORDER BY created_at DESC LIMIT 8");
                }
                
                $movies_found = false;
                while ($movie = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $movies_found = true;
                    echo '<div class="col-md-3 mb-4">';
                    echo '<div class="card movie-card h-100" onclick="window.location=\'watch.php?id='.$movie['id'].'\'">';
                    echo '<img src="'.htmlspecialchars($movie['poster_url']).'" class="card-img-top" alt="'.htmlspecialchars($movie['title']).'" onerror="this.src=\'https://via.placeholder.com/300x450?text=No+Image\'">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">'.htmlspecialchars($movie['title']).'</h5>';
                    echo '<span class="badge bg-secondary">'.htmlspecialchars($movie['genre'] ?? 'N/A').'</span>';
                    echo '<span class="badge bg-info text-dark ms-1">'.$movie['release_year'].'</span>';
                    echo '</div></div></div>';
                }
                
                if (!$movies_found) {
                    echo '<div class="col-12"><p class="text-center text-muted">No featured movies found.</p></div>';
                }
            } catch (PDOException $e) {
                echo '<div class="col-12"><p class="text-center text-danger">Error loading movies</p></div>';
            }
            ?>
        </div>
    </div>

    <!-- Latest Movies -->
    <div class="container mt-5 mb-5">
        <h2 class="section-title">
            <?php 
            if ($search_query) {
                echo 'Search Results for "'.htmlspecialchars($search_query).'"';
            } else {
                echo 'Latest Releases';
            }
            ?>
        </h2>
        <div class="row">
            <?php
            try {
                if ($search_query) {
                    // When searching, show all matching movies in latest releases section too
                    $stmt = $pdo->prepare("SELECT m.*, g.name as genre FROM movies m LEFT JOIN genres g ON m.genre_id = g.id WHERE m.title LIKE ? ORDER BY created_at DESC LIMIT 12");
                    $stmt->execute(['%'.$search_query.'%']);
                } elseif ($genre_filter) {
                    $stmt = $pdo->prepare("SELECT m.*, g.name as genre FROM movies m LEFT JOIN genres g ON m.genre_id = g.id WHERE m.genre_id = ? ORDER BY created_at DESC LIMIT 12");
                    $stmt->execute([$genre_filter]);
                } else {
                    $stmt = $pdo->query("SELECT m.*, g.name as genre FROM movies m LEFT JOIN genres g ON m.genre_id = g.id ORDER BY created_at DESC LIMIT 12");
                }
                
                $movies_found = false;
                while ($movie = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $movies_found = true;
                    echo '<div class="col-md-2 mb-4">';
                    echo '<div class="card movie-card h-100" onclick="window.location=\'watch.php?id='.$movie['id'].'\'">';
                    echo '<img src="'.htmlspecialchars($movie['poster_url']).'" class="card-img-top" alt="'.htmlspecialchars($movie['title']).'" onerror="this.src=\'https://via.placeholder.com/200x300?text=No+Image\'">';
                    echo '<div class="card-body p-2">';
                    echo '<h6 class="card-title">'.htmlspecialchars($movie['title']).'</h6>';
                    echo '</div></div></div>';
                }
                
                if (!$movies_found) {
                    echo '<div class="col-12"><p class="text-center text-muted">';
                    if ($search_query) {
                        echo 'No movies found matching "'.htmlspecialchars($search_query).'".';
                    } else {
                        echo 'No movies found.';
                    }
                    echo '</p></div>';
                }
            } catch (PDOException $e) {
                echo '<div class="col-12"><p class="text-center text-danger">Error loading movies</p></div>';
            }
            ?>
        </div>
    </div>

    <!-- Admin Link -->
    <a href="admin/login.php" class="btn btn-danger admin-link rounded-pill shadow">
        <i class="bi bi-person-lock"></i> Admin Login
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>