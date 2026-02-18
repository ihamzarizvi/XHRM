<?php
/**
 * Diagnostic: Check DB tables for Password Manager
 * Visit: https://mimar.xsofty.com/web/diag_db.php
 * DELETE THIS FILE after debugging!
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>\n";

// Load XHRM's own config
$confFile = __DIR__ . '/../lib/confs/Conf.php';
if (!file_exists($confFile)) {
    echo "ERROR: Config not found at $confFile\n";
    // Try to find it
    $found = glob(__DIR__ . '/../lib/**/*.php');
    echo "Files in lib/: " . implode("\n", $found) . "\n";
    exit;
}

require_once $confFile;

// Connect using XHRM constants
try {
    $pdo = new PDO(
        'mysql:host=' . XHRM_CONF_DBHOST . ';dbname=' . XHRM_CONF_DBNAME . ';charset=utf8',
        XHRM_CONF_DBUSER,
        XHRM_CONF_DBPASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ DB Connected to: " . XHRM_CONF_DBNAME . "\n\n";
} catch (Exception $e) {
    echo "❌ DB Connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Check all Password Manager tables
$tables = [
    'ohrm_vault_item',
    'ohrm_vault_category',
    'ohrm_vault_share',
    'ohrm_vault_user_key',
];

echo "=== Password Manager Tables ===\n";
foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($stmt->fetch()) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "✅ $table EXISTS (rows: $count)\n";
    } else {
        echo "❌ $table MISSING - migration not run!\n";
    }
}

// Check API permissions
echo "\n=== API Permissions for Password Manager ===\n";
$stmt = $pdo->query("
    SELECT ap.id, ap.api_name, ap.module_id, ap.data_group_id, dg.name as dg_name
    FROM ohrm_api_permission ap
    LEFT JOIN ohrm_data_group dg ON ap.data_group_id = dg.id
    WHERE ap.api_name LIKE 'XHRM%PasswordManager%'
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($rows) {
    foreach ($rows as $row) {
        $dgOk = $row['dg_name'] ? "✅" : "❌ NO DATA GROUP";
        echo "$dgOk {$row['api_name']} -> dg: {$row['dg_name']}\n";
    }
} else {
    echo "❌ No API permissions found!\n";
}

// Check data group permissions
echo "\n=== Data Group Permissions (ohrm_user_role_data_group) ===\n";
$stmt = $pdo->query("
    SELECT urd.id, ur.name as role, dg.name as data_group,
           urd.can_read, urd.can_create, urd.can_update, urd.can_delete, urd.self
    FROM ohrm_user_role_data_group urd
    JOIN ohrm_user_role ur ON urd.user_role_id = ur.id
    JOIN ohrm_data_group dg ON urd.data_group_id = dg.id
    WHERE dg.name = 'password_manager'
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($rows) {
    foreach ($rows as $row) {
        echo "✅ Role={$row['role']} self={$row['self']} R={$row['can_read']} C={$row['can_create']} U={$row['can_update']} D={$row['can_delete']}\n";
    }
} else {
    echo "❌ No data group permissions found for password_manager!\n";
}

echo "\n=== Done ===\n";
echo "</pre>";
