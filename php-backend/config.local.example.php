<?php
/**
 * KejaLink API - LOCAL DEVELOPMENT Configuration
 * Copy this file to config.local.php and update with your local credentials
 * NEVER commit config.local.php to git!
 */

// ============================================
// ERROR REPORTING (verbose for debugging)
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// CORS HEADERS (for local Vite dev server)
// ============================================
if (php_sapi_name() !== 'cli') {
    // Allow requests from Vite dev server
    header('Access-Control-Allow-Origin: http://localhost:5173');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=UTF-8');

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// ============================================
// LOCAL DATABASE CONFIGURATION
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'kejalink_local'); // Change if you used a different name
define('DB_USER', 'kejalink_dev'); // Or 'root' if using MySQL root user
define('DB_PASS', 'dev_password_123'); // Your MySQL password
define('DB_CHARSET', 'utf8mb4');

// ============================================
// JWT CONFIGURATION (use different secret for local!)
// ============================================

define('JWT_SECRET', 'local-dev-secret-key-DO-NOT-USE-IN-PRODUCTION');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 604800); // 7 days in seconds

// ============================================
// LOCAL APPLICATION CONFIGURATION
// ============================================
define('APP_URL', 'http://localhost:5173');

// ============================================
// UPLOAD PATHS (for local file uploads)
// ============================================
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'http://localhost:8080/uploads/');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    mkdir(UPLOAD_DIR . 'properties/', 0755, true);
    mkdir(UPLOAD_DIR . 'profiles/', 0755, true);
}

// ============================================
// EMAIL CONFIGURATION (optional for local)
// ============================================
// You can test without email by commenting out email-config include
// or use a test SMTP service like Mailtrap.io

/*
require_once __DIR__ . '/email-config.local.php';
*/

// ============================================
// DATABASE CONNECTION HELPER
// ============================================
function getDatabase() {
    static $db = null;
    
    if ($db === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    
    return $db;
}

// ============================================
// DEBUG MODE
// ============================================
define('DEBUG_MODE', true); // Shows detailed error messages

?>
