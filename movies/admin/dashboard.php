<?php 
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
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
                            <a class="nav-link" href="../index.php">
                                <i class="bi bi-house me-2"></i>Back to Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="movies.php">
                                <i class="bi bi-film me-2"></i>Movies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="slides.php">
                                <i class="bi bi-images me-2"></i>Slides
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="monetization.php">
                                <i class="bi bi-currency-dollar me-2"></i>Monetization
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="shareDashboard()"><i class="bi bi-share"></i> Share</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportDashboard()"><i class="bi bi-download"></i> Export</button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card bg-primary">
                            <h5>Total Movies</h5>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) as total FROM movies");
                            $count = $stmt->fetch();
                            ?>
                            <h2><?= $count['total'] ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-success">
                            <h5>Total Views</h5>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) as total FROM views");
                            $count = $stmt->fetch();
                            ?>
                            <h2><?= $count['total'] ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-info">
                            <h5>Active Slides</h5>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) as total FROM slides WHERE active = TRUE");
                            $count = $stmt->fetch();
                            ?>
                            <h2><?= $count['total'] ?></h2>
                        </div>
                    </div>
                </div>

                <!-- Recent Movies -->
                <div class="mt-5">
                    <h4>Recent Movies</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Genre</th>
                                    <th>Year</th>
                                    <th>Views</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT m.id, m.title, m.release_year, g.name as genre, 
                                                    (SELECT COUNT(*) FROM views WHERE movie_id = m.id) as views
                                                    FROM movies m LEFT JOIN genres g ON m.genre_id = g.id
                                                    ORDER BY m.created_at DESC LIMIT 5");
                                while ($movie = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td>'.$movie['id'].'</td>';
                                    echo '<td>'.$movie['title'].'</td>';
                                    echo '<td>'.$movie['genre'].'</td>';
                                    echo '<td>'.$movie['release_year'].'</td>';
                                    echo '<td>'.$movie['views'].'</td>';
                                    echo '<td>
                                            <a href="movies.php?action=edit&id='.$movie['id'].'" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="movies.php?action=delete&id='.$movie['id'].'" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</a>
                                          </td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Views -->
                <div class="mt-5">
                    <h4>Recent Views</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Movie</th>
                                    <th>Date</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT v.id, m.title, v.view_date, v.ip_address 
                                                    FROM views v JOIN movies m ON v.movie_id = m.id
                                                    ORDER BY v.view_date DESC LIMIT 5");
                                while ($view = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td>'.$view['id'].'</td>';
                                    echo '<td>'.$view['title'].'</td>';
                                    echo '<td>'.date('M d, Y H:i', strtotime($view['view_date'])).'</td>';
                                    echo '<td>'.$view['ip_address'].'</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function shareDashboard() {
            const dashboardUrl = window.location.href;
            
            // Check if Web Share API is available
            if (navigator.share) {
                navigator.share({
                    title: 'Admin Dashboard',
                    text: 'Check out this dashboard',
                    url: dashboardUrl
                }).catch((error) => console.log('Error sharing:', error));
            } else {
                // Fallback: Copy to clipboard
                navigator.clipboard.writeText(dashboardUrl).then(() => {
                    alert('Dashboard link copied to clipboard!');
                }).catch(() => {
                    // Final fallback: Show URL in prompt
                    prompt('Copy this link:', dashboardUrl);
                });
            }
        }
        
        function exportDashboard() {
            // Get dashboard data
            const data = {
                exportDate: new Date().toISOString(),
                stats: {
                    totalMovies: document.querySelector('.stat-card.bg-primary h2').textContent.trim(),
                    totalViews: document.querySelector('.stat-card.bg-success h2').textContent.trim(),
                    activeSlides: document.querySelector('.stat-card.bg-info h2').textContent.trim()
                },
                recentMovies: [],
                recentViews: []
            };
            
            // Get recent movies data
            const movieRows = document.querySelectorAll('table')[0].querySelectorAll('tbody tr');
            movieRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    data.recentMovies.push({
                        id: cells[0].textContent,
                        title: cells[1].textContent,
                        genre: cells[2].textContent,
                        year: cells[3].textContent,
                        views: cells[4].textContent
                    });
                }
            });
            
            // Get recent views data
            const viewRows = document.querySelectorAll('table')[1].querySelectorAll('tbody tr');
            viewRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    data.recentViews.push({
                        id: cells[0].textContent,
                        movie: cells[1].textContent,
                        date: cells[2].textContent,
                        ipAddress: cells[3].textContent
                    });
                }
            });
            
            // Create CSV content
            let csvContent = 'Dashboard Export\n\n';
            csvContent += 'Export Date:,' + new Date().toLocaleString() + '\n\n';
            csvContent += 'Statistics:\n';
            csvContent += 'Total Movies,' + data.stats.totalMovies + '\n';
            csvContent += 'Total Views,' + data.stats.totalViews + '\n';
            csvContent += 'Active Slides,' + data.stats.activeSlides + '\n\n';
            
            csvContent += 'Recent Movies:\n';
            csvContent += 'ID,Title,Genre,Year,Views\n';
            data.recentMovies.forEach(movie => {
                csvContent += `${movie.id},"${movie.title}",${movie.genre},${movie.year},${movie.views}\n`;
            });
            
            csvContent += '\nRecent Views:\n';
            csvContent += 'ID,Movie,Date,IP Address\n';
            data.recentViews.forEach(view => {
                csvContent += `${view.id},"${view.movie}",${view.date},${view.ipAddress}\n`;
            });
            
            // Create and download file
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'dashboard_export_' + new Date().toISOString().split('T')[0] + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>