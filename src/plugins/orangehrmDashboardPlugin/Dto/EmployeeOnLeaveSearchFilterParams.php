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

namespace XHRM\Dashboard\Dto;

use DateTime;
use XHRM\Core\Dto\FilterParams;

class EmployeeOnLeaveSearchFilterParams extends FilterParams
{
    public const ALLOWED_SORT_FIELDS = ['leaveList.date', 'employee.firstName'];

    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * @var array|null
     */
    protected ?array $accessibleEmpNumber = null;

    public function __construct()
    {
        $this->setSortField('employee.firstName');
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return void
     */
    public function setDate(DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return array|null
     */
    public function getAccessibleEmpNumber(): ?array
    {
        return $this->accessibleEmpNumber;
    }

    /**
     * @param array|null $accessibleEmpNumber
     */
    public function setAccessibleEmpNumber(?array $accessibleEmpNumber): void
    {
        $this->accessibleEmpNumber = $accessibleEmpNumber;
    }
}
