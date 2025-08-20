<?php
/**
 * Secure Logout Handler for MedicalNotes
 * Ensures complete session cleanup and security
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log the logout attempt
if (isset($_SESSION['member_id'])) {
    $userId = $_SESSION['member_id'];
    $username = $_SESSION['username'] ?? 'Unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    error_log("User logout: User ID $userId, Username: $username, IP: $ip, Time: " . date('Y-m-d H:i:s'));
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear any other cookies that might be set
if (isset($_COOKIE['PHPSESSID'])) {
    setcookie('PHPSESSID', '', time() - 3600, '/');
}

// Redirect to login page with success message
header("Location: login.php?logout=success");
exit;
?>

