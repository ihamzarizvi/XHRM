<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Find and load Conf
$confPaths = [
    __DIR__ . '/../lib/confs/Conf.php',
    __DIR__ . '/../src/lib/confs/Conf.php',
];
foreach ($confPaths as $cp) {
    if (file_exists($cp)) {
        require_once $cp;
        break;
    }
}

$conf = new Conf();
echo "<pre>";
print_r(get_object_vars($conf));
echo "</pre>";
