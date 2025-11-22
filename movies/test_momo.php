<?php
/**
 * Test script for MoMo API integration
 */
include 'config.php';
require_once 'MoMoAPI.php';

// Test the MoMo API class
try {
    $momo = new MoMoAPI($pdo);
    
    echo "<h2>MoMo API Integration Test</h2>";
    
    // Test phone number formatting
    echo "<h3>Phone Number Formatting Test</h3>";
    $testNumbers = ['0781234567', '250781234567', '+250781234567'];
    foreach ($testNumbers as $number) {
        // We'll need to make the formatPhoneNumber method public for testing
        // For now, we'll just show the numbers
        echo "Input: $number<br>";
    }
    
    echo "<p>MoMo API class instantiated successfully!</p>";
    echo "<p>Configuration loaded:</p>";
    echo "<ul>";
    echo "<li>API URL: " . (defined('MOMO_API_URL') ? MOMO_API_URL : 'Not defined') . "</li>";
    echo "<li>Environment: " . (defined('MOMO_ENVIRONMENT') ? MOMO_ENVIRONMENT : 'Not defined') . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>