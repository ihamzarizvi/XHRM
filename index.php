<?php

/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.orangehrm.com
 *
 * XHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * XHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

include_once('./src/config/log_settings.php');

use XHRM\Config\Config;
use XHRM\Framework\Framework;
use XHRM\Framework\Http\RedirectResponse;
use XHRM\Framework\Http\Request;
use Symfony\Component\ErrorHandler\Debug;

require realpath(__DIR__ . '/vendor/autoload.php');

$env = 'prod';
$debug = 'prod' !== $env;

if ($debug) {
    umask(0000);
    Debug::enable();
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

$kernel = new Framework($env, $debug);
$request = Request::createFromGlobals();

if (Config::isInstalled()) {
    $response = $kernel->handleRequest($request);
} else {
    $response = new RedirectResponse('./installer/index.php');
}

$response->send();
$kernel->terminate($request, $response);

