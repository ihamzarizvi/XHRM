<?php
/**
 * Direct payroll error diagnostic
 * Access via: https://mimar.xsofty.com/web/diag_payroll2.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Payroll Error Diagnostic</h2>";

// Check PHP error log location
echo "<h3>PHP error_log setting:</h3>";
echo "<pre>" . ini_get('error_log') . "</pre>";

// Check the Symfony/XHRM var/log directory
$varLogDir = __DIR__ . '/../var/log';
$srcCacheDir = __DIR__ . '/../src/cache';

echo "<h3>Checking log directories:</h3>";

// Check all possible log locations
$logLocations = [
    __DIR__ . '/../var/log',
    __DIR__ . '/../src/log',
    __DIR__ . '/../src/cache',
    '/tmp',
];

foreach ($logLocations as $dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $f) {
            $path = $dir . '/' . $f;
            if (is_file($path) && (strpos($f, '.log') !== false || strpos($f, 'error') !== false)) {
                $mtime = filemtime($path);
                // Only show files modified in last 24 hours
                if (time() - $mtime < 86400) {
                    echo "<p><b>$path</b> (modified: " . date('Y-m-d H:i:s', $mtime) . ", size: " . filesize($path) . " bytes)</p>";
                    $lines = file($path);
                    $lastLines = array_slice($lines, -20);
                    echo "<pre style='background:#111;color:#0f0;padding:8px;font-size:11px;max-height:300px;overflow:auto'>";
                    echo htmlspecialchars(implode('', $lastLines));
                    echo "</pre>";
                }
            }
        }
    }
}

// Try to reproduce the error by loading the framework
echo "<h3>Framework Loading Test:</h3>";
try {
    // Load the Conf
    $confPath = __DIR__ . '/../lib/confs/Conf.php';
    if (!file_exists($confPath)) {
        // Check alternate location
        $confPath = __DIR__ . '/../src/lib/confs/Conf.php';
    }
    if (file_exists($confPath)) {
        echo "<p style='color:green'>Conf found at: $confPath</p>";
    } else {
        echo "<p style='color:red'>Conf.php NOT FOUND</p>";
    }

    // Check autoload
    $autoloadPaths = [
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/../src/vendor/autoload.php',
    ];
    $autoloadFound = false;
    foreach ($autoloadPaths as $ap) {
        if (file_exists($ap)) {
            echo "<p style='color:green'>Autoload found: $ap</p>";
            $autoloadFound = true;
            break;
        }
    }
    if (!$autoloadFound) {
        echo "<p style='color:red'>No autoload.php found!</p>";
    }

} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Check what web server error log says
echo "<h3>Server Error Log (last entries):</h3>";
$serverLogPaths = [
    '/home/u118669189/logs/error.log',
    '/home/u118669189/domains/xsofty.com/logs/error.log',
    '/home/u118669189/public_html/error_log',
    __DIR__ . '/../error_log',
    __DIR__ . '/error_log',
    '/home/u118669189/domains/xsofty.com/public_html/mimarhrm/error_log',
    '/home/u118669189/domains/xsofty.com/public_html/mimarhrm/web/error_log',
];

foreach ($serverLogPaths as $slp) {
    if (file_exists($slp) && is_readable($slp)) {
        $size = filesize($slp);
        echo "<p><b>Found: $slp</b> ($size bytes)</p>";
        if ($size > 0) {
            // Read last 5KB
            $fp = fopen($slp, 'r');
            $offset = max(0, $size - 5000);
            fseek($fp, $offset);
            $content = fread($fp, 5000);
            fclose($fp);
            echo "<pre style='background:#111;color:#f90;padding:8px;font-size:11px;max-height:400px;overflow:auto'>";
            echo htmlspecialchars($content);
            echo "</pre>";
        }
    }
}

// Direct test: try to hit the payroll route internally
echo "<h3>Internal Route Test:</h3>";
$testUrl = 'https://mimar.xsofty.com/web/index.php/payroll/generatePayroll';
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE'] ?? '');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: <b>$httpCode</b></p>";
if ($httpCode >= 400) {
    // Show first 2000 chars of response
    echo "<pre style='background:#300;color:#fff;padding:8px;font-size:11px;max-height:300px;overflow:auto'>";
    echo htmlspecialchars(substr($response, 0, 2000));
    echo "</pre>";
}
