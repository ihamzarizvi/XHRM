<?php
/**
 * Script to register Password Manager module in the database
 * Run this from command line: php add_menu_item.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use XHRM\Config\Config;
use XHRM\ORM\Doctrine;

header('Content-Type: text/plain');

try {
    // Check if application is installed
    if (!Config::isInstalled()) {
        die("Error: Application not installed. Please run the installer first.\n");
    }

    // Get Doctrine Entity Manager
    $em = Doctrine::getEntityManager();
    $conn = $em->getConnection();

    echo "Connected to database: " . $conn->getDatabase() . "\n\n";

    // Read and execute the SQL script
    $sqlFile = __DIR__ . '/register_password_manager_full.sql';

    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);

    // Split by semicolons and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($stmt) {
            // Filter out empty statements and comments
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    echo "Executing " . count($statements) . " SQL statements...\n\n";

    foreach ($statements as $index => $statement) {
        try {
            // Skip SELECT statements for now, we'll run them at the end
            if (stripos($statement, 'SELECT') === 0) {
                continue;
            }

            $conn->executeStatement($statement);
            echo "✓ Statement " . ($index + 1) . " executed successfully\n";
        } catch (Exception $e) {
            echo "✗ Statement " . ($index + 1) . " failed: " . $e->getMessage() . "\n";
        }
    }

    echo "\n--- Verification ---\n";

    // Verify the registration
    $result = $conn->fetchAllAssociative("
        SELECT 
            m.name AS Module, 
            s.name AS Screen, 
            mi.menu_title AS Menu, 
            urs.can_read AS Admin_Access 
        FROM ohrm_module m
        JOIN ohrm_screen s ON s.module_id = m.id
        LEFT JOIN ohrm_menu_item mi ON mi.screen_id = s.id
        LEFT JOIN ohrm_user_role_screen urs ON urs.screen_id = s.id
        WHERE m.name = 'passwordManager'
    ");

    if (empty($result)) {
        echo "⚠ Warning: No results found. Module may not be registered correctly.\n";
    } else {
        echo "✓ Module registered successfully!\n\n";
        foreach ($result as $row) {
            echo "Module: " . $row['Module'] . "\n";
            echo "Screen: " . $row['Screen'] . "\n";
            echo "Menu: " . ($row['Menu'] ?? 'N/A') . "\n";
            echo "Admin Access: " . ($row['Admin_Access'] ?? 'N/A') . "\n";
        }
    }

    echo "\n✓ Registration complete!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
