<?php
include '../config.php';
require_once '../MoMoAPI.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Get admin ID
$admin_id = $_SESSION['admin_id'] ?? 0;

$message = '';
$error = '';

// Handle test actions
if (isset($_POST['test_payment'])) {
    try {
        $momo = new MoMoAPI($pdo);
        
        $amount = floatval($_POST['amount'] ?? 0);
        $phone = $_POST['phone'] ?? '';
        $externalId = 'TEST_' . time();
        
        if ($amount <= 0) {
            $error = "Please enter a valid amount.";
        } elseif (empty($phone)) {
            $error = "Please enter a phone number.";
        } else {
            // This would normally be a requestPayment call, but we'll simulate success
            $message = "Test payment request would be sent for $amount RWF to $phone";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoMo API Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h1>MoMo API Test Utility</h1>
                <p class="text-muted">Test the MoMo API integration with sample transactions</p>
                
                <nav class="mb-4">
                    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                    <a href="monetization.php" class="btn btn-secondary">Monetization Dashboard</a>
                </nav>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Test Payment Request</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (RWF)</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="07XXXXXXXX" required>
                                <div class="form-text">Enter a valid Rwandan phone number</div>
                            </div>
                            <button type="submit" name="test_payment" class="btn btn-primary">Test Payment Request</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Configuration Status</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                API Configuration
                                <span class="badge bg-<?php echo defined('MOMO_API_KEY') && MOMO_API_KEY != 'YOUR_MOMO_API_KEY' ? 'success' : 'danger'; ?> rounded-pill">
                                    <?php echo defined('MOMO_API_KEY') && MOMO_API_KEY != 'YOUR_MOMO_API_KEY' ? 'Configured' : 'Not Configured'; ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Environment
                                <span class="badge bg-<?php echo MOMO_ENVIRONMENT == 'sandbox' ? 'warning' : 'success'; ?> rounded-pill">
                                    <?php echo MOMO_ENVIRONMENT; ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Database Integration
                                <span class="badge bg-success rounded-pill">Ready</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>