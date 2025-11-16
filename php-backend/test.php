<?php
/**
 * Test Endpoints - Check backend setup
 */

// Load config
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
} else {
    require_once __DIR__ . '/config.php';
}

header('Content-Type: application/json');

$response = [
    'status' => 'ok',
    'message' => 'KejaLink Backend is running!',
    'php_version' => PHP_VERSION,
    'config_loaded' => defined('DB_NAME') ? 'Yes' : 'No',
    'database' => [
        'host' => DB_HOST ?? 'Not configured',
        'name' => DB_NAME ?? 'Not configured',
        'user' => DB_USER ?? 'Not configured'
    ]
];

// Try to connect to database
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Test query
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response['database']['connection'] = 'Success';
    $response['database']['users_count'] = $result['count'];
    
    // Get table names
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $response['database']['tables'] = $tables;
    
} catch (PDOException $e) {
    $response['database']['connection'] = 'Failed';
    $response['database']['error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
