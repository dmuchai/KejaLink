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

// Validate file size (5MB = 5,242,880 bytes)
$maxSizeMB = MAX_FILE_SIZE / 1024 / 1024;
$fileSizeMB = round($file['size'] / 1024 / 1024, 2);

if ($file['size'] > MAX_FILE_SIZE) {
    errorResponse("File too large. Your file is {$fileSizeMB}MB, but the maximum allowed size is {$maxSizeMB}MB. Please compress or resize your image.", 413);
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
    
    // If listing_id is provided, save to property_images table
    $imageId = null;
    if (isset($_POST['listing_id']) && !empty($_POST['listing_id'])) {
        $listingId = $_POST['listing_id'];
        
        // Verify listing exists and user owns it
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT agent_id FROM property_listings WHERE id = ?");
        $stmt->execute([$listingId]);
        $listing = $stmt->fetch();
        
        if ($listing && ($listing['agent_id'] === $currentUser['id'] || $currentUser['role'] === 'admin')) {
            // Get current max display_order for this listing
            $stmt = $db->prepare("SELECT COALESCE(MAX(display_order), -1) + 1 as next_order FROM property_images WHERE listing_id = ?");
            $stmt->execute([$listingId]);
            $orderResult = $stmt->fetch();
            $displayOrder = $orderResult['next_order'];
            
            // Insert image record
            $imageId = generateUUID();
            $stmt = $db->prepare("INSERT INTO property_images (id, listing_id, url, display_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$imageId, $listingId, $publicUrl, $displayOrder]);
        }
    }
    
    jsonResponse([
        'message' => 'File uploaded successfully',
        'url' => $publicUrl,
        'filename' => $uniqueFilename,
        'id' => $imageId
    ], 201);
    
} catch (Exception $e) {
    error_log("Upload Error: " . $e->getMessage());
    errorResponse('File upload failed', 500);
}

?>
