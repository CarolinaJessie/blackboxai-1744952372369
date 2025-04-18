<?php
/**
 * Admin Login Page
 * 
 * This file handles the admin authentication process with enhanced security.
 */

// Start session
session_start();

// Include configuration and helper files
require_once 'config.php';
require_once 'includes/rate_limiter.php';
require_once 'includes/functions.php';

// Initialize variables
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Initialize rate limiter for login attempts
$ip_address = $_SERVER['REMOTE_ADDR'];
$rate_limiter = new RateLimiter('login', 5, 300);

// Check if the IP is already rate limited
if (!$rate_limiter->check($ip_address)) {
    $remaining_time = $rate_limiter->getTimeUntilReset($ip_address);
    $login_err = "Terlalu banyak percobaan login. Silakan coba lagi dalam " . ceil($remaining_time / 60) . " menit.";
} 
// Process form data when form is submitted and not rate limited
elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate CSRF token with form-specific token
        if (!isset($_POST['csrf_token'])) {
            throw new Exception("CSRF token is not set");
        } elseif (!verify_csrf_token($_POST['csrf_token'], 'login_form')) {
            throw new Exception("CSRF token validation failed");
        }
        
        // Validate username
        if (empty(trim($_POST["username"]))) {
            $username_err = "Silakan masukkan username.";
        } else {
            $username = sanitize_input($_POST["username"]);
        }
        
        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Silakan masukkan password.";
        } else {
            $password = trim($_POST["password"]);
        }
        
        // Check input errors before authenticating
        if (empty($username_err) && empty($password_err)) {
            try {
                // Prepare a select statement
                $sql = "SELECT id, username, password FROM admin_users WHERE username = :username";
                $stmt = $conn->prepare($sql);
                
                // Bind parameters
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                
                // Execute the prepared statement
                $stmt->execute();
                
                // Fetch the result
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (password_verify($password, $row['password'])) {
                        // Password is correct, store data in session variables
                        $_SESSION["admin_id"] = $row['id'];
                        $_SESSION["admin_username"] = $row['username'];
                        
                        // Regenerate session ID for security
                        session_regenerate_id(true);
                        
                        // Reset rate limiter for this IP after successful login
                        $rate_limiter->reset($ip_address);
                        
                        // Log successful login
                        error_log("Successful login: User {$row['username']} (ID: {$row['id']}) from IP: $ip_address");
                        
                        // Redirect user to dashboard
                        header("location: dashboard.php");
                        exit;
                    } else {
                        // Password is not valid
                        $login_err = "Username atau password tidak valid.";
                        error_log("Failed login attempt: Invalid password for user {$row['username']} from IP: $ip_address");
                    }
                } else {
                    // Username doesn't exist
                    $login_err = "Username atau password tidak valid.";
                    error_log("Failed login attempt: Invalid username '$username' from IP: $ip_address");
                }
            } catch (PDOException $e) {
                $login_err = "Terjadi kesalahan. Silakan coba lagi nanti.";
                error_log("Login database error: " . $e->getMessage() . " for user $username from IP: $ip_address");
            }
        }
    } catch (Exception $e) {
        $login_err = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
        error_log("Login exception: " . $e->getMessage() . " from IP: $ip_address");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Sim Store</title>
    <link rel="stylesheet" href="/styles.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn-login:hover {
            background: #0056b3;
        }
        .form-group .error-message {
            margin-top: 5px;
            margin-bottom: 0;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        
        <?php if (!empty($login_err)): ?>
            <div class="error-message"><?php echo $login_err; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
                <?php if (!empty($username_err)): ?>
                    <div class="error-message"><?php echo $username_err; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password">
                <?php if (!empty($password_err)): ?>
                    <div class="error-message"><?php echo $password_err; ?></div>
                <?php endif; ?>
            </div>
            
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token('login_form'); ?>">
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html>
