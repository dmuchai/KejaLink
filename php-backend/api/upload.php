<?php
/**
 * Image Upload API
 * Handles file uploads for property images
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Require authentication
$currentUser = Auth::requireAuth();

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    errorResponse('No file uploaded or upload error occurred');
}

$file = $_FILES['image'];

// Validate file size
if ($file['size'] > MAX_FILE_SIZE) {
    errorResponse('File too large. Maximum size is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
}

// Validate file extension
$filename = $file['name'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($extension, ALLOWED_EXTENSIONS)) {
    errorResponse('Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS));
}

// Validate file is actually an image
$imageInfo = getimagesize($file['tmp_name']);
if ($imageInfo === false) {
    errorResponse('File is not a valid image');
}

try {
    // Generate unique filename
    $uniqueFilename = uniqid('listing_', true) . '.' . $extension;
    $uploadPath = UPLOAD_DIR . $uniqueFilename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        errorResponse('Failed to save file', 500);
    }
    
    // Set proper permissions
    chmod($uploadPath, 0644);
    
    // Generate public URL
    $publicUrl = UPLOAD_URL . $uniqueFilename;
    
    // Optionally: Save to database if you want to track uploads
    // $db = Database::getInstance()->getConnection();
    // $stmt = $db->prepare("INSERT INTO uploads (user_id, filename, url) VALUES (?, ?, ?)");
    // $stmt->execute([$currentUser['id'], $uniqueFilename, $publicUrl]);
    
    jsonResponse([
        'message' => 'File uploaded successfully',
        'url' => $publicUrl,
        'filename' => $uniqueFilename
    ], 201);
    
} catch (Exception $e) {
    error_log("Upload Error: " . $e->getMessage());
    errorResponse('File upload failed', 500);
}

?>
