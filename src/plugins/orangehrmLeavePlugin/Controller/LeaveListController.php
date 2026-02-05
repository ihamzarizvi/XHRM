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

use XHRM\Admin\Service\CompanyStructureService;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\Leave;
use XHRM\Framework\Http\Request;
use XHRM\I18N\Traits\Service\I18NHelperTrait;
use XHRM\Leave\Traits\Service\LeaveRequestServiceTrait;
use XHRM\Leave\Traits\Service\LeaveTypeServiceTrait;
use XHRM\Pim\Traits\Service\EmployeeServiceTrait;

class LeaveListController extends AbstractVueController
{
    use LeaveRequestServiceTrait;
    use UserRoleManagerTrait;
    use EmployeeServiceTrait;
    use LeaveTypeServiceTrait;
    use I18NHelperTrait;

    public const LEAVE_STATUSES = [
        ['id' => Leave::LEAVE_STATUS_LEAVE_REJECTED, 'label' => 'Rejected'],
        ['id' => Leave::LEAVE_STATUS_LEAVE_CANCELLED, 'label' => 'Cancelled'],
        ['id' => Leave::LEAVE_STATUS_LEAVE_PENDING_APPROVAL, 'label' => 'Pending Approval'],
        ['id' => Leave::LEAVE_STATUS_LEAVE_APPROVED, 'label' => 'Scheduled'],
        ['id' => Leave::LEAVE_STATUS_LEAVE_TAKEN, 'label' => 'Taken'],
    ];

    protected ?CompanyStructureService $companyStructureService = null;

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
        $component = new Component('leave-list');
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
        $this->addFromToDateProps($request, $component);
        $this->addLeaveStatusProp($request, $component);

        $subunits = $this->getCompanyStructureService()->getSubunitArray();
        $component->addProp(new Prop('subunits', Prop::TYPE_ARRAY, $subunits));
        $this->addLeaveStatusesProp($component);

        $this->setComponent($component);
    }

    /**
     * @param Component $component
     */
    protected function addLeaveStatusesProp(Component $component): void
    {
        $component->addProp(
            new Prop(
                'leave-statuses',
                Prop::TYPE_ARRAY,
                array_map(
                    fn(array $leaveStatus) => [
                        'id' => $leaveStatus['id'],
                        'label' => $this->getI18NHelper()->transBySource($leaveStatus['label'])
                    ],
                    self::LEAVE_STATUSES
                )
            )
        );
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
    protected function addLeaveStatusProp(Request $request, Component $component): void
    {
        $leaveStatus = $request->query->get('status');
        if (!is_null($leaveStatus)) {
            $key = array_search($leaveStatus, array_column(self::LEAVE_STATUSES, 'id'));
            if ($key) {
                $component->addProp(new Prop('leave-status', Prop::TYPE_OBJECT, self::LEAVE_STATUSES[$key]));
            }
        }
    }

    /**
     * @param Request $request
     * @param Component $component
     */
    protected function addFromToDateProps(Request $request, Component $component): void
    {
        $fromDate = $request->query->get('fromDate');
        $toDate = $request->query->get('toDate');
        if ($fromDate && $toDate) {
            $component->addProp(new Prop('from-date', Prop::TYPE_STRING, $fromDate));
            $component->addProp(new Prop('to-date', Prop::TYPE_STRING, $toDate));
        }
    }
}
