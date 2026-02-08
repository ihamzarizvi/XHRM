<?php
// test_bootstrap.php - Test the application bootstrap to catch errors

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Application Bootstrap Test</h1>";

try {
    echo "<p>1. Loading autoloader...</p>";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "<p style='color:green'>✓ Autoloader loaded</p>";

    echo "<p>2. Loading index.php bootstrap...</p>";

    // Capture output
    ob_start();
    try {
        require_once __DIR__ . '/index.php';
        $output = ob_get_clean();
        echo "<p style='color:green'>✓ Bootstrap completed</p>";
        echo "<h2>Output:</h2>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    } catch (Throwable $e) {
        ob_end_clean();
        echo "<p style='color:red'>✗ Bootstrap failed</p>";
        echo "<h2>Error Details:</h2>";
        echo "<pre style='background:#ffe6e6; padding:10px; border:1px solid red;'>";
        echo "Type: " . get_class($e) . "\n";
        echo "Message: " . htmlspecialchars($e->getMessage()) . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n\n";
        echo "Stack Trace:\n" . htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
    }

} catch (Throwable $e) {
    echo "<p style='color:red'>✗ Fatal error during bootstrap</p>";
    echo "<pre style='background:#ffe6e6; padding:10px; border:1px solid red;'>";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n" . htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}
