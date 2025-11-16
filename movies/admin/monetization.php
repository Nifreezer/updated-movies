<?php 
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Get admin ID
$admin_id = $_SESSION['admin_id'] ?? 0;

// Handle withdrawal approval
if (isset($_POST['approve_withdrawal'])) {
    $withdrawal_id = $_POST['withdrawal_id'] ?? 0;
    
    if ($withdrawal_id > 0) {
        try {
            // Get withdrawal details before updating
            $stmt = $pdo->prepare("SELECT w.*, u.username FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.id = ?");
            $stmt->execute([$withdrawal_id]);
            $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($withdrawal) {
                $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'approved', processed_date = NOW() WHERE id = ?");
                $stmt->execute([$withdrawal_id]);
                if ($stmt->rowCount() > 0) {
                    $success_message = "Withdrawal approved successfully!";
                    $success_message .= " Payment of " . number_format($withdrawal['amount'], 2) . " RWF to be sent to " . ($withdrawal['recipient_phone'] ?? 'N/A') . " via " . ($withdrawal['payment_method'] ?? 'N/A') . ".";
                } else {
                    $error_message = "Withdrawal not found or already processed.";
                }
            } else {
                $error_message = "Withdrawal not found.";
            }
        } catch (Exception $e) {
            $error_message = "Error approving withdrawal: " . $e->getMessage();
        }
    } else {
        $error_message = "Invalid withdrawal request.";
    }
}

