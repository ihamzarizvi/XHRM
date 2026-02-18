<?php
/**
 * ONE-TIME: Delete corrupted vault user key so fresh one is generated
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

// Show current keys
$rows = $pdo->query("SELECT id, user_id, created_at FROM ohrm_vault_user_key")->fetchAll(PDO::FETCH_ASSOC);
echo "Keys BEFORE delete:\n";
foreach ($rows as $r) {
    echo "  id={$r['id']} user_id={$r['user_id']} created={$r['created_at']}\n";
}

// Delete ALL corrupted keys
$deleted = $pdo->exec("DELETE FROM ohrm_vault_user_key");
echo "\nDeleted $deleted key(s).\n";

// Verify
$remaining = $pdo->query("SELECT COUNT(*) FROM ohrm_vault_user_key")->fetchColumn();
echo "Keys AFTER delete: $remaining\n";

if ($remaining == 0) {
    echo "\n✅ Done! Now unlock the vault with your master password to generate a fresh key pair.\n";
    echo "⚠️  DELETE this file from the server immediately!\n";
} else {
    echo "\n❌ Delete failed - keys still present!\n";
}

echo "</pre>";
