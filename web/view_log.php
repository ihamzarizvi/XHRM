<?php
header('Content-Type: text/plain');

echo "=== Holiday 500 Error Diagnosis ===\n\n";

try {
    // Find the config file
    $possibleConfigs = [
        __DIR__ . '/../src/config/Conf.php',
        __DIR__ . '/../src/config/conf.php',
        __DIR__ . '/../conf.php',
    ];

    foreach ($possibleConfigs as $c) {
        echo "Config: $c - " . (file_exists($c) ? 'EXISTS' : 'not found') . "\n";
    }

    // Use the symfony/doctrine approach
    require_once __DIR__ . '/../vendor/autoload.php';

    // Bootstrap the framework 
    $kernel = new \XHRM\Framework\Framework('prod', false);
    $em = $kernel->getContainer()->get('doctrine.orm.entity_manager') ??
        $kernel->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);

    $conn = $em->getConnection();

    // Check if xhrm_holiday table exists
    $tables = $conn->executeQuery("SHOW TABLES LIKE 'xhrm_holiday'")->fetchFirstColumn();
    echo "\nTable xhrm_holiday: " . (count($tables) > 0 ? 'EXISTS' : 'MISSING') . "\n";

    if (count($tables) > 0) {
        // Check DQL YEAR function
        try {
            $dql = "SELECT h FROM XHRM\Entity\Holiday h WHERE YEAR(h.date) = :year";
            $q = $em->createQuery($dql)->setParameter('year', 2026);
            $results = $q->getResult();
            echo "DQL YEAR() query works, results: " . count($results) . "\n";
        } catch (Throwable $e) {
            echo "DQL YEAR() error: " . $e->getMessage() . "\n";
        }
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
