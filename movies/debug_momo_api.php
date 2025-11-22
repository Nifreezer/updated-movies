<?php
/**
 * Debug script for MoMo API integration
 */
include 'config.php';
require_once 'MoMoAPI.php';

echo "<h1>MoMo API Debug Script</h1>";

try {
    // Initialize the MoMo API
    echo "<h2>Initializing MoMo API...</h2>";
    $momo = new MoMoAPI($pdo);
    echo "<p style='color: green;'>✓ MoMoAPI class instantiated successfully</p>";
    
    // Display configuration
    echo "<h2>Configuration Check</h2>";
    echo "<ul>";
    echo "<li>API URL: " . MOMO_API_URL . "</li>";
    echo "<li>API Key: " . (defined('MOMO_API_KEY') && MOMO_API_KEY != 'YOUR_MOMO_API_KEY' ? '✓ Set' : '✗ Not set') . "</li>";
    echo "<li>API User: " . (defined('MOMO_API_USER') && MOMO_API_USER != 'YOUR_MOMO_API_USER' ? '✓ Set' : '✗ Not set') . "</li>";
    echo "<li>API Secret: " . (defined('MOMO_API_SECRET') && MOMO_API_SECRET != 'YOUR_MOMO_API_SECRET' ? '✓ Set' : '✗ Not set') . "</li>";
    echo "<li>Subscription Key: " . (defined('MOMO_SUBSCRIPTION_KEY') && MOMO_SUBSCRIPTION_KEY != 'YOUR_MOMO_SUBSCRIPTION_KEY' ? '✓ Set' : '✗ Not set') . "</li>";
    echo "<li>Environment: " . MOMO_ENVIRONMENT . "</li>";
    echo "</ul>";
    
    // Test access token retrieval
    echo "<h2>Testing Access Token Retrieval</h2>";
    try {
        // Make the getAccessToken method temporarily public for testing
        $reflection = new ReflectionClass('MoMoAPI');
        $method = $reflection->getMethod('getAccessToken');
        $method->setAccessible(true);
        
        $token = $method->invoke($momo);
        echo "<p style='color: green;'>✓ Access token retrieved successfully</p>";
        echo "<p>Token: " . (strlen($token) > 0 ? 'REDACTED (' . strlen($token) . ' characters)' : 'EMPTY') . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error retrieving access token: " . $e->getMessage() . "</p>";
        echo "<p>This indicates an authentication issue with your credentials.</p>";
    }
    
    // Test phone number formatting
    echo "<h2>Testing Phone Number Formatting</h2>";
    $testNumbers = ['0795967720', '250795967720', '+250795967720'];
    foreach ($testNumbers as $number) {
        try {
            $reflection = new ReflectionClass('MoMoAPI');
            $method = $reflection->getMethod('formatPhoneNumber');
            $method->setAccessible(true);
            
            $formatted = $method->invoke($momo, $number);
            echo "<p style='color: green;'>✓ $number → $formatted</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error formatting $number: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li>If access token retrieval failed, check your credentials in momo_config.php</li>";
    echo "<li>If phone number formatting works, the core functionality is intact</li>";
    echo "<li>Test a real withdrawal through the admin panel and check for error messages</li>";
    echo "<li>Look at the success/error messages displayed in the monetization dashboard</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Check that all required files exist and are properly configured.</p>";
}
?>