<?php
/**
 * Diagnostic: Check DB tables for Password Manager
 * DELETE THIS FILE after debugging!
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>\n";

$base = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm';
$confFile = $base . '/lib/confs/Conf.php';

// Capture constants defined BEFORE loading Conf.php
$before = get_defined_constants();
require_once $confFile;
$after = get_defined_constants();

// Find new constants added by Conf.php
$new = array_diff_key($after, $before);
$xhrmConsts = array_filter($new, fn($k) => stripos($k, 'xhrm') !== false || stripos($k, 'db') !== false || stripos($k, 'conf') !== false, ARRAY_FILTER_USE_KEY);

echo "=== Constants defined by Conf.php ===\n";
foreach ($xhrmConsts as $k => $v) {
    // Mask password
    if (stripos($k, 'pass') !== false || stripos($k, 'pwd') !== false) {
        echo "$k = ***\n";
    } else {
        echo "$k = $v\n";
    }
}

echo "\n=== All new constants (non-PHP builtins) ===\n";
foreach ($new as $k => $v) {
    if (strpos($k, 'E_') === 0 || strpos($k, 'PHP_') === 0 || strpos($k, 'T_') === 0)
        continue;
    if (stripos($k, 'pass') !== false || stripos($k, 'pwd') !== false) {
        echo "$k = ***\n";
    } else {
        echo "$k = $v\n";
    }
}
echo "</pre>";
