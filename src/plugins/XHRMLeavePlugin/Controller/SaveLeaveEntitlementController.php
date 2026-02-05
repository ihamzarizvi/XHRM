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

namespace XHRM\Leave\Controller;

use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Vue\Prop;
use XHRM\Core\Vue\Component;
use XHRM\Entity\LeaveEntitlement;
use XHRM\Framework\Http\Request;
use XHRM\Admin\Service\LocationService;
use XHRM\Admin\Service\CompanyStructureService;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Leave\Traits\Service\LeaveEntitlementServiceTrait;
use XHRM\Leave\Traits\Service\LeavePeriodServiceTrait;

class SaveLeaveEntitlementController extends AbstractVueController
{
    use LeavePeriodServiceTrait;
    use LeaveEntitlementServiceTrait;
    use UserRoleManagerTrait;

    protected ?CompanyStructureService $companyStructureService = null;
    protected ?LocationService $locationService = null;

    /**
     * @return LocationService
     */
    protected function getLocationService(): LocationService
    {
        if (!$this->locationService instanceof LocationService) {
            $this->locationService = new LocationService();
        }
        return $this->locationService;
    }

    /**
     * @return CompanyStructureService
     */
    protected function getCompanyStructureService(): CompanyStructureService
    {
        if (!$this->companyStructureService instanceof CompanyStructureService) {
            $this->companyStructureService = new CompanyStructureService();
        }
        return $this->companyStructureService;
    }

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')) {
            $id = $request->attributes->getInt('id');
            $component = new Component('leave-edit-entitlement');
            $component->addProp(new Prop('entitlement-id', Prop::TYPE_NUMBER, $id));
            $leaveEntitlementRecord = $this->getLeaveEntitlementService()
                ->getLeaveEntitlementDao()
                ->getLeaveEntitlement($id);
            if (
                !$leaveEntitlementRecord instanceof LeaveEntitlement ||
                !$this->getUserRoleManagerHelper()->isEmployeeAccessible(
                    $leaveEntitlementRecord->getEmployee()->getEmpNumber()
                )
            ) {
                throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
            }
        } else {
            $component = new Component('leave-add-entitlement');
        }

        $subunits = $this->getCompanyStructureService()->getSubunitArray();
        $component->addProp(new Prop('subunits', Prop::TYPE_ARRAY, $subunits));

        $locations = $this->getLocationService()->getAccessibleLocationsArray();
        $component->addProp(new Prop('locations', Prop::TYPE_ARRAY, $locations));

        $leavePeriod = $this->getLeavePeriodService()->getNormalizedCurrentLeavePeriod();
        $leavePeriod = [
            "id" => $leavePeriod['startDate'] . "_" . $leavePeriod['endDate'],
            "label" => $leavePeriod['startDate'] . " - " . $leavePeriod['endDate'],
            "startDate" => $leavePeriod['startDate'],
            "endDate" => $leavePeriod['endDate'],
        ];

        $component->addProp(new Prop('leave-period', Prop::TYPE_OBJECT, $leavePeriod));

        $this->setComponent($component);
    }
}

