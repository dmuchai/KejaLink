<?php
/**
 * Authentication API Endpoints
 * Handles register, login, logout, and password reset
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Load email configuration for password reset emails
// This file should exist in the same directory (api/)
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
        errorResponse('Invalid endpoint', 404);
}

/**
 * Register new user
 * POST /api/auth.php?action=register
 * Body: { email, password, full_name, role }
 */
function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $data = getRequestBody();
    
    // Validate required fields
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $fullName = $data['full_name'] ?? '';
    $role = $data['role'] ?? 'tenant';
    
    if (!$email || !$password || !$fullName) {
        errorResponse('Email, password, and full name are required');
    }
    
    if (!validateEmail($email)) {
        errorResponse('Invalid email format');
    }
    
    if (strlen($password) < 6) {
        errorResponse('Password must be at least 6 characters');
    }
    
    if (!in_array($role, ['tenant', 'agent'])) {
        errorResponse('Invalid role. Must be tenant or agent');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            errorResponse('Email already registered', 409);
        }
        
        // Create user
        $userId = generateUUID();
        $passwordHash = Auth::hashPassword($password);
        
        $stmt = $db->prepare("
            INSERT INTO users (id, email, password_hash, full_name, role, is_verified_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $isVerified = 0; // Agents need manual verification
        
        $stmt->execute([
            $userId,
            $email,
            $passwordHash,
            sanitizeString($fullName),
            $role,
            $isVerified
        ]);
        
        // Generate token
        $token = Auth::generateToken($userId, $email, $role);
        
        // Get user profile
        $stmt = $db->prepare("
            SELECT id, email, full_name, role, is_verified_agent, profile_picture_url, 
                   phone_number, created_at
            FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        jsonResponse([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user
        ], 201);
        
    } catch (PDOException $e) {
        error_log("Register Error: " . $e->getMessage());
        errorResponse('Registration failed', 500);
    }
}

/**
 * Login user
 * POST /api/auth.php?action=login
 * Body: { email, password }
 */
function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $data = getRequestBody();
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (!$email || !$password) {
        errorResponse('Email and password are required');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Get user
        $stmt = $db->prepare("
            SELECT id, email, password_hash, full_name, role, is_verified_agent, 
                   profile_picture_url, phone_number
            FROM users WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            errorResponse('Invalid email or password', 401);
        }
        
        // Verify password
        if (!Auth::verifyPassword($password, $user['password_hash'])) {
            errorResponse('Invalid email or password', 401);
        }
        
        // Generate token
        $token = Auth::generateToken($user['id'], $user['email'], $user['role']);
        
        // Remove password hash from response
        unset($user['password_hash']);
        
        jsonResponse([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
        
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        errorResponse('Login failed', 500);
    }
}

/**
 * Get current user profile
 * GET /api/auth.php?action=profile
 * Headers: Authorization: Bearer <token>
 */
function handleProfile() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    $currentUser = Auth::requireAuth();
    
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT id, email, full_name, role, is_verified_agent, profile_picture_url, 
                   phone_number, created_at, updated_at
            FROM users WHERE id = ?
        ");
        $stmt->execute([$currentUser['id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            errorResponse('User not found', 404);
        }
        
        jsonResponse(['user' => $user]);
        
    } catch (PDOException $e) {
        error_log("Profile Error: " . $e->getMessage());
        errorResponse('Failed to fetch profile', 500);
    }
}

/**
 * Logout user (placeholder - client should delete token)
 * POST /api/auth.php?action=logout
 */
function handleLogout() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    // In a stateless JWT system, logout is typically handled client-side
    // by deleting the token from localStorage/cookies
    
    jsonResponse(['message' => 'Logout successful']);
}

/**
 * Forgot Password - Request password reset
 * POST /api/auth.php?action=forgot-password
 * Body: { email }
 */
function handleForgotPassword() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $data = getRequestBody();
    $email = $data['email'] ?? '';
    
    if (!$email || !validateEmail($email)) {
        errorResponse('Valid email is required');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Check if user exists
        $stmt = $db->prepare("SELECT id, email, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Always return success (security best practice - don't reveal if email exists)
        if (!$user) {
            jsonResponse(['message' => 'If an account exists with this email, you will receive password reset instructions.']);
            return;
        }
        
        // Generate secure random token
        $token = bin2hex(random_bytes(32)); // 64-character hex string
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour
        
        // Invalidate any existing tokens for this user
        $stmt = $db->prepare("UPDATE password_reset_tokens SET used = TRUE WHERE user_id = ? AND used = FALSE");
        $stmt->execute([$user['id']]);
        
        // Create new reset token
        $stmt = $db->prepare("
            INSERT INTO password_reset_tokens (id, user_id, token, expires_at)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            generateUUID(),
            $user['id'],
            $token,
            $expiresAt
        ]);
        
        // Send email with reset link
        $resetLink = APP_URL . "/reset-password?token=" . $token;
        sendPasswordResetEmail($user['email'], $user['full_name'], $resetLink);
        
        jsonResponse(['message' => 'If an account exists with this email, you will receive password reset instructions.']);
        
    } catch (PDOException $e) {
        error_log("Forgot Password Error: " . $e->getMessage());
        errorResponse('Failed to process request', 500);
    }
}

