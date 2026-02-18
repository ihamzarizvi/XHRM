<?php
/**
 * Diagnostic: Directly test VaultUserKeyAPI endpoint
 * Visit: https://mimar.xsofty.com/web/diag_userkey.php
 * DELETE THIS FILE after debugging!
 */

// Show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>\n";
echo "=== Testing VaultUserKey endpoint ===\n\n";

// Check if the entity table exists
try {
    // Bootstrap the app to get DB connection
    $rootDir = __DIR__ . '/..';

    // Try to get a PDO connection using the app's config
    $configFile = $rootDir . '/config/Includes/Conf.php';
    if (file_exists($configFile)) {
        require_once $configFile;
        echo "Config loaded.\n";
    } else {
        // Try alternate config locations
        $configs = glob($rootDir . '/config/**/*.php');
        echo "Config files found: " . implode(', ', $configs) . "\n";
    }

    // Try direct DB connection using known constants
    if (defined('XHRM_CONF_DBHOST')) {
        $pdo = new PDO(
            'mysql:host=' . XHRM_CONF_DBHOST . ';dbname=' . XHRM_CONF_DBNAME,
            XHRM_CONF_DBUSER,
            XHRM_CONF_DBPASS
        );
        echo "DB connected via constants.\n";
    } else {
        // Try reading the config file directly
        $confFile = $rootDir . '/lib/config/Conf.php';
        if (file_exists($confFile)) {
            require_once $confFile;
            echo "Loaded lib config.\n";
        }

        // Try symfony .env
        $envFile = $rootDir . '/.env';
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            echo "ENV vars: " . implode(', ', array_keys($env)) . "\n";
        }
    }

    // Check ohrm_vault_user_key table
    if (isset($pdo)) {
        $stmt = $pdo->query("SHOW TABLES LIKE 'ohrm_vault_user_key'");
        $table = $stmt->fetch();
        if ($table) {
            echo "✅ ohrm_vault_user_key table EXISTS\n";
            $stmt2 = $pdo->query("DESCRIBE ohrm_vault_user_key");
            echo "Columns:\n";
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
        } else {
            echo "❌ ohrm_vault_user_key table DOES NOT EXIST\n";
            echo "This is why the API returns 500!\n";
        }

        // Also check ohrm_api_permission for user-keys
        $stmt3 = $pdo->query("SELECT * FROM ohrm_api_permission WHERE api_name LIKE '%VaultUserKey%'");
        $row = $stmt3->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "\n✅ VaultUserKeyAPI permission: " . json_encode($row) . "\n";
        } else {
            echo "\n❌ VaultUserKeyAPI NOT in ohrm_api_permission\n";
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Check PHP error log for recent errors
echo "\n=== Recent PHP errors ===\n";
$logFiles = [
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log',
    ini_get('error_log'),
    __DIR__ . '/../var/log/prod.log',
    __DIR__ . '/../var/log/dev.log',
];
foreach ($logFiles as $log) {
    if ($log && file_exists($log) && is_readable($log)) {
        echo "Found log: $log\n";
        $lines = file($log);
        $recent = array_slice($lines, -20);
        echo implode('', $recent);
        break;
    }
}

echo "\n=== Symfony/App logs ===\n";
$appLog = __DIR__ . '/../var/log/prod.log';
if (file_exists($appLog)) {
    $lines = file($appLog);
    $recent = array_slice($lines, -30);
    echo implode('', $recent);
} else {
    echo "No var/log/prod.log found.\n";
    // List what's in var/log
    $logDir = __DIR__ . '/../var/log';
    if (is_dir($logDir)) {
        echo "Files in var/log: " . implode(', ', scandir($logDir)) . "\n";
    }
}

echo "</pre>";
