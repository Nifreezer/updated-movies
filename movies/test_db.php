<?php
include 'config.php';

try {
    // Test database connection
    echo "Database connection successful!\n";
    
    // Check if withdrawals table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'withdrawals'");
    if ($stmt->rowCount() > 0) {
        echo "Withdrawals table exists.\n";
        
        // Check columns in withdrawals table
        $stmt = $pdo->query("SHOW COLUMNS FROM withdrawals");
        echo "Columns in withdrawals table:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "Withdrawals table does not exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>