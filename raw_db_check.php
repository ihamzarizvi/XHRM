<?php
// Simple DB Check Script (No Framework dependencies)
$host = 'localhost';
$user = 'u118669189_mimarhrm'; // I recall this from previous logs or config
$pass = 'Pakistan#78601%';
$db = 'u118669189_mimarhrm';

try {
    // Attempting to find credentials from src/config/db_settings.php or similar
    $db_config = include(__DIR__ . '/src/config/db_config.php');
    if (is_array($db_config)) {
        // use those if found
    }
} catch (Exception $e) {
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "COLUMNS IN ohrm_vault_item:\n";
    $stmt = $pdo->query("DESCRIBE ohrm_vault_item");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\nCOLUMNS IN ohrm_vault_category:\n";
    $stmt = $pdo->query("DESCRIBE ohrm_vault_category");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
