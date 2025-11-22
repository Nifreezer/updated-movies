<?php
/**
 * Test script to simulate withdrawal processing
 */
include 'config.php';
require_once 'MoMoAPI.php';

echo "<h1>Withdrawal Processing Test</h1>";

// Create a test withdrawal in the database
try {
    // First, check if we have any users
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "<p style='color: red;'>✗ No admin user found. Please create an admin user first.</p>";
        exit;
    }
    
    $adminId = $admin['id'];
    
    // Insert a test withdrawal
    $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, payment_method, recipient_phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$adminId, 100, 'MTN Mobile Money', '0795967720']);
    $withdrawalId = $pdo->lastInsertId();
    
    echo "<p style='color: green;'>✓ Created test withdrawal with ID: $withdrawalId</p>";
    
    // Approve the withdrawal
    $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'approved' WHERE id = ?");
    $stmt->execute([$withdrawalId]);
    
    echo "<p style='color: green;'>✓ Approved test withdrawal</p>";
    
    // Now test the MoMo API processing
    echo "<h2>Testing MoMo API Processing</h2>";
    
    $momo = new MoMoAPI($pdo);
    $result = $momo->processWithdrawal($withdrawalId);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✓ MoMo API processing successful!</p>";
        echo "<p>Transaction ID: " . $result['transactionId'] . "</p>";
        echo "<p>Message: " . $result['message'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ MoMo API processing failed!</p>";
        echo "<p>Error: " . $result['error'] . "</p>";
        echo "<p>This is the actual error that would be shown in the admin panel.</p>";
    }
    
    // Clean up - remove test withdrawal
    $stmt = $pdo->prepare("DELETE FROM withdrawals WHERE id = ?");
    $stmt->execute([$withdrawalId]);
    echo "<p style='color: green;'>✓ Cleaned up test withdrawal</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?><?php
/**
 * Test script to simulate withdrawal processing
 */
include 'config.php';
require_once 'MoMoAPI.php';

echo "<h1>Withdrawal Processing Test</h1>";

// Create a test withdrawal in the database
try {
    // First, check if we have any users
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "<p style='color: red;'>✗ No admin user found. Please create an admin user first.</p>";
        exit;
    }
    
    $adminId = $admin['id'];
    
    // Insert a test withdrawal
    $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, payment_method, recipient_phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$adminId, 100, 'MTN Mobile Money', '0795967720']);
    $withdrawalId = $pdo->lastInsertId();
    
    echo "<p style='color: green;'>✓ Created test withdrawal with ID: $withdrawalId</p>";
    
    // Approve the withdrawal
    $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'approved' WHERE id = ?");
    $stmt->execute([$withdrawalId]);
    
    echo "<p style='color: green;'>✓ Approved test withdrawal</p>";
    
    // Now test the MoMo API processing
    echo "<h2>Testing MoMo API Processing</h2>";
    
    $momo = new MoMoAPI($pdo);
    $result = $momo->processWithdrawal($withdrawalId);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✓ MoMo API processing successful!</p>";
        echo "<p>Transaction ID: " . $result['transactionId'] . "</p>";
        echo "<p>Message: " . $result['message'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ MoMo API processing failed!</p>";
        echo "<p>Error: " . $result['error'] . "</p>";
        echo "<p>This is the actual error that would be shown in the admin panel.</p>";
    }
    
    // Clean up - remove test withdrawal
    $stmt = $pdo->prepare("DELETE FROM withdrawals WHERE id = ?");
    $stmt->execute([$withdrawalId]);
    echo "<p style='color: green;'>✓ Cleaned up test withdrawal</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>