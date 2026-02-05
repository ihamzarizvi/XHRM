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

namespace XHRM\Entity\Decorator;

use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\Timesheet;

class TimesheetDecorator
{
    use DateTimeHelperTrait;
    use EntityManagerHelperTrait;

    private Timesheet $timesheet;

    /**
     * @param Timesheet $timesheet
     */
    public function __construct(Timesheet $timesheet)
    {
        $this->timesheet = $timesheet;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->getDateTimeHelper()->formatDate($this->getTimesheet()->getStartDate());
    }

    /**
     * @return Timesheet
     */
    protected function getTimesheet(): Timesheet
    {
        return $this->timesheet;
    }

    /**
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->getDateTimeHelper()->formatDate($this->getTimesheet()->getEndDate());
    }

    /**
     * @param int $empNumber
     * @return void
     */
    public function setEmployeeByEmployeeNumber(int $empNumber): void
    {
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getTimesheet()->setEmployee($employee);
    }

    /**
     * @return string
     */
    public function getTimesheetState(): string
    {
        return ucwords(strtolower($this->getTimesheet()->getState()));
    }
}

