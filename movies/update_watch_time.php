<?php
include 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

// Get POST data
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;
$watch_time = isset($_POST['watch_time']) ? intval($_POST['watch_time']) : 0;

// Validate data
if ($user_id <= 0 || $movie_id <= 0 || $watch_time <= 0) {
    http_response_code(400);
    exit();
}

// Verify that the user is updating their own watch time
if ($user_id != $_SESSION['user_id']) {
    http_response_code(403);
    exit();
}

try {
    // First, try to update the existing record
    $stmt = $pdo->prepare("UPDATE user_views SET watch_time = watch_time + ? WHERE user_id = ? AND movie_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$watch_time, $user_id, $movie_id]);
    
    // Check if any rows were affected
    if ($stmt->rowCount() == 0) {
        // No existing record found, insert a new one
        $stmt = $pdo->prepare("INSERT INTO user_views (user_id, movie_id, watch_time) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $movie_id, $watch_time]);
    }
    
    // Return success
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>