<?php
/**
 * Clear Doctrine caches + verify encrypted_item_key column exists.
 * DELETE THIS FILE AFTER USE.
 */
$token = 'xhrm_reset_2026';
if (!isset($_GET['t']) || $_GET['t'] !== $token) {
    die('Unauthorized');
}

echo "<pre>";
echo "=== Doctrine Cache Cleaner ===\n\n";

// 1. Clear doctrine metadata cache
$cacheDir = dirname(__DIR__) . '/src/cache';
$dirs = [
    $cacheDir . '/doctrine_metadata',
    $cacheDir . '/doctrine_queries',
    $cacheDir . '/XHRM',
];

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
                $count++;
            } elseif ($file->isDir()) {
                rmdir($file->getPathname());
            }
        }
        echo "Cleared $dir ($count files)\n";
    } else {
        echo "Not found: $dir\n";
    }
}

// 2. Also clear any OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared\n";
}

echo "\n=== Verifying DB Schema ===\n\n";

// Connect to DB
$possibleConfs = [
    dirname(__DIR__) . '/lib/confs/Conf.php',
    dirname(__DIR__) . '/config/Conf.php',
    __DIR__ . '/config/Conf.php',
    __DIR__ . '/../lib/confs/Conf.php',
];

$dbHost = 'localhost';
$dbName = $dbUser = $dbPass = '';

foreach ($possibleConfs as $f) {
    if (file_exists($f)) {
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

if (!$dbName) {
    $envPaths = [dirname(__DIR__) . '/.env', dirname(__DIR__) . '/.env.local'];
    foreach ($envPaths as $ef) {
        if (file_exists($ef)) {
            foreach (file($ef) as $line) {
                if (preg_match('/^DATABASE_URL=mysql:\/\/([^:]+):([^@]*)@([^:\/]+)[^\/]*\/([^\?]+)/', trim($line), $m)) {
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

if (!$dbName) {
    die("Could not find DB config.\n</pre>");
}

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column exists
    $cols = $pdo->query("SHOW COLUMNS FROM ohrm_vault_item LIKE 'encrypted_item_key'")->fetchAll();
    if (count($cols) > 0) {
        echo "Column 'encrypted_item_key' EXISTS in ohrm_vault_item\n";
    } else {
        echo "Column 'encrypted_item_key' MISSING - adding it now...\n";
        $pdo->exec("ALTER TABLE ohrm_vault_item ADD COLUMN encrypted_item_key TEXT NULL AFTER favorite");
        echo "Column added!\n";
    }

    // Show all columns
    echo "\nFull schema:\n";
    $cols = $pdo->query("SHOW COLUMNS FROM ohrm_vault_item")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "  {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }

    // Show current items and their encrypted_item_key status
    echo "\nCurrent items:\n";
    $items = $pdo->query("SELECT id, name, CASE WHEN encrypted_item_key IS NULL THEN 'NULL' WHEN encrypted_item_key = '' THEN 'EMPTY' ELSE CONCAT('SET (', LENGTH(encrypted_item_key), ' chars)') END AS key_status FROM ohrm_vault_item")->fetchAll(PDO::FETCH_ASSOC);
    if (count($items) === 0) {
        echo "  (no items)\n";
    }
    foreach ($items as $item) {
        echo "  ID={$item['id']}, name={$item['name']}, encrypted_item_key={$item['key_status']}\n";
    }

    // Clean corrupt items
    echo "\nCleaning items with NULL encrypted_item_key...\n";
    $deleted = $pdo->exec("DELETE FROM ohrm_vault_item WHERE encrypted_item_key IS NULL OR encrypted_item_key = ''");
    echo "Deleted $deleted items with missing keys.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nAll done! Doctrine cache cleared, schema verified.\n";
echo "Now hard-refresh the Password Manager page and create a new item.\n";
echo '<a href="/web/index.php/passwordManager/viewPasswordManager">Go to Password Manager</a>';
echo "</pre>";
