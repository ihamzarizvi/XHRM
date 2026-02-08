<?php
// setup_log.php - Run this once to create log directory and file

$root = __DIR__;
$logDir = $root . '/log';
$logFile = $logDir . '/ohrm.log';

echo "<h1>Log Directory Setup</h1>";

// Create log directory if it doesn't exist
if (!file_exists($logDir)) {
    if (mkdir($logDir, 0777, true)) {
        echo "<p style='color:green'>✓ Created log directory: $logDir</p>";
        chmod($logDir, 0777);
    } else {
        echo "<p style='color:red'>✗ Failed to create log directory: $logDir</p>";
        echo "<p>Error: " . error_get_last()['message'] . "</p>";
    }
} else {
    echo "<p style='color:blue'>Log directory already exists: $logDir</p>";
    chmod($logDir, 0777);
}

// Create log file if it doesn't exist
if (!file_exists($logFile)) {
    if (file_put_contents($logFile, '') !== false) {
        echo "<p style='color:green'>✓ Created log file: $logFile</p>";
        chmod($logFile, 0666);
    } else {
        echo "<p style='color:red'>✗ Failed to create log file: $logFile</p>";
    }
} else {
    echo "<p style='color:blue'>Log file already exists: $logFile</p>";
    chmod($logFile, 0666);
}

// Verify permissions
if (is_writable($logFile)) {
    echo "<p style='color:green'>✓ Log file is writable</p>";
} else {
    echo "<p style='color:orange'>⚠ Log file exists but may not be writable</p>";
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Refresh the <a href='debug_xhrm.php'>debug page</a> to see if logs are now readable</li>";
echo "<li>Try accessing the <a href='auth/login'>login page</a></li>";
echo "<li>If login still fails, check the debug page for error logs</li>";
echo "</ol>";

echo "<p><em>You can delete this file (setup_log.php) after running it once.</em></p>";
