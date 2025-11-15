<?php
/**
 * Authentication API Endpoints
 * Handles register, login, logout, and password reset
 * 
 * PRODUCTION VERSION - Adjusted paths for HostAfrica deployment
 */

// Adjust paths based on server structure
// On HostAfrica: config.php and auth.php are in same directory as this file
require_once __DIR__ . '/config.php';

// Check if auth.php exists in same directory, if not try parent
if (file_exists(__DIR__ . '/auth.php')) {
    require_once __DIR__ . '/auth.php';
} elseif (file_exists(__DIR__ . '/../auth.php')) {
    require_once __DIR__ . '/../auth.php';
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Server configuration error: auth.php not found']);
    exit;
}

// Load email configuration for password reset emails
if (file_exists(__DIR__ . '/email-config.php')) {
    require_once __DIR__ . '/email-config.php';
} else {
    error_log("WARNING: email-config.php not found. Password reset emails will not work.");
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';

switch ($path) {
    case 'register':
        handleRegister();
        break;
    case 'login':
        handleLogin();
        break;
    case 'profile':
        handleProfile();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'forgot-password':
        handleForgotPassword();
        break;
    case 'validate-reset-token':
        handleValidateResetToken();
        break;
    case 'reset-password':
        handleResetPassword();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid endpoint']);
        break;
}
