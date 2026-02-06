<?php
// VERSION: 2026-02-06 08:55
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM File Search (v1.6)</h1>";

function search_file($dir, $filename)
{
    $it = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->getFilename() === $filename) {
            echo "<li>FOUND: " . $file->getPathname() . " (" . filesize($file->getPathname()) . " bytes)</li>";
        }
    }
}

echo "<ul>";
try {
    search_file(__DIR__, "index.umd.js");
} catch (Throwable $e) {
    echo "<li>Error: " . $e->getMessage() . "</li>";
}
echo "</ul>";

echo "<h3>Check for common library paths:</h3>";
$paths = [
    'web/dist/js/index.umd.js',
    'web/index.umd.js',
    'lib/confs/index.umd.js'
];
foreach ($paths as $p) {
    echo "<li>$p: " . (file_exists(__DIR__ . '/' . $p) ? "YES" : "NO") . "</li>";
}
?>