/**
 * Validate Reset Token
 * GET /api/auth.php?action=validate-reset-token&token=xxx
 */
function handleValidateResetToken() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    $token = $_GET['token'] ?? '';
    
    if (!$token) {
        errorResponse('Token is required');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT id, user_id, expires_at, used 
            FROM password_reset_tokens 
            WHERE token = ?
        ");
        $stmt->execute([$token]);
        $resetToken = $stmt->fetch();
        
        if (!$resetToken) {
            errorResponse('Invalid reset token', 404);
        }
        
        if ($resetToken['used']) {
            errorResponse('This reset link has already been used', 400);
        }
        
        if (strtotime($resetToken['expires_at']) < time()) {
            errorResponse('This reset link has expired', 400);
        }
        
        jsonResponse(['valid' => true, 'message' => 'Token is valid']);
        
    } catch (PDOException $e) {
        error_log("Validate Token Error: " . $e->getMessage());
        errorResponse('Failed to validate token', 500);
    }
}

/**
 * Reset Password
 * POST /api/auth.php?action=reset-password
 * Body: { token, new_password }
 */
function handleResetPassword() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $data = getRequestBody();
    $token = $data['token'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    
    if (!$token || !$newPassword) {
        errorResponse('Token and new password are required');
    }
    
    if (strlen($newPassword) < 6) {
        errorResponse('Password must be at least 6 characters');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // Validate token
        $stmt = $db->prepare("
            SELECT id, user_id, expires_at, used 
            FROM password_reset_tokens 
            WHERE token = ?
        ");
        $stmt->execute([$token]);
        $resetToken = $stmt->fetch();
        
        if (!$resetToken) {
            $db->rollBack();
            errorResponse('Invalid reset token', 404);
        }
        
        if ($resetToken['used']) {
            $db->rollBack();
            errorResponse('This reset link has already been used', 400);
        }
        
        if (strtotime($resetToken['expires_at']) < time()) {
            $db->rollBack();
            errorResponse('This reset link has expired', 400);
        }
        
        // Update user password
        $passwordHash = Auth::hashPassword($newPassword);
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $resetToken['user_id']]);
        
        // Mark token as used
        $stmt = $db->prepare("UPDATE password_reset_tokens SET used = TRUE WHERE id = ?");
        $stmt->execute([$resetToken['id']]);
        
        $db->commit();
        
        jsonResponse(['message' => 'Password reset successful']);
        
    } catch (PDOException $e) {
        if (isset($db)) $db->rollBack();
        error_log("Reset Password Error: " . $e->getMessage());
        errorResponse('Failed to reset password', 500);
    }
}

?>
