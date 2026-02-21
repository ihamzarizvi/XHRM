<?php
header('Content-Type: text/plain');
echo "=== Validator File Check ===\n\n";

// Check both possible paths
$paths = [
    __DIR__ . '/../src/plugins/XHRMCorePlugin/Api/V2/Validator/Validator.php',
    __DIR__ . '/../vendor/orangehrm/core/plugins/XHRMCorePlugin/Api/V2/Validator/Validator.php',
];

foreach ($paths as $p) {
    if (file_exists($p)) {
        echo "FOUND: $p\n";
        echo "Size: " . filesize($p) . " bytes\n";
        echo "Modified: " . date('Y-m-d H:i:s', filemtime($p)) . "\n";
        $content = file_get_contents($p);
        echo "Has debug logging: " . (strpos($content, 'api_debug_log') !== false ? 'YES' : 'NO') . "\n";
        echo "Has setStrict: " . (strpos($content, 'setStrict') !== false ? 'YES' : 'NO') . "\n\n";
    } else {
        echo "NOT FOUND: $p\n\n";
    }
}

// Also check what the autoloader would resolve
require_once __DIR__ . '/../vendor/autoload.php';
$reflector = new ReflectionClass('XHRM\\Core\\Api\\V2\\Validator\\Validator');
$actualFile = $reflector->getFileName();
echo "Autoloader resolves Validator to: $actualFile\n";
$content = file_get_contents($actualFile);
echo "Has debug logging: " . (strpos($content, 'api_debug_log') !== false ? 'YES' : 'NO') . "\n";
echo "File size: " . filesize($actualFile) . "\n";
echo "Modified: " . date('Y-m-d H:i:s', filemtime($actualFile)) . "\n\n";

// Show first 60 lines of the actual file
$lines = explode("\n", $content);
echo "=== First 60 lines of actual Validator.php ===\n";
for ($i = 0; $i < min(60, count($lines)); $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

// Also check the SalaryComponentAPI location
$reflector2 = new ReflectionClass('XHRM\\Payroll\\Api\\SalaryComponentAPI');
echo "\n\nSalaryComponentAPI resolved to: " . $reflector2->getFileName() . "\n";
$apiContent = file_get_contents($reflector2->getFileName());
echo "Has setStrict(false): " . (strpos($apiContent, 'setStrict(false)') !== false ? 'YES' : 'NO') . "\n";
