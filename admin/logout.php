<?php
/**
 * Logout Script
 * 
 * This file handles the logout process for admin users.
 */

// Include configuration file
require_once 'config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("location: login.php");
exit;
?>
