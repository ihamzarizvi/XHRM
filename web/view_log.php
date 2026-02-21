<?php
header('Content-Type: text/plain');

// This is called from the root index.php so __DIR__ path traversal goes
// 5 levels up to the project root, then appends /web/api_debug_log.json
// But let's find it wherever it may be

$possibleLogs = [
    __DIR__ . '/api_debug_log.json',     // web/ dir
    __DIR__ . '/../api_debug_log.json',   // project root
    '/tmp/api_debug_log.json',
    '/home/u118669189/api_debug_log.json',
];

foreach ($possibleLogs as $log) {
    echo "Checking: $log\n";
    if (file_exists($log)) {
        echo "FOUND! Size: " . filesize($log) . " bytes\n";
        echo file_get_contents($log) . "\n\n";
    } else {
        echo "Not found\n\n";
    }
}

// Also verify the Validator.php debug code path
require_once __DIR__ . '/../vendor/autoload.php';
$ref = new ReflectionClass('XHRM\\Core\\Api\\V2\\Validator\\Validator');
$validatorFile = $ref->getFileName();
$content = file_get_contents($validatorFile);

// Extract the debug log path from the code
preg_match('/debugLogFile.*?\'([^\']+)\'/s', $content, $match);
echo "Debug log path expression in code: " . ($match[1] ?? 'NOT FOUND') . "\n";

// Simulate the path resolution as the Validator would do it
$validatorDir = dirname($validatorFile);
$debugLogFile = realpath($validatorDir . '/../../../../../') . '/web/api_debug_log.json';
echo "Resolved debug log path from Validator dir: $debugLogFile\n";
echo "Path would resolve to dir: " . realpath($validatorDir . '/../../../../../') . "\n";
echo "File would exist at: " . (file_exists($debugLogFile) ? 'YES' : 'NO') . "\n";
