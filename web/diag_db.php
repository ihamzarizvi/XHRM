<?php
/**
 * Diagnostic script - DELETE after debugging
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

// 1. Check VaultUserKeyModel fix
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

// 2. Check deployed frontend bundle version
$appJs = glob($base . '/web/dist/js/app.*.js');
if (empty($appJs)) {
    $appJs = glob($base . '/web/dist/js/app.js');
}
echo "\n=== Frontend bundle ===\n";
if (!empty($appJs)) {
    $f = $appJs[0];
    echo "File: " . basename($f) . "\n";
    echo "Size: " . number_format(filesize($f)) . " bytes\n";
    echo "Modified: " . date('Y-m-d H:i:s', filemtime($f)) . "\n";
} else {
    echo "No app.js found!\n";
}

// 3. Vault items
echo "\n=== ohrm_vault_item ===\n";
$items = $pdo->query("SELECT id, name, user_id, 
    CASE WHEN encrypted_item_key IS NOT NULL THEN 'YES' ELSE 'NO' END as has_item_key,
    created_at FROM ohrm_vault_item ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
if (empty($items)) {
    echo "No items\n";
} else {
    foreach ($items as $r) {
        echo "id={$r['id']} name={$r['name']} user={$r['user_id']} has_item_key={$r['has_item_key']} created={$r['created_at']}\n";
    }
}

// 4. User keys
echo "\n=== ohrm_vault_user_key ===\n";
$keys = $pdo->query("SELECT id, user_id, LENGTH(public_key) as pk_len, LENGTH(encrypted_private_key) as epk_len, created_at FROM ohrm_vault_user_key")->fetchAll(PDO::FETCH_ASSOC);
if (empty($keys)) {
    echo "No keys\n";
} else {
    foreach ($keys as $r) {
        echo "id={$r['id']} user_id={$r['user_id']} pk_len={$r['pk_len']} epk_len={$r['epk_len']} created={$r['created_at']}\n";
    }
}

// 5. Recent log
$logFile = $base . '/log/xhrm.log';
echo "\n=== Recent log (last 8 lines) ===\n";
if (file_exists($logFile)) {
    $lines = file($logFile);
    echo "Total lines: " . count($lines) . " | Last modified: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n";
    echo implode('', array_slice($lines, -8));
} else {
    echo "Log not found\n";
}

echo "\n=== Done ===\n</pre>";
