<?php
/**
 * Database Configuration File
 */

// Error handling configuration
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set up custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Log the error
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    
    // Don't execute PHP's internal error handler
    return true;
});

// Set up exception handler
set_exception_handler(function($exception) {
    // Log the exception
    error_log("Uncaught Exception: " . $exception->getMessage() . 
              " in " . $exception->getFile() . 
              " on line " . $exception->getLine());
    
    // Provide a user-friendly message
    http_response_code(500);
    echo "<div style='text-align:center;padding:40px;'>";
    echo "<h1>System Error</h1>";
    echo "<p>An unexpected error occurred. Please try again later.</p>";
    echo "<p><a href='/index.html'>Return to Homepage</a></p>";
    echo "</div>";
    exit;
});

// Database configuration
$db_path = __DIR__ . '/database/esim_store.db';
$db_dir = dirname($db_path);

// Create database directory if it doesn't exist
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0777, true);
}

try {
    // Create PDO connection to SQLite database
    $conn = new PDO("sqlite:$db_path");
    
    // Set PDO to throw exceptions on error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            email TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        );
        
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            stock INTEGER NOT NULL DEFAULT 0,
            price DECIMAL(10, 2) NOT NULL,
            description TEXT,
            image_path TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME
        );
    ");
    
    // Insert default admin user if it doesn't exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
    $stmt->execute(['admin']);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin@example.com']);
    }
    
} catch(PDOException $e) {
    // Log the error
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
