<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Advanced Diagnostic</h1>";

try {
    require 'vendor/autoload.php';
    echo "<p style='color:green'>✅ Autoloader loaded.</p>";
    
    use XHRM\Config\Config;
    
    echo "<h3>Testing Core Functions</h3>";
    $installed = Config::isInstalled();
    echo "<p>Config::isInstalled(): " . ($installed ? "TRUE" : "FALSE") . "</p>";

    echo "<h3>Checking Directories</h3>";
    $dirs = [
        'src/config',
        'src/log',
        'src/confs',
        'web',
        'installer'
    ];
    
    foreach ($dirs as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (is_dir($path)) {
            $writable = is_writable($path) ? "Writable" : "NOT Writable";
            echo "<p>✅ $dir: Exists ($writable)</p>";
        } else {
            echo "<p style='color:red'>❌ $dir: DOES NOT EXIST</p>";
        }
    }

} catch (Throwable $e) {
    echo "<div style='background:#fee; padding:10px; border:1px solid red;'>";
    echo "<h3>🔥 Fatal Error Caught:</h3>";
    echo "<p><b>Message:</b> " . $e->getMessage() . "</p>";
    echo "<p><b>File:</b> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>