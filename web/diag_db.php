<?php
/**
 * Diagnostic: Check DB tables for Password Manager
 * DELETE THIS FILE after debugging!
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>\n";

// Find the conf file using the real server path
$base = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm';
$confFile = $base . '/lib/confs/Conf.php';

echo "Looking for config at: $confFile\n";

if (!file_exists($confFile)) {
    echo "Not found. Searching...\n";
    // Search for any Conf.php
    $found = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base . '/lib')) as $f) {
        if ($f->getFilename() === 'Conf.php') {
            $found[] = $f->getPathname();
        }
    }
    echo "Found Conf.php files:\n" . implode("\n", $found) . "\n";

    if (empty($found)) {
        echo "\nlib/ directory contents:\n";
        foreach (scandir($base . '/lib') as $item) {
            echo "  $item\n";
        }
    }
    exit;
}

require_once $confFile;
echo "✅ Config loaded.\n\n";

// Connect
try {
    $pdo = new PDO(
        'mysql:host=' . XHRM_CONF_DBHOST . ';dbname=' . XHRM_CONF_DBNAME . ';charset=utf8',
        XHRM_CONF_DBUSER,
        XHRM_CONF_DBPASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ DB Connected: " . XHRM_CONF_DBNAME . "\n\n";
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
    echo "$ok {$row['api_name']} -> {$row['dg_name']}\n";
}

// Check data group permissions
echo "\n=== Role Permissions (ohrm_user_role_data_group) ===\n";
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
    echo "❌ No role permissions for password_manager data group!\n";
}

echo "\n=== Done ===\n</pre>";
