<?php
/**
 * Migration: Move files from an old uploads path (outside public_html) into the public web root uploads path.
 * Place in public_html/api/ and visit once. DELETE after running.
 */
if (file_exists(__DIR__ . '/../config.local.php')) {
    require_once __DIR__ . '/../config.local.php';
} else {
    require_once __DIR__ . '/../config.php';
}

header('Content-Type: application/json; charset=UTF-8');

$docRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
$publicUploads = rtrim(UPLOAD_DIR, '/\\');

// Build a set of candidate legacy locations to search and migrate from
$candidates = [];

// 1) Under current DOCUMENT_ROOT (private_html case)
$candidates[] = $docRoot . '/api/uploads';

// 2) Sibling of DOCUMENT_ROOT (e.g., domains/.../api/uploads next to public_html or private_html)
$candidates[] = rtrim(dirname($docRoot), '/\\') . '/api/uploads';

// 3) Sibling of the computed public root as an extra safeguard
$publicRoot = rtrim(dirname($publicUploads), '/\\'); // .../public_html/api
$candidates[] = rtrim(dirname($publicRoot), '/\\') . '/api/uploads';

// De-duplicate
$candidates = array_values(array_unique($candidates));

$result = [
    'document_root' => $docRoot,
    'public_uploads' => $publicUploads,
    'old_uploads' => $oldUploads,
    'moved' => 0,
    'skipped' => 0,
    'errors' => [],
];

if (!is_dir($publicUploads)) {
    @mkdir($publicUploads, 0755, true);
}

foreach ($candidates as $oldUploads) {
    if (!is_dir($oldUploads)) {
        $result['errors'][] = 'Candidate not found: ' . $oldUploads;
        continue;
    }
    if (realpath($oldUploads) === realpath($publicUploads)) {
        $result['errors'][] = 'Candidate equals public path (skipping): ' . $oldUploads;
        continue;
    }

    $files = scandir($oldUploads) ?: [];
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $src = $oldUploads . '/' . $f;
        $dst = $publicUploads . '/' . $f;
        if (is_file($src)) {
            if (@rename($src, $dst)) {
                @chmod($dst, 0644);
                $result['moved']++;
            } else {
                if (@copy($src, $dst)) {
                    @chmod($dst, 0644);
                    @unlink($src);
                    $result['moved']++;
                } else {
                    $result['errors'][] = 'Failed to move: ' . $src;
                }
            }
        } else {
            $result['skipped']++;
        }
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>