<?php
/**
 * Diagnostic: Check vault_item table columns and find 500 error
 * DELETE THIS FILE after debugging!
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>\n";

$base = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm';
require_once $base . '/lib/confs/Conf.php';
$conf = new Conf();

$pdo = new PDO(
    'mysql:host=' . $conf->getDbHost() . ';dbname=' . $conf->getDbName() . ';charset=utf8',
    $conf->getDbUser(),
    $conf->getDbPass(),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Check ohrm_vault_item columns
echo "=== ohrm_vault_item columns ===\n";
$stmt = $pdo->query("DESCRIBE ohrm_vault_item");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}

// Check ohrm_vault_user_key columns
echo "\n=== ohrm_vault_user_key columns ===\n";
$stmt = $pdo->query("DESCRIBE ohrm_vault_user_key");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}

// Check ohrm_vault_share columns
echo "\n=== ohrm_vault_share columns ===\n";
$stmt = $pdo->query("DESCRIBE ohrm_vault_share");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}

// Check PHP error log
echo "\n=== PHP error_log ===\n";
$errorLog = ini_get('error_log');
echo "Path: $errorLog\n";
if ($errorLog && file_exists($errorLog)) {
    $lines = file($errorLog);
    $recent = array_slice($lines, -30);
    foreach ($recent as $line) {
        if (stripos($line, 'vault') !== false || stripos($line, 'password') !== false || stripos($line, 'error') !== false) {
            echo $line;
        }
    }
}

// Check XHRM log
$xhrmLog = $base . '/log/xhrm.log';
if (file_exists($xhrmLog)) {
    echo "\n=== XHRM log (last 20 lines) ===\n";
    $lines = file($xhrmLog);
    echo implode('', array_slice($lines, -20));
}

echo "\n=== Done ===\n</pre>";
