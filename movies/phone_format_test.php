<?php
/**
 * Phone number formatting utility for MoMo API
 */
include 'config.php';
require_once 'MoMoAPI.php';

echo "<h1>Phone Number Formatting Utility</h1>";

// Test phone numbers
$testNumbers = [
    '0781234567',
    '250781234567',
    '+250781234567',
    '781234567',
    '0721234567'
];

echo "<h2>Phone Number Formatting Test</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Input</th><th>Formatted Output</th></tr>";

foreach ($testNumbers as $number) {
    // We'll create a simple formatting function here for testing
    $formatted = formatPhoneNumber($number);
    echo "<tr><td>$number</td><td>$formatted</td></tr>";
}

echo "</table>";

function formatPhoneNumber($phoneNumber) {
    // Remove any spaces, dashes, or parentheses
    $phoneNumber = preg_replace('/[\s\-()]/', '', $phoneNumber);
    
    // If it starts with 0, replace with country code (250 for Rwanda)
    if (strpos($phoneNumber, '0') === 0) {
        $phoneNumber = '250' . substr($phoneNumber, 1);
    }
    
    // If it doesn't start with +, add it
    if (strpos($phoneNumber, '+') !== 0) {
        $phoneNumber = '+' . $phoneNumber;
    }
    
    return $phoneNumber;
}

echo "<h2>Usage Notes</h2>";
echo "<ul>";
echo "<li>MoMo API requires phone numbers in international format (+250XXXXXXXXX)</li>";
echo "<li>The system automatically converts local Rwandan numbers (07XXXXXXXX) to international format</li>";
echo "<li>Already formatted international numbers are preserved</li>";
echo "</ul>";
?>