<?php
// Load config first to get SESSION_NAME
require_once 'config/config.php';

// Start session with the same name used in header.php
session_name(SESSION_NAME);
session_start();

// Unset all session variables
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[SESSION_NAME])) {
    setcookie(SESSION_NAME, '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to home page
header('Location: ' . SITE_URL . '/index.php');
exit;
