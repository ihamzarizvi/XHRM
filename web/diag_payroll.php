<?php
/**
 * Payroll Diagnostic - Place this in web/ directory
 * Access via: https://mimar.xsofty.com/web/diag_payroll.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Payroll Plugin Diagnostics</h2>";

// Check 1: Doctrine entity mapping
echo "<h3>1. Entity Files</h3>";
$entityDir = __DIR__ . '/../src/plugins/XHRMPayrollPlugin/entity';
if (is_dir($entityDir)) {
    $files = glob($entityDir . '/*.php');
    echo "<p>Found " . count($files) . " entity files:</p><ul>";
    foreach ($files as $f) {
        echo "<li>" . basename($f) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>Entity directory not found: $entityDir</p>";
}

// Check 2: Controller files
echo "<h3>2. Controller Files</h3>";
$ctrlDir = __DIR__ . '/../src/plugins/XHRMPayrollPlugin/Controller';
if (is_dir($ctrlDir)) {
    $files = glob($ctrlDir . '/*.php');
    echo "<p>Found " . count($files) . " controller files:</p><ul>";
    foreach ($files as $f) {
        echo "<li>" . basename($f) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>Controller directory not found: $ctrlDir</p>";
}

// Check 3: Routes config
echo "<h3>3. Routes Config</h3>";
$routesFile = __DIR__ . '/../src/plugins/XHRMPayrollPlugin/config/routes.yaml';
if (file_exists($routesFile)) {
    echo "<p style='color:green'>routes.yaml exists (" . filesize($routesFile) . " bytes)</p>";
} else {
    echo "<p style='color:red'>routes.yaml NOT FOUND</p>";
}

// Check 4: Plugin Configuration
echo "<h3>4. Plugin Configuration</h3>";
$configFile = __DIR__ . '/../src/plugins/XHRMPayrollPlugin/config/PayrollPluginConfiguration.php';
if (file_exists($configFile)) {
    echo "<p style='color:green'>PayrollPluginConfiguration.php exists</p>";
} else {
    echo "<p style='color:red'>PayrollPluginConfiguration.php NOT FOUND</p>";
}

// Check 5: Check Symfony error log
echo "<h3>5. Recent Error Logs</h3>";
$logDirs = [
    __DIR__ . '/../src/log',
    __DIR__ . '/../var/log',
    __DIR__ . '/../src/cache/log',
];
foreach ($logDirs as $logDir) {
    if (is_dir($logDir)) {
        echo "<p>Log directory found: $logDir</p>";
        $logFiles = glob($logDir . '/*.log');
        if (empty($logFiles)) {
            $logFiles = glob($logDir . '/*');
        }
        foreach ($logFiles as $lf) {
            if (is_file($lf)) {
                echo "<p><b>" . basename($lf) . "</b> (" . filesize($lf) . " bytes, modified: " . date('Y-m-d H:i:s', filemtime($lf)) . ")</p>";
                // Show last 30 lines
                $lines = file($lf);
                $lastLines = array_slice($lines, -30);
                echo "<pre style='background:#111;color:#0f0;padding:10px;font-size:11px;max-height:400px;overflow:auto'>";
                echo htmlspecialchars(implode('', $lastLines));
                echo "</pre>";
            }
        }
    }
}

// Check 6: Try to autoload a controller class
echo "<h3>6. Class Autoloading Test</h3>";
try {
    require_once __DIR__ . '/../src/vendor/autoload.php';

    $classes = [
        'XHRM\\Payroll\\Controller\\GeneratePayrollController',
        'XHRM\\Payroll\\Service\\PayrollService',
        'XHRM\\Payroll\\Dao\\PayrollDao',
    ];
    foreach ($classes as $cls) {
        if (class_exists($cls)) {
            echo "<p style='color:green'>✓ $cls - loaded successfully</p>";
        } else {
            echo "<p style='color:red'>✗ $cls - NOT FOUND</p>";
        }
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>Autoloader error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Check 7: Check the Symfony cache
echo "<h3>7. Symfony Cache</h3>";
$cacheDirs = [
    __DIR__ . '/../src/cache',
    __DIR__ . '/../var/cache',
];
foreach ($cacheDirs as $cd) {
    if (is_dir($cd)) {
        echo "<p>Cache dir: $cd</p>";
        $subdirs = glob($cd . '/*', GLOB_ONLYDIR);
        foreach ($subdirs as $sd) {
            echo "<li>" . basename($sd) . "</li>";
        }
    }
}

echo "<h3>8. Database Connection Test</h3>";
try {
    $confFile = __DIR__ . '/../lib/confs/Conf.php';
    if (file_exists($confFile)) {
        require_once $confFile;
        if (class_exists('Conf')) {
            $conf = new Conf();
            $pdo = new PDO(
                "mysql:host={$conf->dbhost};dbname={$conf->dbname};port={$conf->dbport}",
                $conf->dbuser,
                $conf->dbpass
            );

            // Check payroll tables exist
            $stmt = $pdo->query("SHOW TABLES LIKE 'xhrm_%'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p>Payroll tables found: " . count($tables) . "</p><ul>";
            foreach ($tables as $t) {
                echo "<li>$t</li>";
            }
            echo "</ul>";

            // Check screens
            $stmt = $pdo->query("SELECT id, name, action_url FROM ohrm_screen WHERE module_id = 102");
            $screens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<p>Payroll screens: " . count($screens) . "</p>";
            echo "<table border='1' cellpadding='4'><tr><th>ID</th><th>Name</th><th>action_url</th></tr>";
            foreach ($screens as $s) {
                echo "<tr><td>{$s['id']}</td><td>{$s['name']}</td><td>{$s['action_url']}</td></tr>";
            }
            echo "</table>";
        }
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>DB Error: " . $e->getMessage() . "</p>";
}
