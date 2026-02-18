<?php
/**
 * Check recent log entries - DELETE after debugging
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

// Check VaultUserKeyModel on server
$modelFile = $base . '/src/plugins/XHRMPasswordManagerPlugin/Api/Model/VaultUserKeyModel.php';
$contents = file_get_contents($modelFile);
echo "=== VaultUserKeyModel fix status ===\n";
if (strpos($contents, "['getUser', 'getId', true]") !== false) {
    echo "❌ OLD CODE - boolean 'true' still present!\n";
} elseif (strpos($contents, "['getUser', 'getId']") !== false) {
    echo "✅ FIXED - boolean removed correctly\n";
} else {
    echo "⚠️ Unknown state\n";
}

// Show last 10 lines of XHRM log (most recent entries)
$logFile = $base . '/log/xhrm.log';
echo "\n=== Most recent log entries ===\n";
if (file_exists($logFile)) {
    $lines = file($logFile);
    $total = count($lines);
    echo "Total log lines: $total\n";
    echo "Last updated: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n\n";
    // Show last 10 lines
    $recent = array_slice($lines, -10);
    echo implode('', $recent);
} else {
    echo "Log file not found\n";
}

// Quick test: check if user-keys table has data
echo "\n=== ohrm_vault_user_key data ===\n";
$stmt = $pdo->query("SELECT id, user_id, LENGTH(public_key) as pk_len, LENGTH(encrypted_private_key) as epk_len, created_at FROM ohrm_vault_user_key");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "id={$row['id']} user_id={$row['user_id']} pk_len={$row['pk_len']} epk_len={$row['epk_len']} created={$row['created_at']}\n";
}

echo "\n=== Done ===\n</pre>";
