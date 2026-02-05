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

namespace XHRM\Dashboard\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Helper\VueControllerHelper;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Core\Vue\Component;
use XHRM\Framework\Http\Request;

class DashboardController extends AbstractVueController
{
    use AuthUserTrait;
    use ServiceContainerTrait;
    use UserRoleManagerTrait;

    public function preRender(Request $request): void
    {
        $component = new Component('view-dashboard');
        $this->setComponent($component);

        $dataGroups = [
            'dashboard_subunit_widget',
            'dashboard_location_widget',
            'dashboard_leave_widget',
            'dashboard_time_widget',
            'dashboard_buzz_widget',
            'dashboard_employees_on_leave_today_config',
        ];

        $permissions = $this->getUserRoleManagerHelper()
            ->geEntityIndependentDataGroupPermissionCollection($dataGroups);

        $this->getContext()->set(
            VueControllerHelper::PERMISSIONS,
            $permissions->toArray()
        );
    }
}
