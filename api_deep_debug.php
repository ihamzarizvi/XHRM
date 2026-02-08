<?php
// api_deep_debug.php
// Access via https://mimar.xsofty.com/api_deep_debug.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Deep API Debugger</h1>";

require_once __DIR__ . '/vendor/autoload.php';
use XHRM\Config\Config;
use XHRM\Framework\Framework;
use XHRM\Framework\Http\Request;
use XHRM\PasswordManager\Api\VaultItemAPI;
use XHRM\PasswordManager\Entity\VaultItem;

try {
    echo "<p>Starting Framework Kernel...</p>";
    $kernel = new Framework('prod', false);
    $request = Request::createFromGlobals();

    // We need to simulate a logged-in user or at least boot the container
    echo "<p>Booting Kernel...</p>";
    $kernel->boot();
    $container = $kernel->getContainer();
    echo "<p>Kernel Booted Successfully!</p>";

    echo "<h2>Service Check</h2>";
    $serviceId = 'XHRM\PasswordManager\Service\PasswordManagerService';
    if ($container->has($serviceId)) {
        echo "<p style='color:green'>Found PasswordManagerService!</p>";
    } else {
        echo "<p style='color:red'>MISSING PasswordManagerService in Container!</p>";
        // Check if the plugin class is found
        if (class_exists('XHRM\PasswordManager\XHRMPasswordManagerPlugin')) {
            echo "<p>Plugin class exists, but service might not be registered.</p>";
        }
    }

    echo "<h2>Logic Execution Test</h2>";
    try {
        echo "<p>Attempting to instantiate VaultItemAPI...</p>";
        $api = new VaultItemAPI();
        $api->setContainer($container);
        echo "<p style='color:green'>Instantiated VaultItemAPI!</p>";

        echo "<p>Attempting to fetch items (getAll)...</p>";
        // Simulate a user if possible or just try the logic
        $items = $container->get($serviceId)->getVaultItems(1); // Try with user ID 1
        echo "<p style='color:green'>Successfully fetched " . count($items) . " items via Service!</p>";

    } catch (\Throwable $e) {
        echo "<div style='background:#fee; padding:15px; border:1px solid red;'>";
        echo "<h3>CRASH DETECTED:</h3>";
        echo "<p><b>Message:</b> " . $e->getMessage() . "</p>";
        echo "<p><b>File:</b> " . $e->getFile() . " (Line " . $e->getLine() . ")</p>";
        echo "<h4>Stack Trace:</h4>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }

} catch (\Throwable $e) {
    echo "<h2 style='color:red'>FATAL STARTUP ERROR:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p>Debug finished.</p>";
