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

namespace XHRM\Core\Registration\Helper;

use XHRM\Config\Config;
use XHRM\ORM\Doctrine;
use PDO;

class SystemConfigurationHelper
{
    /**
     * @return array
     */
    public function getSystemDetailsAsArray(): array
    {
        return [
            'os' => $this->getOsDetails(),
            'php' => $this->getPhpDetails(),
            'mysql' => $this->getMySqlDetails(),
            'server' => $this->getServerDetails(),
            'ohrm' => $this->getOHRMDetails(),
        ];
    }

    /**
     * @return array
     */
    public function getOsDetails(): array
    {
        return [
            'os' => php_uname('s'),
            'release_name' => php_uname('r'),
            'version_info' => php_uname('v'),
        ];
    }

    /**
     * @return array
     */
    public function getPhpDetails(): array
    {
        return [
            'version' => phpversion()
        ];
    }

    /**
     * @return string|null
     */
    public function getServerDetails()
    {
        return $_SERVER['SERVER_SOFTWARE'] ?? null;
    }

    /**
     * @return string[]
     */
    public function getMySqlDetails(): array
    {
        return [
            'client_version' => 'Not captured',
            'server_version' => $this->getMySqlServerVersion(),
            'conn_type' => 'Not captured',
        ];
    }

    /**
     * @return array
     */
    public function getOHRMDetails(): array
    {
        return [
            'version' => Config::PRODUCT_VERSION,
        ];
    }

    /**
     * @return false|string
     */
    public function getSystemDetailsAsJson()
    {
        return json_encode($this->getSystemDetailsAsArray());
    }

    /**
     * Return MySQL server version
     * @return string
     */
    public function getMySqlServerVersion(): string
    {
        return Doctrine::getEntityManager()
            ->getConnection()
            ->getNativeConnection()
            ->getAttribute(PDO::ATTR_SERVER_VERSION) ?? '';
    }
}
