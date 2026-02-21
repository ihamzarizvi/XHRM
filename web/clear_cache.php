<?php
header('Content-Type: text/plain');

// 1. Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared\n";
}

// 2. Clear the log
file_put_contents(__DIR__ . '/api_debug_log.json', '[]');
echo "Log cleared\n";

// 3. Verify the Validator.php now has debug logging
require_once __DIR__ . '/../vendor/autoload.php';
$reflector = new ReflectionClass('XHRM\\Core\\Api\\V2\\Validator\\Validator');
$content = file_get_contents($reflector->getFileName());
echo "Validator file: " . $reflector->getFileName() . "\n";
echo "Has debug logging: " . (strpos($content, 'api_debug_log') !== false ? 'YES' : 'NO') . "\n";
echo "File size: " . filesize($reflector->getFileName()) . "\n";
echo "Modified: " . date('Y-m-d H:i:s', filemtime($reflector->getFileName())) . "\n";
echo "\nReady! Now visit a payroll page then check /web/view_log.php\n";
