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

namespace XHRM\Leave\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;
use XHRM\Leave\Controller\Traits\PermissionTrait;
use XHRM\Leave\Traits\Service\LeaveTypeServiceTrait;
use XHRM\Pim\Traits\Service\EmployeeServiceTrait;
use XHRM\Leave\Traits\Service\LeavePeriodServiceTrait;

class ViewLeaveEntitlementController extends AbstractVueController
{
    use UserRoleManagerTrait;
    use EmployeeServiceTrait;
    use LeaveTypeServiceTrait;
    use PermissionTrait;
    use LeavePeriodServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('leave-view-entitlement');
        $empNumber = $request->query->get('empNumber');
        if (!is_null($empNumber)) {
            if (!$this->getUserRoleManagerHelper()->isEmployeeAccessible($empNumber)) {
                throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
            }

            $component->addProp(
                new Prop(
                    'employee',
                    Prop::TYPE_OBJECT,
                    $this->getEmployeeService()->getEmployeeAsArray($empNumber)
                )
            );
        }
        $this->addLeaveTypeProp($request, $component);
        $this->addLeavePeriodProp($request, $component);

        $this->setComponent($component);

        // $empNumber can be null
        $this->setPermissionsForEmployee(['leave_entitlements'], $empNumber);
    }

    /**
     * @param Request $request
     * @param Component $component
     */
    protected function addLeaveTypeProp(Request $request, Component $component): void
    {
        $leaveTypeId = $request->query->get('leaveTypeId');
        if (!is_null($leaveTypeId)) {
            $leaveType = $this->getLeaveTypeService()->getLeaveTypeAsArray($leaveTypeId);
            $component->addProp(new Prop('leave-type', Prop::TYPE_OBJECT, $leaveType));
        }
    }

    /**
     * @param Request $request
     * @param Component $component
     */
    protected function addLeavePeriodProp(Request $request, Component $component): void
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        if ($startDate && $endDate) {
            $leavePeriod = [
                'id' => "{$startDate}_$endDate",
                'label' => "$startDate - $endDate",
                'startDate' => "$startDate",
                'endDate' => "$endDate"
            ];
        } else {
            $leavePeriod = $this->getLeavePeriodService()->getNormalizedCurrentLeavePeriod();
            $leavePeriod = [
                'id' => $leavePeriod['startDate'] . '_' . $leavePeriod['endDate'],
                'label' => $leavePeriod['startDate'] . ' - ' . $leavePeriod['endDate'],
                'startDate' => $leavePeriod['startDate'],
                'endDate' => $leavePeriod['endDate'],
            ];
        }
        $component->addProp(new Prop('leave-period', Prop::TYPE_OBJECT, $leavePeriod));
    }
}
