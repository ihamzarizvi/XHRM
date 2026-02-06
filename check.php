<?php
// VERSION: 2026-02-06 08:45
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Asset Check (v1.5)</h1>";

$assets = [
    'web/dist/css/app.css',
    'web/dist/js/app.js',
    'web/dist/js/chunk-vendors.js',
    'web/dist/css/chunk-vendors.css'
];

echo "<ul>";
foreach ($assets as $asset) {
    $fullPath = __DIR__ . '/' . $asset;
    echo "<li><b>$asset:</b> ";
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        echo "<span style='color:green'>FOUND</span> ($size bytes, perms: $perms)";
    } else {
        echo "<span style='color:red'>NOT FOUND</span>";
    }
    echo "</li>";
}
echo "</ul>";

echo "<h3>Directory Check</h3>";
$dir = __DIR__ . '/web/dist';
if (is_dir($dir)) {
    echo "<p>web/dist contents:</p><pre>";
    print_r(scandir($dir));
    echo "</pre>";
} else {
    echo "<p style='color:red'>web/dist directory does not exist!</p>";
}
?>