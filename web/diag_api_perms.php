<?php
/**
 * Standalone diagnostic - no app bootstrap needed.
 * Reads the PHP error log and tests the API via curl with session cookie.
 * DELETE THIS FILE after debugging!
 */
header('Content-Type: text/html; charset=utf-8');

echo "<h2>XHRM API Diagnostic</h2>";

// 1. Find and show recent PHP errors
$logPaths = [
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log',
    '/tmp/php_errors.log',
    ini_get('error_log'),
    dirname(__DIR__) . '/var/log/dev.log',
    dirname(__DIR__) . '/var/log/prod.log',
];

echo "<h3>PHP Error Log</h3>";
$foundLog = false;
foreach ($logPaths as $path) {
    if ($path && file_exists($path) && is_readable($path)) {
        $lines = array_slice(file($path), -50); // last 50 lines
        $relevant = array_filter($lines, fn($l) => stripos($l, 'password') !== false || stripos($l, 'vault') !== false || stripos($l, 'user-key') !== false || stripos($l, 'InvalidParam') !== false || stripos($l, '422') !== false);
        if ($relevant) {
            echo "<p>Found log: <code>$path</code></p>";
            echo "<pre style='background:#111;color:#0f0;padding:10px;font-size:11px;overflow:auto;max-height:300px'>";
            echo htmlspecialchars(implode('', array_slice($relevant, -20)));
            echo "</pre>";
            $foundLog = true;
        }
    }
}
if (!$foundLog) {
    echo "<p>No relevant log entries found. Checked: " . implode(', ', array_filter($logPaths)) . "</p>";
}

// 2. Direct API call using the current session cookie
echo "<h3>Direct API Test (user-keys)</h3>";

$sessionCookie = '';
foreach ($_COOKIE as $k => $v) {
    $sessionCookie .= "$k=" . urlencode($v) . "; ";
}

// Try both localhost and 127.0.0.1
$urls = [
    'http://127.0.0.1/web/index.php/api/v2/password-manager/user-keys',
    'http://localhost/web/index.php/api/v2/password-manager/user-keys',
    'https://mimar.xsofty.com/web/index.php/api/v2/password-manager/user-keys',
];

foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest',
        ],
        CURLOPT_COOKIE => $sessionCookie,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    echo "<p><strong>URL:</strong> $url<br>";
    echo "<strong>HTTP:</strong> $code<br>";
    if ($err)
        echo "<strong>cURL error:</strong> $err<br>";
    echo "<strong>Response:</strong></p>";
    echo "<pre style='background:#f0f0f0;padding:8px;overflow:auto'>" . htmlspecialchars(substr($body, 0, 2000)) . "</pre>";
    echo "<hr>";
}

// 3. Show PHP version and loaded extensions
echo "<h3>Environment</h3>";
echo "<p>PHP: " . PHP_VERSION . " | SAPI: " . PHP_SAPI . "</p>";

// 4. Check if the class exists
$class = 'XHRM\\PasswordManager\\Api\\VaultUserKeyAPI';
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    echo "<p>Class exists: " . (class_exists($class) ? '✅ YES' : '❌ NO') . "</p>";
} else {
    echo "<p style='color:orange'>vendor/autoload.php not found at expected path</p>";
}
