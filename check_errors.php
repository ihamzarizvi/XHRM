<?php
// check_errors.php - View server error logs and create log directory

echo "<h1>XHRM Error Diagnostic</h1>";

// 1. Create log directory
$root = __DIR__;
$logDir = $root . '/log';
$logFile = $logDir . '/ohrm.log';

if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
    chmod($logDir, 0777);
    echo "<p style='color:green'>✓ Created log directory</p>";
} else {
    echo "<p style='color:blue'>Log directory exists</p>";
}

if (!file_exists($logFile)) {
    file_put_contents($logFile, '');
    chmod($logFile, 0666);
    echo "<p style='color:green'>✓ Created log file</p>";
} else {
    echo "<p style='color:blue'>Log file exists</p>";
}

// 2. Check Hostinger error logs
echo "<h2>Hostinger PHP Error Log (Last 100 lines)</h2>";
$hostingerLog = '/home/u118669189/.logs/error_log_mimar_xsofty_com';

if (file_exists($hostingerLog) && is_readable($hostingerLog)) {
    $lines = file($hostingerLog);
    $lastLines = array_slice($lines, -100);
    echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc; overflow:auto; max-height:600px;'>";
    foreach ($lastLines as $line) {
        // Highlight errors
        if (stripos($line, 'fatal') !== false || stripos($line, 'error') !== false) {
            echo "<span style='color:red; font-weight:bold;'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color:orange'>Cannot read Hostinger error log at: $hostingerLog</p>";
}

// 3. Test autoloader
echo "<h2>Autoloader Test</h2>";
$autoloadPath = $root . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "<p style='color:green'>✓ Autoloader exists</p>";
    try {
        require_once $autoloadPath;
        echo "<p style='color:green'>✓ Autoloader loaded successfully</p>";

        // Test if our classes are loadable
        if (class_exists('XHRM\PasswordManager\XHRMPasswordManagerPlugin')) {
            echo "<p style='color:green'>✓ Password Manager plugin class found</p>";
        } else {
            echo "<p style='color:red'>✗ Password Manager plugin class NOT found</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Autoloader error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red'>✗ Autoloader not found</p>";
}

echo "<hr>";
echo "<p><strong>Next:</strong> Try accessing <a href='auth/login'>the login page</a> again. If it fails, refresh this page to see the new errors.</p>";
