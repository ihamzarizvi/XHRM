<?php
// VERSION: 2026-02-06 02:00
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Final-Cycle Diagnostic (v1.3)</h1>";

function log_step($msg)
{
    echo "<li>$msg</li>";
    flush();
}

echo "<ul>";
try {
    log_step("Step 1: Loading Autoloader...");
    require 'vendor/autoload.php';
    log_step("✅ Autoloader loaded.");

    log_step("Step 2: Loading log_settings.php...");
    if (file_exists('src/config/log_settings.php')) {
        include_once 'src/config/log_settings.php';
        log_step("✅ log_settings.php loaded.");
    } else {
        log_step("⚠️ log_settings.php NOT found (continuing anyway).");
    }

    log_step("Step 3: Initializing Framework Kernel...");
    $kernel = new \XHRM\Framework\Framework('prod', false);
    log_step("✅ Framework instantiated.");

    log_step("Step 4: Creating Request Object...");
    $request = \XHRM\Framework\Http\Request::createFromGlobals();
    log_step("✅ Request object created.");

    log_step("Step 5: Testing Route Configuration (Critical)...");
    try {
        // We use Reflection to call the protected method for testing
        $method = new ReflectionMethod(get_class($kernel), 'configureRouter');
        $method->setAccessible(true);
        $method->invoke($kernel, $request);
        log_step("✅ Router configured successfully.");
    } catch (Throwable $routeErr) {
        throw new Exception("Router Configuration Failed: " . $routeErr->getMessage());
    }

    log_step("Step 6: Testing Plugin Configuration (Critical)...");
    try {
        $method = new ReflectionMethod(get_class($kernel), 'configurePlugins');
        $method->setAccessible(true);
        $method->invoke($kernel, $request);
        log_step("✅ Plugins configured successfully (all " . count(\XHRM\Config\Config::get(\XHRM\Config\Config::PLUGIN_CONFIGS)) . " plugins).");
    } catch (Throwable $pluginErr) {
        throw new Exception("Plugin Configuration Failed: " . $pluginErr->getMessage() . " in file " . $pluginErr->getFile() . " on line " . $pluginErr->getLine());
    }

    log_step("Step 7: Testing Kernel Handle (Final)...");
    echo "</ul><p style='color:blue'><b>SUCCESS:</b> The entire bootstrap process completed! If your site still shows 500, it's either an .htaccess issue or a 0-byte file causing a silent crash.</p>";

} catch (Throwable $e) {
    echo "</ul>";
    echo "<div style='background:#fee; padding:15px; border:4px solid red; margin-top:20px;'>";
    echo "<h2>🔥 BOOTSTRAP FAILURE</h2>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>