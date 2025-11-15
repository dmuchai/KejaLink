<?php
/**
 * Test all API endpoints to verify they're accessible
 * Upload to /public_html/api/ and visit to check status
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>KejaLink API Endpoint Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: #059669; }
        .error { color: #dc2626; }
        .warning { color: #f59e0b; }
        h2 { color: #1f2937; border-bottom: 2px solid #10b981; padding-bottom: 5px; }
        .endpoint { margin: 10px 0; padding: 10px; background: #f9fafb; border-radius: 3px; }
        button { background: #10b981; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #059669; }
        #results { white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>🔍 KejaLink API Endpoint Test</h1>

    <div class="section">
        <h2>Current Location</h2>
        <p><strong>This file:</strong> <code><?php echo __FILE__; ?></code></p>
        <p><strong>Directory:</strong> <code><?php echo __DIR__; ?></code></p>
        <p><strong>Current URL:</strong> <code id="currentUrl"></code></p>
    </div>

    <div class="section">
        <h2>File Checks</h2>
        <?php
        $files = [
            'auth.php' => __DIR__ . '/auth.php',
            'listings.php' => __DIR__ . '/listings.php',
            'upload.php' => __DIR__ . '/upload.php',
            'config.php' => __DIR__ . '/config.php',
            'email-config.php' => __DIR__ . '/email-config.php',
        ];

        foreach ($files as $name => $path) {
            $exists = file_exists($path);
            $class = $exists ? 'success' : 'error';
            $status = $exists ? '✅' : '❌';
            echo "<p class='$class'>$status <strong>$name:</strong> " . ($exists ? 'EXISTS' : 'NOT FOUND') . "</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>Test API Endpoints</h2>
        <p>Click buttons to test each endpoint from the browser:</p>
        
        <div class="endpoint">
            <strong>GET /api/listings.php</strong>
            <button onclick="testEndpoint('GET', '/api/listings.php')">Test</button>
        </div>

        <div class="endpoint">
            <strong>GET /api/auth.php (should return method not allowed)</strong>
            <button onclick="testEndpoint('GET', '/api/auth.php')">Test</button>
        </div>

        <div class="endpoint">
            <strong>POST /api/auth.php?action=forgot-password</strong>
            <button onclick="testForgotPassword()">Test</button>
        </div>

        <div id="results"></div>
    </div>

    <div class="section">
        <h2>URL Construction Test</h2>
        <p>Expected API base: <code id="expectedBase"></code></p>
        <p>Expected listings URL: <code id="expectedListings"></code></p>
        <p>Expected auth URL: <code id="expectedAuth"></code></p>
    </div>

    <script>
        // Show current URL
        document.getElementById('currentUrl').textContent = window.location.href;

        // Determine base URL
        const protocol = window.location.protocol;
        const host = window.location.host;
        const baseUrl = `${protocol}//${host}`;
        
        document.getElementById('expectedBase').textContent = baseUrl;
        document.getElementById('expectedListings').textContent = `${baseUrl}/api/listings.php`;
        document.getElementById('expectedAuth').textContent = `${baseUrl}/api/auth.php`;

        async function testEndpoint(method, endpoint) {
            const results = document.getElementById('results');
            results.textContent = `Testing ${method} ${endpoint}...\n`;

            try {
                const url = `${baseUrl}${endpoint}`;
                results.textContent += `URL: ${url}\n\n`;

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                results.textContent += `Status: ${response.status} ${response.statusText}\n`;
                results.textContent += `Headers:\n`;
                response.headers.forEach((value, key) => {
                    results.textContent += `  ${key}: ${value}\n`;
                });

                const text = await response.text();
                results.textContent += `\nResponse Body:\n${text}\n`;

                try {
                    const json = JSON.parse(text);
                    results.textContent += `\n✅ Valid JSON Response:\n${JSON.stringify(json, null, 2)}\n`;
                } catch (e) {
                    results.textContent += `\n❌ Invalid JSON: ${e.message}\n`;
                }

            } catch (error) {
                results.textContent += `\n❌ Error: ${error.message}\n`;
            }
        }

        async function testForgotPassword() {
            const results = document.getElementById('results');
            results.textContent = `Testing POST /api/auth.php?action=forgot-password...\n`;

            try {
                const url = `${baseUrl}/api/auth.php?action=forgot-password`;
                results.textContent += `URL: ${url}\n\n`;

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: 'test@example.com'
                    })
                });

                results.textContent += `Status: ${response.status} ${response.statusText}\n`;
                
                const text = await response.text();
                results.textContent += `\nResponse Body:\n${text}\n`;

                try {
                    const json = JSON.parse(text);
                    results.textContent += `\n✅ Valid JSON Response:\n${JSON.stringify(json, null, 2)}\n`;
                } catch (e) {
                    results.textContent += `\n❌ Invalid JSON: ${e.message}\n`;
                }

            } catch (error) {
                results.textContent += `\n❌ Error: ${error.message}\n`;
            }
        }
    </script>

    <div class="section">
        <h2>Instructions</h2>
        <p>1. Use the buttons above to test each endpoint</p>
        <p>2. Check if endpoints return valid JSON</p>
        <p>3. Verify the URLs match expected paths</p>
        <p>4. <strong style="color: red;">DELETE THIS FILE after testing!</strong></p>
    </div>
</body>
</html>
