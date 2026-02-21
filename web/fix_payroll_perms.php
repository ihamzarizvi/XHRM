<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Fix Payroll API Permissions</h2>";

// Find and load Conf
$confPaths = [
    __DIR__ . '/../lib/confs/Conf.php',
    __DIR__ . '/../src/lib/confs/Conf.php',
];
foreach ($confPaths as $cp) {
    if (file_exists($cp)) {
        require_once $cp;
        echo "<p>Conf loaded from: $cp</p>";
        break;
    }
}

$conf = new Conf();

try {
    $pdo = new PDO(
        "mysql:host={$conf->getDbHost()};dbname={$conf->getDbName()};port={$conf->getDbPort()}",
        $conf->getDbUser(),
        $conf->getDbPass(),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color:green'>✓ Database connected</p>";

    $statements = [
        // 1. Create data group for Payroll
        "INSERT IGNORE INTO ohrm_data_group (name, description, can_read, can_create, can_update, can_delete) VALUES ('payroll', 'Payroll Module Data', 1, 1, 1, 1)",

        // 2. Register all Payroll API classes
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\SalaryComponentAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\AttendanceRuleAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\OvertimeRuleAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\HolidayAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\FinancialYearAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\TaxSlabAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\PayrollRunAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\PayrollApprovalAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\PayslipAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\MyPayslipAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\EmployeeLoanAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",
        "INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id) SELECT 'XHRM\\\\Payroll\\\\Api\\\\PayrollEmailAPI', m.id, dg.id FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll'",

        // 3. Grant Admin role full access (non-self)
        "INSERT IGNORE INTO ohrm_data_group_permission (user_role_id, data_group_id, can_read, can_create, can_update, can_delete, self) SELECT r.id, dg.id, 1, 1, 1, 1, 0 FROM ohrm_user_role r, ohrm_data_group dg WHERE r.name = 'Admin' AND dg.name = 'payroll'",

        // 4. Grant Admin role full access (self)
        "INSERT IGNORE INTO ohrm_data_group_permission (user_role_id, data_group_id, can_read, can_create, can_update, can_delete, self) SELECT r.id, dg.id, 1, 1, 1, 1, 1 FROM ohrm_user_role r, ohrm_data_group dg WHERE r.name = 'Admin' AND dg.name = 'payroll'",

        // 5. Grant ESS role read access (self)
        "INSERT IGNORE INTO ohrm_data_group_permission (user_role_id, data_group_id, can_read, can_create, can_update, can_delete, self) SELECT r.id, dg.id, 1, 0, 0, 0, 1 FROM ohrm_user_role r, ohrm_data_group dg WHERE r.name = 'ESS' AND dg.name = 'payroll'",
    ];

    foreach ($statements as $i => $sql) {
        try {
            $affected = $pdo->exec($sql);
            echo "<p style='color:green'>✓ Statement " . ($i + 1) . " OK (affected: $affected)</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>✗ Statement " . ($i + 1) . " FAILED: " . $e->getMessage() . "</p>";
        }
    }

    // Verify
    echo "<h3>Verification</h3>";

    $stmt = $pdo->query("SELECT * FROM ohrm_data_group WHERE name = 'payroll'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><b>Data Group:</b></p><pre>" . json_encode($rows, JSON_PRETTY_PRINT) . "</pre>";

    $stmt = $pdo->query("SELECT ap.id, ap.api_name FROM ohrm_api_permission ap JOIN ohrm_data_group dg ON ap.data_group_id = dg.id WHERE dg.name = 'payroll'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><b>API Permissions registered (" . count($rows) . "):</b></p><pre>" . json_encode($rows, JSON_PRETTY_PRINT) . "</pre>";

    $stmt = $pdo->query("SELECT ur.name AS role_name, p.can_read, p.can_create, p.can_update, p.can_delete, p.self FROM ohrm_data_group_permission p JOIN ohrm_user_role ur ON p.user_role_id = ur.id JOIN ohrm_data_group dg ON p.data_group_id = dg.id WHERE dg.name = 'payroll'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><b>Role Permissions:</b></p><pre>" . json_encode($rows, JSON_PRETTY_PRINT) . "</pre>";

} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
