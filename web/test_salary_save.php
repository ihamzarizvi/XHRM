<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Salary Component Save</h2>";

// Bootstrap the application like the framework does
$confPaths = [
    __DIR__ . '/../lib/confs/Conf.php',
    __DIR__ . '/../src/lib/confs/Conf.php',
];
foreach ($confPaths as $cp) {
    if (file_exists($cp)) {
        require_once $cp;
        break;
    }
}

$conf = new Conf();
$pdo = new PDO(
    "mysql:host={$conf->getDbHost()};dbname={$conf->getDbName()};port={$conf->getDbPort()}",
    $conf->getDbUser(),
    $conf->getDbPass(),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "<p style='color:green'>✓ Connected</p>";

// Check if the table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'xhrm_salary_component'");
    $result = $stmt->fetchAll();
    if (count($result) > 0) {
        echo "<p style='color:green'>✓ Table xhrm_salary_component exists</p>";

        // Show table structure
        $stmt = $pdo->query("DESCRIBE xhrm_salary_component");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' cellpadding='4'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
        }
        echo "</table>";

        // Try direct insert
        try {
            $stmt = $pdo->prepare("INSERT INTO xhrm_salary_component (name, code, type, calculation_type, default_value, is_taxable, is_active, sort_order, applies_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Basic Salary', 'BASIC', 'earning', 'fixed', '50000.00', 1, 1, 0, 'all']);
            $id = $pdo->lastInsertId();
            echo "<p style='color:green'>✓ Direct insert worked! ID = $id</p>";

            // Also insert HRA
            $stmt->execute(['House Rent Allowance', 'HRA', 'earning', 'percentage', '40.00', 0, 1, 1, 'all']);
            $id2 = $pdo->lastInsertId();
            echo "<p style='color:green'>✓ HRA inserted! ID = $id2</p>";

            // Also insert Tax Deduction
            $stmt->execute(['Income Tax', 'TAX', 'deduction', 'fixed', '5000.00', 0, 1, 2, 'all']);
            $id3 = $pdo->lastInsertId();
            echo "<p style='color:green'>✓ Income Tax inserted! ID = $id3</p>";

            // Show all records
            $stmt = $pdo->query("SELECT * FROM xhrm_salary_component");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<p><b>All Salary Components (" . count($rows) . "):</b></p><pre>" . json_encode($rows, JSON_PRETTY_PRINT) . "</pre>";

        } catch (PDOException $e) {
            echo "<p style='color:orange'>⚠ Insert failed (might already exist): " . $e->getMessage() . "</p>";
        }

    } else {
        echo "<p style='color:red'>✗ Table xhrm_salary_component does NOT exist!</p>";

        // List all xhrm_ tables
        $stmt = $pdo->query("SHOW TABLES LIKE 'xhrm_%'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Available xhrm_ tables:</p><ul>";
        foreach ($tables as $t)
            echo "<li>$t</li>";
        echo "</ul>";
    }

    // Also check all payroll-related tables
    echo "<h3>All Payroll Tables</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'xhrm_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "<p style='color:red'>No xhrm_ tables found!</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $t)
            echo "<li>$t</li>";
        echo "</ul>";
    }

    // Check the Doctrine proxy cache
    echo "<h3>Doctrine Cache</h3>";
    $cacheDirs = [
        __DIR__ . '/../src/cache',
        __DIR__ . '/../var/cache',
    ];
    foreach ($cacheDirs as $cd) {
        if (is_dir($cd)) {
            echo "<p>Cache dir exists: $cd</p>";
            $proxyDir = $cd . '/doctrine/orm/Proxies';
            if (is_dir($proxyDir)) {
                $files = glob($proxyDir . '/*SalaryComponent*');
                echo "<p>SalaryComponent proxy files: " . count($files) . "</p>";
                foreach ($files as $f)
                    echo "<p>- " . basename($f) . "</p>";
            }
        }
    }

} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
