<?php

/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.XHRM.com
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

use XHRM\Authentication\Dto\UserCredential;
use XHRM\Config\Config;
use XHRM\Framework\Http\Session\MemorySessionStorage;
use XHRM\Framework\Http\Session\Session;
use XHRM\Framework\ServiceContainer;
use XHRM\Framework\Services;
use XHRM\Installer\Framework\HttpKernel;
use XHRM\Installer\Util\AppSetupUtility;
use XHRM\Installer\Util\StateContainer;
use Symfony\Component\Yaml\Yaml;
use XHRM\Framework\Http\Request;
use XHRM\Installer\Controller\Installer\Api\InstallerDataRegistrationAPI;

$pathToAutoload = realpath(__DIR__ . '/../src/vendor/autoload.php');

$deprecatedNote = "
This CLI installer is deprecated. Instead use following commands\n
$ cd %s
$ php installer/console install:on-new-database
# OR
$ php installer/console install:on-existing-database\n
";

echo(sprintf($deprecatedNote, realpath(__DIR__ . '/../')));

$errorMessage = "
Cannot find composer dependencies.
Run below command and try again;\n
$ cd %s
$ composer install -d src
";

if ($pathToAutoload === false) {
    die(sprintf($errorMessage, realpath(__DIR__ . '/../')));
}

require_once $pathToAutoload;

if (Config::isInstalled()) {
    die("This system already installed.\n");
}

new HttpKernel('prod', false);
$sessionStorage = new MemorySessionStorage();
ServiceContainer::getContainer()->set(Services::SESSION_STORAGE, $sessionStorage);
$session = new Session($sessionStorage);
$session->start();
ServiceContainer::getContainer()->set(Services::SESSION, $session);

$cliConfig = Yaml::parseFile(realpath(__DIR__ . '/cli_install_config.yaml'));

if ($cliConfig['license']['agree'] != 'y') {
    $licenseFilePath = realpath(__DIR__ . '/../LICENSE');
    echo "For continue installation need to accept XHRM license agreement. It is available in '$licenseFilePath'.";
    die;
}
echo "Agreed to license from config file\n";

$dbType = $cliConfig['database']['isExistingDatabase'] == 'n' ? AppSetupUtility::INSTALLATION_DB_TYPE_NEW : AppSetupUtility::INSTALLATION_DB_TYPE_EXISTING;
$dbHost = $cliConfig['database']['hostName'];
$dbPort = $cliConfig['database']['hostPort'];
$dbUser = $cliConfig['database']['privilegedDatabaseUser'];
$dbPassword = $cliConfig['database']['privilegedDatabasePassword'];
$dbName = $cliConfig['database']['databaseName'];
$useSameDbUserForXHRM = $cliConfig['database']['useSameDbUserForXHRM'] == 'y';
$enableDataEncryption = $cliConfig['database']['enableDataEncryption'] == 'y';

$organizationName = $cliConfig['organization']['name'];
$countryCode = $cliConfig['organization']['country'];

$adminUsername = $cliConfig['admin']['adminUserName'];
$adminPassword = $cliConfig['admin']['adminPassword'];

$firstName = $cliConfig['admin']['adminEmployeeFirstName'];
$lastName = $cliConfig['admin']['adminEmployeeLastName'];
$email = $cliConfig['admin']['workEmail'];
$contact = $cliConfig['admin']['contactNumber'];


if ($dbType === AppSetupUtility::INSTALLATION_DB_TYPE_NEW) {
    $ohrmDbUser = $dbUser;
    $ohrmDbPassword = $dbPassword;
    if (!$useSameDbUserForXHRM) {
        $ohrmDbUser = $cliConfig['database']['XHRMDatabaseUser'];
        $ohrmDbPassword = $cliConfig['database']['XHRMDatabasePassword'];
    }

    StateContainer::getInstance()->storeDbInfo(
        $dbHost,
        $dbPort,
        new UserCredential($dbUser, $dbPassword),
        $dbName,
        new UserCredential($ohrmDbUser, $ohrmDbPassword)
    );
    StateContainer::getInstance()->setDbType(AppSetupUtility::INSTALLATION_DB_TYPE_NEW);
} else {
    // `existing` database
    StateContainer::getInstance()->storeDbInfo(
        $dbHost,
        $dbPort,
        new UserCredential($dbUser, $dbPassword),
        $dbName
    );
    StateContainer::getInstance()->setDbType(AppSetupUtility::INSTALLATION_DB_TYPE_EXISTING);
}

// Instance data
StateContainer::getInstance()->storeInstanceData($organizationName, $countryCode, null, null);

// Admin user
StateContainer::getInstance()->storeAdminUserData(
    $firstName,
    $lastName,
    $email,
    new UserCredential($adminUsername, $adminPassword),
    $contact
);

StateContainer::getInstance()->storeRegConsent($cliConfig['admin']['registrationConsent']);

$appSetupUtility = new AppSetupUtility();
echo "Database creation\n";
$appSetupUtility->createDatabase();
echo "Applying database changes\n";
$appSetupUtility->runMigrations('3.3.3', Config::PRODUCT_VERSION);
echo "Instance creation & Admin user creation\n";
$appSetupUtility->insertSystemConfiguration();
echo "Create XHRM database user\n";
$appSetupUtility->createDBUser();
echo "Creating configuration files\n";
$appSetupUtility->writeConfFile();
if ($enableDataEncryption) {
    $appSetupUtility->writeKeyFile();
}

$request = new Request();
$request->setMethod(Request::METHOD_POST);
(new InstallerDataRegistrationAPI())->handle($request);

echo "Done\n";
