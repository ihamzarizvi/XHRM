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

namespace XHRM\Installer\Migration\V4_1;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use XHRM\Installer\Util\V1\AbstractMigration;

class Migration extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->getSchemaHelper()->changeColumn(
            'hs_hr_config',
            'value',
            ['Type' => Type::getType(Types::TEXT), 'Notnull' => true]
        );
        $this->getConfigHelper()->setConfigValue(
            'open_source_integrations',
            '<xml><integrations></integrations></xml>'
        );
        $this->getConfigHelper()->setConfigValue('authentication.status', 'Enable');
        $this->getConfigHelper()->setConfigValue('authentication.enforce_password_strength', 'on');
        $this->getConfigHelper()->setConfigValue('authentication.default_required_password_strength', 'strong');
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return '4.1';
    }
}
