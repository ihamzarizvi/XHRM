<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Config Diagnostic</h1>";

try {
    require 'vendor/autoload.php';
    echo "<p style='color:green'>✅ Autoloader loaded.</p>";
    
    use XHRM\Config\Config;

    $confPath = Config::get(Config::CONF_FILE_PATH);
    echo "<p><b>Configured Conf Path:</b> " . htmlspecialchars($confPath) . "</p>";

    if (file_exists($confPath)) {
        echo "<p style='color:blue'>💡 THE FILE EXISTS ON DISK!</p>";
        echo "<pre>";
        echo htmlspecialchars(file_get_contents($confPath));
        echo "</pre>";
    } else {
        echo "<p style='color:red'>❌ THE FILE DOES NOT EXIST ON DISK.</p>";
    }

    echo "<p>Config::isInstalled() returns: " . (Config::isInstalled() ? "TRUE" : "FALSE") . "</p>";

} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
