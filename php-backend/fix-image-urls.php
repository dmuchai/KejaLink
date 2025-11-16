<?php
/**
 * Maintenance Script: Fix stored image URLs that point to /api/uploads/ instead of /uploads/
 * Usage: Upload to server (e.g., public_html/api/ or run locally with correct DB creds) then visit in browser or via CLI.
 * Security: DELETE this file after running. It is read-only safe but should not remain deployed.
 */

if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
} else {
    require_once __DIR__ . '/config.php';
}

header('Content-Type: application/json; charset=UTF-8');

try {
    $db = Database::getInstance()->getConnection();

    // Count affected rows first
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM property_images WHERE url LIKE '%/api/uploads/%'");
    $stmt->execute();
    $count = (int)$stmt->fetch()['cnt'];

    if ($count === 0) {
        echo json_encode(['updated' => 0, 'message' => 'No legacy /api/uploads/ URLs found.']);
        exit();
    }

    // Perform update
    $update = $db->prepare("UPDATE property_images SET url = REPLACE(url, '/api/uploads/', '/uploads/') WHERE url LIKE '%/api/uploads/%'");
    $update->execute();

    echo json_encode([
        'updated' => $count,
        'message' => 'Image URLs normalized to /uploads/',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
?>