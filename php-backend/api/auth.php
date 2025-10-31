<?php
/**
 * Authentication API Endpoints
 * Handles register, login, logout, and profile
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

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

?>
