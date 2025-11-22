<?php
/**
 * Network connectivity test for MoMo API
 */
echo "<h1>Network Connectivity Test for MoMo API</h1>";

// Test URLs
$testUrls = [
    'https://sandbox.momodeveloper.mtn.com/collection/token/',
    'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay',
    'https://www.google.com' // Control test
];

echo "<h2>Testing Connectivity to MoMo API Endpoints</h2>";

foreach ($testUrls as $url) {
    echo "<h3>Testing: $url</h3>";
    
    // Test with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        echo "<p style='color: red;'>✗ cURL Error: " . $error . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Connection successful (HTTP " . $httpCode . ")</p>";
    }
    
    echo "<hr>";
}

echo "<h2>DNS Resolution Test</h2>";
$domains = ['sandbox.momodeveloper.mtn.com', 'www.google.com'];

foreach ($domains as $domain) {
    echo "<h3>Resolving: $domain</h3>";
    $ip = gethostbyname($domain);
    
    if ($ip === $domain) {
        echo "<p style='color: red;'>✗ DNS resolution failed</p>";
    } else {
        echo "<p style='color: green;'>✓ Resolved to: " . $ip . "</p>";
    }
    
    echo "<hr>";
}

echo "<h2>Firewall/Proxy Check</h2>";
echo "<p>If you're behind a corporate firewall or proxy, you may need to configure cURL to use it.</p>";
echo "<p>Check with your network administrator if you suspect firewall issues.</p>";
?>