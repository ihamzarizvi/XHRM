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

namespace XHRM\Time\Api\ValidationRules;

use DateTime;
use XHRM\Core\Api\V2\Validator\Rules\AbstractRule;
use XHRM\Entity\Timesheet;
use XHRM\Time\Traits\Service\TimesheetServiceTrait;

class TimesheetCommentDateParamRule extends AbstractRule
{
    use TimesheetServiceTrait;

    private $timesheetId;

    /**
     * @var Timesheet|null
     */
    private ?Timesheet $timesheet = null;

    /**
     * @param int $timesheetId
     */
    public function __construct($timesheetId)
    {
        $this->timesheetId = $timesheetId;
    }

    /**
     * @inheritDoc
     */
    public function validate($input): bool
    {
        $timesheet = $this->getTimesheetService()
            ->getTimesheetDao()
            ->getTimesheetById($this->timesheetId);

        if (is_numeric($this->timesheetId) && $this->timesheetId > 0) {
            if (!$timesheet instanceof Timesheet) {
                return false;
            }
        }

        return $timesheet->getStartDate() <= new DateTime($input) && new DateTime($input) <= $timesheet->getEndDate();
    }
}

