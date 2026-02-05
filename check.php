<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Advanced Diagnostic</h1>";

try {
    $autoloader = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoloader)) {
        throw new Exception("Autoloader NOT found at: $autoloader");
    }

    require $autoloader;
    echo "<p style='color:green'>✅ Autoloader loaded successfully.</p>";

    // Testing specific class existence without using 'use' (to avoid parse errors if class missing)
    if (class_exists('XHRM\Config\Config')) {
        echo "<p style='color:green'>✅ XHRM Core Class found.</p>";
        echo "<p>Config::isInstalled(): " . (XHRM\Config\Config::isInstalled() ? "TRUE" : "FALSE") . "</p>";
    } else {
        echo "<p style='color:red'>❌ XHRM Core Class NOT found. Autoloader mappings might be wrong.</p>";
    }

    echo "<h3>System Info</h3>";
    echo "<ul>";
    echo "<li><b>PHP Version:</b> " . phpversion() . "</li>";
    echo "<li><b>Document Root:</b> " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
    echo "<li><b>Current Path:</b> " . __DIR__ . "</li>";
    echo "</ul>";

} catch (Throwable $e) {
    echo "<div style='background:#fee; padding:10px; border:1px solid red;'>";
    echo "<h3>🔥 Fatal Error Caught:</h3>";
    echo "<p><b>Message:</b> " . $e->getMessage() . "</p>";
    echo "<p><b>File:</b> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>
