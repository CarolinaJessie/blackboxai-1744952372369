<?php
/**
 * Helper Functions
 * 
 * This file contains common utility functions used throughout the application.
 */

/**
 * Generate a CSRF token for a specific form
 * 
 * @param string $form_name The name of the form
 * @return string The generated CSRF token
 */
function generate_csrf_token($form_name) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$form_name] = [
        'token' => $token,
        'timestamp' => time()
    ];
    
    return $token;
}

/**
 * Verify a CSRF token for a specific form
 * 
 * @param string $token The token to verify
 * @param string $form_name The name of the form
 * @return bool True if the token is valid, false otherwise
 */
function verify_csrf_token($token, $form_name) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_tokens'][$form_name])) {
        return false;
    }
    
    $stored_token = $_SESSION['csrf_tokens'][$form_name];
    
    // Check if token has expired (30 minutes)
    if (time() - $stored_token['timestamp'] > 1800) {
        unset($_SESSION['csrf_tokens'][$form_name]);
        return false;
    }
    
    // Verify token
    if (hash_equals($stored_token['token'], $token)) {
        // Remove used token
        unset($_SESSION['csrf_tokens'][$form_name]);
        return true;
    }
    
    return false;
}

/**
 * Sanitize user input
 * 
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function sanitize_input($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    return isset($_SESSION["admin_id"]);
}

/**
 * Require user to be logged in
 * 
 * @return void Redirects to login page if user is not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        header("location: login.php");
        exit;
    }
}
