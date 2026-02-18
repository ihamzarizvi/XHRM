<?php
// Temporary SQL runner — DELETE AFTER USE
$token = 'xhrm_reset_2026';
if (!isset($_GET['t']) || $_GET['t'] !== $token) {
    die('Unauthorized');
}

echo "<pre>";

// OrangeHRM stores DB config in config/Conf.php (generated during install)
$possibleConfs = [
    __DIR__ . '/../lib/confs/Conf.php',
    __DIR__ . '/../config/Conf.php',
    __DIR__ . '/config/Conf.php',
    dirname(__DIR__) . '/lib/confs/Conf.php',
];

$dbHost = 'localhost';
$dbName = $dbUser = $dbPass = '';

foreach ($possibleConfs as $f) {
    if (file_exists($f)) {
        echo "Found config: $f\n";
        $content = file_get_contents($f);
        if (preg_match("/dbhost\s*=\s*['\"]([^'\"]+)/", $content, $m))
            $dbHost = $m[1];
        if (preg_match("/dbname\s*=\s*['\"]([^'\"]+)/", $content, $m))
            $dbName = $m[1];
        if (preg_match("/dbuser\s*=\s*['\"]([^'\"]+)/", $content, $m))
            $dbUser = $m[1];
        if (preg_match("/dbpass\s*=\s*['\"]([^'\"]*)/", $content, $m))
            $dbPass = $m[1];
        break;
    }
}

// Fallback: scan .env file
if (!$dbName) {
    $envPaths = [
        dirname(__DIR__) . '/.env',
        dirname(__DIR__) . '/.env.local',
        __DIR__ . '/../.env',
    ];
    foreach ($envPaths as $ef) {
        if (file_exists($ef)) {
            echo "Found .env: $ef\n";
            foreach (file($ef) as $line) {
                $line = trim($line);
                if (preg_match('/^DATABASE_URL=mysql:\/\/([^:]+):([^@]*)@([^:\/]+)[^\/]*\/([^\?]+)/', $line, $m)) {
                    $dbUser = urldecode($m[1]);
                    $dbPass = urldecode($m[2]);
                    $dbHost = $m[3];
                    $dbName = $m[4];
                    break 2;
                }
            }
        }
    }
}

echo "Host: $dbHost | DB: $dbName | User: $dbUser\n\n";

if (!$dbName) {
    // List files to help debug
    echo "Listing parent dir:\n";
    foreach (scandir(dirname(__DIR__)) as $f)
        echo "  $f\n";
    die("\nCould not find DB config.\n");
}

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to $dbName!\n\n";

    $sqls = [
        "DELETE FROM ohrm_vault_user_key",
        "DELETE FROM ohrm_vault_item",
        "DELETE FROM ohrm_vault_audit_log",
    ];

    foreach ($sqls as $sql) {
        try {
            $rows = $pdo->exec($sql);
            echo "$sql\n  → Rows affected: $rows\n";
        } catch (Exception $e) {
            echo "$sql\n  → Error: " . $e->getMessage() . "\n";
        }
    }

    echo "\n✅ Done! Vault keys and items cleared.\n";
    echo '<a href="/web/index.php/passwordManager/viewPasswordManager">Go to Password Manager →</a>';
} catch (Exception $e) {
    echo "❌ Connection error: " . $e->getMessage() . "\n";
}
echo "</pre>";