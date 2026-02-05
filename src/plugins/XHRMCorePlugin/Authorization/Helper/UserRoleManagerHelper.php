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

namespace XHRM\Core\Authorization\Helper;

use XHRM\Core\Authorization\Dto\DataGroupPermissionCollection;
use XHRM\Core\Authorization\Dto\DataGroupPermissionFilterParams;
use XHRM\Core\Authorization\Dto\ResourcePermission;
use XHRM\Core\Authorization\Manager\AbstractUserRoleManager;
use XHRM\Core\Authorization\Manager\BasicUserRoleManager;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Entity\Employee;
use XHRM\Framework\Services;

class UserRoleManagerHelper
{
    use ServiceContainerTrait;

    /**
     * @return AbstractUserRoleManager|BasicUserRoleManager
     */
    private function getUserRoleManager(): AbstractUserRoleManager
    {
        return $this->getContainer()->get(Services::USER_ROLE_MANAGER);
    }

    /**
     * Returns the data group permissions without considering any employee or other entity
     *
     * @param string[]|string $dataGroupNames
     *
     * @return ResourcePermission
     */
    public function getEntityIndependentDataGroupPermissions($dataGroupNames): ResourcePermission
    {
        return $this->getUserRoleManager()->getDataGroupPermissions($dataGroupNames, [], [], false, []);
    }

    /**
     * Returns the data group permission collection without considering any employee or other entity
     *
     * @param array $dataGroups
     * @return DataGroupPermissionCollection
     */
    public function geEntityIndependentDataGroupPermissionCollection(array $dataGroups): DataGroupPermissionCollection
    {
        $dataGroupPermissionFilterParams = new DataGroupPermissionFilterParams();
        $dataGroupPermissionFilterParams->setDataGroups($dataGroups);
        return $this->getUserRoleManager()->getDataGroupPermissionCollection($dataGroupPermissionFilterParams);
    }

    /**
     * @param string|string[] $dataGroupName
     * @param int|null $empNumber
     * @return ResourcePermission
     */
    public function getDataGroupPermissionsForEmployee($dataGroupName, ?int $empNumber = null): ResourcePermission
    {
        return $this->getUserRoleManager()->getDataGroupPermissions(
            $dataGroupName,
            [],
            [],
            $this->isSelfByEmpNumber($empNumber),
            is_null($empNumber) ? [] : [Employee::class => $empNumber]
        );
    }

    /**
     * @param array $dataGroups
     * @param int|null $empNumber
     * @return DataGroupPermissionCollection
     */
    public function getDataGroupPermissionCollectionForEmployee(
        array $dataGroups,
        ?int $empNumber = null
    ): DataGroupPermissionCollection {
        $dataGroupPermissionFilterParams = new DataGroupPermissionFilterParams();
        $dataGroupPermissionFilterParams->setDataGroups($dataGroups);
        $dataGroupPermissionFilterParams->setEntities(is_null($empNumber) ? [] : [Employee::class => $empNumber]);
        $dataGroupPermissionFilterParams->setSelfPermissions($this->isSelfByEmpNumber($empNumber));
        return $this->getUserRoleManager()->getDataGroupPermissionCollection($dataGroupPermissionFilterParams);
    }

    /**
     * @param int|null $empNumber
     * @return bool
     */
    public function isSelfByEmpNumber(?int $empNumber = null): bool
    {
        $loggedInEmpNumber = $this->getUserRoleManager()->getUser()->getEmpNumber();
        return ($loggedInEmpNumber === $empNumber) && null !== $empNumber;
    }

    /**
     * @param int|null $empNumber
     * @return bool
     */
    public function isEmployeeAccessible(?int $empNumber): bool
    {
        return $this->getUserRoleManager()->isEntityAccessible(Employee::class, $empNumber) ||
            $this->isSelfByEmpNumber($empNumber);
    }
}

