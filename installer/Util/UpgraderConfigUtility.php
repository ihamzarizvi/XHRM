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

namespace XHRM\Installer\Util;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use XHRM\Installer\Exception\SystemCheckException;
use XHRM\Installer\Util\Dto\DatabaseConnectionWrapper;

class UpgraderConfigUtility
{
    /**
     * @return bool
     */
    public function checkDatabaseStatus(): bool
    {
        return $this->getSchemaManager()->tablesExist(['ohrm_upgrade_status']);
    }

    /**
     * @return DBALConnection
     */
    private function getConnection(): DBALConnection
    {
        return Connection::getConnection();
    }

    /**
     * @return AbstractSchemaManager
     * @throws Exception
     */
    private function getSchemaManager(): AbstractSchemaManager
    {
        return $this->getConnection()->createSchemaManager();
    }

    /**
     * @throws SystemCheckException
     */
    public function checkDatabaseConnection(): void
    {
        $connection = DatabaseConnectionWrapper::establishConnection(fn () => $this->getConnection());
        if ($connection->hasError()) {
            throw new SystemCheckException($connection->getErrorMessage());
        }

        if ($this->checkDatabaseStatus()) {
            throw new SystemCheckException('Failed to Proceed: Interrupted Database');
        }
    }
}
