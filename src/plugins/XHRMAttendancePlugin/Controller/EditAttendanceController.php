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

namespace XHRM\Attendance\Controller;

use XHRM\Attendance\Traits\Service\AttendanceServiceTrait;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\Common\DisabledModuleController;
use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\AttendanceRecord;
use XHRM\Framework\Http\Request;

class EditAttendanceController extends AbstractVueController
{
    use AttendanceServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')) {
            $attendanceRecordId = $request->attributes->getInt('id');
            $attendanceRecord = $this->getAttendanceService()
                ->getAttendanceDao()
                ->getAttendanceRecordById($attendanceRecordId);
            //no attendance record for the given id
            if (!$attendanceRecord instanceof AttendanceRecord) {
                throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
            }
            //check auth user's permission to update attendance record
            if (!$this->getAttendanceService()->isAuthUserAllowedToPerformTheEditActions($attendanceRecord)) {
                throw new RequestForwardableException(DisabledModuleController::class . '::handle');
            }
            $component = new Component('edit-attendance');
            $component->addProp(new Prop('attendance-id', Prop::TYPE_NUMBER, $attendanceRecordId));
        } else {
            throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
        }
        $this->setComponent($component);
    }
}

