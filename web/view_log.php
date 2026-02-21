<?php
header('Content-Type: text/plain');

echo "=== Holiday Diagnosis via diag_db.php approach ===\n\n";

// Use the existing diag_db.php connection approach
$dbPath = __DIR__ . '/../web/diag_db.php';
if (file_exists($dbPath)) {
    $content = file_get_contents($dbPath);
    preg_match('/new PDO\([\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]/', $content, $m);
    echo "PDO from diag_db: " . json_encode($m) . "\n";
}

// Try to get DB config from the installed config
$possibleDirs = [
    '/home/u118669189/domains/xsofty.com/public_html/mimarhrm/',
    __DIR__ . '/../',
];

foreach ($possibleDirs as $dir) {
    $confFiles = glob($dir . '*/install/conf.php');
    foreach ($confFiles as $f) {
        echo "Found: $f\n";
    }

    // Check log files
    $logDirs = glob($dir . '*/log/');
    foreach ($logDirs as $logDir) {
        echo "Log dir: $logDir\n";
        $logFiles = glob($logDir . '*.log');
        foreach ($logFiles as $lf) {
            echo "  Log: $lf (" . filesize($lf) . " bytes)\n";
            // Show last 20 lines
            $lines = explode("\n", file_get_contents($lf));
            $tail = array_slice($lines, -20);
            echo "  Last 20 lines:\n";
            echo implode("\n", $tail) . "\n\n";
        }
    }
}

// Fall back - read the config from the application
$confSearch = __DIR__ . '/../src/config/';
echo "\nSearching for DB config in: $confSearch\n";
$files = glob($confSearch . '*.php');
foreach ($files as $f) {
    echo "File: " . basename($f) . "\n";
    $content = file_get_contents($f);
    if (strpos($content, 'DB_HOST') !== false || strpos($content, 'db_host') !== false || strpos($content, 'database') !== false) {
        echo "  [Contains DB config]\n";
        // Find consts
        preg_match_all('/define\([\'"]([A-Z_]+)[\'"],\s*[\'"]([^\'"]*)[\'"]/', $content, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            echo "  " . $matches[1][$i] . " = " . $matches[2][$i] . "\n";
        }
    }
}
