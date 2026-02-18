<?php
/**
 * ONE-TIME: Clear all test vault data so fresh items can be created
 * DELETE THIS FILE immediately after running!
 */
$base = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm';
require_once $base . '/lib/confs/Conf.php';
$conf = new Conf();

$pdo = new PDO(
    'mysql:host=' . $conf->getDbHost() . ';dbname=' . $conf->getDbName() . ';charset=utf8',
    $conf->getDbUser(),
    $conf->getDbPass(),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "<pre>\n";

// Show current state
$itemCount = $pdo->query("SELECT COUNT(*) FROM ohrm_vault_item")->fetchColumn();
$keyCount = $pdo->query("SELECT COUNT(*) FROM ohrm_vault_user_key")->fetchColumn();
echo "BEFORE:\n";
echo "  ohrm_vault_item: $itemCount rows\n";
echo "  ohrm_vault_user_key: $keyCount rows\n\n";

// Show item names so user knows what will be deleted
$items = $pdo->query("SELECT id, name FROM ohrm_vault_item")->fetchAll(PDO::FETCH_ASSOC);
echo "Items to be deleted:\n";
foreach ($items as $item) {
    echo "  - [{$item['id']}] {$item['name']}\n";
}
echo "\n";

// Delete shares first (foreign key)
$pdo->exec("DELETE FROM ohrm_vault_share");
// Delete items
$deletedItems = $pdo->exec("DELETE FROM ohrm_vault_item");
// Delete user keys
$deletedKeys = $pdo->exec("DELETE FROM ohrm_vault_user_key");

echo "Deleted $deletedItems vault item(s)\n";
echo "Deleted $deletedKeys user key(s)\n\n";

// Verify
$itemCount = $pdo->query("SELECT COUNT(*) FROM ohrm_vault_item")->fetchColumn();
$keyCount = $pdo->query("SELECT COUNT(*) FROM ohrm_vault_user_key")->fetchColumn();
echo "AFTER:\n";
echo "  ohrm_vault_item: $itemCount rows\n";
echo "  ohrm_vault_user_key: $keyCount rows\n\n";

if ($itemCount == 0 && $keyCount == 0) {
    echo "✅ All test data cleared!\n";
    echo "Now:\n";
    echo "  1. Go to Password Manager\n";
    echo "  2. Enter your master password to unlock\n";
    echo "  3. A fresh key pair will be generated\n";
    echo "  4. Add new vault items - they will encrypt/decrypt correctly\n";
    echo "\n⚠️  DELETE this file from the server immediately!\n";
} else {
    echo "❌ Some data remains - check for errors\n";
}

echo "</pre>";
