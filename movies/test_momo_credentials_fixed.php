<?php
/**
 * Test script for MoMo API credentials verification
 */
include 'config.php';
require_once 'MoMoAPI.php';

echo "<h1>MoMo API Credentials Verification</h1>";

try {
    // Initialize the MoMo API
    echo "<h2>Initializing MoMo API...</h2>";
    $momo = new MoMoAPI($pdo);
    echo "<p style='color: green;'>✓ MoMoAPI class instantiated successfully</p>";
    
    // Display configuration
    echo "<h2>Configuration Check</h2>";
    echo "<ul>";
    echo "<li>API URL: " . MOMO_API_URL . "</li>";
    echo "<li>API Key: " . (defined('MOMO_API_KEY') && MOMO_API_KEY != 'YOUR_ACTUAL_API_KEY' ? '✓ Set' : '✗ Not set or using placeholder') . "</li>";
    echo "<li>API User: " . (defined('MOMO_API_USER') && MOMO_API_USER != 'YOUR_ACTUAL_API_USER' ? '✓ Set' : '✗ Not set or using placeholder') . "</li>";
    echo "<li>API Secret: " . (defined('MOMO_API_SECRET') && MOMO_API_SECRET != 'YOUR_ACTUAL_API_SECRET' ? '✓ Set' : '✗ Not set or using placeholder') . "</li>";
    echo "<li>Subscription Key: " . (defined('MOMO_SUBSCRIPTION_KEY') && MOMO_SUBSCRIPTION_KEY != 'YOUR_ACTUAL_SUBSCRIPTION_KEY' ? '✓ Set' : '✗ Not set or using placeholder') . "</li>";
    echo "<li>Environment: " . MOMO_ENVIRONMENT . "</li>";
    echo "</ul>";
    
    // Check for placeholder values
    $hasPlaceholders = false;
    if (MOMO_API_KEY === 'YOUR_ACTUAL_API_KEY' || 
        MOMO_API_USER === 'YOUR_ACTUAL_API_USER' || 
        MOMO_API_SECRET === 'YOUR_ACTUAL_API_SECRET' || 
        MOMO_SUBSCRIPTION_KEY === 'YOUR_ACTUAL_SUBSCRIPTION_KEY') {
        $hasPlaceholders = true;
        echo "<p style='color: orange;'>⚠ Warning: Placeholder values detected. Please update momo_config.php with your actual credentials.</p>";
    }
    
    if (!$hasPlaceholders) {
        // Test access token retrieval
        echo "<h2>Testing Access Token Retrieval</h2>";
        echo "<p>Attempting to retrieve access token from MoMo API...</p>";
        
        try {
            // Use reflection to access the private method for testing
            $reflection = new ReflectionClass('MoMoAPI');
            $method = $reflection->getMethod('getAccessToken');
            $method->setAccessible(true);
            
            $startTime = microtime(true);
            $token = $method->invoke($momo);
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            
            if (!empty($token)) {
                echo "<p style='color: green;'>✓ Access token retrieved successfully in {$duration}ms</p>";
                echo "<p>Token: " . (strlen($token) > 0 ? 'REDACTED (' . strlen($token) . ' characters)' : 'EMPTY') . "</p>";
                echo "<p style='color: green;'>✓ Your MoMo API credentials are valid!</p>";
            } else {
                echo "<p style='color: red;'>✗ Access token is empty. Check your credentials.</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error retrieving access token: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>This indicates an authentication issue with your credentials.</p>";
            
            // Provide specific guidance based on error message
            if (strpos($e->getMessage(), '401') !== false) {
                echo "<p><strong>Fix:</strong> The HTTP 401 error means your subscription key is invalid. Please:</p>";
                echo "<ol>";
                echo "<li>Verify your subscription key in momo_config.php</li>";
                echo "<li>Check that your subscription is active in the MTN MoMo Developer Portal</li>";
                echo "<li>Ensure you're using the correct environment (sandbox vs production)</li>";
                echo "</ol>";
            }
        }
    }
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    if ($hasPlaceholders) {
        echo "<li>Update momo_config.php with your actual MoMo API credentials</li>";
        echo "<li>Re-run this test script to verify the credentials</li>";
    } else {
        echo "<li>Run setup_db.php to ensure database schema is up to date</li>";
        echo "<li>Test the integration using the admin dashboard</li>";
        echo "<li>Approve a withdrawal to trigger MoMo API processing</li>";
    }
    echo "<li>Refer to MOMO_API_FIX_INSTRUCTIONS.txt for detailed troubleshooting steps</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Check that all required files exist and are properly configured.</p>";
}

echo "<hr>";
echo "<p><a href='admin/monetization.php'>Go to Admin Dashboard</a> | <a href='momo_config.php'>View Configuration</a></p>";
?>