<?php
/**
 * Images API
 * Currently supports deletion of a listing image by id.
 *
 * DELETE /api/images.php?id=<imageId>
 */

// Load local config if it exists, otherwise use production config
if (file_exists(__DIR__ . '/../config.local.php')) {
    require_once __DIR__ . '/../config.local.php';
} else {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/../auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

switch ($method) {
    case 'OPTIONS':
        // Preflight is handled in config include; just exit
        http_response_code(204);
        exit();
    case 'DELETE':
        if (!$id) errorResponse('Image ID required');
        deleteImage($id);
        break;
    default:
        errorResponse('Method not allowed', 405);
}

function deleteImage($imageId) {
    $currentUser = Auth::requireRole('agent');

    try {
        $db = Database::getInstance()->getConnection();

        // Fetch image and listing to verify ownership
        $stmt = $db->prepare("SELECT pi.id, pi.url, pi.listing_id, pl.agent_id FROM property_images pi JOIN property_listings pl ON pi.listing_id = pl.id WHERE pi.id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();

        if (!$image) {
            errorResponse('Image not found', 404);
        }

        if ($image['agent_id'] !== $currentUser['id'] && $currentUser['role'] !== 'admin') {
            errorResponse('You can only remove images from your own listings', 403);
        }

        // Delete DB record first
        $stmt = $db->prepare("DELETE FROM property_images WHERE id = ?");
        $stmt->execute([$imageId]);

        // Best-effort remove the physical file if it resides under our uploads path
        $url = $image['url'];
        $filename = null;
        if (defined('UPLOAD_URL') && strpos($url, UPLOAD_URL) === 0) {
            $filename = substr($url, strlen(UPLOAD_URL));
        } else {
            // fallback to basename
            $filename = basename(parse_url($url, PHP_URL_PATH));
        }

        if ($filename && defined('UPLOAD_DIR')) {
            $filepath = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($filepath)) {
                @unlink($filepath);
            }
        }

        jsonResponse(['message' => 'Image deleted successfully']);
    } catch (PDOException $e) {
        error_log('Delete Image Error: ' . $e->getMessage());
        errorResponse('Failed to delete image', 500);
    }
}

?>
