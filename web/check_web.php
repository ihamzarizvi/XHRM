<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Web-Folder Diagnostic</h1>";
echo "<p>✅ If you see this, your domain is pointing to the <b>web/</b> folder.</p>";

$parent_autoloader = __DIR__ . '/../vendor/autoload.php';

if (file_exists($parent_autoloader)) {
    echo "<p style='color:green'>✅ Autoloader found in parent directory!</p>";
    try {
        require $parent_autoloader;
        echo "<p style='color:green'>✅ Autoloader loaded successfully!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Error loading autoloader: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>❌ Parent Autoloader NOT found. Current dir: " . __DIR__ . "</p>";
}

echo "<p><b>PHP Version:</b> " . phpversion() . "</p>";
?>