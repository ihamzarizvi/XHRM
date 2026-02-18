<?php
// Temporary SQL runner — DELETE AFTER USE
$token = 'xhrm_reset_2026';

if (!isset($_GET['t']) || $_GET['t'] !== $token) {
    die('Unauthorized');
}

// Load DB config from Symfony .env or config
$envFile = __DIR__ . '/../config/.env';
$dbHost = $dbName = $dbUser = $dbPass = '';

if (file_exists($envFile)) {
    foreach (file($envFile) as $line) {
        if (preg_match('/^DATABASE_HOST=(.+)/', trim($line), $m))
            $dbHost = trim($m[1]);
        if (preg_match('/^DATABASE_NAME=(.+)/', trim($line), $m))
            $dbName = trim($m[1]);
        if (preg_match('/^DATABASE_USER=(.+)/', trim($line), $m))
            $dbUser = trim($m[1]);
        if (preg_match('/^DATABASE_PASSWORD=(.+)/', trim($line), $m))
            $dbPass = trim($m[1]);
    }
}

// Fallback: try symfony config
if (!$dbName) {
    $configFiles = glob(__DIR__ . '/../config/packages/*.yaml');
    foreach ($configFiles as $f) {
        $content = file_get_contents($f);
        if (preg_match('/dbname=([^%\s&]+)/', $content, $m))
            $dbName = $m[1];
        if (preg_match('/host=([^%\s&]+)/', $content, $m))
            $dbHost = $m[1];
        if (preg_match('/user=([^%\s&]+)/', $content, $m))
            $dbUser = $m[1];
        if (preg_match('/password=([^%\s&]+)/', $content, $m))
            $dbPass = $m[1];
    }
}

// Try reading from the OrangeHRM config directly
if (!$dbName) {
    $cfgFile = __DIR__ . '/../lib/confs/Conf.php';
    if (file_exists($cfgFile)) {
        include_once $cfgFile;
        if (class_exists('Conf')) {
            $conf = new Conf();
            $dbHost = $conf->dbhost ?? 'localhost';
            $dbName = $conf->dbname ?? '';
            $dbUser = $conf->dbuser ?? '';
            $dbPass = $conf->dbpass ?? '';
        }
    }
}

echo "<pre>";
echo "DB Host: $dbHost\nDB Name: $dbName\nDB User: $dbUser\n\n";

if (!$dbName) {
    echo "Could not detect DB config. Trying PDO with env vars...\n";
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: '';
    $dbUser = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: '';
    $dbPass = getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: '';
    echo "From env: DB=$dbName User=$dbUser\n";
}

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected!\n\n";

    $sqls = [
        "DELETE FROM ohrm_vault_user_key",
        "DELETE FROM ohrm_vault_item",
        "DELETE FROM ohrm_vault_audit_log",
    ];

    foreach ($sqls as $sql) {
        $stmt = $pdo->exec($sql);
        echo "$sql\n  → Rows affected: $stmt\n";
    }

    echo "\n✅ Done! Vault keys and items cleared.\n";
    echo "Now visit the Password Manager to generate fresh keys.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>