<?php
/**
 * Diagnostic: Check DB tables for Password Manager
 * DELETE THIS FILE after debugging!
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>\n";

$base = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm';

// Load the Conf class (it's in lib/confs/Conf.php)
$confFile = $base . '/lib/confs/Conf.php';
require_once $confFile;

$conf = new Conf();

// Connect using Conf methods
try {
    $pdo = new PDO(
        'mysql:host=' . $conf->getDbHost() . ';dbname=' . $conf->getDbName() . ';charset=utf8',
        $conf->getDbUser(),
        $conf->getDbPass(),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ DB Connected: " . $conf->getDbName() . "\n\n";
} catch (Exception $e) {
    echo "❌ DB failed: " . $e->getMessage() . "\n";
    exit;
}

// Check Password Manager tables
$tables = ['ohrm_vault_item', 'ohrm_vault_category', 'ohrm_vault_share', 'ohrm_vault_user_key'];
echo "=== Password Manager Tables ===\n";
foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($stmt->fetch()) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "✅ $table (rows: $count)\n";
    } else {
        echo "❌ $table MISSING — migration not run!\n";
    }
}

// Check API permissions
echo "\n=== API Permissions ===\n";
$stmt = $pdo->query("
    SELECT ap.api_name, ap.data_group_id, dg.name as dg_name
    FROM ohrm_api_permission ap
    LEFT JOIN ohrm_data_group dg ON ap.data_group_id = dg.id
    WHERE ap.api_name LIKE '%PasswordManager%'
");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $ok = $row['dg_name'] ? '✅' : '❌ NULL dg';
    echo "$ok {$row['api_name']} -> dg={$row['dg_name']}\n";
}

// Check data group permissions
echo "\n=== Role Permissions ===\n";
$stmt = $pdo->query("
    SELECT ur.name as role, dg.name as dg, urd.can_read, urd.can_create, urd.self
    FROM ohrm_user_role_data_group urd
    JOIN ohrm_user_role ur ON urd.user_role_id = ur.id
    JOIN ohrm_data_group dg ON urd.data_group_id = dg.id
    WHERE dg.name = 'password_manager'
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($rows) {
    foreach ($rows as $row) {
        echo "✅ role={$row['role']} self={$row['self']} read={$row['can_read']} create={$row['can_create']}\n";
    }
} else {
    echo "❌ No role permissions for password_manager!\n";
}

echo "\n=== Done ===\n</pre>";