// Handle withdrawal rejection
if (isset($_POST['reject_withdrawal'])) {
    $withdrawal_id = $_POST['withdrawal_id'] ?? 0;
    
    if ($withdrawal_id > 0) {
        try {
            // Get withdrawal details before updating
            $stmt = $pdo->prepare("SELECT w.*, u.username FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.id = ?");
            $stmt->execute([$withdrawal_id]);
            $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($withdrawal) {
                $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'rejected', processed_date = NOW() WHERE id = ?");
                $stmt->execute([$withdrawal_id]);
                if ($stmt->rowCount() > 0) {
                    $success_message = "Withdrawal rejected successfully!";
                    $success_message .= " Payment of " . number_format($withdrawal['amount'], 2) . " RWF to " . $withdrawal['username'] . " has been cancelled.";
                } else {
                    $error_message = "Withdrawal not found or already processed.";
                }
            } else {
                $error_message = "Withdrawal not found.";
            }
        } catch (Exception $e) {
            $error_message = "Error rejecting withdrawal: " . $e->getMessage();
        }
    } else {
        $error_message = "Invalid withdrawal request.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monetization Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.5);
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.1);
        }
        .main-content {
            padding: 20px;
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }
        .earning-card {
            background: linear-gradient(45deg, #28a745, #20c997);
        }
        .view-card {
            background: linear-gradient(45deg, #007bff, #6610f2);
        }
        .time-card {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
        }
        .withdrawal-card {
            background: linear-gradient(45deg, #dc3545, #e83e8c);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar bg-dark">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="bi bi-house me-2"></i>Back to Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="monetization.php">
                                <i class="bi bi-currency-dollar me-2"></i>Monetization
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="movies.php">
                                <i class="bi bi-film me-2"></i>Movies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="slides.php">
                                <i class="bi bi-images me-2"></i>Slides
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Monetization Dashboard</h1>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?= $success_message ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="row">
                    <?php
                    // Calculate total earnings
                    $total_views = 0;
                    $total_watch_time = 0;
                    
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) as total_views, SUM(watch_time) as total_watch_time FROM user_views");
                        $stats = $stmt->fetch();
                        
                        $total_views = $stats['total_views'] ?? 0;
                        $total_watch_time = $stats['total_watch_time'] ?? 0;
                    } catch (Exception $e) {
                        // Table might not exist yet, use default values
                        $total_views = 0;
                        $total_watch_time = 0;
                    }
                    
                    // Conversion rate: 1 view + 5 minutes = 2000 RWF
                    $conversion_rate = 2000; // RWF per view + 5 minutes
                    $minutes_for_conversion = 5;
                    
                    // Calculate earnings
                    $earnings_from_views = $total_views * ($conversion_rate / 2); // Half from views
                    $earnings_from_watch_time = ($total_watch_time / 60 / $minutes_for_conversion) * ($conversion_rate / 2); // Half from watch time
                    $total_earnings = $earnings_from_views + $earnings_from_watch_time;
                    ?>
                    
                    <div class="col-md-3">
                        <div class="stat-card earning-card">
                            <h5>Total Earnings</h5>
                            <h2><?= number_format($total_earnings, 2) ?> RWF</h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card view-card">
                            <h5>Total Views</h5>
                            <h2><?= $total_views ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card time-card">
                            <h5>Total Watch Time</h5>
                            <h2><?= gmdate("H:i:s", $total_watch_time) ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card withdrawal-card">
                            <h5>Pending Withdrawals</h5>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as pending_count FROM withdrawals WHERE status = 'pending'");
                                $pending = $stmt->fetch();
                                $pending_count = $pending['pending_count'];
                            } catch (Exception $e) {
                                // Table might not exist yet, use default value
                                $pending_count = 0;
                            }
                            ?>
                            <h2><?= $pending_count ?></h2>
                        </div>
                    </div>
                </div>

                <!-- Updated Balance -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="stat-card bg-success">
                            <h5>Current Balance</h5>
                            <?php
                            // Calculate current balance (total earnings minus approved withdrawals)
                            $current_balance = $total_earnings;
                            
                            try {
                                // Subtract approved withdrawals
                                $stmt = $pdo->prepare("SELECT SUM(amount) as total_withdrawn FROM withdrawals WHERE user_id = ? AND status = 'approved'");
                                $stmt->execute([$admin_id]);
                                $withdrawn = $stmt->fetch();
                                $total_withdrawn = $withdrawn['total_withdrawn'] ?? 0;
                                
                                $current_balance = $total_earnings - $total_withdrawn;
                            } catch (Exception $e) {
                                // If there's an error, use total earnings as balance
                                $current_balance = $total_earnings;
                            }
                            ?>
                            <h2><?= number_format($current_balance, 2) ?> RWF</h2>
                            <p class="mb-0">Total Earnings: <?= number_format($total_earnings, 2) ?> RWF | Approved Withdrawals: <?= number_format($total_withdrawn ?? 0, 2) ?> RWF</p>
                        </div>
                    </div>
                </div>

                <!-- Earnings Breakdown -->
                <div class="mt-5">
                    <h4>Earnings Breakdown</h4>
                    <div class="card">
                        <div class="card-body">
                            <h5>Conversion Formula</h5>
                            <p>1 view + 5 minutes of watch time = 2,000 Rwandan Francs (RWF)</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Earnings from Views</h6>
                                    <p><?= $total_views ?> views × <?= $conversion_rate/2 ?> RWF = <?= number_format($earnings_from_views, 2) ?> RWF</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Earnings from Watch Time</h6>
                                    <p><?= gmdate("H:i:s", $total_watch_time) ?> ÷ 5 minutes × <?= $conversion_rate/2 ?> RWF = <?= number_format($earnings_from_watch_time, 2) ?> RWF</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Withdrawal Request -->
                <div class="mt-5">
                    <h4>Request Withdrawal</h4>
                    <div class="card">
                        <div class="card-body">
                            <?php
                            // Handle admin withdrawal request
                            if (isset($_POST['request_admin_withdrawal'])) {
                                $amount = floatval($_POST['amount'] ?? 0);
                                $payment_method = $_POST['payment_method'] ?? '';
                                $recipient_phone = $_POST['recipient_phone'] ?? '';
                                
                                // Validate amount and admin ID
                                if ($amount <= 0) {
                                    $admin_error_message = "Please enter a valid amount.";
                                } else if (empty($payment_method)) {
                                    $admin_error_message = "Please select a payment method.";
                                } else if (empty($recipient_phone)) {
                                    $admin_error_message = "Please enter the recipient's phone number.";
                                } else if (!preg_match('/^07[0-9]{8}$/', $recipient_phone)) {
                                    $admin_error_message = "Please enter a valid Rwandan phone number (format: 07XXXXXXXX).";
                                } else if ($admin_id <= 0) {
                                    $admin_error_message = "Admin session not found. Please log in again.";
                                } else {
                                    // Check if admin has enough earnings (using same calculation as above)
                                    $total_views = 0;
                                    $total_watch_time = 0;
                                    
                                    try {
                                        $stmt = $pdo->query("SELECT COUNT(*) as total_views, SUM(watch_time) as total_watch_time FROM user_views");
                                        $stats = $stmt->fetch();
                                        
                                        $total_views = $stats['total_views'] ?? 0;
                                        $total_watch_time = $stats['total_watch_time'] ?? 0;
                                    } catch (Exception $e) {
                                        $total_views = 0;
                                        $total_watch_time = 0;
                                    }
                                    
                                    // Conversion rate: 1 view + 5 minutes = 2000 RWF
                                    $conversion_rate = 2000; // RWF per view + 5 minutes
                                    $minutes_for_conversion = 5;
                                    
                                    // Calculate earnings
                                    $earnings_from_views = $total_views * ($conversion_rate / 2); // Half from views
                                    $earnings_from_watch_time = ($total_watch_time / 60 / $minutes_for_conversion) * ($conversion_rate / 2); // Half from watch time
                                    $total_earnings = $earnings_from_views + $earnings_from_watch_time;
                                    
                                    // Calculate current balance (total earnings minus approved withdrawals)
                                    $current_balance = $total_earnings;
                                    
                                    try {
                                        // Subtract approved withdrawals
                                        $stmt = $pdo->prepare("SELECT SUM(amount) as total_withdrawn FROM withdrawals WHERE user_id = ? AND status = 'approved'");
                                        $stmt->execute([$admin_id]);
                                        $withdrawn = $stmt->fetch();
                                        $total_withdrawn = $withdrawn['total_withdrawn'] ?? 0;
                                        
                                        $current_balance = $total_earnings - $total_withdrawn;
                                    } catch (Exception $e) {
                                        // If there's an error, use total earnings as balance
                                        $current_balance = $total_earnings;
                                    }
                                    
                                    if ($amount > $current_balance) {
                                        $admin_error_message = "You don't have enough balance for this withdrawal. Current balance: " . number_format($current_balance, 2) . " RWF";
                                    } else {
                                        // Process withdrawal request - for admin, we'll use the admin's user ID
                                        try {
                                            
                                            // Check if admin exists in users table
                                            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin'");
                                            $stmt->execute([$admin_id]);
                                            $admin_user = $stmt->fetch();
                                            
                                            if ($admin_user) {
                                                $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, payment_method, recipient_phone) VALUES (?, ?, ?, ?)");
                                                $stmt->execute([$admin_id, $amount, $payment_method, $recipient_phone]);
                                                $admin_success_message = "Withdrawal request submitted successfully!";
                                            } else {
                                                $admin_error_message = "Admin user not found.";
                                            }
                                        } catch (Exception $e) {
                                            $admin_error_message = "Error submitting withdrawal request: " . $e->getMessage();
                                        }
                                    }
                                }
                            }
                            ?>
                            
                            <?php if (isset($admin_success_message)): ?>
                                <div class="alert alert-success"><?= $admin_success_message ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($admin_error_message)): ?>
                                <div class="alert alert-danger"><?= $admin_error_message ?></div>
                            <?php endif; ?>
                            
                            <form method="post">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (RWF)</label>
                                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-control" id="payment_method" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="MTN Mobile Money">MTN Mobile Money</option>
                                        <option value="Airtel Money">Airtel Money</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="recipient_phone" class="form-label">Recipient Phone Number</label>
                                    <input type="text" class="form-control" id="recipient_phone" name="recipient_phone" placeholder="07XXXXXXXX" required>
                                    <div class="form-text">Enter a valid Rwandan phone number</div>
                                </div>
                                <button type="submit" name="request_admin_withdrawal" class="btn btn-primary">Request Withdrawal</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Withdrawal Requests -->
                <div class="mt-5">
                    <h4>Withdrawal Requests</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Amount (RWF)</th>
                                    <th>Payment Method</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Request Date</th>
                                    <th>Processed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $stmt = $pdo->query("SELECT w.*, u.username FROM withdrawals w JOIN users u ON w.user_id = u.id ORDER BY w.request_date DESC");
                                    $has_withdrawals = false;
                                    while ($withdrawal = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $has_withdrawals = true;
                                        echo '<tr>';
                                        echo '<td>'.$withdrawal['id'].'</td>';
                                        echo '<td>'.$withdrawal['username'].'</td>';
                                        echo '<td>'.number_format($withdrawal['amount'], 2).'</td>';
                                        echo '<td>'.($withdrawal['payment_method'] ?? '-').'</td>';
                                        echo '<td>'.($withdrawal['recipient_phone'] ?? '-').'</td>';
                                        echo '<td><span class="badge bg-'.($withdrawal['status'] == 'pending' ? 'warning' : ($withdrawal['status'] == 'approved' ? 'success' : 'danger')).'">'.ucfirst($withdrawal['status']).'</span></td>';
                                        echo '<td>'.date('M d, Y H:i', strtotime($withdrawal['request_date'])).'</td>';
                                        echo '<td>'.($withdrawal['processed_date'] ? date('M d, Y H:i', strtotime($withdrawal['processed_date'])) : '-').'</td>';
                                        echo '<td>';
                                        
                                        if ($withdrawal['status'] == 'pending') {
                                            echo '<form method="post" style="display:inline;">
                                                    <input type="hidden" name="withdrawal_id" value="'.$withdrawal['id'].'">
                                                    <button type="submit" name="approve_withdrawal" class="btn btn-sm btn-success">Approve</button>
                                                  </form>';
                                            echo '<form method="post" style="display:inline;">
                                                    <input type="hidden" name="withdrawal_id" value="'.$withdrawal['id'].'">
                                                    <button type="submit" name="reject_withdrawal" class="btn btn-sm btn-danger">Reject</button>
                                                  </form>';
                                        } else {
                                            echo '-';
                                        }
                                        
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    
                                    if (!$has_withdrawals) {
                                        echo '<tr><td colspan="9" class="text-center">No withdrawal requests found</td></tr>';
                                    }
                                } catch (Exception $e) {
                                    // Table might not exist yet
                                    echo '<tr><td colspan="9" class="text-center">No withdrawal requests found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Withdrawal Notifications -->
                <div class="mt-5">
                    <h4>Recent Withdrawal Activities</h4>
                    <div class="card">
                        <div class="card-body">
                            <?php
                            try {
                                // Get recent withdrawal activities
                                $stmt = $pdo->prepare("SELECT w.*, u.username FROM withdrawals w JOIN users u ON w.user_id = u.id ORDER BY w.request_date DESC LIMIT 5");
                                $stmt->execute();
                                
                                if ($stmt->rowCount() > 0) {
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-striped">';
                                    echo '<thead><tr><th>Date</th><th>User</th><th>Amount</th><th>Status</th><th>Payment Method</th></tr></thead>';
                                    echo '<tbody>';
                                    
                                    while ($activity = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<tr>';
                                        echo '<td>'.date('M d, Y H:i', strtotime($activity['request_date'])).'</td>';
                                        echo '<td>'.$activity['username'].'</td>';
                                        echo '<td>'.number_format($activity['amount'], 2).' RWF</td>';
                                        echo '<td><span class="badge bg-'.($activity['status'] == 'pending' ? 'warning' : ($activity['status'] == 'approved' ? 'success' : 'danger')).'">'.ucfirst($activity['status']).'</span></td>';
                                        echo '<td>'.($activity['payment_method'] ?? '-').'</td>';
                                        echo '</tr>';
                                    }
                                    
                                    echo '</tbody>';
                                    echo '</table>';
                                    echo '</div>';
                                } else {
                                    echo '<p class="text-muted">No recent withdrawal activities.</p>';
                                }
                            } catch (Exception $e) {
                                echo '<p class="text-danger">Error loading withdrawal activities: ' . $e->getMessage() . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Admin's Withdrawal History -->
                <div class="mt-5">
                    <h4>Your Withdrawal History</h4>
                    <div class="card">
                        <div class="card-body">
                            <?php
                            try {
                                // Get admin's withdrawal history
                                $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY request_date DESC");
                                $stmt->execute([$admin_id]);
                                
                                if ($stmt->rowCount() > 0) {
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-striped">';
                                    echo '<thead><tr><th>Date</th><th>Amount</th><th>Payment Method</th><th>Phone</th><th>Status</th></tr></thead>';
                                    echo '<tbody>';
                                    
                                    while ($withdrawal = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<tr>';
                                        echo '<td>'.date('M d, Y H:i', strtotime($withdrawal['request_date'])).'</td>';
                                        echo '<td>'.number_format($withdrawal['amount'], 2).' RWF</td>';
                                        echo '<td>'.$withdrawal['payment_method'].'</td>';
                                        echo '<td>'.$withdrawal['recipient_phone'].'</td>';
                                        echo '<td><span class="badge bg-'.($withdrawal['status'] == 'pending' ? 'warning' : ($withdrawal['status'] == 'approved' ? 'success' : 'danger')).'">'.ucfirst($withdrawal['status']).'</span></td>';
                                        echo '</tr>';
                                    }
                                    
                                    echo '</tbody>';
                                    echo '</table>';
                                    echo '</div>';
                                } else {
                                    echo '<p class="text-muted">You have not made any withdrawal requests yet.</p>';
                                }
                            } catch (Exception $e) {
                                echo '<p class="text-danger">Error loading your withdrawal history: ' . $e->getMessage() . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>