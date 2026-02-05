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

namespace XHRM\Admin\Controller;

use XHRM\Core\Authorization\Controller\CapableViewController;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Helper\VueControllerHelper;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Framework\Http\Request;

abstract class BaseAdminController extends AbstractVueController implements CapableViewController
{
    use UserRoleManagerTrait;

    /**
     * @return string[]
     */
    protected function getDataGroupsForCapabilityCheck(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function isCapable(Request $request): bool
    {
        $permission = $this->getUserRoleManagerHelper()->getEntityIndependentDataGroupPermissions(
            $this->getDataGroupsForCapabilityCheck()
        );
        return $permission->canRead();
    }

    /**
     * Sets the data group permissions into the Vue context
     *
     * @param array $dataGroups
     */
    protected function setPermissionsForController(array $dataGroups)
    {
        $permissions = $this->getUserRoleManagerHelper()
                            ->geEntityIndependentDataGroupPermissionCollection($dataGroups);
        $this->getContext()->set(
            VueControllerHelper::PERMISSIONS,
            $permissions->toArray()
        );
    }
}

