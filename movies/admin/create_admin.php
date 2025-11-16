<?php 
include '../config.php';

// ============================================
// SECURITY TOKEN - COPY THIS VALUE TO USE THE FORM
// ============================================
$SETUP_TOKEN = 'ADMIN_SETUP_2025_SECURE';
// ============================================

$success = false;
$admin_username = '';
$admin_email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'] ?? '';
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if ($token !== $SETUP_TOKEN) {
        $error = "Invalid security token. Please check the setup token in the script.";
    } elseif (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least one number";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = "Username or email already exists";
            } else {
                // Create new admin user with hashed password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
                
                if ($stmt->execute([$username, $email, $hashed_password])) {
                    $success = true;
                    $admin_username = $username;
                    $admin_email = $email;
                } else {
                    $error = "Failed to create admin user. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        .setup-container {
            max-width: 600px;
            width: 100%;
            padding: 40px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 30px;
            margin-top: 20px;
        }
        .credentials-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .token-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .token-display {
            background: #f8f9fa;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            text-align: center;
        }
        .token-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
            font-family: monospace;
            letter-spacing: 2px;
            margin: 10px 0;
            user-select: all;
        }
        .warning-text {
            color: #856404;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="setup-container">
                    <?php if ($success): ?>
                        <div class="success-box">
                            <h2 class="text-success mb-3">✓ Admin User Created Successfully!</h2>
                            
                            <div class="credentials-box">
                                <h5>Admin Credentials</h5>
                                <p><strong>Username:</strong> <?= htmlspecialchars($admin_username) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($admin_email) ?></p>
                                <p class="text-muted mb-0"><small>Password has been securely hashed and stored</small></p>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <strong>Next Steps:</strong>
                                <ol class="mb-0 mt-2">
                                    <li>Save your credentials in a secure location</li>
                                    <li>Delete or rename this file (create_admin.php) for security</li>
                                    <li>Use the login link below to access the admin panel</li>
                                </ol>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="login.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Go to Admin Login
                                </a>
                            </div>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Admin Login URL: <code><?= SITE_URL ?>/admin/login.php</code>
                                </small>
                            </div>
                        </div>
                    <?php else: ?>
                        <h2 class="text-center mb-4">🔐 Create Admin User</h2>
                        
                        <div class="token-display">
                            <div><strong>📋 Your Security Token:</strong></div>
                            <div class="token-value"><?= $SETUP_TOKEN ?></div>
                            <div><small class="text-muted">Click to select and copy</small></div>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="token" class="form-label">Security Token *</label>
                                <input type="text" class="form-control" id="token" name="token" required 
                                       placeholder="Copy and paste the token from above">
                                <div class="form-text">Copy the security token displayed above</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">
                                    Password requirements:
                                    <ul class="mb-0">
                                        <li>Minimum 8 characters</li>
                                        <li>At least one uppercase letter</li>
                                        <li>At least one lowercase letter</li>
                                        <li>At least one number</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 btn-lg">Create Admin User</button>
                        </form>
                        
                        <div class="alert alert-warning mt-4">
                            <strong>⚠️ Security Warning:</strong>
                            <p class="mb-0">After creating your admin user, please delete or rename this file to prevent unauthorized admin account creation.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>