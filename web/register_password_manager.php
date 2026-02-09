<?php
/**
 * Web-accessible script to register Password Manager module
 * Access via: https://mimar.xsofty.com/register_password_manager.php
 * 
 * DELETE THIS FILE AFTER RUNNING!
 */

require_once __DIR__ . '/../vendor/autoload.php';

use XHRM\Config\Config;
use XHRM\ORM\Doctrine;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Password Manager Registration</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #1e293b;
            color: #e2e8f0;
        }

        .success {
            color: #10b981;
        }

        .error {
            color: #ef4444;
        }

        .warning {
            color: #f59e0b;
        }

        pre {
            background: #0f172a;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <h1>Password Manager Module Registration</h1>
    <pre>
<?php

try {
    // Get Doctrine Entity Manager
    $em = Doctrine::getEntityManager();
    $conn = $em->getConnection();

    echo "<span class='success'>✓ Connected to database: " . $conn->getDatabase() . "</span>\n\n";

    // SQL statements to execute
    $statements = [
        // 1. Insert Module
        "INSERT INTO `ohrm_module` (`name`, `status`)
         SELECT 'passwordManager', 1
         WHERE NOT EXISTS (SELECT * FROM `ohrm_module` WHERE `name` = 'passwordManager')",

        // 2. Insert Screen
        "INSERT INTO `ohrm_screen` (`name`, `module_id`, `action_url`)
         SELECT 'Password Manager', 
                (SELECT `id` FROM `ohrm_module` WHERE `name` = 'passwordManager'), 
                'viewPasswordManager'
         WHERE NOT EXISTS (SELECT * FROM `ohrm_screen` WHERE `name` = 'Password Manager')",

        // 3. Insert Menu Item
        "INSERT INTO `ohrm_menu_item` (`menu_title`, `screen_id`, `parent_id`, `level`, `order_hint`, `url_extras`, `status`)
         SELECT 'Password Manager', 
                (SELECT `id` FROM `ohrm_screen` WHERE `name` = 'Password Manager'), 
                NULL, 1, 1100, NULL, 1
         WHERE NOT EXISTS (SELECT * FROM `ohrm_menu_item` WHERE `menu_title` = 'Password Manager')",

        // 4. Grant Admin Permissions
        "INSERT INTO `ohrm_user_role_screen` (`user_role_id`, `screen_id`, `can_read`, `can_create`, `can_update`, `can_delete`)
         SELECT 1, 
                (SELECT `id` FROM `ohrm_screen` WHERE `name` = 'Password Manager'), 
                1, 1, 1, 1
         WHERE NOT EXISTS (
             SELECT * FROM `ohrm_user_role_screen` 
             WHERE `user_role_id` = 1 
             AND `screen_id` = (SELECT `id` FROM `ohrm_screen` WHERE `name` = 'Password Manager')
         )"
    ];

    echo "Executing " . count($statements) . " SQL statements...\n\n";

    foreach ($statements as $index => $statement) {
        try {
            $result = $conn->executeStatement($statement);
            $affected = $result;

            if ($affected > 0) {
                echo "<span class='success'>✓ Statement " . ($index + 1) . " executed successfully ($affected rows affected)</span>\n";
            } else {
                echo "<span class='warning'>⚠ Statement " . ($index + 1) . " skipped (already exists)</span>\n";
            }
        } catch (Exception $e) {
            echo "<span class='error'>✗ Statement " . ($index + 1) . " failed: " . $e->getMessage() . "</span>\n";
        }
    }

    echo "\n<span class='success'>--- Verification ---</span>\n";

    // Verify the registration
    $result = $conn->fetchAllAssociative("
        SELECT 
            m.id as module_id,
            m.name AS module_name, 
            s.id as screen_id,
            s.name AS screen_name, 
            s.action_url,
            mi.id as menu_id,
            mi.menu_title,
            mi.order_hint,
            urs.can_read,
            urs.can_create,
            urs.can_update,
            urs.can_delete
        FROM ohrm_module m
        JOIN ohrm_screen s ON s.module_id = m.id
        LEFT JOIN ohrm_menu_item mi ON mi.screen_id = s.id
        LEFT JOIN ohrm_user_role_screen urs ON urs.screen_id = s.id AND urs.user_role_id = 1
        WHERE m.name = 'passwordManager'
    ");

    if (empty($result)) {
        echo "<span class='error'>⚠ Warning: No results found. Module may not be registered correctly.</span>\n";
    } else {
        echo "<span class='success'>✓ Module registered successfully!</span>\n\n";
        foreach ($result as $row) {
            echo "Module ID: " . $row['module_id'] . "\n";
            echo "Module Name: " . $row['module_name'] . "\n";
            echo "Screen ID: " . $row['screen_id'] . "\n";
            echo "Screen Name: " . $row['screen_name'] . "\n";
            echo "Action URL: " . $row['action_url'] . "\n";
            echo "Menu ID: " . ($row['menu_id'] ?? 'N/A') . "\n";
            echo "Menu Title: " . ($row['menu_title'] ?? 'N/A') . "\n";
            echo "Order Hint: " . ($row['order_hint'] ?? 'N/A') . "\n";
            echo "Admin Permissions: Read=" . ($row['can_read'] ?? '0') .
                ", Create=" . ($row['can_create'] ?? '0') .
                ", Update=" . ($row['can_update'] ?? '0') .
                ", Delete=" . ($row['can_delete'] ?? '0') . "\n";
        }
    }

    echo "\n<span class='success'>✓ Registration complete!</span>\n";
    echo "\n<span class='warning'>⚠ IMPORTANT: Delete this file (register_password_manager.php) for security!</span>\n";
    echo "\nThe Password Manager should now appear in your main navigation menu.\n";
    echo "You may need to clear your browser cache or log out and log back in to see it.\n";

} catch (Exception $e) {
    echo "<span class='error'>Error: " . $e->getMessage() . "</span>\n";
    echo "<span class='error'>Stack trace:\n" . $e->getTraceAsString() . "</span>\n";
}
?>
</pre>
</body>

</html>