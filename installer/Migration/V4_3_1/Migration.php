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

namespace XHRM\Installer\Migration\V4_3_1;

use Doctrine\DBAL\Types\Types;
use XHRM\Installer\Util\V1\AbstractMigration;

class Migration extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        if ($this->getSchemaManager()->tablesExist('ohrm_reset_password')) {
            $this->getSchemaManager()->dropTable('ohrm_reset_password');
        }
        $this->getSchemaHelper()->createTable('ohrm_reset_password')
            ->addColumn('id', Types::BIGINT, ['Unsigned' => true, 'Autoincrement' => true])
            ->addColumn('reset_email', Types::STRING, ['Length' => 60, 'Notnull' => true])
            ->addColumn('reset_request_date', Types::DATETIMETZ_MUTABLE, ['Notnull' => true])
            ->addColumn('reset_code', Types::STRING, ['Length' => 200, 'Notnull' => true])
            ->setPrimaryKey(['id'])
            ->create();
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return '4.3.1';
    }
}
