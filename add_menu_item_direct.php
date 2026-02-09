<?php
/**
 * Script to register Password Manager module in the database
 * Run this from command line: php add_menu_item_direct.php
 * 
 * This script uses direct PDO connection for local development
 */

header('Content-Type: text/plain');

// Database configuration for local XAMPP
$dbHost = 'localhost';
$dbName = 'xhrm';
$dbUser = 'root';
$dbPass = '';

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "✓ Connected to database: $dbName\n\n";

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

    $selectStatements = [];

    foreach ($statements as $index => $statement) {
        try {
            // Save SELECT statements for later
            if (stripos($statement, 'SELECT') === 0) {
                $selectStatements[] = $statement;
                continue;
            }

            $pdo->exec($statement);
            echo "✓ Statement " . ($index + 1) . " executed successfully\n";
        } catch (PDOException $e) {
            // Ignore duplicate entry errors (module already exists)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "⚠ Statement " . ($index + 1) . " skipped (already exists)\n";
            } else {
                echo "✗ Statement " . ($index + 1) . " failed: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n--- Verification ---\n";

    // Verify the registration
    $stmt = $pdo->query("
        SELECT 
            m.name AS Module, 
            s.name AS Screen, 
            mi.menu_title AS Menu, 
            urs.can_read AS Admin_Access 
        FROM ohrm_module m
        JOIN ohrm_screen s ON s.module_id = m.id
        LEFT JOIN ohrm_menu_item mi ON mi.screen_id = s.id
        LEFT JOIN ohrm_user_role_screen urs ON urs.screen_id = s.id AND urs.user_role_id = 1
        WHERE m.name = 'passwordManager'
    ");

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        echo "⚠ Warning: No results found. Module may not be registered correctly.\n";
    } else {
        echo "✓ Module registered successfully!\n\n";
        foreach ($results as $row) {
            echo "Module: " . $row['Module'] . "\n";
            echo "Screen: " . $row['Screen'] . "\n";
            echo "Menu: " . ($row['Menu'] ?? 'N/A') . "\n";
            echo "Admin Access: " . ($row['Admin_Access'] ?? 'N/A') . "\n";
        }
    }

    echo "\n✓ Registration complete! The Password Manager should now appear in the main menu.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
