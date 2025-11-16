<?php
/**
 * JWT Authentication Helper
 * Handles token creation, validation, and user authentication
 */

// Load local config if it exists, otherwise use production config (only if not already loaded)
if (!defined('DB_HOST')) {
    if (file_exists(__DIR__ . '/config.local.php')) {
        require_once __DIR__ . '/config.local.php';
    } else {
        require_once __DIR__ . '/config.php';
    }
}

class Auth {
    
    /**
     * Generate JWT token for user
     */
    public static function generateToken($userId, $email, $role) {
        $header = json_encode(['typ' => 'JWT', 'alg' => JWT_ALGORITHM]);
        $payload = json_encode([
            'iss' => APP_URL,
            'aud' => APP_URL,
            'iat' => time(),
            'exp' => time() + JWT_EXPIRY,
            'userId' => $userId,
            'email' => $email,
            'role' => $role
        ]);
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    /**
     * Validate and decode JWT token
     */
    public static function validateToken($token) {
        if (!$token) {
            return null;
        }
        
        // Remove "Bearer " prefix if present
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        
        // Verify signature
        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }
        
        // Decode payload
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        
        // Check expiration
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }
    
    /**
     * Get current authenticated user from request
     */
    public static function getCurrentUser() {
        $authHeader = getAuthHeader();
        if (!$authHeader) {
            return null;
        }
        
        $tokenData = self::validateToken($authHeader);
        if (!$tokenData) {
            return null;
        }
        
        return [
            'id' => $tokenData['userId'],
            'email' => $tokenData['email'],
            'role' => $tokenData['role']
        ];
    }
    
    /**
     * Require authentication - dies if not authenticated
     */
    public static function requireAuth() {
        $user = self::getCurrentUser();
        if (!$user) {
            errorResponse('Unauthorized - Invalid or expired token', 401);
        }
        return $user;
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($requiredRole) {
        $user = self::requireAuth();
        if ($user['role'] !== $requiredRole && $user['role'] !== 'admin') {
            errorResponse('Forbidden - Insufficient permissions', 403);
        }
        return $user;
    }
    
    /**
     * Hash password using bcrypt
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}

?>
