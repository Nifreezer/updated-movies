<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'movie_website');

// Create connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Website configuration
define('SITE_URL', 'http://localhost/movies');
define('SITE_NAME', 'MovieFlix');
define('SITE_LOGO_URL', 'https://ik.imagekit.io/amanikennedy/log.png?updatedAt=1762881794415');
session_start();
?>
