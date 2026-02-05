<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Config Diagnostic</h1>";

try {
    $autoloader = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoloader)) {
        echo "<p style='color:red'>❌ Autoloader NOT found at: " . htmlspecialchars($autoloader) . "</p>";
        exit;
    }

    require $autoloader;
    echo "<p style='color:green'>✅ Autoloader loaded.</p>";

    // Using full namespace to avoid 'use' placement issues
    $confPath = \XHRM\Config\Config::get(\XHRM\Config\Config::CONF_FILE_PATH);
    echo "<p><b>Configured Conf Path:</b> " . htmlspecialchars($confPath) . "</p>";

    if (file_exists($confPath)) {
        echo "<p style='color:blue'>💡 THE FILE EXISTS ON DISK!</p>";
        echo "<pre style='background:#eee; padding:10px;'>";
        echo htmlspecialchars(file_get_contents($confPath));
        echo "</pre>";
    } else {
        echo "<p style='color:red'>❌ THE FILE DOES NOT EXIST ON DISK.</p>";
    }

    $isInstalled = \XHRM\Config\Config::isInstalled();
    echo "<p>Config::isInstalled() returns: " . ($isInstalled ? "<b style='color:blue'>TRUE</b>" : "FALSE") . "</p>";

} catch (Throwable $e) {
    echo "<div style='background:#fee; padding:10px; border:1px solid red;'>";
    echo "<h3>🔥 Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "</div>";
}
?>