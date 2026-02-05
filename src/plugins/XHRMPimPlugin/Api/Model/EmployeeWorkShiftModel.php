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

namespace XHRM\Pim\Api\Model;

use XHRM\Admin\Dto\WorkShiftStartAndEndTime;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;

/**
 * @OA\Schema(
 *     schema="Pim-EmployeeWorkShiftModel",
 *     type="object",
 *     @OA\Property(property="startTime", type="string", format="time"),
 *     @OA\Property(property="endTime", type="string", format="time"),
 * )
 */
class EmployeeWorkShiftModel implements Normalizable
{
    use DateTimeHelperTrait;

    /**
     * @var WorkShiftStartAndEndTime
     */
    private WorkShiftStartAndEndTime $workShiftStartAndEndTime;

    public function __construct(WorkShiftStartAndEndTime $workShiftStartAndEndTime)
    {
        $this->workShiftStartAndEndTime = $workShiftStartAndEndTime;
    }

    public function toArray(): array
    {
        return [
            "startTime" => $this->getDateTimeHelper()->formatDateTimeToTimeString(
                $this->workShiftStartAndEndTime->getStartTime()
            ),
            "endTime" => $this->getDateTimeHelper()->formatDateTimeToTimeString(
                $this->workShiftStartAndEndTime->getEndTime()
            )
        ];
    }
}

