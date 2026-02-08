<?php
// web/debug_xhrm.php

// 1. Enable full error reporting to catch startup issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>XHRM Debug Utility</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// 2. Check Document Root and Paths
$root = dirname(__DIR__);
echo "<p>Project Root: " . $root . "</p>";

// 3. Autoloader verification
$autoloadPath = $root . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("<h2 style='color:red'>CRITICAL: vendor/autoload.php not found. Run 'composer install'.</h2>");
}

echo "<p>Autoloader found. Loading...</p>";
require_once $autoloadPath;
echo "<p>Autoloader loaded.</p>";

// 4. Check for Plugin Classes
echo "<h2>Class Availability Check</h2>";
$classesToCheck = [
    'XHRM\Core\Controller\AbstractVueController',
    'XHRM\PasswordManager\XHRMPasswordManagerPlugin',
    'XHRM\PasswordManager\Api\VaultItemAPI',
    'XHRM\PasswordManager\Entity\VaultItem'
];

echo "<ul>";
foreach ($classesToCheck as $class) {
    if (class_exists($class)) {
        echo "<li><span style='color:green'>Found:</span> $class</li>";
    } else {
        echo "<li><span style='color:red'>MISSING:</span> $class (Check composer.json autoload/psr-4 mapping and dump-autoload)</li>";
    }
}
echo "</ul>";

// 5. Check Log Permissions and Content
echo "<h2>Application Log (Last 50 Lines)</h2>";
$logPath = $root . '/log/ohrm.log';

if (!file_exists($logPath)) {
    echo "<p style='color:orange'>Log file not found at: $logPath</p>";
} elseif (!is_readable($logPath)) {
    echo "<p style='color:red'>Log file exists but is NOT readable. Check permissions.</p>";
} else {
    echo "<p>Reading log file: $logPath (" . filesize($logPath) . " bytes)</p>";
    $lines = file($logPath);
    if ($lines === false) {
        echo "<p style='color:red'>Failed to read log file.</p>";
    } else {
        $lastLines = array_slice($lines, -50);
        echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc; overflow:auto;'>";
        foreach ($lastLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    }
}

// 6. Test Environment Variables (Optional, be careful with secrets)
echo "<h2>Environment Basic Check</h2>";
if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL')) {
    echo "<p style='color:green'>DATABASE_URL is set.</p>";
} else {
    echo "<p style='color:orange'>DATABASE_URL is NOT detected (might be in .env file handled by Symfony Dotenv).</p>";
}

echo "<p>Done.</p>";
