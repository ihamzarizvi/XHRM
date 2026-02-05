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

namespace XHRM\Leave\Controller\Traits;

use LogicException;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Helper\VueControllerHelper;
use XHRM\Core\Traits\UserRoleManagerTrait;

trait PermissionTrait
{
    use UserRoleManagerTrait;

    /**
     * @param array $dataGroups
     * @param int|null $empNumber
     */
    protected function setPermissionsForEmployee(array $dataGroups, ?int $empNumber = null)
    {
        $permissions = $this->getUserRoleManagerHelper()->getDataGroupPermissionCollectionForEmployee(
            $dataGroups,
            $empNumber
        );
        if (!$this instanceof AbstractVueController) {
            throw new LogicException(
                PermissionTrait::class . ' should use in instanceof' . AbstractVueController::class
            );
        }
        $this->getContext()->set(
            VueControllerHelper::PERMISSIONS,
            $permissions->toArray()
        );
    }
}

