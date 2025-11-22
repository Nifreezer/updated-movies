<?php
/**
 * Database schema verification script
 */
include 'config.php';

echo "<h1>Database Schema Verification</h1>";

try {
    // Check if required tables exist
    $requiredTables = ['users', 'movies', 'views', 'user_views', 'withdrawals', 'genres', 'slides'];
    
    echo "<h2>Table Verification</h2>";
    echo "<ul>";
    
    foreach ($requiredTables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "<li style='color: green;'>✓ $table - Found</li>";
        } else {
            echo "<li style='color: red;'>✗ $table - Missing</li>";
        }
    }
    
    echo "</ul>";
    
    // Check withdrawals table structure
    echo "<h2>Withdrawals Table Structure</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM withdrawals");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Check for specific required columns
    echo "<h2>Required Column Verification</h2>";
    $requiredColumns = ['user_id', 'amount', 'payment_method', 'recipient_phone', 'status', 'transaction_id'];
    
    $stmt = $pdo->query("SHOW COLUMNS FROM withdrawals");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }
    
    echo "<ul>";
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "<li style='color: green;'>✓ $column - Found</li>";
        } else {
            echo "<li style='color: red;'>✗ $column - Missing</li>";
        }
    }
    echo "</ul>";
    
    echo "<h2>Integration Status</h2>";
    echo "<p>If all required tables and columns are present, the database schema is ready for MoMo API integration.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>