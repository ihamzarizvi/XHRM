<?php
/**
 * Diagnostic: Simulate VaultUserKeyAPI call to find 500 error
 * DELETE THIS FILE after debugging!
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>\n";

$base = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm';

// Load Conf and connect to DB
require_once $base . '/lib/confs/Conf.php';
$conf = new Conf();

$pdo = new PDO(
    'mysql:host=' . $conf->getDbHost() . ';dbname=' . $conf->getDbName() . ';charset=utf8',
    $conf->getDbUser(),
    $conf->getDbPass(),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "✅ DB Connected\n\n";

// Check ohrm_vault_user_key structure
echo "=== ohrm_vault_user_key columns ===\n";
$stmt = $pdo->query("DESCRIBE ohrm_vault_user_key");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']}) null={$row['Null']} default={$row['Default']}\n";
}

// Check the actual data
echo "\n=== ohrm_vault_user_key data ===\n";
$stmt = $pdo->query("SELECT id, user_id, LENGTH(public_key) as pub_len, LENGTH(encrypted_private_key) as priv_len FROM ohrm_vault_user_key LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  id={$row['id']} user_id={$row['user_id']} pub_key_len={$row['pub_len']} priv_key_len={$row['priv_len']}\n";
}

// Check VaultUserKeyModel to see what fields it maps
echo "\n=== VaultUserKeyModel.php ===\n";
$modelFile = $base . '/src/plugins/XHRMPasswordManagerPlugin/Api/Model/VaultUserKeyModel.php';
if (file_exists($modelFile)) {
    echo file_get_contents($modelFile);
} else {
    echo "NOT FOUND at $modelFile\n";
}

// Check PHP error log
echo "\n=== PHP error_log setting ===\n";
echo "error_log = " . ini_get('error_log') . "\n";

// Check recent app log
$logFiles = [
    $base . '/log/xhrm.log',
    $base . '/var/log/prod.log',
    $base . '/log/error.log',
];
foreach ($logFiles as $log) {
    if (file_exists($log)) {
        echo "\n=== Last 20 lines of $log ===\n";
        $lines = file($log);
        echo implode('', array_slice($lines, -20));
        break;
    } else {
        echo "No log at: $log\n";
    }
}

echo "\n=== Done ===\n</pre>";
