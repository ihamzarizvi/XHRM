<?php
require_once __DIR__ . '/vendor/autoload.php';

use XHRM\ORM\Doctrine;

header('Content-Type: text/plain');

try {
    $em = Doctrine::getEntityManager();
    $conn = $em->getConnection();
    echo "DB Connected: " . $conn->getDatabase() . "\n";

    // Check if table exists
    $schemaManager = $conn->getSchemaManager();
    if (!$schemaManager->tablesExist(['ohrm_vault_item'])) {
        die("Table 'ohrm_vault_item' does not exist.\n");
    }

    echo "Fetching items from ohrm_vault_item...\n\n";

    $stmt = $conn->executeQuery("SELECT id, name, username_encrypted, password_encrypted FROM ohrm_vault_item");

    while ($row = $stmt->fetchAssociative()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Name: " . $row['name'] . "\n";

        $fields = ['username_encrypted', 'password_encrypted'];
        foreach ($fields as $field) {
            $val = $row[$field];
            echo "$field Raw: [" . $val . "]\n";

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

                    // Check base64 validity
                    if (base64_decode($iv, true) === false)
                        echo "  IV Base64: INVALID\n";
                    if (base64_decode($content, true) === false)
                        echo "  Content Base64: INVALID\n";

                } else {
                    echo "  Format: INVALID (No '::' separator)\n";
                    // Check if it looks like just base64 without IV?
                }
            }
        }
        echo "---------------------------------------------------\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
