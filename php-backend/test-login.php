<?php
/**
 * Test Login Endpoint
 * This script tests the login functionality directly
 */

require_once __DIR__ . '/config.local.php';
require_once __DIR__ . '/auth.php';

// Test data
$testEmail = 'test@example.com';
$testPassword = 'test123';

echo "=== Testing Login Functionality ===\n\n";

// 1. Check if user exists
try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT id, email, password_hash, full_name, role FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✓ User found: {$user['email']}\n";
        echo "  - ID: {$user['id']}\n";
        echo "  - Name: {$user['full_name']}\n";
        echo "  - Role: {$user['role']}\n";
        echo "  - Password hash: " . substr($user['password_hash'], 0, 20) . "...\n\n";
        
        // Test password verification
        $isValid = Auth::verifyPassword($testPassword, $user['password_hash']);
        echo "Password verification: " . ($isValid ? "✓ VALID" : "✗ INVALID") . "\n";
        
        if (!$isValid) {
            echo "\n⚠️  Password doesn't match. You may need to:\n";
            echo "   1. Register a new user with this password, OR\n";
            echo "   2. Update the password hash in the database\n\n";
        }
    } else {
        echo "✗ User not found: {$testEmail}\n";
        echo "\nTo create a test user, run:\n";
        echo "  curl -X POST http://localhost:8080/api/auth.php?action=register \\\n";
        echo "    -H 'Content-Type: application/json' \\\n";
        echo "    -d '{\"email\":\"{$testEmail}\",\"password\":\"{$testPassword}\",\"full_name\":\"Test User\",\"role\":\"tenant\"}'\n\n";
    }
    
    // List all users
    echo "\n=== All Users in Database ===\n";
    $stmt = $db->prepare("SELECT id, email, full_name, role FROM users LIMIT 10");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "No users found in database.\n";
    } else {
        foreach ($users as $u) {
            echo "  - {$u['email']} ({$u['full_name']}) - {$u['role']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";

