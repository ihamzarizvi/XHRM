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

namespace XHRM\Entity\Decorator;

use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\Project;
use XHRM\Entity\ProjectActivity;
use XHRM\Entity\Timesheet;
use XHRM\Entity\TimesheetItem;

class TimesheetItemDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;

    private TimesheetItem $timesheetItem;

    /**
     * @param TimesheetItem $timesheetItem
     */
    public function __construct(TimesheetItem $timesheetItem)
    {
        $this->timesheetItem = $timesheetItem;
    }

    /**
     * @return TimesheetItem
     */
    protected function getTimesheetItem(): TimesheetItem
    {
        return $this->timesheetItem;
    }

    /**
     * @param int $id
     */
    public function setProjectById(int $id): void
    {
        $project = $this->getReference(Project::class, $id);
        $this->getTimesheetItem()->setProject($project);
    }

    /**
     * @param int $id
     */
    public function setProjectActivityById(int $id): void
    {
        $projectActivity = $this->getReference(ProjectActivity::class, $id);
        $this->getTimesheetItem()->setProjectActivity($projectActivity);
    }

    /**
     * @param int $timesheetId
     */
    public function setTimesheetById(int $timesheetId): void
    {
        $timesheet = $this->getReference(Timesheet::class, $timesheetId);
        $this->getTimesheetItem()->setTimesheet($timesheet);
    }

    /**
     * @param int $employeeNumber
     */
    public function setEmployeeByEmployeeNumber(int $employeeNumber): void
    {
        $employee = $this->getReference(Employee::class, $employeeNumber);
        $this->getTimesheetItem()->setEmployee($employee);
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->getDateTimeHelper()->formatDate($this->getTimesheetItem()->getDate());
    }
}
