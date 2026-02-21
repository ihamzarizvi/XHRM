<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>XHRM Error Log</h2>";

$logPath = '/home/u118669189/.logs/error_log_mimar_xsofty_com';

if (file_exists($logPath) && is_readable($logPath)) {
    $size = filesize($logPath);
    echo "<p>File: $logPath ($size bytes, modified: " . date('Y-m-d H:i:s', filemtime($logPath)) . ")</p>";

    $fp = fopen($logPath, 'r');
    $offset = max(0, $size - 15000);
    fseek($fp, $offset);
    $content = fread($fp, 15000);
    fclose($fp);

    echo "<pre style='background:#111;color:#0f0;padding:10px;font-size:11px;max-height:800px;overflow:auto;white-space:pre-wrap'>";
    echo htmlspecialchars($content);
    echo "</pre>";
} else {
    echo "<p style='color:red'>Cannot read: $logPath</p>";
    echo "<p>Exists: " . (file_exists($logPath) ? 'yes' : 'no') . "</p>";
    echo "<p>Readable: " . (is_readable($logPath) ? 'yes' : 'no') . "</p>";

    // Try to find any error_log files in .logs
    $logsDir = '/home/u118669189/.logs';
    if (is_dir($logsDir)) {
        echo "<p>Contents of $logsDir:</p><ul>";
        foreach (scandir($logsDir) as $f) {
            if ($f !== '.' && $f !== '..') {
                $path = $logsDir . '/' . $f;
                echo "<li>$f (" . filesize($path) . " bytes, " . date('Y-m-d H:i:s', filemtime($path)) . ")</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p>Directory $logsDir does not exist</p>";
    }
}

// Also check Symfony-level logs
echo "<h3>Symfony Framework Log:</h3>";
$sfLogDir = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm/src/cache';
if (is_dir($sfLogDir)) {
    // Look for any PHP/exception log files
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sfLogDir));
    foreach ($iter as $file) {
        if ($file->isFile() && preg_match('/\.(log|txt)$/i', $file->getFilename())) {
            $mtime = $file->getMTime();
            if (time() - $mtime < 7200) { // 2 hours
                echo "<p><b>" . $file->getPathname() . "</b> (modified: " . date('H:i:s', $mtime) . ")</p>";
                $content = file_get_contents($file->getPathname());
                $lastChunk = substr($content, -3000);
                echo "<pre style='background:#220;color:#ff0;padding:8px;font-size:11px;max-height:300px;overflow:auto'>";
                echo htmlspecialchars($lastChunk);
                echo "</pre>";
            }
        }
    }
}
