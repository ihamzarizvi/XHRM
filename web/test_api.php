<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>API Validation Debug</h2>";

// Directly inspect the log file for payroll API errors
$confPaths = [__DIR__ . '/../lib/confs/Conf.php', __DIR__ . '/../src/lib/confs/Conf.php'];
foreach ($confPaths as $cp) {
    if (file_exists($cp)) {
        require_once $cp;
        break;
    }
}

// Check the XHRM log which the LoggerTrait writes to
$logFiles = [
    __DIR__ . '/../src/log/xhrm.log',
    __DIR__ . '/../var/log/xhrm.log',
    __DIR__ . '/../src/log/ohrm.log',
    __DIR__ . '/../src/cache/log/xhrm.log',
];
foreach ($logFiles as $lf) {
    if (file_exists($lf)) {
        echo "<h3>$lf</h3>";
        echo "<p>Size: " . filesize($lf) . " bytes, Modified: " . date('Y-m-d H:i:s', filemtime($lf)) . "</p>";
        $lines = file($lf);
        // Get only the last 50 lines and filter for payroll/salary related
        $lastLines = array_slice($lines, -100);
        $payrollLines = array_filter($lastLines, function ($line) {
            return stripos($line, 'payroll') !== false ||
                stripos($line, 'salary') !== false ||
                stripos($line, 'Unexpected') !== false ||
                stripos($line, 'Invalid') !== false ||
                stripos($line, '422') !== false ||
                stripos($line, 'alary') !== false ||
                stripos($line, 'oliday') !== false;
        });
        if (empty($payrollLines)) {
            echo "<p>No payroll-related entries in last 100 lines</p>";
            // Show last 20 lines anyway
            $last20 = array_slice($lastLines, -20);
            echo "<pre style='font-size:9px;max-height:300px;overflow:auto'>" . htmlspecialchars(implode('', $last20)) . "</pre>";
        } else {
            echo "<pre style='font-size:9px;max-height:400px;overflow:auto'>" . htmlspecialchars(implode('', $payrollLines)) . "</pre>";
        }
    }
}

// Also check the PHP error log
$phpErrorLog = ini_get('error_log');
echo "<h3>PHP Error Log: " . ($phpErrorLog ?: 'default') . "</h3>";

// Check the application log directory structure
echo "<h3>Log Directories</h3>";
$dirs = [
    __DIR__ . '/../src/log',
    __DIR__ . '/../var/log',
    __DIR__ . '/../src/cache/log',
    '/home/u118669189/.logs',
];
foreach ($dirs as $d) {
    if (is_dir($d)) {
        $files = glob($d . '/*');
        echo "<p><b>$d</b> (" . count($files) . " files)</p><ul>";
        foreach ($files as $f) {
            $size = is_file($f) ? filesize($f) : 'DIR';
            $mod = date('Y-m-d H:i', filemtime($f));
            echo "<li>" . basename($f) . " ($size bytes, $mod)</li>";
        }
        echo "</ul>";
    }
}
