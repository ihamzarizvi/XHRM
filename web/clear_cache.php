<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>OPcache Clear + Verify Deployment</h2>";

// 1. Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p style='color:green'>✓ OPcache cleared!</p>";
} else {
    echo "<p style='color:orange'>OPcache extension not available</p>";
}

// 2. Verify the SalaryComponentAPI has the non-strict fix
$apiFile = __DIR__ . '/../src/plugins/XHRMPayrollPlugin/Api/SalaryComponentAPI.php';
if (file_exists($apiFile)) {
    $content = file_get_contents($apiFile);
    if (strpos($content, 'setStrict(false)') !== false) {
        echo "<p style='color:green'>✓ SalaryComponentAPI has setStrict(false) - FIX IS DEPLOYED</p>";
    } else {
        echo "<p style='color:red'>✗ SalaryComponentAPI does NOT have setStrict(false) - FIX NOT DEPLOYED YET!</p>";
    }
    // Show relevant method
    preg_match('/getValidationRuleForGetAll.*?return \$rules;/s', $content, $matches);
    if ($matches) {
        echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>";
    }
} else {
    echo "<p style='color:red'>File not found: $apiFile</p>";
}

// 3. Verify the HolidayAPI 
$holidayFile = __DIR__ . '/../src/plugins/XHRMPayrollPlugin/Api/HolidayAPI.php';
if (file_exists($holidayFile)) {
    $content = file_get_contents($holidayFile);
    if (strpos($content, 'setStrict(false)') !== false) {
        echo "<p style='color:green'>✓ HolidayAPI has setStrict(false)</p>";
    } else {
        echo "<p style='color:red'>✗ HolidayAPI does NOT have setStrict(false)</p>";
    }
    if (strpos($content, 'Rules::POSITIVE') !== false && strpos($content, "Rules::INT_TYPE") === false) {
        echo "<p style='color:green'>✓ HolidayAPI uses POSITIVE (not INT_TYPE)</p>";
    } else {
        echo "<p style='color:red'>✗ HolidayAPI still uses INT_TYPE</p>";
    }
}

// 4. Check git status on server
echo "<h3>File Modification Times</h3>";
$files = [
    $apiFile,
    $holidayFile,
    __DIR__ . '/../src/plugins/XHRMPayrollPlugin/Api/AttendanceRuleAPI.php',
    __DIR__ . '/../src/plugins/XHRMPayrollPlugin/Api/FinancialYearAPI.php',
];
foreach ($files as $f) {
    if (file_exists($f)) {
        echo "<p>" . basename($f) . ": " . date('Y-m-d H:i:s', filemtime($f)) . "</p>";
    }
}

// 5. Also clear Doctrine caches
$cacheDir = __DIR__ . '/../src/cache';
if (is_dir($cacheDir)) {
    $metaDir = $cacheDir . '/doctrine_metadata';
    $queryDir = $cacheDir . '/doctrine_queries';
    foreach ([$metaDir, $queryDir] as $d) {
        if (is_dir($d)) {
            $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iter as $item) {
                if ($item->isDir())
                    rmdir($item->getRealPath());
                else
                    unlink($item->getRealPath());
            }
            echo "<p style='color:green'>Cleared: " . basename($d) . "</p>";
        }
    }
}

echo "<p style='font-weight:bold'>Done! Reload payroll pages now.</p>";
