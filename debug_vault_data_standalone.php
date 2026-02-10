<?php
// Standalone script, no includes

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = ''; // Default XAMPP/WAMP password
$dbs = ['xhrm', 'orangehrm', 'orangehrm_mysql', 'hrm'];

$pdo = null;
$connectedDb = '';

foreach ($dbs as $db) {
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
        $connectedDb = $db;
        echo "Connected to database: $db\n";
        break;
    } catch (PDOException $e) {
        // Try next
    }
}

if (!$pdo) {
    die("Could not connect to any likely database (xhrm, orangehrm, etc) with root/empty password.\n");
}

try {
    echo "Fetching items from ohrm_vault_item...\n\n";

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'ohrm_vault_item'");
    if ($stmt->rowCount() === 0) {
        die("Table 'ohrm_vault_item' does not exist in $connectedDb.\n");
    }

    $stmt = $pdo->query("SELECT id, name, username_encrypted, password_encrypted FROM ohrm_vault_item");

    while ($row = $stmt->fetch()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Name: " . $row['name'] . "\n";

        $fields = ['username_encrypted', 'password_encrypted'];
        foreach ($fields as $field) {
            $val = $row[$field];
            $displayVal = $val;
            if (strlen($val) > 50) {
                $displayVal = substr($val, 0, 20) . '...' . substr($val, -20);
            }
            echo "$field: [$displayVal]\n";

            if ($val === '[Encrypted Data]') {
                echo "  WARNING: Value is literally '[Encrypted Data]'. Double reference issue!\n";
            } else {
                $parts = explode('::', $val);
                if (count($parts) === 2) {
                    $iv = $parts[0];
                    $content = $parts[1];
                    echo "  Format: Valid (IV::Content)\n";
                    echo "  IV Len: " . strlen($iv) . " (Spaces: " . (strpos($iv, ' ') !== false ? "YES" : "NO") . ")\n";
                    echo "  Content Len: " . strlen($content) . " (Spaces: " . (strpos($content, ' ') !== false ? "YES" : "NO") . ")\n";
                } else {
                    echo "  Format: INVALID (No '::' separator)\n";
                }
            }
        }
        echo "---------------------------------------------------\n";
    }
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
