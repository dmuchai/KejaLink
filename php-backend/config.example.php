<?php
/**
 * KejaLink API Configuration - EXAMPLE TEMPLATE
 * Copy this file to config.php and fill in your actual credentials
 */

// ============================================
// ERROR REPORTING
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // PRODUCTION: Disable display_errors to prevent breaking JSON responses
ini_set('log_errors', 1); // Log errors to file instead

// ============================================
// CORS HEADERS (only for web requests)
// ============================================
if (php_sapi_name() !== 'cli') {
    header('Access-Control-Allow-Origin: *');
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
// DATABASE CONFIGURATION
// ============================================

define('DB_HOST', 'localhost'); // Usually 'localhost' on shared hosting
define('DB_NAME', 'your_database_name'); // CHANGE THIS to your actual database name
define('DB_USER', 'your_database_user'); // CHANGE THIS to your actual database user
define('DB_PASS', 'your_database_password'); // CHANGE THIS to your actual password
define('DB_CHARSET', 'utf8mb4');

// ============================================
// JWT CONFIGURATION
// ============================================

define('JWT_SECRET', 'CHANGE-THIS-TO-A-LONG-RANDOM-STRING'); // IMPORTANT: Generate a secure random string
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 604800); // 7 days in seconds

// ============================================
// APPLICATION CONFIGURATION
// ============================================
define('APP_URL', 'https://yourdomain.com'); // CHANGE THIS to your domain

// ============================================
// FILE UPLOAD CONFIGURATION
// ============================================

// Uploads: store in /uploads at web root for direct serving
define('UPLOAD_DIR', __DIR__ . '/uploads/'); // Upload directory
define('UPLOAD_URL', 'https://yourdomain.com/uploads/'); // CHANGE THIS - Public URL to uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ============================================
// APPLICATION SETTINGS
// ============================================

define('APP_NAME', 'KejaLink');
define('APP_URL', 'https://yourdomain.com'); // CHANGE THIS
define('API_VERSION', 'v1');

// ============================================
// DATABASE CONNECTION CLASS
// ============================================

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function errorResponse($message, $statusCode = 400) {
    jsonResponse(['error' => $message], $statusCode);
}

function getAuthHeader() {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        return $headers['Authorization'];
    }
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return $_SERVER['HTTP_AUTHORIZATION'];
    }
    return null;
}

function getRequestBody() {
    $body = file_get_contents('php://input');
    return json_decode($body, true) ?? [];
}

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitizeString($string) {
    return htmlspecialchars(strip_tags(trim($string)));
}

// ============================================
// CREATE UPLOAD DIRECTORY IF NOT EXISTS
// ============================================

if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Create .htaccess for uploads directory security
$htaccessContent = <<<HTACCESS
# Prevent PHP execution in uploads directory
php_flag engine off

# Allow only image files
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
HTACCESS;

$htaccessPath = UPLOAD_DIR . '.htaccess';
if (!file_exists($htaccessPath)) {
    file_put_contents($htaccessPath, $htaccessContent);
}

?>
