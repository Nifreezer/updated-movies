<?php 
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if ($action == 'delete' && $id) {
        // Delete slide
        $stmt = $pdo->prepare("DELETE FROM slides WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Slide deleted successfully";
        header("Location: slides.php");
        exit();
    } elseif ($action == 'toggle' && $id) {
        // Toggle slide active status
        $stmt = $pdo->prepare("UPDATE slides SET active = NOT active WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Slide status updated";
        header("Location: slides.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];
    $video_url = $_POST['video_url'];
    $active = isset($_POST['active']) ? 1 : 0;
    
    if ($id) {
        // Update existing slide
        $stmt = $pdo->prepare("UPDATE slides SET title=?, description=?, image_url=?, video_url=?, active=? WHERE id=?");
        $stmt->execute([$title, $description, $image_url, $video_url, $active, $id]);
        $_SESSION['message'] = "Slide updated successfully";
    } else {
        // Add new slide
        $stmt = $pdo->prepare("INSERT INTO slides (title, description, image_url, video_url, active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $image_url, $video_url, $active]);
        $_SESSION['message'] = "Slide added successfully";
    }
    
    header("Location: slides.php");
    exit();
}

// Get slide for editing
$slide = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM slides WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $slide = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all slides
$slides = $pdo->query("SELECT * FROM slides ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Slides</title>
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
                            <a class="nav-link" href="movies.php">
                                <i class="bi bi-film me-2"></i>Movies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="slides.php">
                                <i class="bi bi-images me-2"></i>Slides
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
                    <h1 class="h2"><?= isset($slide) ? 'Edit Slide' : 'Add New Slide' ?></h1>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <!-- Slide Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="POST">
                            <?php if (isset($slide)): ?>
                                <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?= isset($slide) ? $slide['title'] : '' ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?= isset($slide) ? $slide['description'] : '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="image_url" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" value="<?= isset($slide) ? $slide['image_url'] : '' ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="video_url" class="form-label">Video URL</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" value="<?= isset($slide) ? $slide['video_url'] : '' ?>" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="active" name="active" <?= (isset($slide) && $slide['active']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                            <button type="submit" class="btn btn-primary"><?= isset($slide) ? 'Update Slide' : 'Add Slide' ?></button>
                        </form>
                    </div>
                </div>

                <!-- Slides List -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">All Slides</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($slides as $s): ?>
                                        <tr>
                                            <td><?= $s['id'] ?></td>
                                            <td><img src="<?= $s['image_url'] ?>" width="100" height="60" style="object-fit: cover;"></td>
                                            <td><?= $s['title'] ?></td>
                                            <td><?= substr($s['description'], 0, 50) ?>...</td>
                                            <td>
                                                <?= $s['active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                                            </td>
                                            <td>
                                                <a href="slides.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="slides.php?action=toggle&id=<?= $s['id'] ?>" class="btn btn-sm btn-info">Toggle</a>
                                                <a href="slides.php?action=delete&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
