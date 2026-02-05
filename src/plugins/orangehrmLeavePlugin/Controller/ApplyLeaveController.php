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
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;
use XHRM\Leave\Traits\Service\WorkScheduleServiceTrait;

class ApplyLeaveController extends AbstractVueController
{
    use WorkScheduleServiceTrait;
    use DateTimeHelperTrait;
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('leave-apply');

        $workShiftStartEndTime = $this->getWorkScheduleService()
            ->getWorkSchedule($this->getAuthUser()->getEmpNumber())
            ->getWorkShiftStartEndTime();
        $workShift = [
            'startTime' => $this->getDateTimeHelper()
                ->formatDateTimeToTimeString($workShiftStartEndTime->getStartTime()),
            'endTime' => $this->getDateTimeHelper()
                ->formatDateTimeToTimeString($workShiftStartEndTime->getEndTime()),
        ];
        $component->addProp(new Prop('work-shift', Prop::TYPE_OBJECT, $workShift));
        $this->setComponent($component);
    }
}
