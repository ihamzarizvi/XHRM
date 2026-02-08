<?php
// fix_final.php - Automated database and cache fix

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

use XHRM\ORM\Doctrine;

echo "<h1>XHRM Final Fix</h1>";

try {
    $em = Doctrine::getEntityManager();
    $conn = $em->getConnection();

    echo "<h3>1. Updating Database...</h3>";

    // Register Module
    $conn->executeStatement("INSERT IGNORE INTO ohrm_module (id, name, status) VALUES (100, 'passwordManager', 1)");
    echo "<p style='color:green'>✓ Module 'passwordManager' registered.</p>";

    // Link Screen to Module and set Action URL
    $conn->executeStatement("UPDATE ohrm_screen SET module_id = 100, action_url = 'viewPasswordManager' WHERE name = 'Password Manager'");
    echo "<p style='color:green'>✓ Screen 'Password Manager' updated.</p>";

    // Fix Menu Item
    $conn->executeStatement("UPDATE ohrm_menu_item SET additional_params = '{\"icon\": \"key\"}' WHERE menu_title = 'Password Manager'");
    echo "<p style='color:green'>✓ Menu item cleaned.</p>";

    echo "<h3>2. Clearing Cache...</h3>";
    $cacheDir = __DIR__ . '/cache';
    if (file_exists($cacheDir)) {
        // Recursive delete or similar could go here, but let's try to just warn for now
        echo "<p>Please manually delete the contents of: <strong>$cacheDir</strong> via your File Manager.</p>";
    }

    echo "<hr>";
    echo "<p style='color:blue; font-weight:bold;'>All database steps complete! Please try the <a href='auth/login'>Login</a> or <a href='index.php'>Dashboard</a> now.</p>";

} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
