<?php
require_once __DIR__ . '/vendor/autoload.php';
use XHRM\Config\Config;
use XHRM\ORM\Doctrine;

header('Content-Type: text/plain');

echo "Log Error Ini: " . ini_get('log_errors') . "\n";
echo "Error Log Path: " . ini_get('error_log') . "\n";

$logFile = ini_get('error_log');
if (file_exists($logFile)) {
    echo "Log file exists. Last 20 lines:\n";
    echo shell_exec("tail -n 20 " . escapeshellarg($logFile));
} else {
    echo "Log file does NOT exist at that path.\n";
}

try {
    $em = Doctrine::getEntityManager();
    $conn = $em->getConnection();
    echo "DB Connected: " . $conn->getDatabase() . "\n";

    $tables = $conn->getSchemaManager()->listTableNames();
    echo "Tables: " . implode(', ', $tables) . "\n";

    if (in_array('ohrm_vault_item', $tables)) {
        echo "\nColumns in ohrm_vault_item:\n";
        $columns = $conn->getSchemaManager()->listTableColumns('ohrm_vault_item');
        foreach ($columns as $column) {
            echo $column->getName() . " (" . $column->getType()->getName() . ")\n";
        }
    } else {
        echo "\nTable ohrm_vault_item NOT FOUND!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
