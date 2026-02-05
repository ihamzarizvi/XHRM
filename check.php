<?php
// VERSION: 2026-02-06 02:05
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Page Simulation (v1.4)</h1>";

try {
    log_msg("Step 1: Loading environment...");
    require 'vendor/autoload.php';
    include_once 'src/config/log_settings.php';
    log_msg("✅ Autoloader and logs ready.");

    log_msg("Step 2: Mocking Login Request...");
    // Force the request URI to /auth/login
    $_SERVER['REQUEST_URI'] = '/web/index.php/auth/login';
    $_SERVER['SCRIPT_NAME'] = '/web/index.php';

    $kernel = new \XHRM\Framework\Framework('prod', true); // Enable debug to see more errors
    $request = \XHRM\Framework\Http\Request::createFromGlobals();
    log_msg("✅ Mock Request created for: " . $request->getPathInfo());

    log_msg("Step 3: Handling Request (Actual page logic)...");
    echo "<div style='background:#f0f0f0; padding:10px; border:1px dashed #666;'>";
    echo "<b>--- START OF PAGE OUTPUT ---</b><br>";

    // We wrap this in an output buffer to catch any stray echo's
    ob_start();
    $response = $kernel->handleRequest($request);
    $response->send();
    $output = ob_get_clean();

    echo "<b>--- END OF PAGE OUTPUT ---</b>";
    echo "</div>";

    if (empty($output)) {
        log_msg("⚠️ THE PAGE OUTPUT IS COMPLETELY EMPTY.", 'red');
    } else {
        log_msg("✅ RECEIVED OUTPUT (" . strlen($output) . " bytes).", 'green');
        echo "<h3>First 500 characters of HTML:</h3>";
        echo "<pre style='background:#f9f9f9; border:1px solid #ccc; padding:5px;'>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
    }

} catch (Throwable $e) {
    if (ob_get_level() > 0)
        ob_end_clean();
    echo "<div style='background:#fee; padding:15px; border:4px solid red; margin-top:20px;'>";
    echo "<h2>🔥 PAGE CRASH DETECTED</h2>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "<h3>Trace:</h3><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

function log_msg($msg, $color = 'black')
{
    echo "<p style='color:$color'>$msg</p>";
}
?>