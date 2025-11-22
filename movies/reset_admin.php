<?php
include 'config.php';

try {
    // Update the admin user password
    $hashed_password = password_hash('amani1', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = ?");
    $stmt->execute([$hashed_password, 'amani kennedy']);
    
    if ($stmt->rowCount() > 0) {
        echo "Admin password reset successfully!<br>";
        echo "Username: amani kennedy<br>";
        echo "Password: amani1<br><br>";
        echo "<a href='admin/login.php'>Go to Admin Login</a>";
    } else {
        echo "Admin user 'amani kennedy' not found. Creating new admin user...<br>";
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['amani kennedy', 'amani.kennedy@example.com', $hashed_password, 'admin']);
        echo "Admin user created successfully!<br>";
        echo "Username: amani kennedy<br>";
        echo "Password: amani1<br><br>";
        echo "<a href='admin/login.php'>Go to Admin Login</a>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>