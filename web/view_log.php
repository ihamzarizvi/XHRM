<?php
header('Content-Type: text/plain');
$logFile = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm/src/log/XHRM.log';
if (file_exists($logFile)) {
    // Get last 100 lines
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $tail = array_slice($lines, -80);
    echo implode("\n", $tail);
} else {
    echo "Log file not found\n";
}
