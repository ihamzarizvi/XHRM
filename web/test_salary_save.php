<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Payroll Data Check & Fix</h2>";

$confPaths = [__DIR__ . '/../lib/confs/Conf.php', __DIR__ . '/../src/lib/confs/Conf.php'];
foreach ($confPaths as $cp) {
    if (file_exists($cp)) {
        require_once $cp;
        break;
    }
}
$conf = new Conf();
$pdo = new PDO("mysql:host={$conf->getDbHost()};dbname={$conf->getDbName()};port={$conf->getDbPort()}", $conf->getDbUser(), $conf->getDbPass(), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// 1. Show existing salary components
echo "<h3>1. Existing Salary Components</h3>";
$rows = $pdo->query("SELECT * FROM xhrm_salary_component")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($rows, JSON_PRETTY_PRINT) . "</pre>";

// 2. Insert more if missing
$components = [
    ['Basic Salary', 'BASIC', 'earning', 'fixed', '50000.00', 1, 1, 0, 'all'],
    ['House Rent Allowance', 'HRA', 'earning', 'percentage', '40.00', 0, 1, 1, 'all'],
    ['Income Tax', 'TAX', 'deduction', 'fixed', '5000.00', 0, 1, 2, 'all'],
    ['Conveyance Allowance', 'CONV', 'earning', 'fixed', '5000.00', 0, 1, 3, 'all'],
];
foreach ($components as $c) {
    try {
        $pdo->prepare("INSERT IGNORE INTO xhrm_salary_component (name, code, type, calculation_type, default_value, is_taxable, is_active, sort_order, applies_to) VALUES (?,?,?,?,?,?,?,?,?)")->execute($c);
        echo "<p style='color:green'>✓ $c[0] ($c[1]) OK</p>";
    } catch (PDOException $e) {
        echo "<p style='color:orange'>⚠ $c[0]: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>After insert</h3>";
$rows = $pdo->query("SELECT * FROM xhrm_salary_component")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($rows, JSON_PRETTY_PRINT) . "</pre>";

// 3. Insert financial year  
echo "<h3>2. Financial Years</h3>";
$pdo->exec("INSERT IGNORE INTO xhrm_financial_year (name, start_date, end_date, is_active) VALUES ('FY 2025-2026', '2025-07-01', '2026-06-30', 1)");
$pdo->exec("INSERT IGNORE INTO xhrm_financial_year (name, start_date, end_date, is_active) VALUES ('FY 2026-2027', '2026-07-01', '2027-06-30', 0)");
$rows = $pdo->query("SELECT * FROM xhrm_financial_year")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($rows, JSON_PRETTY_PRINT) . "</pre>";

// 4. Insert holidays
echo "<h3>3. Holidays</h3>";
$pdo->exec("INSERT IGNORE INTO xhrm_holiday (name, date, type, is_half_day, applies_to, is_recurring) VALUES ('Pakistan Day', '2026-03-23', 'national', 0, 'all', 0)");
$pdo->exec("INSERT IGNORE INTO xhrm_holiday (name, date, type, is_half_day, applies_to, is_recurring) VALUES ('Eid ul Fitr', '2026-03-31', 'religious', 0, 'all', 0)");
$pdo->exec("INSERT IGNORE INTO xhrm_holiday (name, date, type, is_half_day, applies_to, is_recurring) VALUES ('Labour Day', '2026-05-01', 'national', 0, 'all', 0)");
$rows = $pdo->query("SELECT * FROM xhrm_holiday")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($rows, JSON_PRETTY_PRINT) . "</pre>";

// 5. Check Doctrine proxy files for SalaryComponent
echo "<h3>4. Doctrine Proxy Status</h3>";
$proxyDir = __DIR__ . '/../src/cache/doctrine/orm/Proxies';
if (is_dir($proxyDir)) {
    $files = glob($proxyDir . '/*');
    echo "<p>Total proxy files: " . count($files) . "</p>";
    $payrollProxies = array_filter($files, function ($f) {
        return stripos(basename($f), 'Salary') !== false || stripos(basename($f), 'Holiday') !== false || stripos(basename($f), 'Financial') !== false;
    });
    if (empty($payrollProxies)) {
        echo "<p style='color:orange'>No payroll proxy files found - Doctrine may need to regenerate proxies</p>";
    } else {
        foreach ($payrollProxies as $f)
            echo "<p>- " . basename($f) . "</p>";
    }
}

// 6. Check if the Doctrine entity metadata is cached
echo "<h3>5. Entity Metadata Check</h3>";
$metaCacheDir = __DIR__ . '/../src/cache/doctrine';
if (is_dir($metaCacheDir)) {
    $payrollMeta = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($metaCacheDir));
    foreach ($iterator as $file) {
        if (stripos($file->getFilename(), 'Salary') !== false || stripos($file->getFilename(), 'alary') !== false) {
            $payrollMeta[] = $file->getPathname();
        }
    }
    echo "<p>Payroll metadata cache files: " . count($payrollMeta) . "</p>";
    foreach ($payrollMeta as $m)
        echo "<p style='font-size:10px'>$m</p>";
}

// 7. Now clear the Doctrine metadata cache so entities get re-registered
echo "<h3>6. Cache Clear</h3>";
$cacheFiles = [
    __DIR__ . '/../src/cache/doctrine/orm/Proxies/__CG__XHRMEntitySalaryComponent.php',
];
foreach ($cacheFiles as $cf) {
    if (file_exists($cf)) {
        unlink($cf);
        echo "<p style='color:green'>Deleted: $cf</p>";
    }
}

echo "<h3>Done!</h3>";
