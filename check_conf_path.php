<?php
require_once __DIR__ . '/vendor/autoload.php';
use XHRM\Config\Config;

echo "CONF_FILE_PATH: " . Config::get(Config::CONF_FILE_PATH) . "\n";
echo "Is Installed: " . (Config::isInstalled() ? 'YES' : 'NO') . "\n";
