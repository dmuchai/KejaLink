<?php
/**
 * DIAGNOSTIC SCRIPT: Check file paths and structure
 * Upload to /public_html/api/ and visit to diagnose the issue
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>KejaLink Path Diagnostic</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: #059669; }
        .error { color: #dc2626; }
        .warning { color: #f59e0b; }
        h2 { color: #1f2937; border-bottom: 2px solid #10b981; padding-bottom: 5px; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 KejaLink Path Diagnostic</h1>

    <div class="section">
        <h2>Current Script Location</h2>
        <p><strong>__FILE__:</strong> <code><?php echo __FILE__; ?></code></p>
        <p><strong>__DIR__:</strong> <code><?php echo __DIR__; ?></code></p>
        <p><strong>getcwd():</strong> <code><?php echo getcwd(); ?></code></p>
    </div>

    <div class="section">
        <h2>File Existence Checks</h2>
        <?php
        $filesToCheck = [
            'email-config.php (same dir)' => __DIR__ . '/email-config.php',
            'config.php (same dir)' => __DIR__ . '/config.php',
            'auth.php (same dir)' => __DIR__ . '/auth.php',
            'email-config.php (parent)' => dirname(__DIR__) . '/email-config.php',
            'PHPMailer autoload' => __DIR__ . '/../phpmailer/src/PHPMailer.php',
        ];

        foreach ($filesToCheck as $label => $path) {
            $exists = file_exists($path);
            $class = $exists ? 'success' : 'error';
            $status = $exists ? '✅ EXISTS' : '❌ NOT FOUND';
            $readable = $exists ? (is_readable($path) ? '(readable)' : '(NOT readable)') : '';
            echo "<p class='$class'><strong>$status</strong> $label: <code>$path</code> $readable</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>Directory Listing: <?php echo __DIR__; ?></h2>
        <?php
        $files = scandir(__DIR__);
        echo "<ul>";
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = __DIR__ . '/' . $file;
            $type = is_dir($fullPath) ? '📁 DIR' : '📄 FILE';
            $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
            echo "<li>$type <code>$file</code> (permissions: $perms)</li>";
        }
        echo "</ul>";
        ?>
    </div>

    <div class="section">
        <h2>Parent Directory: <?php echo dirname(__DIR__); ?></h2>
        <?php
        $files = scandir(dirname(__DIR__));
        echo "<ul>";
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = dirname(__DIR__) . '/' . $file;
            $type = is_dir($fullPath) ? '📁 DIR' : '📄 FILE';
            echo "<li>$type <code>$file</code></li>";
        }
        echo "</ul>";
        ?>
    </div>

    <div class="section">
        <h2>PHP Include Path</h2>
        <code><?php echo get_include_path(); ?></code>
    </div>

    <div class="section">
        <h2>Recommendations</h2>
        <?php
        if (!file_exists(__DIR__ . '/email-config.php')) {
            echo "<p class='error'>⚠️ <strong>email-config.php NOT found in current directory</strong></p>";
            echo "<p>Expected location: <code>" . __DIR__ . "/email-config.php</code></p>";
            if (file_exists(dirname(__DIR__) . '/email-config.php')) {
                echo "<p class='warning'>✋ Found in parent directory! Move it to: <code>" . __DIR__ . "/</code></p>";
            }
        } else {
            echo "<p class='success'>✅ email-config.php found successfully!</p>";
        }
        ?>
    </div>

    <p style="margin-top: 30px; padding: 15px; background: #fef3c7; border-radius: 5px;">
        <strong>🗑️ DELETE THIS FILE after reviewing the output!</strong>
    </p>
</body>
</html>
