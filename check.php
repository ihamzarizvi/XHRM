<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Diagnostic Tool</h1>";

$autoloader = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloader)) {
    echo "<p style='color:green'>✅ Autoloader found at: " . htmlspecialchars($autoloader) . "</p>";
    try {
        require $autoloader;
        echo "<p style='color:green'>✅ Autoloader loaded successfully!</p>";

        if (class_exists('XHRM\Config\Config')) {
            echo "<p style='color:green'>✅ XHRM Core Class found!</p>";
        } else {
            echo "<p style='color:red'>❌ XHRM Core Class NOT found. Check your composer.json autoload paths.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Error loading autoloader: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>❌ Autoloader NOT found at: " . htmlspecialchars($autoloader) . "</p>";
}

echo "<p><b>PHP Version:</b> " . phpversion() . "</p>";
echo "<p><b>Document Root:</b> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><b>Current Script:</b> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
?>