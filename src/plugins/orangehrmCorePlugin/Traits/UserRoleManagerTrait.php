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

namespace XHRM\Core\Traits;

use XHRM\Core\Authorization\Helper\UserRoleManagerHelper;
use XHRM\Core\Authorization\Manager\AbstractUserRoleManager;
use XHRM\Core\Authorization\Manager\BasicUserRoleManager;
use XHRM\Framework\Services;

trait UserRoleManagerTrait
{
    use ServiceContainerTrait;

    /**
     * @return AbstractUserRoleManager|BasicUserRoleManager
     */
    protected function getUserRoleManager(): AbstractUserRoleManager
    {
        return $this->getContainer()->get(Services::USER_ROLE_MANAGER);
    }

    /**
     * @return UserRoleManagerHelper
     */
    protected function getUserRoleManagerHelper(): UserRoleManagerHelper
    {
        return $this->getContainer()->get(Services::USER_ROLE_MANAGER_HELPER);
    }
}
