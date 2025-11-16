<?php
/**
 * Maintenance Script: Switch stored image URLs to /api/uploads/ scheme
 * Use this if you previously saved URLs as /uploads/ and now want /api/uploads/.
 * DELETE after running.
 */

if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
} else {
    require_once __DIR__ . '/config.php';
}

header('Content-Type: application/json; charset=UTF-8');

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM property_images WHERE url LIKE '%/uploads/%' AND url NOT LIKE '%/api/uploads/%'");
    $stmt->execute();
    $count = (int)$stmt->fetch()['cnt'];

    if ($count === 0) {
        echo json_encode(['updated' => 0, 'message' => 'No /uploads/ URLs to convert']);
        exit();
    }

    $update = $db->prepare("UPDATE property_images SET url = REPLACE(url, '/uploads/', '/api/uploads/') WHERE url LIKE '%/uploads/%' AND url NOT LIKE '%/api/uploads/%'");
    $update->execute();

    echo json_encode(['updated' => $count, 'message' => 'Converted URLs to /api/uploads/']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
?>