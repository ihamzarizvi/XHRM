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

namespace XHRM\Pim\Event;

use DateTime;
use XHRM\Entity\Employee;
use XHRM\Framework\Event\Event;

/**
 * @Event("XHRM\Pim\Event\EmployeeJoinedDateChangedEvent")
 */
class EmployeeJoinedDateChangedEvent extends Event
{
    /**
     * @var Employee
     */
    private Employee $employee;

    /**
     * @var DateTime|null
     */
    private ?DateTime $previousJoinedDate = null;

    /**
     * @param Employee $employee
     * @param DateTime|null $previousJoinedDate
     */
    public function __construct(Employee $employee, ?DateTime $previousJoinedDate)
    {
        $this->employee = $employee;
        $this->previousJoinedDate = $previousJoinedDate;
    }

    /**
     * @return Employee
     */
    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    /**
     * @return DateTime|null
     */
    public function getPreviousJoinedDate(): ?DateTime
    {
        return $this->previousJoinedDate;
    }
}

