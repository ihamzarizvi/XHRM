<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Direct Doctrine Test</h2>";

// Bootstrap the app with autoloader
require_once __DIR__ . '/../src/vendor/autoload.php';

$confPaths = [__DIR__ . '/../lib/confs/Conf.php', __DIR__ . '/../src/lib/confs/Conf.php'];
foreach ($confPaths as $cp) {
    if (file_exists($cp)) {
        require_once $cp;
        break;
    }
}

$conf = new Conf();
$pdo = new PDO("mysql:host={$conf->getDbHost()};dbname={$conf->getDbName()};port={$conf->getDbPort()}", $conf->getDbUser(), $conf->getDbPass(), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Test 1: Try to use the Entity class directly
echo "<h3>1. Entity Class Check</h3>";
try {
    $cls = 'XHRM\\Entity\\SalaryComponent';
    if (class_exists($cls)) {
        echo "<p style='color:green'>✓ $cls class exists</p>";
        $obj = new $cls();
        $obj->setName('Test');
        $obj->setCode('TEST999');
        $obj->setType('earning');
        echo "<p style='color:green'>✓ Object created, name=" . $obj->getName() . "</p>";
    } else {
        echo "<p style='color:red'>✗ $cls NOT FOUND</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Test 2: Check if Doctrine can find the entity metadata
echo "<h3>2. Doctrine ORM Check</h3>";
try {
    // Try to setup doctrine
    $dbParams = [
        'driver' => 'pdo_mysql',
        'host' => $conf->getDbHost(),
        'dbname' => $conf->getDbName(),
        'user' => $conf->getDbUser(),
        'password' => $conf->getDbPass(),
        'port' => $conf->getDbPort(),
        'charset' => 'utf8mb4',
    ];

    // Check entity paths
    $entityPaths = [
        __DIR__ . '/../src/plugins/XHRMPayrollPlugin/entity',
        __DIR__ . '/../src/plugins/XHRMCorePlugin/entity',
    ];

    foreach ($entityPaths as $path) {
        echo "<p>Path $path: " . (is_dir($path) ? 'EXISTS' : 'MISSING') . "</p>";
        if (is_dir($path)) {
            $files = glob($path . '/*.php');
            echo "<p>  Files: " . count($files) . "</p>";
        }
    }

    // Check if the SalaryComponent entity annotation is parseable
    $refClass = new ReflectionClass('XHRM\\Entity\\SalaryComponent');
    $docComment = $refClass->getDocComment();
    echo "<p>Entity doc comment: <code>" . htmlspecialchars($docComment) . "</code></p>";

    // Check namespace/file location
    echo "<p>File: " . $refClass->getFileName() . "</p>";

} catch (Throwable $e) {
    echo "<p style='color:red'>Doctrine error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 3: Check if the Doctrine ORM integration finds entity paths
echo "<h3>3. App Entity Dir Config</h3>";
try {
    // Check the config to see where entities are loaded from
    $pluginConfig = __DIR__ . '/../src/plugins/XHRMPayrollPlugin/config/PayrollPluginConfiguration.php';
    if (file_exists($pluginConfig)) {
        echo "<p style='color:green'>✓ Plugin config exists</p>";
        echo "<pre>" . htmlspecialchars(file_get_contents($pluginConfig)) . "</pre>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Test 4: Check the xhrm.log for recent errors
echo "<h3>4. Recent Errors</h3>";
$logFiles = [
    '/home/u118669189/.logs/error_log_xsofty_com',
    __DIR__ . '/../src/log/xhrm.log',
    __DIR__ . '/../var/log/xhrm.log',
];
foreach ($logFiles as $lf) {
    if (file_exists($lf)) {
        $lines = file($lf);
        $lastLines = array_slice($lines, -20);
        echo "<h4>$lf (last 20 lines)</h4>";
        echo "<pre style='font-size:9px;max-height:300px;overflow:auto'>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
    }
}
