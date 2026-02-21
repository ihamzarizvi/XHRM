<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Clear Doctrine Cache</h2>";

$cacheDir = __DIR__ . '/../src/cache';

if (is_dir($cacheDir)) {
    // Clear doctrine metadata cache
    $metadataDir = $cacheDir . '/doctrine_metadata';
    if (is_dir($metadataDir)) {
        clearDir($metadataDir);
        echo "<p style='color:green'>✓ Cleared doctrine_metadata cache</p>";
    } else {
        echo "<p>No doctrine_metadata dir found</p>";
    }

    // Clear doctrine queries cache
    $queriesDir = $cacheDir . '/doctrine_queries';
    if (is_dir($queriesDir)) {
        clearDir($queriesDir);
        echo "<p style='color:green'>✓ Cleared doctrine_queries cache</p>";
    } else {
        echo "<p>No doctrine_queries dir found</p>";
    }

    // Clear doctrine ORM proxies
    $proxyDir = $cacheDir . '/doctrine/orm/Proxies';
    if (is_dir($proxyDir)) {
        clearDir($proxyDir);
        echo "<p style='color:green'>✓ Cleared doctrine proxy cache</p>";
    }

    // Also check for other cache dirs
    $dirs = glob($cacheDir . '/*', GLOB_ONLYDIR);
    echo "<p>Remaining cache dirs:</p><ul>";
    foreach ($dirs as $d) {
        $count = count(glob($d . '/*'));
        echo "<li>" . basename($d) . " ($count items)</li>";
    }
    echo "</ul>";

} else {
    echo "<p style='color:red'>Cache directory not found: $cacheDir</p>";
}

// Also check for separate src/config/proxy directory
$proxyDir2 = __DIR__ . '/../src/config/proxy';
if (is_dir($proxyDir2)) {
    echo "<p>Proxy dir2 exists: $proxyDir2</p>";
    $files = glob($proxyDir2 . '/__CG__*');
    echo "<p>Proxy files: " . count($files) . "</p>";
}

echo "<p style='color:green'><b>Cache cleared! Reload payroll pages to test.</b></p>";

function clearDir($dir)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $f) {
        if ($f->isDir()) {
            rmdir($f->getRealPath());
        } else {
            unlink($f->getRealPath());
        }
    }
}
