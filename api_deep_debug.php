<?php
// api_deep_debug.php
// Access via https://mimar.xsofty.com/api_deep_debug.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Deep API Debugger (v2.0)</h1>";

require_once __DIR__ . '/vendor/autoload.php';

use XHRM\Framework\Framework;
use XHRM\Framework\Http\Request;
use XHRM\PasswordManager\Api\VaultItemAPI;
use XHRM\PasswordManager\Entity\VaultItem;
use XHRM\Framework\ServiceContainer;
use XHRM\Framework\Services;

try {
    echo "<p>Initializing Framework Kernel...</p>";
    $kernel = new Framework('prod', false);
    $request = Request::createFromGlobals();

    // Use reflection to call protected configuration methods
    echo "<p>Configuring Router and Plugins...</p>";
    $refl = new \ReflectionClass($kernel);

    $confRouter = $refl->getMethod('configureRouter');
    $confRouter->setAccessible(true);
    $confRouter->invoke($kernel, $request);

    $confPlugins = $refl->getMethod('configurePlugins');
    $confPlugins->setAccessible(true);
    $confPlugins->invoke($kernel, $request);

    echo "<p>Accessing Container...</p>";
    $container = ServiceContainer::getContainer();
    echo "<p>Container accessed.</p>";

    echo "<h2>Doctrine Connection Test</h2>";
    try {
        echo "<p>Attempting to get Entity Manager...</p>";
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(Services::DOCTRINE);
        echo "<p style='color:green'>Entity Manager retrieved!</p>";

        echo "<p>Testing connection (SELECT 1)...</p>";
        $em->getConnection()->connect();
        $stmt = $em->getConnection()->executeQuery('SELECT 1');
        $stmt->fetchAllAssociative();
        echo "<p style='color:green'>Database connection successful!</p>";

        echo "<h2>Entity Mapping Test</h2>";
        echo "<p>Checking VaultItem mapping...</p>";
        $cmf = $em->getMetadataFactory();
        $metadata = $cmf->getMetadataFor(VaultItem::class);
        echo "<p style='color:green'>Mapping for VaultItem is valid!</p>";
        echo "<h4>Fields:</h4><pre>";
        print_r($metadata->getFieldNames());
        echo "</pre>";

    } catch (\Throwable $e) {
        echo "<div style='background:#fee; padding:15px; border:1px solid red;'>";
        echo "<h3>DOCTRINE CRASH:</h3>";
        echo "<p><b>Message:</b> " . $e->getMessage() . "</p>";
        echo "<p><b>File:</b> " . $e->getFile() . " (Line " . $e->getLine() . ")</p>";
        echo "<h4>Stack Trace:</h4>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }

    echo "<h2>API Class Check</h2>";
    try {
        echo "<p>Attempting to instantiate VaultItemAPI...</p>";
        // Correctly wrap the request for API V2
        $apiV2Request = new \XHRM\Core\Api\V2\Request($request);
        $api = new VaultItemAPI($apiV2Request);
        echo "<p style='color:green'>Instantiated VaultItemAPI!</p>";

        echo "<p>Checking Service Dependency...</p>";
        $service = $api->getPasswordManagerService();
        echo "<p style='color:green'>Service obtained within API class!</p>";

        echo "<p>Checking User Identity...</p>";
        $userRoleManager = $container->get(Services::USER_ROLE_MANAGER);
        $user = $userRoleManager->getUser();
        if ($user) {
            echo "<p style='color:green'>Logged in as: " . $user->getUserName() . " (ID: " . $user->getId() . ")</p>";
        } else {
            echo "<p style='color:orange'>No user logged in (expected in debug script if no session).</p>";
        }

    } catch (\Throwable $e) {
        echo "<div style='background:#fee; padding:15px; border:1px solid red;'>";
        echo "<h3>API INSTANTIATION CRASH:</h3>";
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
