<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/../vendor/autoload.php';

echo "=== Holiday 500 Error Diagnosis ===\n\n";

try {
    // Check DB directly
    $configFile = __DIR__ . '/../src/config/Conf.php';
    require_once $configFile;

    $host = defined('CONF_DB_HOST') ? CONF_DB_HOST : 'localhost';
    $dbname = defined('CONF_DB_NAME') ? CONF_DB_NAME : '';
    $user = defined('CONF_DB_USER') ? CONF_DB_USER : '';
    $pass = defined('CONF_DB_PASSWORD') ? CONF_DB_PASSWORD : '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

    // Check if xhrm_holiday table exists
    $result = $pdo->query("SHOW TABLES LIKE 'xhrm_holiday'")->fetchAll();
    echo "Table xhrm_holiday exists: " . (count($result) > 0 ? 'YES' : 'NO') . "\n";

    if (count($result) > 0) {
        // Check structure
        $cols = $pdo->query("DESCRIBE xhrm_holiday")->fetchAll(PDO::FETCH_ASSOC);
        echo "\nColumns:\n";
        foreach ($cols as $col) {
            echo "  " . $col['Field'] . " " . $col['Type'] . "\n";
        }

        // Check records
        $count = $pdo->query("SELECT COUNT(*) FROM xhrm_holiday")->fetchColumn();
        echo "\nRecord count: $count\n";

        // Test YEAR() function
        $test = $pdo->query("SELECT YEAR('2026-01-01') as yr")->fetchColumn();
        echo "YEAR() SQL function works: $test\n";

        // Test the actual query
        $rows = $pdo->query("SELECT * FROM xhrm_holiday WHERE YEAR(date) = 2026 ORDER BY date ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo "Holidays in 2026: " . count($rows) . "\n";
        foreach ($rows as $row) {
            echo "  - " . $row['name'] . " (" . $row['date'] . ")\n";
        }
    } else {
        echo "\n*** TABLE MISSING! Need to run migrations ***\n";
    }

    // Check HolidayModel
    $modelFile = __DIR__ . '/../src/plugins/XHRMPayrollPlugin/Api/Model/HolidayModel.php';
    if (file_exists($modelFile)) {
        echo "\nHolidayModel.php exists\n";
        $content = file_get_contents($modelFile);
        echo substr($content, 0, 500) . "\n";
    } else {
        echo "\nHolidayModel.php MISSING!\n";
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
