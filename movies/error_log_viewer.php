<?php
/**
 * Error log viewer for MoMo API integration
 */
echo "<h1>MoMo API Error Log Viewer</h1>";

// Check if error logging is enabled
echo "<h2>PHP Error Logging Status</h2>";
$logErrors = ini_get('log_errors');
$errorLog = ini_get('error_log');

echo "<ul>";
echo "<li>log_errors: " . ($logErrors ? 'On' : 'Off') . "</li>";
echo "<li>error_log: " . ($errorLog ? $errorLog : 'Not set') . "</li>";
echo "</ul>";

// Try to read the last few lines of the error log if it exists
if ($errorLog && file_exists($errorLog)) {
    echo "<h2>Recent Error Log Entries</h2>";
    
    // Read last 20 lines
    $lines = file($errorLog);
    $lastLines = array_slice($lines, -20);
    
    echo "<pre>";
    foreach ($lastLines as $line) {
        if (strpos($line, 'MoMo') !== false || strpos($line, 'cURL') !== false || strpos($line, 'API') !== false) {
            echo "<span style='color: red;'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p>No error log file found or error logging not configured.</p>";
}

// Check for common issues
echo "<h2>Common Issue Checklist</h2>";
echo "<ul>";
echo "<li>✓ Check that all API credentials are correctly set in momo_config.php</li>";
echo "<li>✓ Verify the environment is set correctly (sandbox vs production)</li>";
echo "<li>✓ Ensure network connectivity to MoMo API endpoints</li>";
echo "<li>✓ Check that SSL certificates are properly configured</li>";
echo "<li>✓ Verify that the withdrawals table has the transaction_id column</li>";
echo "<li>✓ Check PHP error logs for detailed error messages</li>";
echo "</ul>";

echo "<h2>Manual Error Log Check</h2>";
echo "<p>To manually check for errors, look in your PHP error log or web server error log for entries containing 'MoMo', 'API', or 'cURL'.</p>";
?>