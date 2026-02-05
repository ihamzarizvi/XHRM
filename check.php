<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Deep Diagnostic</h1>";

try {
    require 'vendor/autoload.php';
    echo "<p style='color:green'>✅ Autoloader loaded.</p>";
    
    use XHRM\Config\Config;
    use XHRM\Framework\Framework;
    use XHRM\Framework\Http\Request;

    echo "<h3>1. Application State</h3>";
    $installed = Config::isInstalled();
    echo "<p>Config::isInstalled(): " . ($installed ? "<b style='color:blue'>TRUE (Application thinks it is installed)</b>" : "FALSE") . "</p>";

    echo "<h3>2. Testing Framework Boot</h3>";
    echo "<p>Attempting to initialize XHRM Framework...</p>";
    
    // This is what web/index.php does
    $env = 'prod';
    $debug = false;
    $kernel = new Framework($env, $debug);
    echo "<p style='color:green'>✅ Framework object created.</p>";

    echo "<h3>3. Testing Request Handling (Dry Run)</h3>";
    $request = Request::createFromGlobals();
    echo "<p style='color:green'>✅ Request object created.</p>";
    
    echo "<p><i>If the page stops here or goes blank, the crash is inside the handleRequest() call.</i></p>";
    
    // We won't call handleRequest yet as it might redirect/exit
    // But we can check for common missing extensions again
    $required_extensions = ['pdo', 'mysqlnd', 'curl', 'gd', 'mbstring', 'xml', 'zip', 'openssl'];
    echo "<h3>4. Extension Check</h3>";
    echo "<ul>";
    foreach ($required_extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<li>✅ $ext</li>";
        } else {
            echo "<li style='color:red'>❌ $ext (MISSING)</li>";
        }
    }
    echo "</ul>";

} catch (Throwable $e) {
    echo "<div style='background:#fee; padding:10px; border:1px solid red;'>";
    echo "<h3>🔥 Fatal Error Caught:</h3>";
    echo "<p><b>Message:</b> " . $e->getMessage() . "</p>";
    echo "<p><b>File:</b> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "</div>";
}
?>
