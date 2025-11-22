<?php
/**
 * MoMo API Integration Class
 * Handles Mobile Money payment processing for the monetization system
 */
require_once 'momo_config.php';

class MoMoAPI {
    private $apiUrl;
    private $apiKey;
    private $apiUser;
    private $apiSecret;
    private $subscriptionKey;
    private $pdo;

    /**
     * Constructor
     * @param PDO $pdo Database connection
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
        
        // Configuration from config file
        $this->apiUrl = MOMO_API_URL;
        $this->apiKey = MOMO_API_KEY;
        $this->apiUser = MOMO_API_USER;
        $this->apiSecret = MOMO_API_SECRET;
        $this->subscriptionKey = MOMO_SUBSCRIPTION_KEY;
    }

    /**
     * Initialize a payment request
     * @param float $amount Amount to transfer
     * @param string $phoneNumber Recipient's phone number
     * @param string $externalId External transaction ID
     * @param string $payerMessage Message to payer
     * @param string $payeeNote Note to payee
     * @return array Response from API
     */
    public function requestPayment($amount, $phoneNumber, $externalId, $payerMessage = '', $payeeNote = '') {
        try {
            // Format phone number (ensure it starts with country code)
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            // Prepare request data
            $data = [
                'amount' => $amount,
                'currency' => 'RWF', // Rwandan Franc
                'externalId' => $externalId,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $formattedPhone
                ],
                'payerMessage' => $payerMessage ?: 'Payment request from MovieFlix',
                'payeeNote' => $payeeNote ?: 'Payment to MovieFlix'
            ];

            // Make API request
            $response = $this->makeApiRequest('/collection/v1_0/requesttopay', 'POST', $data);
            
            return [
                'success' => true,
                'transactionId' => $response['transactionId'] ?? null,
                'status' => 'pending',
                'message' => 'Payment request initiated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check payment status
     * @param string $transactionId Transaction ID
     * @return array Payment status
     */
    public function checkPaymentStatus($transactionId) {
        try {
            $response = $this->makeApiRequest("/collection/v1_0/requesttopay/{$transactionId}", 'GET');
            
            return [
                'success' => true,
                'status' => $response['status'] ?? 'unknown',
                'financialTransactionId' => $response['financialTransactionId'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Transfer funds (disbursement)
     * @param float $amount Amount to transfer
     * @param string $phoneNumber Recipient's phone number
     * @param string $externalId External transaction ID
     * @param string $payeeNote Note to payee
     * @param string $payerMessage Message to payer
     * @return array Response from API
     */
    public function transferFunds($amount, $phoneNumber, $externalId, $payeeNote = '', $payerMessage = '') {
        try {
            // Format phone number
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            // Prepare request data
            $data = [
                'amount' => $amount,
                'currency' => 'RWF', // Rwandan Franc
                'externalId' => $externalId,
                'payee' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $formattedPhone
                ],
                'payerMessage' => $payerMessage ?: 'Transfer from MovieFlix',
                'payeeNote' => $payeeNote ?: 'Payment received from MovieFlix'
            ];

            // Make API request
            $response = $this->makeApiRequest('/disbursement/v1_0/transfer', 'POST', $data);
            
            return [
                'success' => true,
                'transactionId' => $response['transactionId'] ?? null,
                'status' => 'pending',
                'message' => 'Transfer initiated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check transfer status
     * @param string $transactionId Transaction ID
     * @return array Transfer status
     */
    public function checkTransferStatus($transactionId) {
        try {
            $response = $this->makeApiRequest("/disbursement/v1_0/transfer/{$transactionId}", 'GET');
            
            return [
                'success' => true,
                'status' => $response['status'] ?? 'unknown',
                'financialTransactionId' => $response['financialTransactionId'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to international format
     * @param string $phoneNumber Local phone number
     * @return string Formatted phone number
     */
    private function formatPhoneNumber($phoneNumber) {
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

    /**
     * Make API request to MoMo endpoint
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array Decoded response
     */
    private function makeApiRequest($endpoint, $method = 'GET', $data = []) {
        // Get access token
        $accessToken = $this->getAccessToken();
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set URL
        $fullUrl = $this->apiUrl . $endpoint;
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        
        // Set headers
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'X-Reference-Id: ' . uniqid(), // Generate unique reference ID
            'X-Target-Environment: ' . MOMO_ENVIRONMENT,
            'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Set method
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        // Other options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only for testing, should be true in production
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Log request for debugging
        error_log("MoMo API Request - Endpoint: $endpoint, Method: $method, HTTP Code: $httpCode, Subscription Key: " . substr($this->subscriptionKey, 0, 8) . '...');
        
        // Check for cURL errors
        if ($error) {
            error_log('MoMo API cURL Error: ' . $error);
            throw new Exception('cURL Error: ' . $error);
        }
        
        // Decode response
        $responseData = json_decode($response, true);
        
        // Log response for debugging (without sensitive data)
        if (is_array($responseData)) {
            $logData = [];
            foreach ($responseData as $key => $value) {
                // Mask sensitive data
                if (in_array($key, ['access_token', 'token', 'secret'])) {
                    $logData[$key] = substr($value, 0, 5) . '***';
                } else {
                    $logData[$key] = $value;
                }
            }
            error_log('MoMo API Response: ' . json_encode($logData));
        }
        
        // Check HTTP status code
        if ($httpCode >= 400) {
            $errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'API Error';
            $errorDetails = '';
            if (isset($responseData['error_description'])) {
                $errorDetails = ' (' . $responseData['error_description'] . ')';
            }
            error_log("MoMo API Error - HTTP {$httpCode}: {$errorMessage}{$errorDetails} - URL: $fullUrl");
            throw new Exception("HTTP {$httpCode}: {$errorMessage}{$errorDetails}");
        }
        
        return $responseData;
    }

    /**
     * Get access token from MoMo API
     * @return string Access token
     */
    private function getAccessToken() {
        // Initialize cURL
        $ch = curl_init();
        
        // Set URL
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/collection/token/');
        
        // Set authentication
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiUser . ':' . $this->apiSecret);
        
        // Set headers
        $headers = [
            'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Set method
        curl_setopt($ch, CURLOPT_POST, true);
        
        // Other options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only for testing, should be true in production
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Log request for debugging
        error_log("MoMo API Token Request - HTTP Code: $httpCode, Subscription Key: " . substr($this->subscriptionKey, 0, 8) . '...');
        
        // Check for cURL errors
        if ($error) {
            error_log('MoMo API cURL Error: ' . $error);
            throw new Exception('cURL Error: ' . $error);
        }
        
        // Decode response
        $responseData = json_decode($response, true);
        
        // Check HTTP status code
        if ($httpCode >= 400) {
            $errorMessage = $responseData['error_description'] ?? $responseData['error'] ?? $responseData['message'] ?? 'Authentication Error';
            error_log("MoMo API Authentication Error - HTTP {$httpCode}: {$errorMessage}");
            throw new Exception("HTTP {$httpCode}: {$errorMessage}");
        }
        
        // Return access token
        $token = $responseData['access_token'] ?? '';
        if (empty($token)) {
            error_log('MoMo API Error: Empty access token received');
        }
        
        return $token;
    }

    /**
     * Process withdrawal through MoMo API
     * @param int $withdrawalId Withdrawal request ID
     * @return array Processing result
     */
    public function processWithdrawal($withdrawalId) {
        try {
            // Get withdrawal details
            $stmt = $this->pdo->prepare("SELECT w.*, u.username FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.id = ?");
            $stmt->execute([$withdrawalId]);
            $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$withdrawal) {
                throw new Exception('Withdrawal not found');
            }
            
            if ($withdrawal['status'] !== 'approved') {
                throw new Exception('Withdrawal must be approved before processing');
            }
            
            // Process payment via MoMo
            $result = $this->transferFunds(
                $withdrawal['amount'],
                $withdrawal['recipient_phone'],
                'WITHDRAWAL_' . $withdrawalId,
                'Withdrawal from MovieFlix for ' . $withdrawal['username'],
                'Payment from MovieFlix'
            );
            
            if ($result['success']) {
                // Update withdrawal with transaction ID
                $stmt = $this->pdo->prepare("UPDATE withdrawals SET transaction_id = ? WHERE id = ?");
                $stmt->execute([$result['transactionId'], $withdrawalId]);
                
                return [
                    'success' => true,
                    'transactionId' => $result['transactionId'],
                    'message' => 'Withdrawal processed successfully'
                ];
            } else {
                throw new Exception($result['error']);
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check withdrawal status
     * @param int $withdrawalId Withdrawal request ID
     * @return array Status result
     */
    public function checkWithdrawalStatus($withdrawalId) {
        try {
            // Get withdrawal details
            $stmt = $this->pdo->prepare("SELECT transaction_id FROM withdrawals WHERE id = ?");
            $stmt->execute([$withdrawalId]);
            $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$withdrawal || !$withdrawal['transaction_id']) {
                throw new Exception('Withdrawal not found or not processed');
            }
            
            // Check transaction status
            $result = $this->checkTransferStatus($withdrawal['transaction_id']);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'status' => $result['status'],
                    'financialTransactionId' => $result['financialTransactionId']
                ];
            } else {
                throw new Exception($result['error']);
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>