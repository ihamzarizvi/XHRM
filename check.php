<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Final Diagnostic</h1>";

try {
    require 'vendor/autoload.php';
    echo "<p style='color:green'>✅ Autoloader loaded.</p>";

    require_once 'lib/confs/Conf.php';
    echo "<p style='color:green'>✅ Conf.php loaded.</p>";

    echo "<h3>1. Permission Check</h3>";
    $paths = [
        'src/log' => 'Log Directory',
        'src/cache' => 'Cache Directory',
        'src/config/proxy' => 'Doctrine Proxies',
        'lib/confs' => 'Config Directory'
    ];

    echo "<ul>";
    foreach ($paths as $path => $name) {
        $fullPath = __DIR__ . '/' . $path;
        if (is_dir($fullPath)) {
            $writable = is_writable($fullPath) ? "<span style='color:green'>Writable</span>" : "<span style='color:red'>NOT Writable</span>";
            echo "<li>✅ $name: Exists ($writable)</li>";
        } else {
            echo "<li>❌ $name: <span style='color:red'>MISSING</span> ($fullPath)</li>";
        }
    }
    echo "</ul>";

    echo "<h3>2. Bootstrapping Framework</h3>";
    echo "<p>Attempting to boot the Framework (this is where the 500 error usually happens)...</p>";

    try {
        $env = 'prod';
        $debug = false;
        $kernel = new \XHRM\Framework\Framework($env, $debug);
        echo "<p style='color:green'>✅ Framework instantiated.</p>";

        // This is the heavy lifting
        echo "<p>Checking Service Container initialization...</p>";
        $container = $kernel->getContainer();
        echo "<p style='color:green'>✅ Service Container initialized.</p>";

        echo "<p style='color:blue'>💡 If you see this, the core framework is OK. The 500 error might be caused by a redirect loop or a specific plugin route.</p>";

    } catch (Throwable $bootError) {
        echo "<div style='background:#fee; padding:15px; border:2px solid red;'>";
        echo "<h3>🔥 BOOT ERROR DETECTED:</h3>";
        echo "<p><b>Message:</b> " . htmlspecialchars($bootError->getMessage()) . "</p>";
        echo "<p><b>File:</b> " . $bootError->getFile() . " on line " . $bootError->getLine() . "</p>";
        echo "<p><b>Trace Hint:</b> " . substr($bootError->getTraceAsString(), 0, 500) . "...</p>";
        echo "</div>";
    }

} catch (Throwable $e) {
    echo "<p style='color:red'>Fatal Page Error: " . $e->getMessage() . "</p>";
}
?>