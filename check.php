<?php
// VERSION: 2026-02-06 01:55
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Hyper-Diagnostic (v1.2)</h1>";
echo "<p><b>Server Time:</b> " . date('Y-m-d H:i:s') . "</p>";

function log_debug($msg, $color = 'black')
{
    echo "<p style='color:$color'>$msg</p>";
}

try {
    log_debug("Step 1: Loading Autoloader...");
    require 'vendor/autoload.php';
    log_debug("✅ Autoloader loaded.", 'green');

    log_debug("Step 2: Loading Conf.php...");
    require_once 'lib/confs/Conf.php';
    log_debug("✅ Conf.php loaded.", 'green');

    log_debug("Step 3: Checking XHRM Classes...");
    if (class_exists('XHRM\Framework\Framework')) {
        log_debug("✅ Framework class exists.", 'green');
    } else {
        throw new Exception("CRITICAL: Framework class NOT found!");
    }

    log_debug("Step 4: Initializing Framework (Kernel)...");
    try {
        $kernel = new \XHRM\Framework\Framework('prod', false);
        log_debug("✅ Framework instantiated.", 'green');
    } catch (Throwable $ke) {
        log_debug("❌ FRAMEWORK INSTANTIATION FAILED: " . $ke->getMessage(), 'red');
        echo "<pre>" . $ke->getTraceAsString() . "</pre>";
        exit;
    }

    log_debug("Step 5: Accessing Container...");
    try {
        $container = \XHRM\Framework\ServiceContainer::getContainer();
        log_debug("✅ ServiceContainer accessed.", 'green');
    } catch (Throwable $ce) {
        log_debug("❌ CONTAINER ACCESS FAILED: " . $ce->getMessage(), 'red');
        exit;
    }

    log_debug("Step 6: Testing Logger (Common crash point)...");
    try {
        $logger = $container->get(\XHRM\Framework\Services::LOGGER);
        log_debug("✅ Logger service retrieved.", 'green');
    } catch (Throwable $le) {
        log_debug("❌ LOGGER INITIALIZATION FAILED: " . $le->getMessage(), 'red');
        log_debug("<i>Check if /src/log folder is writable!</i>", 'blue');
    }

    log_debug("<h3>Final Results</h3>");
    log_debug("If you reached here, the core system can boot! The 500 error is likely inside a specific page or route.", 'blue');

} catch (Throwable $e) {
    echo "<div style='background:#fee; padding:15px; border:4px solid red; margin-top:20px;'>";
    echo "<h2>🔥 CRITICAL SYSTEM ERROR</h2>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>