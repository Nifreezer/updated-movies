<?php
/**
 * MoMo API Configuration
 * Store your MoMo API credentials here
 * 
 * IMPORTANT: Replace these placeholder values with your actual MoMo API credentials
 * Obtain your credentials from the MTN MoMo Developer Portal
 */

// MoMo API Configuration
// NOTE: These are placeholder values. Replace with your actual credentials from MTN MoMo Developer Portal
define('MOMO_API_URL', 'https://sandbox.momodeveloper.mtn.com');
define('MOMO_API_KEY', 'a9d33af3407645329b558481e03fab54');
define('MOMO_API_USER', 'Amani Kennedy');
define('MOMO_API_SECRET', 'Kennedy@123456');
define('MOMO_SUBSCRIPTION_KEY', 'a9d33af3407645329b558481e03fab54');

// Environment
define('MOMO_ENVIRONMENT', 'sandbox'); // Change to 'production' in live environment

// Error message for invalid configuration
if (MOMO_API_KEY === 'YOUR_MOMO_API_KEY_HERE' || 
    MOMO_API_USER === 'YOUR_MOMO_API_USER_HERE' || 
    MOMO_API_SECRET === 'YOUR_MOMO_API_SECRET_HERE') {
    error_log('MoMo API Error: Invalid credentials detected. Please update momo_config.php with your actual credentials.');
}
?>