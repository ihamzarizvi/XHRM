<?php
/**
 * Read the actual XHRM error log
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>XHRM Payroll Error Log Reader</h2>";

// The real error log is at this path
$logPath = '/home/u118669189/.logs/error_log_mimar_xsofty_com';

if (file_exists($logPath) && is_readable($logPath)) {
    $size = filesize($logPath);
    echo "<p>Log file: $logPath ($size bytes, modified: " . date('Y-m-d H:i:s', filemtime($logPath)) . ")</p>";

    // Read last 10KB for recent errors
    $fp = fopen($logPath, 'r');
    $offset = max(0, $size - 10000);
    fseek($fp, $offset);
    $content = fread($fp, 10000);
    fclose($fp);

    echo "<pre style='background:#111;color:#0f0;padding:10px;font-size:11px;max-height:600px;overflow:auto'>";
    echo htmlspecialchars($content);
    echo "</pre>";
} else {
    echo "<p style='color:red'>Log file not found or not readable: $logPath</p>";
}

// Also check the XHRM framework cache directory for any Symfony errors
echo "<h3>Checking Symfony cache for errors:</h3>";
$cacheDir = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm/src/cache';
if (is_dir($cacheDir)) {
    // Check for any error files
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $recentFiles = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && time() - $file->getMTime() < 3600) {
            $recentFiles[] = $file->getPathname() . ' (' . $file->getSize() . ' bytes, ' . date('H:i:s', $file->getMTime()) . ')';
        }
    }

    if (!empty($recentFiles)) {
        echo "<p>Recently modified files in cache:</p><ul>";
        foreach (array_slice($recentFiles, 0, 20) as $f) {
            echo "<li>$f</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No recently modified files in cache directory</p>";
    }
}

// Check the xhrm.log for recent entries
$xhrmLog = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm/src/log/xhrm.log';
if (file_exists($xhrmLog)) {
    $size = filesize($xhrmLog);
    echo "<h3>XHRM Log (last entries):</h3>";
    echo "<p>$xhrmLog ($size bytes, modified: " . date('Y-m-d H:i:s', filemtime($xhrmLog)) . ")</p>";
    $fp = fopen($xhrmLog, 'r');
    $offset = max(0, $size - 5000);
    fseek($fp, $offset);
    $content = fread($fp, 5000);
    fclose($fp);
    echo "<pre style='background:#111;color:#f90;padding:10px;font-size:11px;max-height:400px;overflow:auto'>";
    echo htmlspecialchars($content);
    echo "</pre>";
}
