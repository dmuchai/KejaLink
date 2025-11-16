<?php
/**
 * Reset Test User Password
 * This script creates or updates a test user with a known password
 */

require_once __DIR__ . '/config.local.php';
require_once __DIR__ . '/auth.php';

$testEmail = 'test@example.com';
$testPassword = 'test123';
$testName = 'Test User';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Update existing user's password
        $passwordHash = Auth::hashPassword($testPassword);
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$passwordHash, $testEmail]);
        echo "✓ Updated password for existing user: {$testEmail}\n";
        echo "  Password: {$testPassword}\n";
    } else {
        // Create new user
        $userId = generateUUID();
        $passwordHash = Auth::hashPassword($testPassword);
        
        $stmt = $db->prepare("
            INSERT INTO users (id, email, password_hash, full_name, role, is_verified_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $testEmail,
            $passwordHash,
            $testName,
            'tenant',
            0
        ]);
        echo "✓ Created new test user: {$testEmail}\n";
        echo "  Password: {$testPassword}\n";
    }
    
    // Verify the password works
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch();
    
    if (Auth::verifyPassword($testPassword, $user['password_hash'])) {
        echo "✓ Password verification successful!\n";
        echo "\nYou can now login with:\n";
        echo "  Email: {$testEmail}\n";
        echo "  Password: {$testPassword}\n";
    } else {
        echo "✗ Password verification failed - something went wrong!\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

