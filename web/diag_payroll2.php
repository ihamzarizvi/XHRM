<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Find the 500 Error</h2>";

// Read the error_log_xsofty_com file
$logPath = '/home/u118669189/.logs/error_log_xsofty_com';
if (file_exists($logPath)) {
    $size = filesize($logPath);
    echo "<h3>error_log_xsofty_com (last 10KB)</h3>";
    echo "<p>Size: $size, Modified: " . date('Y-m-d H:i:s', filemtime($logPath)) . "</p>";
    $fp = fopen($logPath, 'r');
    fseek($fp, max(0, $size - 10000));
    $content = fread($fp, 10000);
    fclose($fp);
    echo "<pre style='background:#111;color:#0f0;padding:10px;font-size:11px;max-height:500px;overflow:auto;white-space:pre-wrap'>" . htmlspecialchars($content) . "</pre>";
}

// Check XHRM's own log
$xhrmLog = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm/src/log/xhrm.log';
if (file_exists($xhrmLog)) {
    $size = filesize($xhrmLog);
    echo "<h3>xhrm.log (last 5KB)</h3>";
    echo "<p>Size: $size, Modified: " . date('Y-m-d H:i:s', filemtime($xhrmLog)) . "</p>";
    $fp = fopen($xhrmLog, 'r');
    fseek($fp, max(0, $size - 5000));
    $content = fread($fp, 5000);
    fclose($fp);
    echo "<pre style='background:#111;color:#f90;padding:10px;font-size:11px;max-height:500px;overflow:auto;white-space:pre-wrap'>" . htmlspecialchars($content) . "</pre>";
}

// Manually test: try to init the framework and catch the error
echo "<h3>Direct Framework Test:</h3>";
try {
    $baseDir = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm';
    require_once $baseDir . '/vendor/autoload.php';
    require_once $baseDir . '/lib/confs/Conf.php';

    // Try to load a payroll controller class
    $testClasses = [
        'XHRM\\Payroll\\Controller\\GeneratePayrollController',
        'XHRM\\Payroll\\Controller\\HolidayCalendarController',
        'XHRM\\Payroll\\Service\\PayrollService',
        'XHRM\\Payroll\\Dao\\PayrollDao',
        'XHRM\\Payroll\\entity\\SalaryComponent',
    ];
    foreach ($testClasses as $cls) {
        try {
            if (class_exists($cls, true)) {
                echo "<p style='color:green'>✓ $cls loaded OK</p>";
            } else {
                echo "<p style='color:red'>✗ $cls NOT FOUND</p>";
            }
        } catch (Throwable $e) {
            echo "<p style='color:red'>✗ $cls ERROR: " . $e->getMessage() . "</p>";
        }
    }

    // Try to instantiate the controller
    echo "<h3>Controller Instantiation Test:</h3>";
    try {
        $ctrl = new \XHRM\Payroll\Controller\GeneratePayrollController();
        echo "<p style='color:green'>✓ GeneratePayrollController instantiated successfully</p>";
    } catch (Throwable $e) {
        echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }

} catch (Throwable $e) {
    echo "<p style='color:red'>Framework Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
