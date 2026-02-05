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

namespace XHRM\Time\Controller;

use XHRM\Core\Authorization\Controller\CapableViewController;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\Timesheet;
use XHRM\Framework\Http\Request;
use XHRM\Time\Traits\Service\TimesheetServiceTrait;

class EditTimesheetController extends AbstractVueController implements CapableViewController
{
    use AuthUserTrait;
    use TimesheetServiceTrait;
    use UserRoleManagerTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        // TODO: show 404 if no id
        if ($request->attributes->has('id')) {
            $timesheetId = $request->attributes->getInt('id');
            $component = new Component('edit-timesheet');
            $component->addProp(new Prop('timesheet-id', Prop::TYPE_NUMBER, $timesheetId));

            $timesheet = $this->getTimesheetService()->getTimesheetDao()->getTimesheetById($timesheetId);
            $timesheetOwnerEmpNumber = $timesheet->getEmployee()->getEmpNumber();
            $currentUserEmpNumber = $this->getAuthUser()->getEmpNumber();
            if ($timesheetOwnerEmpNumber === $currentUserEmpNumber) {
                $component->addProp(new Prop('my-timesheet', Prop::TYPE_BOOLEAN, true));
            }
        }

        $this->setComponent($component);
    }

    /**
     * @inheritDoc
     */
    public function isCapable(Request $request): bool
    {
        if ($request->attributes->has('id')) {
            $timesheet = $this->getTimesheetService()
                ->getTimesheetDao()
                ->getTimesheetById($request->attributes->getInt('id'));
            if ($timesheet instanceof Timesheet) {
                if (
                    $this->getUserRoleManagerHelper()->isSelfByEmpNumber($timesheet->getEmployee()->getEmpNumber())
                    && $timesheet->getState() === 'APPROVED'
                ) {
                    return false;
                }
                return $this->getUserRoleManagerHelper()
                    ->isEmployeeAccessible($timesheet->getEmployee()->getEmpNumber());
            }
            throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
        }
        return true;
    }
}

