<?php
/**
 * Verification script for MoMo API integration
 */
echo "<h1>MoMo API Integration Verification</h1>";

// Check if required files exist
$files = [
    'MoMoAPI.php' => 'Main MoMo API class',
    'momo_config.php' => 'Configuration file',
    'admin/monetization.php' => 'Updated admin dashboard'
];

echo "<h2>File Verification</h2>";
echo "<ul>";
foreach ($files as $file => $description) {
    $path = "c:/xampp/htdocs/movies/" . $file;
    if (file_exists($path)) {
        echo "<li style='color: green;'>✓ $file - Found ($description)</li>";
    } else {
        echo "<li style='color: red;'>✗ $file - Missing</li>";
    }
}
echo "</ul>";

// Check if configuration file has been updated
echo "<h2>Configuration Check</h2>";
$configPath = "c:/xampp/htdocs/movies/momo_config.php";
if (file_exists($configPath)) {
    $configContent = file_get_contents($configPath);
    if (strpos($configContent, 'MOMO_API_KEY') !== false) {
        echo "<p style='color: green;'>✓ Configuration file contains required constants</p>";
    } else {
        echo "<p style='color: red;'>✗ Configuration file missing required constants</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Configuration file not found</p>";
}

// Check if admin file has been updated
echo "<h2>Admin Dashboard Integration</h2>";
$adminPath = "c:/xampp/htdocs/movies/admin/monetization.php";
if (file_exists($adminPath)) {
    $adminContent = file_get_contents($adminPath);
    if (strpos($adminContent, 'MoMoAPI') !== false) {
        echo "<p style='color: green;'>✓ Admin dashboard includes MoMo API integration</p>";
    } else {
        echo "<p style='color: red;'>✗ Admin dashboard missing MoMo API integration</p>";
    }
    
    if (strpos($adminContent, 'check_status') !== false) {
        echo "<p style='color: green;'>✓ Admin dashboard includes status checking functionality</p>";
    } else {
        echo "<p style='color: red;'>✗ Admin dashboard missing status checking functionality</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Admin dashboard file not found</p>";
}

echo "<h2>Integration Summary</h2>";
echo "<p>The MoMo API integration includes:</p>";
echo "<ul>";
echo "<li>MoMoAPI.php - Main class for handling Mobile Money transactions</li>";
echo "<li>momo_config.php - Configuration file for API credentials</li>";
echo "<li>Updated admin/monetization.php - Dashboard with MoMo integration</li>";
echo "<li>Database schema updates for transaction tracking</li>";
echo "<li>Automatic processing of withdrawals via MoMo API</li>";
echo "<li>Status checking for processed transactions</li>";
echo "</ul>";

echo "<p>To complete the integration:</p>";
echo "<ol>";
echo "<li>Update momo_config.php with your actual MoMo API credentials</li>";
echo "<li>Run setup_db.php to ensure database schema is up to date</li>";
echo "<li>Test the integration using the admin dashboard</li>";
echo "</ol>";
?>